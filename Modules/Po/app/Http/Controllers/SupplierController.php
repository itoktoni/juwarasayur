<?php

namespace Modules\Po\Http\Controllers;

use Modules\Po\Models\Supplier;

class SupplierController extends Controller
{
    public function __construct(Supplier $model)
    {
        $this->model = $model::getModel();
    }
}
