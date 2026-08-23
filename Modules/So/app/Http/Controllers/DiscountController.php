<?php

namespace Modules\So\Http\Controllers;

use Modules\So\Models\SoDiscount;

class DiscountController extends Controller
{
    public function __construct(SoDiscount $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        return $this->model->filter()->sort();
    }
}
