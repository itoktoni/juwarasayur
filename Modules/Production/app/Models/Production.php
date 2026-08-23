<?php

namespace Modules\Production\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Models\Product;
use Modules\Production\Enums\ProductionStatusEnum;

#[Fillable(['production_code', 'production_type', 'production_status', 'production_id_product', 'production_qty_output', 'production_orders', 'production_note'])]
class Production extends BaseModel
{
    protected $table = 'productions';

    public static $sortColumns = ['production_code', 'production_type', 'production_status', 'production_qty_output'];

    public static $filterColumns = [
        'production_code' => 'Kode',
        'production_type' => 'Tipe',
        'production_status' => 'Status',
    ];

    protected function casts(): array
    {
        return [
            'production_orders' => 'array',
            'production_qty_output' => 'integer',
        ];
    }

    public static function field_name(): string
    {
        return 'production_code';
    }

    protected $attributes = [
        'production_type' => 'routine',
        'production_status' => ProductionStatusEnum::PENDING,
        'production_qty_output' => 1,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->production_code ??= static::generateCode();
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'WO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('production_code', $code)->exists());

        return $code;
    }

    public function has_product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'production_id_product');
    }

    public function has_items(): HasMany
    {
        return $this->hasMany(ProductionItem::class, 'production_item_id_production');
    }

    public function rules(): array
    {
        return [
            'production_type' => ['required', 'in:order,routine'],
            'production_status' => ['required', 'in:pending,completed,cancelled'],
            'production_id_product' => ['required', 'exists:catalog_products,id'],
            'production_qty_output' => ['required', 'integer', 'min:1'],
            'production_orders' => ['nullable', 'array'],
            'production_orders.*' => ['integer'],
            'production_note' => ['nullable', 'string', 'max:2000'],
            // Bahan baku divalidasi manual di controller (array dinamis)
        ];
    }
}
