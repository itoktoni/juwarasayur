<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use App\Concerns\DefaultEntity;
use App\Concerns\OptionTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBaseModel
 */
class BaseModel extends Model
{
    use DefaultEntity, Filterable, OptionTrait, Sortable;

    // Remove hardcoded products table reference since it's not part of our application
    // Each model should define its own table name
    protected $primaryKey = 'id';

    public $timestamps = true;

    public $incrementing = true;

    /**
     * Columns available for filtering.
     */
    public static $filterColumns = [];

    /**
     * Columns available for sorting.
     */
    public static $sortColumns = [];

    /**
     * Purity filter/sort whitelists resolved from the static columns above.
     *
     * ponytail: these MUST be declared properties (not assigned dynamically via
     * $this->filterFields = ...). Eloquent's __set() treats undeclared properties
     * as model attributes, so dynamic assignment pollutes getAttributes() and
     * breaks every Eloquent create/insertGetId with "parameterize() string given".
     */
    public $filterFields;

    public $sortFields;

    /**
     * Whitelisted fields consumed by abbasudo/laravel-purity (filter + sort).
     *
     * These mirror the static $filterColumns/$sortColumns so purity can resolve
     * allowed fields without hitting the schema (getColumnListing) on every
     * request. When the whitelist is empty the property stays unset and purity
     * falls back to its default schema-based detection.
     */
    public function __construct(array $attributes = [])
    {
        if (! empty(static::$filterColumns)) {
            $this->filterFields = array_values(static::$filterColumns);
        }

        if (! empty(static::$sortColumns)) {
            $this->sortFields = array_values(static::$sortColumns);
        }

        parent::__construct($attributes);
    }

    /**
     * Accessor: $table->field_primary in blade templates → model ID.
     */
    public function getFieldPrimaryAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Validation rules. Models override this.
     *
     * ponytail: returns [] on purpose — a guessed default (field_name + 'name')
     * produced errors keyed to columns absent from the form, so nothing rendered
     * and the submit looked like a silent no-op.
     */
    public function rules(): array
    {
        return [];
    }
}
