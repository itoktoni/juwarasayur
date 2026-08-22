<?php

namespace Modules\Inventory\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['movement_code', 'movement_type', 'movement_id_product', 'movement_id_lokasi', 'movement_qty', 'movement_expired_date', 'movement_ref_type', 'movement_ref_id', 'movement_note'])]
class StockMovement extends BaseModel
{
    protected $table = 'inv_stock_movements';

    public static $sortColumns = ['movement_code', 'movement_type', 'movement_qty', 'movement_expired_date'];

    public static $filterColumns = ['movement_code', 'movement_type', 'movement_id_product', 'movement_id_lokasi'];

    public static function field_name(): string
    {
        return 'movement_code';
    }

    protected function casts(): array
    {
        return [
            'movement_qty' => 'integer',
            'movement_expired_date' => 'date',
        ];
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'movement_id_product');
    }

    public function has_lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'movement_id_lokasi');
    }
}
