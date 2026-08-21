<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['category_nama', 'category_slug', 'category_deskripsi', 'category_icon', 'category_image', 'parent_id', 'is_active', 'sort_order'])]
class Category extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_categories';

    public static $sortColumns = ['category_nama', 'category_slug', 'is_active', 'sort_order'];

    public static $filterColumns = ['category_nama', 'category_slug', 'is_active'];

    public static function field_name(): string
    {
        return 'category_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getCategoryImageUrlAttribute(): string
    {
        return fileUrl($this->category_image);
    }

    public function has_parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function has_children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function has_products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id_category');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->category_slug) && ! empty($model->category_nama)) {
                $model->category_slug = static::generateUniqueSlug($model->category_nama);
            }
        });

        static::updating(function (self $model) {
            if (empty($model->category_slug) && ! empty($model->category_nama)) {
                $model->category_slug = static::generateUniqueSlug($model->category_nama, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::where('category_slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function rules(): array
    {
        return [
            'category_nama' => ['required', 'string', 'max:255'],
            'category_slug' => ['nullable', 'string', 'max:255', 'unique:catalog_categories,category_slug,'.($this->id ?? '')],
            'category_deskripsi' => ['nullable', 'string'],
            'category_icon' => ['nullable', 'string', 'max:50'],
            'category_image' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:catalog_categories,id'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
