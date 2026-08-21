<?php

namespace Modules\Catalog\Http\Controllers;

use Modules\Catalog\Models\Brand;

class BrandController extends Controller
{
    public function __construct(Brand $model)
    {
        $this->model = $model::getModel();
    }
}
