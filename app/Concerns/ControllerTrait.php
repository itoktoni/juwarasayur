<?php

namespace App\Concerns;

use App\Actions\CreateAction;
use App\Actions\DeleteAction;
use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;

trait ControllerTrait
{
    use PayloadTrait;

    public $model;

    public function index(GeneralRequest $request)
    {
        return redirect()->action([self::class, 'getTable']);
    }

    public function getShow(GeneralRequest $request, $id)
    {
        try {
            return $this->payload(TOAST_SUCCESS, $this->model->findOrFail($id));
        } catch (\Throwable $th) {
            return $this->payload(TOAST_FAILED, $th->getMessage());
        }
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        $data = $this->model->findOrFail($id);

        return $this->views($this->template(), [
            'model' => $data,
        ]);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $response = UpdateAction::run($request, $id, $this->model);

        return $this->response($response);
    }

    public function getDelete(GeneralRequest $request, $id)
    {
        $response = (new DeleteAction)->remove($id, $this->model);

        return $this->response($response);
    }

    public function getTable(GeneralRequest $request)
    {
        $data = $this->getData()->cursorPaginate($request->input('per_page', 25))->withQueryString();

        return $this->views($this->template(), [
            'data' => $data,
            'fields' => $this->getFields(),
        ]);
    }

    public function getCreate(GeneralRequest $request)
    {
        return $this->views($this->template());
    }

    public function postCreate(GeneralRequest $request)
    {
        $response = CreateAction::run($request, $this->model);

        return $this->response($response);
    }

    public function postDelete(GeneralRequest $request)
    {
        $count = DeleteAction::run($request, $this->model);

        return $this->response($count);
    }

    // END FUNCTION CONTROLLER

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
        ];

        return array_merge($default, $data);
    }

    protected function getFields()
    {
        // Build fields for filter from model's $filterColumns
        $fields = [];
        if (property_exists($this->model, 'filterColumns') && ! empty($this->model::$filterColumns)) {
            foreach ($this->model::$filterColumns as $key => $value) {
                if ($value === false || $value === null || $value === '') {
                    continue;
                }

                if (is_numeric($key)) {
                    $fields[$value] = ucwords(str_replace('_', ' ', $value));
                } else {
                    $fields[$key] = $value;
                }
            }
        }

        return $fields;
    }

    protected function views(string $view, array $data = [], int $status = 200)
    {
        if (request()->expectsJson()) {
            return response()->json($data, $status);
        }

        return view($view, $this->share($data));
    }

    protected function template($file = null, $folder = null, $core = false)
    {
        // Get the class name (e.g., UserController)
        $className = class_basename(get_class($this));

        // Remove 'Controller' suffix and convert to lowercase
        $module = strtolower(str_replace('Controller', '', $className));

        // Get the method name (e.g., getCreate)
        $method = debug_backtrace()[1]['function'];

        // Remove 'get' or 'post' prefix and convert to lowercase
        $action = strtolower(preg_replace('/^(get|post)/', '', $method));

        if (in_array($action, ['update', 'create'])) {
            $action = 'form';
        }

        if ($file) {
            $action = $file;
        }

        if ($file === true) {
            return $module;
        }

        if ($folder) {
            $module = $folder;
        }

        $path = 'pages.';

        if ($core) {
            $path = 'core.';
        }

        return $path.$module.'.'.$action;
    }

    protected function isApi(): bool
    {
        if (request()->hasHeader('authorization')) {
            return true;
        }

        if (request()->wantsJson()) {
            return true;
        }

        return request()->expectsJson() || request()->is('api/*');
    }

    protected function getData()
    {
        $query = $this->model->query();
        $request = request();

        // Filters: filters[field][operator] = value
        $filters = $request->input('filters', []);
        $availableColumns = $this->model::$filterColumns ?? [];
        $columnKeys = array_keys($availableColumns);
        // Normalize associative columns to plain list
        $allowedFields = array_map(fn ($k, $v) => is_numeric($k) ? $v : $k, $columnKeys, $availableColumns);

        foreach ($filters as $field => $conditions) {
            // Whitelist: skip fields not in $filterColumns
            if (! empty($allowedFields) && ! in_array($field, $allowedFields)) {
                continue;
            }
            // Handle leftJoin if field contains dot (relation.column)
            if (str_contains($field, '.')) {
                $parts = explode('.', $field);
                $relation = $parts[0];
                $column = $parts[1];
                if (method_exists($this->model, $relation)) {
                    $rel = $this->model->$relation();
                    $relatedTable = $rel->getRelated()->getTable();
                    $foreignKey = $rel->getQualifiedForeignKeyName();
                    $ownerKey = $rel->getQualifiedOwnerKeyName();
                    $query->leftJoin($relatedTable, $foreignKey, '=', $ownerKey);
                    $field = $relatedTable.'.'.$column;
                }
            } elseif (! str_contains($field, '.')) {
                $field = $this->model->getTable().'.'.$field;
            }

            if (is_array($conditions)) {
                foreach ($conditions as $operator => $value) {
                    if ($value === '' || $value === null) {
                        continue;
                    }
                    match ($operator) {
                        '$contains' => $query->whereRaw('LOWER('.$field.') LIKE ?', ['%'.strtolower($value).'%']),
                        '$eq' => $query->whereRaw('LOWER('.$field.') = ?', [strtolower($value)]),
                        '$gt' => $query->where($field, '>', $value),
                        '$gte' => $query->where($field, '>=', $value),
                        '$lt' => $query->where($field, '<', $value),
                        '$lte' => $query->where($field, '<=', $value),
                        '$ne' => $query->whereRaw('LOWER('.$field.') != ?', [strtolower($value)]),
                        '$in' => $query->whereIn($field, (array) $value),
                        default => $query->whereRaw('LOWER('.$field.') LIKE ?', ['%'.strtolower($value).'%']),
                    };
                }
            } elseif (is_string($conditions) && $conditions !== '') {
                $query->whereRaw('LOWER('.$field.') LIKE ?', ['%'.strtolower($conditions).'%']);
            }
        }

        // Legacy _q + _field search
        $q = $request->input('_q');
        $searchField = $request->input('_field');
        if ($q && $searchField && in_array($searchField, $allowedFields)) {
            $query->whereRaw('LOWER('.$searchField.') LIKE ?', ['%'.strtolower($q).'%']);
        }

        // Sort: sort[0] = column:direction
        $sort = $request->input('sort.0');
        $sortColumns = $this->model::$sortColumns ?? [];
        if ($sort) {
            $parts = explode(':', $sort);
            $col = $parts[0] ?? null;
            $dir = ($parts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            if ($col && (empty($sortColumns) || in_array($col, $sortColumns))) {
                $query->orderBy($col, $dir);
            }
        }

        return $query;
    }

    protected function response(array $response, $redirect = null)
    {
        if ($this->isApi()) {
            return response()->json($response);
        } else {
            if ($response['status']) {
                flash()->success($response['message']);
            } else {
                flash()->error($response['data']);
            }
        }

        if ($redirect) {
            return $redirect;
        }

        return redirect()->back();
    }

    protected function respondView(string $view, array $data = [])
    {
        if ($this->isApi()) {
            return response()->json($data['model'] ?? $data[array_key_first($data)] ?? $data);
        }

        return view($view, $data);
    }
}
