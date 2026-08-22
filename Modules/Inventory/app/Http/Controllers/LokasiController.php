<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\Gudang;
use Modules\Inventory\Models\Lokasi;

class LokasiController extends Controller
{
    public function __construct(Lokasi $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'gudangOptions' => Gudang::getOptions(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->with('has_gudang')->filter()->sort();
    }
}
