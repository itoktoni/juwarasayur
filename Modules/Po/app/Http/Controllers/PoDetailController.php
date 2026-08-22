<?php

namespace Modules\Po\Http\Controllers;

use Modules\Po\Models\PoDetail;

class PoDetailController extends Controller
{
    public function __construct(PoDetail $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        return $this->model->with(['has_po', 'has_product'])->filter()->sort();
    }
}
