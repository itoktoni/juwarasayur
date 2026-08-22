<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\Gudang;

class GudangController extends Controller
{
    public function __construct(Gudang $model)
    {
        $this->model = $model::getModel();
    }
}
