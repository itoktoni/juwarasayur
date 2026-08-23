<?php

namespace Modules\Production\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
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

    public function has_costs(): HasMany
    {
        return $this->hasMany(ProductionCost::class, 'production_cost_id_production');
    }

    /**
     * Total biaya bahan baku (qty × harga modal bahan).
     */
    public function totalBiayaBaku(): float
    {
        return (float) $this->has_items()->with('has_product')->get()
            ->sum(fn ($item) => $item->production_item_qty * (float) ($item->has_product?->product_harga_modal ?? 0));
    }

    /**
     * Total biaya tambahan (parkir, konsumsi, dll).
     */
    public function totalBiayaTambahan(): float
    {
        return (float) $this->has_costs()->sum('production_cost_nominal');
    }

    /**
     * Estimasi harga modal per unit paket hasil produksi.
     */
    public function estimasiHargaModal(): float
    {
        $qty = max(1, (int) $this->production_qty_output);

        return round(($this->totalBiayaBaku() + $this->totalBiayaTambahan()) / $qty, 2);
    }

    /**
     * Konsumsi stok bahan & tambah stok paket hasil produksi.
     * Dipanggil sekali saat status work order berubah menjadi completed.
     * Sekaligus hitung & timpa product_harga_modal paket.
     */
    public static function applyStockEffects(Production $production): void
    {
        $production->load('has_items.has_product');

        foreach ($production->has_items as $item) {
            $product = $item->has_product;
            if (! $product) {
                continue;
            }
            $product->decrement('product_stok', $item->production_item_qty);
        }

        Product::where('id', $production->production_id_product)
            ->increment('product_stok', $production->production_qty_output);

        // Timpa harga modal paket dengan hasil perhitungan terbaru
        Product::where('id', $production->production_id_product)
            ->update(['product_harga_modal' => $production->estimasiHargaModal()]);
    }

    public function rules(): array
    {
        return [
            'production_type' => ['required', 'in:order,routine'],
            'production_status' => ['required', 'in:pending,completed,cancelled'],
            // Wajib untuk produksi rutin; tipe order dideteksi otomatis dari SO
            'production_id_product' => ['nullable', 'exists:catalog_products,id', 'required_if:production_type,routine'],
            'production_qty_output' => ['required', 'integer', 'min:1'],
            'production_orders' => ['nullable', 'array'],
            'production_orders.*' => ['integer'],
            'production_note' => ['nullable', 'string', 'max:2000'],
            // Bahan baku divalidasi manual di controller (array dinamis)
        ];
    }
}
