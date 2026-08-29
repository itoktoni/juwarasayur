<?php

namespace Modules\Production\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['production_cost_id_production', 'production_cost_nama', 'production_cost_nominal'])]
class ProductionCost extends BaseModel
{
    protected $table = 'production_costs';

    public static function field_name(): string
    {
        return 'production_cost_nama';
    }

    protected function casts(): array
    {
        return [
            'production_cost_nominal' => 'integer',
        ];
    }

    public function has_production(): BelongsTo
    {
        return $this->belongsTo(Production::class, 'production_cost_id_production');
    }

    public function rules(): array
    {
        return [
            'production_cost_id_production' => ['required', 'exists:productions,id'],
            'production_cost_nama' => ['required', 'string', 'max:100'],
            'production_cost_nominal' => ['required', 'numeric', 'min:0'],
        ];
    }
}
