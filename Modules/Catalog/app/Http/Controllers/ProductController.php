<?php

namespace Modules\Catalog\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Satuan;
use Modules\Catalog\Models\Tag;

class ProductController extends Controller
{
    public function __construct(Product $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'brandOptions' => Brand::getOptions(),
            'satuanOptions' => Satuan::getOptions(),
            'categoryOptions' => Category::getOptions(),
            'tagOptions' => Tag::getOptions(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->query()->filter()->sort();
    }

    public function postCreate(GeneralRequest $request)
    {
        $gambar = $this->handleGambar($request, null);
        if ($gambar !== null) {
            $request->merge(['product_gambar' => $gambar]);
        }

        $response = CreateAction::run($request, $this->model);

        if ($response['status'] ?? false) {
            $product = $response['data'] ?? null;
            if ($product && $request->has('tag_ids')) {
                $product->has_tags()->sync($request->input('tag_ids', []));
            }
        }

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $product = $this->model->findOrFail($id);
        $existing = $product->product_gambar ?? null;

        $gambar = $this->handleGambar($request, $existing);
        if ($gambar !== $existing) {
            $request->merge(['product_gambar' => $gambar]);
        }

        $response = UpdateAction::run($request, $id, $this->model);

        if ($response['status'] ?? false) {
            $fresh = $this->model->find($id);
            if ($fresh && $request->has('tag_ids')) {
                $fresh->has_tags()->sync($request->input('tag_ids', []));
            }
        }

        return $this->response($response);
    }

    private function handleGambar(GeneralRequest $request, ?string $existing): ?string
    {
        if ($request->hasFile('product_gambar')) {
            try {
                $path = uploadFile($request->file('product_gambar'), 'catalog/products', ['max_size' => 2048]);
                $this->deleteFile($existing);

                return $path;
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages(['product_gambar' => $e->getMessage()]);
            }
        }

        if ($request->boolean('remove_product_gambar')) {
            $this->deleteFile($existing);

            return '';
        }

        return $existing;
    }

    private function deleteFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }
        $file = storage_path('app/public/'.$path);
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
