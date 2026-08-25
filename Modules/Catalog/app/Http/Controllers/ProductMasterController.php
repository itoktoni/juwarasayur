<?php

namespace Modules\Catalog\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;
use Modules\Catalog\Models\ProductMaster;
use Modules\Po\Models\Supplier;

class ProductMasterController extends Controller
{
    public function __construct(ProductMaster $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'supplierOptions' => Supplier::where('is_active', true)->orderBy('supplier_nama')->pluck('supplier_nama', 'id')->all(),
        ], $data);
    }

    public function postCreate(GeneralRequest $request)
    {
        $this->normalizeDefaults($request);

        $response = CreateAction::run($request, $this->model);

        if ($response['status'] ?? false) {
            $this->syncSuppliers($request, (int) $response['data']->id);
        }

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $this->normalizeDefaults($request);

        $response = UpdateAction::run($request, $id, $this->model);

        if ($response['status'] ?? false) {
            $this->syncSuppliers($request, (int) $id);
        }

        return $this->response($response);
    }

    /**
     * Kolom NOT NULL — string kosong dari form diubah middleware jadi null
     * dan lolos rule "nullable", padahal DB menolak null. Paksa ke default.
     */
    private function normalizeDefaults(GeneralRequest $request): void
    {
        if ($request->input('sort_order') === null) {
            $request->merge(['sort_order' => 0]);
        }

        if ($request->has('is_active')) {
            $request->merge([
                'is_active' => (int) filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Sync pivot master-supplier; tepat satu supplier ditandai rekomendasi.
     */
    private function syncSuppliers(GeneralRequest $request, int $masterId): void
    {
        $master = ProductMaster::findOrFail($masterId);

        $ids = array_values(array_unique(array_filter((array) ($request->input('supplier_ids') ?? []), fn ($v) => is_numeric($v))));
        $recommendedId = $request->input('recommended_supplier_id');
        $recommendedId = in_array((int) $recommendedId, $ids, true) ? (int) $recommendedId : ($ids[0] ?? null);

        $sync = [];
        foreach ($ids as $sid) {
            $sync[$sid] = ['is_recommended' => (int) $sid === $recommendedId];
        }

        $master->has_suppliers()->sync($sync);
    }
}
