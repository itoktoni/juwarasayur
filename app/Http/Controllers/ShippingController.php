<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use Modules\Ecommerce\Models\CodLocation;

class ShippingController extends Controller
{
    use ControllerTrait;

    public function __construct(CodLocation $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'warehouse' => config('so.shipping.warehouse'),
        ], $data);
    }
}
