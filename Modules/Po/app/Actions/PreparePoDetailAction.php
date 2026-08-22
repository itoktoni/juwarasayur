<?php

namespace Modules\Po\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\Stock;
use Modules\Inventory\Models\StockMovement;
use Modules\Po\Models\PoDetail;

class PreparePoDetailAction
{
    use AsAction;

    public function handle(PoDetail $detail, array $locations)
    {
        return DB::transaction(function () use ($detail, $locations) {
            $product = Product::findOrFail($detail->po_detail_id_product);
            $totalQty = 0;

            foreach ($locations as $row) {
                $qty = (int) $row['qty'];
                $expired = $row['expired_date'] ?? null;

                $stock = Stock::firstOrCreate(
                    [
                        'stock_id_product' => $product->id,
                        'stock_id_lokasi' => (int) $row['lokasi_id'],
                        'stock_expired_date' => $expired,
                        'stock_batch' => null,
                    ],
                    ['stock_code' => $this->generateStockCode(), 'stock_qty' => 0]
                );

                $stock->increment('stock_qty', $qty);

                StockMovement::create([
                    'movement_code' => $this->generateMovementCode(),
                    'movement_type' => 'IN',
                    'movement_id_product' => $product->id,
                    'movement_id_lokasi' => (int) $row['lokasi_id'],
                    'movement_qty' => $qty,
                    'movement_expired_date' => $expired,
                    'movement_ref_type' => PoDetail::class,
                    'movement_ref_id' => $detail->id,
                    'movement_note' => 'Prepare PO detail #'.$detail->id,
                ]);

                $totalQty += $qty;
            }

            $detail->increment('po_detail_prepared', $totalQty);
            $product->increment('product_stok', $totalQty);

            $this->refreshPoStatus($detail);

            return $detail->fresh();
        });
    }

    protected function refreshPoStatus(PoDetail $detail): void
    {
        $po = $detail->has_po()->first();
        if (! $po) {
            return;
        }

        $allPrepared = $po->has_details()->get()->every(
            fn ($d) => (int) $d->po_detail_prepared >= (int) $d->po_detail_qty
        );
        $anyPrepared = $po->has_details()->where('po_detail_prepared', '>', 0)->exists();

        $status = $allPrepared ? 'closed' : ($anyPrepared ? 'partial' : $po->po_status);
        $po->updateQuietly(['po_status' => $status]);
    }

    protected function generateStockCode(): string
    {
        do {
            $code = 'STK-'.strtoupper(Str::random(10));
        } while (Stock::where('stock_code', $code)->exists());

        return $code;
    }

    protected function generateMovementCode(): string
    {
        do {
            $code = 'MVT-IN-'.strtoupper(Str::random(10));
        } while (StockMovement::where('movement_code', $code)->exists());

        return $code;
    }
}
