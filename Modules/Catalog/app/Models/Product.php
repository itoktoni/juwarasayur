<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_products';

    protected $fillable = [
        'product_nama', 'product_slug', 'product_kode', 'product_sku', 'product_barcode',
        'product_deskripsi', 'product_deskripsi_lengkap',
        'product_harga', 'product_harga_modal', 'product_harga_grosir',
        'reseller_fee_percent', 'affiliator_fee_percent',
        'product_berat', 'product_panjang', 'product_lebar', 'product_tinggi',
        'product_stok', 'product_stok_minimum',
        'product_gambar', 'product_galeri', 'product_status',
        'is_featured', 'is_active', 'sort_order',
        'product_id_product_master', 'product_id_brand', 'product_id_satuan', 'product_id_category',
    ];

    public static $sortColumns = ['product_nama', 'product_kode', 'product_harga', 'product_stok', 'product_status', 'sort_order'];

    public static $filterColumns = ['product_nama', 'product_kode', 'product_sku', 'product_status', 'is_active', 'is_featured'];

    public static function field_name(): string
    {
        return 'product_nama';
    }

    protected function casts(): array
    {
        return [
            'product_harga' => 'integer',
            'product_harga_modal' => 'integer',
            'product_harga_grosir' => 'integer',
            'reseller_fee_percent' => 'integer',
            'affiliator_fee_percent' => 'integer',
            'product_berat' => 'integer',
            'product_panjang' => 'integer',
            'product_lebar' => 'integer',
            'product_tinggi' => 'integer',
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

        // Kolom NOT NULL tanpa nullable — isi default kalau request kirim kosong/null,
        // supaya tidak error SQL 1048 (default DB tidak berlaku saat NULL dikirim eksplisit).
        static::saving(function (self $model) {
            foreach (['product_harga', 'product_harga_modal', 'product_stok', 'product_stok_minimum', 'sort_order'] as $field) {
                if ($model->{$field} === null || $model->{$field} === '') {
                    $model->{$field} = 0;
                }
            }

            if (empty($model->product_status)) {
                $model->product_status = 'active';
            }

            $model->is_featured = (bool) $model->is_featured;
            $model->is_active = ($model->is_active === null || $model->is_active === '') ? true : (bool) $model->is_active;
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
            'product_harga' => ['required', 'integer', 'min:0'],
            'product_harga_modal' => ['nullable', 'integer', 'min:0'],
            'product_harga_grosir' => ['nullable', 'integer', 'min:0'],
            'reseller_fee_percent' => ['nullable', 'numeric', 'between:0,100'],
            'affiliator_fee_percent' => ['nullable', 'numeric', 'between:0,100'],
            'product_berat' => ['nullable', 'numeric', 'min:0'],
            'product_panjang' => ['nullable', 'numeric', 'min:0'],
            'product_lebar' => ['nullable', 'numeric', 'min:0'],
            'product_tinggi' => ['nullable', 'numeric', 'min:0'],
            'product_stok' => ['nullable', 'integer', 'min:0'],
            'product_stok_minimum' => ['nullable', 'integer', 'min:0'],
            'product_gambar' => ['nullable', 'string', 'max:255'],
            'product_galeri' => ['nullable', 'array'],
            'product_status' => ['required', 'string', 'in:active,inactive,draft,archived'],
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
