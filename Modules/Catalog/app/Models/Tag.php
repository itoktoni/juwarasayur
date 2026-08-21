<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['tag_nama', 'tag_slug', 'tag_warna', 'is_active', 'sort_order'])]
class Tag extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_tags';

    public static $sortColumns = ['tag_nama', 'tag_slug', 'is_active', 'sort_order'];

    public static $filterColumns = ['tag_nama', 'tag_slug', 'is_active'];

    public static function field_name(): string
    {
        return 'tag_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function has_products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalog_product_tag', 'tag_id', 'product_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->tag_slug) && ! empty($model->tag_nama)) {
                $model->tag_slug = static::generateUniqueSlug($model->tag_nama);
            }
        });

        static::updating(function (self $model) {
            if (empty($model->tag_slug) && ! empty($model->tag_nama)) {
                $model->tag_slug = static::generateUniqueSlug($model->tag_nama, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::where('tag_slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function rules(): array
    {
        return [
            'tag_nama' => ['required', 'string', 'max:255'],
            'tag_slug' => ['nullable', 'string', 'max:255', 'unique:catalog_tags,tag_slug,'.($this->id ?? '')],
            'tag_warna' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
