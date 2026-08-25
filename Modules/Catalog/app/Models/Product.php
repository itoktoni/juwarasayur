<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['product_nama', 'product_slug', 'product_kode', 'product_sku', 'product_barcode', 'product_deskripsi', 'product_deskripsi_lengkap', 'product_harga', 'product_harga_modal', 'product_harga_grosir', 'product_berat', 'product_panjang', 'product_lebar', 'product_tinggi', 'product_stok', 'product_stok_minimum', 'product_gambar', 'product_galeri', 'product_status', 'is_featured', 'is_active', 'sort_order', 'product_id_product_master', 'product_id_brand', 'product_id_satuan', 'product_id_category'])]
class Product extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_products';

    public static $sortColumns = ['product_nama', 'product_kode', 'product_harga', 'product_stok', 'product_status', 'sort_order'];

    public static $filterColumns = ['product_nama', 'product_kode', 'product_sku', 'product_status', 'is_active', 'is_featured'];

    public static function field_name(): string
    {
        return 'product_nama';
    }

    protected function casts(): array
    {
        return [
            'product_harga' => 'decimal:2',
            'product_harga_modal' => 'decimal:2',
            'product_harga_grosir' => 'decimal:2',
            'product_berat' => 'decimal:2',
            'product_panjang' => 'decimal:2',
            'product_lebar' => 'decimal:2',
            'product_tinggi' => 'decimal:2',
            'product_galeri' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getProductGambarUrlAttribute(): string
    {
        return fileUrl($this->product_gambar);
    }

    public function has_product_master(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class, 'product_id_product_master');
    }

    public function has_brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'product_id_brand');
    }

    public function has_satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'product_id_satuan');
    }

    public function has_category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'product_id_category');
    }

    public function has_tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'catalog_product_tag', 'product_id', 'tag_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->product_slug) && ! empty($model->product_nama)) {
                $model->product_slug = static::generateUniqueSlug($model->product_nama);
            }
            if (empty($model->product_kode) && ! empty($model->product_nama)) {
                $model->product_kode = strtoupper(Str::random(8));
            }
        });

        static::updating(function (self $model) {
            if (empty($model->product_slug) && ! empty($model->product_nama)) {
                $model->product_slug = static::generateUniqueSlug($model->product_nama, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::where('product_slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function rules(): array
    {
        return [
            'product_nama' => ['required', 'string', 'max:255'],
            'product_slug' => ['nullable', 'string', 'max:255', 'unique:catalog_products,product_slug,'.($this->id ?? '')],
            'product_kode' => ['nullable', 'string', 'max:50', 'unique:catalog_products,product_kode,'.($this->id ?? '')],
            'product_sku' => ['nullable', 'string', 'max:100', 'unique:catalog_products,product_sku,'.($this->id ?? '')],
            'product_barcode' => ['nullable', 'string', 'max:100'],
            'product_deskripsi' => ['nullable', 'string'],
            'product_deskripsi_lengkap' => ['nullable', 'string'],
            'product_harga' => ['required', 'numeric', 'min:0'],
            'product_harga_modal' => ['nullable', 'numeric', 'min:0'],
            'product_harga_grosir' => ['nullable', 'numeric', 'min:0'],
            'product_berat' => ['nullable', 'numeric', 'min:0'],
            'product_panjang' => ['nullable', 'numeric', 'min:0'],
            'product_lebar' => ['nullable', 'numeric', 'min:0'],
            'product_tinggi' => ['nullable', 'numeric', 'min:0'],
            'product_stok' => ['nullable', 'integer', 'min:0'],
            'product_stok_minimum' => ['nullable', 'integer', 'min:0'],
            'product_gambar' => ['nullable', 'string', 'max:255'],
            'product_galeri' => ['nullable', 'array'],
            'product_status' => ['nullable', 'string', 'in:active,inactive,draft,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'product_id_product_master' => ['nullable', 'exists:catalog_product_masters,id'],
            'product_id_brand' => ['nullable', 'exists:catalog_brands,id'],
            'product_id_satuan' => ['nullable', 'exists:catalog_satuans,id'],
            'product_id_category' => ['nullable', 'exists:catalog_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:catalog_tags,id'],
        ];
    }
}
