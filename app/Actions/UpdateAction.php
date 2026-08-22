<?php

namespace App\Actions;

use App\Concerns\PayloadTrait;
use App\Concerns\RulesTrait;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateAction
{
    use AsAction, PayloadTrait, RulesTrait;

    public function rules(): array
    {
        return $this->mergeRules($this->model);
    }

    public function handle(Request $request, $id, $model)
    {
        $this->model = $model;

        try {
            // Ambil record dulu supaya validasi rules() jalan dengan id terisi
            // (rule "unique:..." memakai ignore(id) agar tidak bentrok dengan dirinya)
            $response = $model->findOrFail($id);
            $data = $request->validate($response->rules());

            $response->update($data);

            return $this->payload(TOAST_SUCCESS, $response);

        } catch (\Throwable $th) {
            return $this->payload(TOAST_FAILED, $th->getMessage());
        }
    }
}
