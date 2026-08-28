<?php

namespace App\Actions;

use App\Models\PrepareAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\Lokasi;
use Modules\Inventory\Models\Stock;
use Modules\Inventory\Models\StockMovement;
use Modules\So\Models\SoDetail;

/**
 * Siapkan barang keluar gudang untuk memenuhi SO.
 *
 * Input: product_id, lokasi_id, qty_total, daftar so_detail_id (sorted tertua).
 * Algoritma: FIFO — distribusi qty ke tiap SO detail sesuai urutan (created_at asc),
 * kurangi sisa per-SO sampai qty_total habis. Contoh: SO A kentang 10, SO B kentang 2,
 * qty disiapkan 8 → SO A dapat 8 (sisa 0), SO B dapat 0 (masih kurang 2, sisanya 4).
 *
 * Setiap alokasi menghasilkan:
 * - Decrement stok di lokasi
 * - StockMovement OUT (ref SoDetail, polymorphic)
 * - Baris prepare_allocations (siap untuk siapa)
 */
class PrepareSoProductAction
{
    use AsAction;

    /**
     * @param  array<int>  $soDetailIds  SO detail yang meminta produk ini
     * @return array<int, array{so_detail_id:int, qty:int, so_code:string, customer:string}> Distribusi per-SO
     */
    public function handle(Product $product, Lokasi $lokasi, int $qtyTotal, array $soDetailIds, ?User $user = null, ?string $expiredDate = null): array
    {
        return DB::transaction(function () use ($product, $lokasi, $qtyTotal, $soDetailIds, $user, $expiredDate) {
            $remaining = $qtyTotal;
            $distributions = [];

            // 1. Ambil SO detail urut tertua, lockForUpdate agar aman dari race
            $soDetails = SoDetail::with('has_so')
                ->whereIn('id', $soDetailIds)
                ->where('so_detail_id_product', $product->id)
                ->orderBy('id', 'asc') // urutan create = id asc
                ->lockForUpdate()
                ->get();

            foreach ($soDetails as $sd) {
                if ($remaining <= 0) {
                    break;
                }

                // Total qty diminta oleh SO ini
                $diminta = (int) $sd->so_detail_qty;
                // Sudah disiapkan berapa
                $sudahDisiapkan = (int) $sd->has_prepare_allocations()->sum('qty');
                $sisaDiminta = max(0, $diminta - $sudahDisiapkan);

                if ($sisaDiminta <= 0) {
                    continue; // SO ini sudah terpenuhi
                }

                $qty = min($remaining, $sisaDiminta);
                $remaining -= $qty;
                $distributions[] = [
                    'so_detail_id' => (int) $sd->id,
                    'qty' => $qty,
                    'so_code' => $sd->has_so?->so_code ?? '-',
                    'customer' => $sd->has_so?->so_customer_name ?? '-',
                ];
            }

            if (empty($distributions)) {
                throw new \RuntimeException('Tidak ada SO yang perlu disiapkan untuk produk ini (semua sudah terpenuhi).');
            }

            $totalDistribusi = array_sum(array_column($distributions, 'qty'));

            // 2. Validasi stok cukup
            $stokTersedia = (int) Stock::where('stock_id_product', $product->id)
                ->where('stock_id_lokasi', $lokasi->id)
                ->where('stock_expired_date', $expiredDate ?: null)
                ->sum('stock_qty');

            if ($stokTersedia < $totalDistribusi) {
                throw new \RuntimeException("Stok tidak cukup. Tersedia: {$stokTersedia}, diminta: {$totalDistribusi}");
            }

            // 3. Decrement stok di lokasi
            $stock = Stock::where('stock_id_product', $product->id)
                ->where('stock_id_lokasi', $lokasi->id)
                ->where('stock_expired_date', $expiredDate ?: null)
                ->first();

            if ($stock) {
                $stock->decrement('stock_qty', $totalDistribusi);
            }

            // 4. Insert StockMovement OUT + prepare_allocations per distribusi
            foreach ($distributions as $dist) {
                // StockMovement — audit trail (polymorphic ref ke SoDetail)
                StockMovement::create([
                    'movement_code' => $this->generateMovementCode(),
                    'movement_type' => 'OUT',
                    'movement_id_product' => $product->id,
                    'movement_id_lokasi' => $lokasi->id,
                    'movement_qty' => $dist['qty'],
                    'movement_expired_date' => $expiredDate,
                    'movement_ref_type' => SoDetail::class,
                    'movement_ref_id' => $dist['so_detail_id'],
                    'movement_note' => 'Prepare untuk '.$dist['so_code'],
                ]);

                // prepare_allocations — sumber kebenaran "siap untuk siapa"
                PrepareAllocation::create([
                    'so_detail_id' => $dist['so_detail_id'],
                    'product_id' => $product->id,
                    'lokasi_id' => $lokasi->id,
                    'qty' => $dist['qty'],
                    'expired_date' => $expiredDate,
                    'prepared_at' => now(),
                    'prepared_by' => $user?->id,
                ]);
            }

            return $distributions;
        });
    }

    protected function generateMovementCode(): string
    {
        do {
            $code = 'MVT-OUT-'.strtoupper(Str::random(10));
        } while (StockMovement::where('movement_code', $code)->exists());

        return $code;
    }
}
