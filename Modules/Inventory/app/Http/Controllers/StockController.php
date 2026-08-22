<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\Stock;

class StockController extends Controller
{
    public function __construct(Stock $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        return $this->model->with(['has_product', 'has_lokasi.has_gudang'])->filter()->sort();
    }
}
