<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Po\Models\Supplier;

#[Fillable(['product_master_nama', 'product_master_slug', 'product_master_deskripsi', 'is_active', 'sort_order'])]
class ProductMaster extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_product_masters';

    public static $sortColumns = ['product_master_nama', 'is_active', 'sort_order'];

    public static $filterColumns = ['product_master_nama', 'is_active'];

    public static function field_name(): string
    {
        return 'product_master_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kolom NOT NULL — paksa default saat menerima null dari form/API.
     */
    protected function sortOrder(): Attribute
    {
        return Attribute::set(fn ($value) => (int) ($value ?? 0));
    }

    protected function isActive(): Attribute
    {
        return Attribute::set(fn ($value) => (int) filter_var($value ?? true, FILTER_VALIDATE_BOOLEAN));
    }

    public function has_products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id_product_master');
    }

    public function has_suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'catalog_product_master_supplier', 'product_master_id', 'supplier_id')
            ->withPivot('is_recommended');
    }

    /**
     * Supplier rekomendasi untuk master ini (pivot is_recommended = true).
     */
    public function getRecommendedSupplierAttribute()
    {
        return $this->has_suppliers()->wherePivot('is_recommended', true)->first();
    }

    /**
     * Id supplier yang terpilih di pivot (untuk default form).
     */
    public function getSupplierIdsAttribute(): array
    {
        return $this->exists ? $this->has_suppliers()->pluck('po_suppliers.id')->all() : [];
    }

    public function getRecommendedSupplierIdAttribute()
    {
        return $this->exists
            ? optional($this->has_suppliers()->wherePivot('is_recommended', true)->first())->id
            : null;
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->product_master_slug) && ! empty($model->product_master_nama)) {
                $model->product_master_slug = static::generateUniqueSlug($model->product_master_nama);
            }
        });

        static::updating(function (self $model) {
            if (empty($model->product_master_slug) && ! empty($model->product_master_nama)) {
                $model->product_master_slug = static::generateUniqueSlug($model->product_master_nama, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::where('product_master_slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function rules(): array
    {
        return [
            'product_master_nama' => ['required', 'string', 'max:255'],
            'product_master_slug' => ['nullable', 'string', 'max:255', 'unique:catalog_product_masters,product_master_slug,'.($this->id ?? '')],
            'product_master_deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
