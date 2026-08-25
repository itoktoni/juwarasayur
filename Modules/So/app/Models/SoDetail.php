<?php

namespace Modules\So\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

#[Fillable(['so_detail_code', 'so_detail_id_so', 'so_detail_id_product', 'so_detail_qty', 'so_detail_harga', 'so_detail_keterangan', 'po_generated_at'])]
class SoDetail extends BaseModel
{
    protected $table = 'so_order_details';

    public static $sortColumns = ['so_detail_code', 'so_detail_qty', 'so_detail_harga'];

    public static $filterColumns = ['so_detail_code'];

    public static function field_name(): string
    {
        return 'so_detail_code';
    }

    protected function casts(): array
    {
        return [
            'so_detail_harga' => 'decimal:2',
            'po_generated_at' => 'datetime',
        ];
    }

    public function has_so(): BelongsTo
    {
        return $this->belongsTo(So::class, 'so_detail_id_so');
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'so_detail_id_product');
    }
}
