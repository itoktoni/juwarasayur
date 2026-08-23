<?php

namespace Modules\Production\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Production\Enums\ProductionStatusEnum;
use Modules\Production\Enums\ProductionTypeEnum;
use Modules\Production\Models\Production;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class ProductionController extends Controller
{
    public function __construct(Production $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'typeOptions' => ProductionTypeEnum::getOptions(),
            'statusOptions' => ProductionStatusEnum::getOptions(),
            'productOptions' => Product::orderBy('product_nama')->pluck('product_nama', 'id')->all(),
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
        return $this->model->with(['has_items.has_product', 'has_product'])->filter()->sort();
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

        $rows = DB::table('so_details')
            ->join('catalog_products', 'catalog_products.id', '=', 'so_details.so_detail_id_product')
            ->whereIn('so_details.so_detail_id_so', $validated['ids'])
            ->groupBy('so_details.so_detail_id_product', 'catalog_products.product_nama')
            ->selectRaw('so_details.so_detail_id_product as product_id, catalog_products.product_nama as product_nama, SUM(so_details.so_detail_qty) as total_qty')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
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

        // Field dinamis bahan (item_*) tanpa rule akan lolos validasi &
        // diabaikan mass-assignment oleh Eloquent.
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

        // Efek stok sekali saja saat transisi ke completed
        if ($oldStatus !== ProductionStatusEnum::COMPLETED
            && $saved->production_status === ProductionStatusEnum::COMPLETED) {
            $this->applyStock($saved);
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

    /**
     * Konsumsi stok bahan & tambah stok paket hasil produksi.
     */
    private function applyStock(Production $production): void
    {
        $production->load('has_items.has_product');

        foreach ($production->has_items as $item) {
            $product = $item->has_product;
            if (! $product) {
                continue;
            }
            $product->decrement('product_stok', $item->production_item_qty);
        }

        Product::where('id', $production->production_id_product)
            ->increment('product_stok', $production->production_qty_output);
    }
}
