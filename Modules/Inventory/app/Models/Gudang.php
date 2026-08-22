<?php

namespace Modules\Inventory\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['gudang_nama', 'gudang_kode', 'gudang_alamat', 'is_active', 'sort_order'])]
class Gudang extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_gudangs';

    public static $sortColumns = ['gudang_nama', 'gudang_kode', 'is_active', 'sort_order'];

    public static $filterColumns = ['gudang_nama', 'gudang_kode', 'is_active'];

    public static function field_name(): string
    {
        return 'gudang_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function has_lokasis(): HasMany
    {
        return $this->hasMany(Lokasi::class, 'lokasi_id_gudang');
    }

    public function rules(): array
    {
        return [
            'gudang_nama' => ['required', 'string', 'max:255'],
            'gudang_kode' => ['nullable', 'string', 'max:50', 'unique:inv_gudangs,gudang_kode,'.($this->id ?? '')],
            'gudang_alamat' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
