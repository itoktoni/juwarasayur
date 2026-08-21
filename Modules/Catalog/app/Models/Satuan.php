<?php

namespace Modules\Catalog\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['satuan_nama', 'satuan_kode', 'satuan_simbol', 'satuan_deskripsi', 'is_active', 'sort_order'])]
class Satuan extends BaseModel
{
    use SoftDeletes;

    protected $table = 'catalog_satuans';

    public static $sortColumns = ['satuan_nama', 'satuan_kode', 'satuan_simbol', 'is_active', 'sort_order'];

    public static $filterColumns = ['satuan_nama', 'satuan_kode', 'satuan_simbol', 'is_active'];

    public static function field_name(): string
    {
        return 'satuan_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function has_products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id_satuan');
    }

    public function rules(): array
    {
        return [
            'satuan_nama' => ['required', 'string', 'max:255'],
            'satuan_kode' => ['nullable', 'string', 'max:50', 'unique:catalog_satuans,satuan_kode,'.($this->id ?? '')],
            'satuan_simbol' => ['nullable', 'string', 'max:20'],
            'satuan_deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
