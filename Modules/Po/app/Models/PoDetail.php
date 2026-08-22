<?php

namespace Modules\Po\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['po_detail_id_po', 'po_detail_id_product', 'po_detail_qty', 'po_detail_prepared', 'po_detail_harga', 'po_detail_subtotal', 'po_detail_keterangan', 'po_detail_code'])]
class PoDetail extends BaseModel
{
    protected $table = 'po_details';

    public static $sortColumns = ['po_detail_code', 'po_detail_qty', 'po_detail_harga'];

    public static $filterColumns = ['po_detail_code', 'po_detail_id_product', 'po_detail_id_po'];

    public static function field_name(): string
    {
        return 'po_detail_code';
    }

    protected function casts(): array
    {
        return [
            'po_detail_qty' => 'integer',
            'po_detail_prepared' => 'integer',
            'po_detail_harga' => 'decimal:2',
            'po_detail_subtotal' => 'decimal:2',
        ];
    }

    public function getPoDetailSisaAttribute(): int
    {
        return max(0, (int) $this->po_detail_qty - (int) $this->po_detail_prepared);
    }

    public function has_po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'po_detail_id_po');
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'po_detail_id_product');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $qty = (int) ($model->po_detail_qty ?? 0);
            $harga = (float) ($model->po_detail_harga ?? 0);
            $model->po_detail_subtotal = $qty * $harga;
        });
    }

    public function rules(): array
    {
        return [
            'po_detail_id_po' => ['required', 'exists:po_pos,id'],
            'po_detail_id_product' => ['required', 'exists:catalog_products,id'],
            'po_detail_qty' => ['required', 'integer', 'min:1'],
            'po_detail_prepared' => ['nullable', 'integer', 'min:0'],
            'po_detail_harga' => ['nullable', 'numeric', 'min:0'],
            'po_detail_subtotal' => ['nullable', 'numeric', 'min:0'],
            'po_detail_keterangan' => ['nullable', 'string', 'max:500'],
            'po_detail_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
