<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Po\Models\Po;
use Modules\Po\Models\PoDetail;
use Modules\Po\Models\Supplier;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

function makePoWithSoCoverage(): array
{
    $supplier = Supplier::create(['supplier_nama' => 'Sup Hapus '.uniqid()]);
    $product = Product::create([
        'product_nama' => 'Hapus Test '.uniqid(),
        'product_harga' => 10000,
    ]);
    $so = So::create([
        'so_tanggal' => now()->toDateString(),
        'so_id_reseller' => User::factory()->create()->id,
        'so_shipping_method' => 'pickup',
    ]);
    $soDetail = SoDetail::create([
        'so_detail_code' => 'DT-'.strtoupper(Str::random(6)),
        'so_detail_id_so' => $so->id,
        'so_detail_id_product' => $product->id,
        'so_detail_qty' => 2,
        'so_detail_harga' => 10000,
        'po_generated_at' => now(),
    ]);
    $so->update(['so_po_generated_at' => now()]);

    $po = Po::create([
        'po_tanggal' => now()->toDateString(),
        'po_id_supplier' => $supplier->id,
    ]);
    $poDetail = PoDetail::create([
        'po_detail_id_po' => $po->id,
        'po_detail_id_product' => $product->id,
        'po_detail_qty' => 2,
        'po_detail_harga' => 10000,
    ]);
    $poDetail->has_so_details()->attach($soDetail->id, ['qty' => 2]);

    return [$po, $poDetail, $so, $soDetail];
}

it('melepas mapping SO saat PO dihapus', function () {
    [$po, $poDetail, $so, $soDetail] = makePoWithSoCoverage();

    $po->delete();

    // pivot + detail milik PO hilang
    expect(DB::table('po_detail_so_details')->where('po_detail_id', $poDetail->id)->exists())->toBeFalse();
    expect(PoDetail::find($poDetail->id))->toBeNull();
    // PO soft-delete
    expect($po->fresh()->trashed())->toBeTrue();
    // flag SO bersih → bisa digenerate ulang
    expect($soDetail->fresh()->po_generated_at)->toBeNull();
    expect($so->fresh()->so_po_generated_at)->toBeNull();
});

it('tidak melepas flag SO yang masih dicover PO aktif lain', function () {
    [$po, $poDetail, $so, $soDetail] = makePoWithSoCoverage();

    $po2 = Po::create([
        'po_tanggal' => now()->toDateString(),
        'po_id_supplier' => $po->po_id_supplier,
    ]);
    $poDetail2 = PoDetail::create([
        'po_detail_id_po' => $po2->id,
        'po_detail_id_product' => $poDetail->po_detail_id_product,
        'po_detail_qty' => 1,
        'po_detail_harga' => 10000,
    ]);
    $poDetail2->has_so_details()->attach($soDetail->id, ['qty' => 1]);

    $po->delete();

    // masih dicover po2 → flag bertahan
    expect($soDetail->fresh()->po_generated_at)->not->toBeNull();
    expect($so->fresh()->so_po_generated_at)->not->toBeNull();
    // pivot po pertama hilang, kedua utuh
    expect(DB::table('po_detail_so_details')->where('po_detail_id', $poDetail->id)->exists())->toBeFalse();
    expect(DB::table('po_detail_so_details')->where('po_detail_id', $poDetail2->id)->exists())->toBeTrue();
});
