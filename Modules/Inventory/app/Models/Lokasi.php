<?php

namespace Modules\Inventory\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['lokasi_nama', 'lokasi_kode', 'lokasi_id_gudang', 'is_active', 'sort_order'])]
class Lokasi extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inv_lokasis';

    public static $sortColumns = ['lokasi_nama', 'lokasi_kode', 'is_active', 'sort_order'];

    public static $filterColumns = ['lokasi_nama', 'lokasi_kode', 'lokasi_id_gudang', 'is_active'];

    public static function field_name(): string
    {
        return 'lokasi_nama';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function has_gudang(): BelongsTo
    {
        return $this->belongsTo(Gudang::class, 'lokasi_id_gudang');
    }

    public function has_stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'stock_id_lokasi');
    }

    public function rules(): array
    {
        return [
            'lokasi_nama' => ['required', 'string', 'max:255'],
            'lokasi_kode' => ['nullable', 'string', 'max:50', 'unique:inv_lokasis,lokasi_kode,'.($this->id ?? '')],
            'lokasi_id_gudang' => ['required', 'exists:inv_gudangs,id'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
