<?php

namespace Modules\Ecommerce\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;

#[Fillable(['location_name', 'address', 'lat', 'lng', 'fee', 'is_active'])]
class CodLocation extends BaseModel
{
    protected $table = 'so_cod_locations';

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
}
