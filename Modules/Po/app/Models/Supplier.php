<?php

namespace Modules\Po\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Catalog\Models\ProductMaster;

#[Fillable(['supplier_nama', 'supplier_kode', 'supplier_telepon', 'supplier_email', 'supplier_alamat', 'supplier_kontak_person', 'supplier_npwp', 'is_active', 'sort_order'])]
class Supplier extends BaseModel
{
    use SoftDeletes;

    protected $table = 'po_suppliers';

    public static $sortColumns = ['supplier_nama', 'supplier_kode', 'is_active', 'sort_order'];

    public static $filterColumns = ['supplier_nama', 'supplier_kode', 'is_active'];

    public static function field_name(): string
    {
        return 'supplier_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function has_pos(): HasMany
    {
        return $this->hasMany(Po::class, 'po_id_supplier');
    }

    public function has_product_masters(): BelongsToMany
    {
        return $this->belongsToMany(ProductMaster::class, 'catalog_product_master_supplier', 'supplier_id', 'product_master_id')
            ->withPivot('is_recommended');
    }

    public function rules(): array
    {
        return [
            'supplier_nama' => ['required', 'string', 'max:255'],
            'supplier_kode' => ['nullable', 'string', 'max:50', 'unique:po_suppliers,supplier_kode,'.($this->id ?? '')],
            'supplier_telepon' => ['nullable', 'string', 'max:50'],
            'supplier_email' => ['nullable', 'email', 'max:255'],
            'supplier_alamat' => ['nullable', 'string'],
            'supplier_kontak_person' => ['nullable', 'string', 'max:255'],
            'supplier_npwp' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
