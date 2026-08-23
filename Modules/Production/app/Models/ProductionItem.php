<?php

namespace Modules\Production\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['production_item_id_production', 'production_item_id_product', 'production_item_qty'])]
class ProductionItem extends BaseModel
{
    protected $table = 'production_items';

    public static function field_name(): string
    {
        return 'id';
    }

    public function has_production(): BelongsTo
    {
        return $this->belongsTo(Production::class, 'production_item_id_production');
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'production_item_id_product');
    }

    public function rules(): array
    {
        return [
            'production_item_id_production' => ['required', 'exists:productions,id'],
            'production_item_id_product' => ['required', 'exists:catalog_products,id'],
            'production_item_qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
