<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\Lokasi;
use Modules\So\Models\SoDetail;

#[Fillable(['so_detail_id', 'product_id', 'lokasi_id', 'qty', 'expired_date', 'prepared_at', 'prepared_by'])]
class PrepareAllocation extends BaseModel
{
    protected $table = 'prepare_allocations';

    public static function field_name(): string
    {
        return 'id';
    }

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'expired_date' => 'date',
            'prepared_at' => 'datetime',
        ];
    }

    public function has_so_detail(): BelongsTo
    {
        return $this->belongsTo(SoDetail::class, 'so_detail_id');
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function has_lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function has_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}
