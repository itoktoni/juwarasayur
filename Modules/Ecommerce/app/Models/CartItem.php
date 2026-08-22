<?php

namespace Modules\Ecommerce\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['user_id', 'product_id', 'qty'])]
class CartItem extends BaseModel
{
    protected $table = 'so_cart_items';

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function has_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
