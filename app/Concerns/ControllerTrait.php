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
        // static::class (bukan self) agar tetap menunjuk controller konkret
        // meskipun index() diwarisi dari kelas Controller abstrak modul
        return redirect()->action([static::class, 'getTable']);
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
        $data = $this->applyLegacyFilter($this->getData())->cursorPaginate($request->input('per_page', 25))->withQueryString();

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
        return $this->applyLegacyFilter($this->model->filter()->sort());
    }

    protected function applyLegacyFilter($query)
    {
        $q = request()->input('_q');
        $searchField = request()->input('_field');
        if ($q !== null && $q !== '' && $searchField) {
            $filterColumns = $this->model::$filterColumns ?? [];
            $allowed = [];
            foreach ($filterColumns as $k => $v) {
                $allowed[] = is_int($k) ? $v : $k;
            }
            if (in_array($searchField, $allowed, true)) {
                $alreadyFiltered = request()->input("filters.{$searchField}") !== null;
                if (! $alreadyFiltered) {
                    $query->whereRaw('LOWER('.$searchField.') LIKE ?', ['%'.strtolower((string) $q).'%']);
                }
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
