<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['brand_nama', 'brand_slug', 'brand_logo', 'brand_deskripsi', 'is_active', 'sort_order'])]
class Brand extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_brands';

    public static $sortColumns = ['brand_nama', 'brand_slug', 'is_active', 'sort_order'];

    public static $filterColumns = ['brand_nama', 'brand_slug', 'is_active'];

    public static function field_name(): string
    {
        return 'brand_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getBrandLogoUrlAttribute(): string
    {
        return fileUrl($this->brand_logo);
    }

    public function has_products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id_brand');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->brand_slug) && ! empty($model->brand_nama)) {
                $model->brand_slug = static::generateUniqueSlug($model->brand_nama);
            }
        });

        static::updating(function (self $model) {
            if (empty($model->brand_slug) && ! empty($model->brand_nama)) {
                $model->brand_slug = static::generateUniqueSlug($model->brand_nama, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::where('brand_slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function rules(): array
    {
        return [
            'brand_nama' => ['required', 'string', 'max:255'],
            'brand_slug' => ['nullable', 'string', 'max:255', 'unique:catalog_brands,brand_slug,'.($this->id ?? '')],
            'brand_logo' => ['nullable', 'string', 'max:255'],
            'brand_deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
