<?php

namespace Modules\Po\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Catalog\Models\Product;
use Modules\Po\Models\Po;
use Modules\Po\Models\PoDetail;
use Modules\Po\Models\Supplier;

class PoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = $this->seedSuppliers();
        $this->seedPos($suppliers);
    }

    private function seedSuppliers(): Collection
    {
        $data = [
            ['supplier_nama' => 'PT Sayur Segar Lembang', 'supplier_kode' => 'SUP-LBG-01', 'supplier_telepon' => '022-8881001', 'supplier_email' => 'order@sayurlembang.id', 'supplier_alamat' => 'Jl. Raya Lembang No. 88, Bandung Barat', 'supplier_kontak_person' => 'Pak Asep'],
            ['supplier_nama' => 'CV Tani Berkah Dieng', 'supplier_kode' => 'SUP-DIG-01', 'supplier_telepon' => '0286-7772002', 'supplier_email' => 'info@taniberkah.id', 'supplier_alamat' => 'Desa Dieng Kulon, Wonosobo', 'supplier_kontak_person' => 'Bu Siti'],
            ['supplier_nama' => 'UD Hidroponik Batu', 'supplier_kode' => 'SUP-BTU-01', 'supplier_telepon' => '0341-5553003', 'supplier_email' => 'halo@hidrobatumalang.id', 'supplier_alamat' => 'Jl. Brantas No. 12, Batu, Malang', 'supplier_kontak_person' => 'Mas Riko'],
            ['supplier_nama' => 'Koperasi Petani Berastagi', 'supplier_kode' => 'SUP-BRT-01', 'supplier_telepon' => '0628-9004004', 'supplier_email' => 'koperasi@berastagi.id', 'supplier_alamat' => 'Jl. Gundaling No. 5, Berastagi, Karo', 'supplier_kontak_person' => 'Pak Ginting'],
            ['supplier_nama' => 'PT Bumbu Nusantara', 'supplier_kode' => 'SUP-BMB-01', 'supplier_telepon' => '021-9995005', 'supplier_email' => 'sales@bumbunusantara.co.id', 'supplier_alamat' => 'Pasar Induk Kramat Jati, Jakarta Timur', 'supplier_kontak_person' => 'Ibu Dewi'],
        ];

        foreach ($data as $i => $row) {
            Supplier::updateOrCreate(
                ['supplier_kode' => $row['supplier_kode']],
                array_merge($row, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return Supplier::whereIn('supplier_kode', collect($data)->pluck('supplier_kode'))->get()->keyBy('supplier_kode');
    }

    private function seedPos(Collection $suppliers): void
    {
        $products = Product::where('is_active', true)->orderBy('id')->get();
        if ($products->isEmpty()) {
            return;
        }

        $poData = [
            ['supplier' => 'SUP-LBG-01', 'status' => 'pending', 'days_ago' => 2, 'discount' => 0, 'discount_type' => 'nominal', 'ppn_rate' => 11, 'pph_rate' => 0],
            ['supplier' => 'SUP-DIG-01', 'status' => 'ordered', 'days_ago' => 5, 'discount' => 10, 'discount_type' => 'percent', 'ppn_rate' => 11, 'pph_rate' => 2],
            ['supplier' => 'SUP-BTU-01', 'status' => 'partial', 'days_ago' => 7, 'discount' => 50000, 'discount_type' => 'nominal', 'ppn_rate' => 11, 'pph_rate' => 2],
            ['supplier' => 'SUP-BRT-01', 'status' => 'closed', 'days_ago' => 10, 'discount' => 0, 'discount_type' => 'nominal', 'ppn_rate' => 0, 'pph_rate' => 0],
        ];

        foreach ($poData as $idx => $row) {
            $supplier = $suppliers[$row['supplier']] ?? $suppliers->first();
            if (! $supplier) {
                continue;
            }

            $take = $products->shuffle()->take(rand(2, 4));

            $po = Po::updateOrCreate(
                ['po_code' => sprintf('PO-SEED-%02d', $idx + 1)],
                [
                    'po_tanggal' => now()->subDays($row['days_ago'])->toDateString(),
                    'po_id_supplier' => $supplier->id,
                    'po_status' => $row['status'],
                    'po_keterangan' => 'Seed PO sayur #'.($idx + 1).' — '.$supplier->supplier_nama.' (disc '.($row['discount_type'] === 'percent' ? $row['discount'].'%' : 'Rp '.number_format($row['discount'], 0, ',', '.')).', PPN '.$row['ppn_rate'].'% PPH '.$row['pph_rate'].'%)',
                    'po_discount' => $row['discount'],
                    'po_discount_type' => $row['discount_type'],
                    'po_ppn_rate' => $row['ppn_rate'],
                    'po_pph_rate' => $row['pph_rate'],
                ]
            );

            $po->has_details()->delete();

            foreach ($take as $seq => $product) {
                $qty = rand(5, 30);
                $harga = (float) ($product->product_harga_modal ?? $product->product_harga ?? 10000);
                $subtotal = $qty * $harga;

                PoDetail::create([
                    'po_detail_id_po' => $po->id,
                    'po_detail_id_product' => $product->id,
                    'po_detail_code' => sprintf('%s-%03d', $po->po_code, $seq + 1),
                    'po_detail_qty' => $qty,
                    'po_detail_harga' => $harga,
                    'po_detail_subtotal' => $subtotal,
                    'po_detail_keterangan' => $product->product_nama,
                ]);
            }

            $po->refresh();
            $po->recalculateTotals();
        }
    }
}
