<?php

namespace Modules\Inventory\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['stock_code', 'stock_id_product', 'stock_id_lokasi', 'stock_qty', 'stock_expired_date', 'stock_batch'])]
class Stock extends BaseModel
{
    protected $table = 'inv_stocks';

    public static $sortColumns = ['stock_code', 'stock_qty', 'stock_expired_date'];

    public static $filterColumns = ['stock_code', 'stock_id_product', 'stock_id_lokasi', 'stock_expired_date'];

    public static function field_name(): string
    {
        return 'stock_code';
    }

    protected function casts(): array
    {
        return [
            'stock_qty' => 'integer',
            'stock_expired_date' => 'date',
        ];
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'stock_id_product');
    }

    public function has_lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'stock_id_lokasi');
    }

    public function rules(): array
    {
        return [
            'stock_code' => ['required', 'string', 'max:50'],
            'stock_id_product' => ['required', 'exists:catalog_products,id'],
            'stock_id_lokasi' => ['required', 'exists:inv_lokasis,id'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'stock_expired_date' => ['nullable', 'date'],
            'stock_batch' => ['nullable', 'string', 'max:100'],
        ];
    }
}
