<?php

namespace Modules\Production\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
use Modules\Production\Enums\ProductionStatusEnum;
use Modules\Production\Enums\ProductionTypeEnum;
use Modules\Production\Models\Production;

/**
 * Produksi rutin: gabungkan beberapa barang menjadi 1 paket.
 */
class RoutineProductionController extends Controller
{
    public function __construct(Production $model)
    {
        $this->model = $model::getModel();
    }

    protected function template($file = null, $folder = null, $core = false)
    {
        return 'production::pages.routine.'.($file ?: $this->currentAction());
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'statusOptions' => ProductionStatusEnum::getOptions(),
            'productOptions' => Product::orderBy('product_nama')->pluck('product_nama', 'id')->all(),
            // Peta harga modal per produk untuk estimasi live di form
            'productPrices' => Product::pluck('product_harga_modal', 'id')->all(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->where('production_type', ProductionTypeEnum::ROUTINE)
            ->with(['has_items.has_product', 'has_product'])
            ->filter()
            ->sort();
    }

    public function postCreate(GeneralRequest $request)
    {
        return $this->saveWithItems($request, null);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        return $this->saveWithItems($request, $id);
    }

    /**
     * Simpan work order + bahan baku, lalu terapkan efek stok
     * saat status berubah menjadi completed.
     */
    private function saveWithItems(GeneralRequest $request, ?int $id)
    {
        $oldStatus = $id !== null
            ? $this->model->findOrFail($id)->production_status
            : null;

        // Paksa tipe rutin; field dinamis bahan diabaikan mass-assignment
        $request->merge(['production_type' => ProductionTypeEnum::ROUTINE]);

        $response = $id !== null
            ? UpdateAction::run($request, $id, $this->model)
            : CreateAction::run($request, $this->model);

        if (! ($response['status'] ?? false)) {
            return $this->response($response);
        }

        $saved = $response['data'];

        // Sinkronkan bahan baku
        $saved->has_items()->delete();
        foreach ($this->itemsFromRequest($request) as $item) {
            $saved->has_items()->create($item);
        }

        // Sinkronkan biaya tambahan (parkir, konsumsi, dll)
        $saved->has_costs()->delete();
        foreach ($this->costsFromRequest($request) as $cost) {
            $saved->has_costs()->create($cost);
        }

        // Efek stok sekali saja saat transisi ke completed
        if ($oldStatus !== ProductionStatusEnum::COMPLETED
            && $saved->production_status === ProductionStatusEnum::COMPLETED) {
            Production::applyStockEffects($saved);
        }

        return $this->response($response);
    }

    private function itemsFromRequest(Request $request): array
    {
        $items = [];

        foreach ((array) $request->input('item_id_product', []) as $i => $productId) {
            $qty = (int) ($request->input('item_qty')[$i] ?? 0);

            if (empty($productId) || $qty < 1) {
                continue;
            }

            $items[] = [
                'production_item_id_product' => (int) $productId,
                'production_item_qty' => $qty,
            ];
        }

        return $items;
    }

    private function costsFromRequest(Request $request): array
    {
        $costs = [];

        foreach ((array) $request->input('cost_nama', []) as $i => $nama) {
            $nominal = (float) ($request->input('cost_nominal')[$i] ?? 0);
            $nama = trim((string) $nama);

            if ($nama === '' || $nominal <= 0) {
                continue;
            }

            $costs[] = [
                'production_cost_nama' => $nama,
                'production_cost_nominal' => $nominal,
            ];
        }

        return $costs;
    }
}
