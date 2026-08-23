<?php

namespace Modules\Production\Http\Controllers;

use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Production\Enums\ProductionStatusEnum;
use Modules\Production\Enums\ProductionTypeEnum;
use Modules\Production\Models\Production;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

/**
 * Produksi dari pesanan: pilih SO → kelompokkan per barang
 * → work order otomatis dibuat satu per barang.
 */
class OrderProductionController extends Controller
{
    public function __construct(Production $model)
    {
        $this->model = $model::getModel();
    }

    protected function template($file = null, $folder = null, $core = false)
    {
        return 'production::pages.order.'.($file ?: $this->currentAction());
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'statusOptions' => ProductionStatusEnum::getOptions(),
            // SO yang bisa dipilih sebagai sumber produksi (bukan batal)
            'soOptions' => So::query()
                ->where('so_status', '!=', SoStatusEnum::CANCELLED)
                ->orderByDesc('id')
                ->limit(200)
                ->get()
                ->pluck('so_code', 'id')
                ->all(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->where('production_type', ProductionTypeEnum::ORDER)
            ->with(['has_items.has_product', 'has_product'])
            ->filter()
            ->sort();
    }

    /**
     * AJAX: kelompokkan kebutuhan produk dari beberapa pesanan (SO).
     */
    public function getGroupOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        return response()->json(['status' => true, 'data' => $this->groupOrders($validated['ids'])]);
    }

    /**
     * Buat work order otomatis PER BARANG dari SO terpilih.
     */
    public function postCreateOrders(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $rows = $this->groupOrders($validated['ids']);

        if ($rows->isEmpty()) {
            flash()->error('Tidak ada detail produk pada pesanan terpilih.');

            return redirect()->route('production-order.getCreate');
        }

        foreach ($rows as $row) {
            /** @var Production $wo */
            $wo = $this->model->create([
                'production_type' => ProductionTypeEnum::ORDER,
                'production_status' => ProductionStatusEnum::PENDING,
                'production_id_product' => $row->product_id,
                'production_qty_output' => (int) $row->total_qty,
                'production_orders' => $validated['ids'],
                'production_note' => null,
            ]);

            $wo->has_items()->create([
                'production_item_id_product' => $row->product_id,
                'production_item_qty' => (int) $row->total_qty,
            ]);
        }

        flash()->success($rows->count().' work order berhasil dibuat (per barang).');

        return redirect()->route('production-order.getTable');
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $oldStatus = $this->model->findOrFail($id)->production_status;

        $response = UpdateAction::run($request, $id, $this->model);

        if (! ($response['status'] ?? false)) {
            return $this->response($response);
        }

        // Efek stok sekali saja saat transisi ke completed
        $saved = $response['data'];
        if ($oldStatus !== ProductionStatusEnum::COMPLETED
            && $saved->production_status === ProductionStatusEnum::COMPLETED) {
            Production::applyStockEffects($saved);
        }

        return $this->response($response);
    }

    /**
     * Grouping kebutuhan produk dari sekumpulan SO.
     */
    private function groupOrders(array $ids)
    {
        return DB::table('so_order_details')
            ->join('catalog_products', 'catalog_products.id', '=', 'so_order_details.so_detail_id_product')
            ->whereIn('so_order_details.so_detail_id_so', $ids)
            ->groupBy('so_order_details.so_detail_id_product', 'catalog_products.product_nama')
            ->selectRaw('so_order_details.so_detail_id_product as product_id, catalog_products.product_nama as product_nama, SUM(so_order_details.so_detail_qty) as total_qty')
            ->get();
    }
}
