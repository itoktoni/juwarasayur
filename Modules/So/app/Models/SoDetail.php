<?php

namespace Modules\So\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Catalog\Models\Product;
use Modules\Po\Models\PoDetail;

#[Fillable(['so_detail_code', 'so_detail_id_so', 'so_detail_id_product', 'so_detail_qty', 'so_detail_harga', 'fee_percent', 'fee_amount', 'fee_source', 'applied_role', 'so_detail_keterangan', 'po_generated_at'])]
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
            'fee_percent' => 'decimal:2',
            'fee_amount' => 'decimal:2',
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

    /**
     * PO detail yang menutupi SO detail ini (relasi many-to-many via pivot).
     * Berguna untuk tracking dari sisi SO (mis. "SO ini sudah di-cover PO apa saja").
     */
    public function has_po_details(): BelongsToMany
    {
        return $this->belongsToMany(
            PoDetail::class,
            'po_detail_so_details',
            'so_detail_id',
            'po_detail_id'
        )->withPivot('qty')->withTimestamps();
    }
}
