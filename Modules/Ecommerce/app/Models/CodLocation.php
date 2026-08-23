<?php

namespace Modules\Ecommerce\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;

#[Fillable(['location_name', 'address', 'lat', 'lng', 'fee', 'is_active'])]
class CodLocation extends BaseModel
{
    protected $table = 'so_cod_locations';

    public static $sortColumns = ['location_name', 'fee', 'is_active'];

    public static $filterColumns = ['location_name' => 'Nama Lokasi', 'is_active'];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'location_name';
    }

    public static function active(): Collection
    {
        return static::where('is_active', true)->orderBy('location_name')->get();
    }

    public function rules(): array
    {
        return [
            'location_name' => ['required', 'string', 'max:100', 'unique:so_cod_locations,location_name,'.($this->id ?? '')],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
