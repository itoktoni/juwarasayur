<?php

namespace Modules\Faq\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Controllers\Controller;
use Modules\Faq\Models\Faq;

class FaqController extends Controller
{
    use ControllerTrait;

    public function __construct(Faq $model)
    {
        $this->model = $model::getModel();
    }
}
