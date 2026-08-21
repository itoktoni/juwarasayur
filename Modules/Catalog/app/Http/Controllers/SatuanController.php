<?php

namespace Modules\Catalog\Http\Controllers;

use Modules\Catalog\Models\Satuan;

class SatuanController extends Controller
{
    public function __construct(Satuan $model)
    {
        $this->model = $model::getModel();
    }
}
