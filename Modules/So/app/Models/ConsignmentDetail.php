<?php

namespace Modules\So\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['consignment_id', 'product_id', 'qty', 'qty_sold', 'qty_returned', 'price'])]
class ConsignmentDetail extends BaseModel
{
    protected $table = 'consignment_details';

    public static $sortColumns = ['id'];

    public static function field_name(): string
    {
        return 'id';
    }

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'qty_sold' => 'decimal:2',
            'qty_returned' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function has_consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class, 'consignment_id');
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getSubtotalAttribute(): float
    {
        return (int) ($this->qty_sold ?? 0) * (float) $this->price;
    }
}
