<?php

namespace Modules\Catalog\Http\Controllers;

use Modules\Catalog\Models\Tag;

class TagController extends Controller
{
    public function __construct(Tag $model)
    {
        $this->model = $model::getModel();
    }
}
