<?php

namespace Modules\Catalog\Http\Controllers;

use Modules\Catalog\Models\Category;

class CategoryController extends Controller
{
    public function __construct(Category $model)
    {
        $this->model = $model::getModel();
    }
}
