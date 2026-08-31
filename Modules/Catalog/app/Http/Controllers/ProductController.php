<?php

namespace Modules\Catalog\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Http\Requests\GeneralRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductMaster;
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
            'masterOptions' => ProductMaster::getOptions(),
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
        $this->normalizeTagIds($request);
        $this->normalizeBooleans($request);

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
        $this->normalizeTagIds($request);
        $this->normalizeBooleans($request);

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

    /**
     * Export produk ke CSV untuk di-download.
     */
    public function getExport()
    {
        $products = Product::select([
            'product_nama', 'product_kode', 'product_harga',
            'product_harga_modal', 'product_stok',
            'reseller_fee_percent', 'affiliator_fee_percent',
        ])->orderBy('product_nama')->get();

        $filename = 'produk_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $delimiter = config('website.csv_delimiter', ';');

        $callback = function () use ($products, $delimiter) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Header
            fputcsv($handle, ['Nama Produk', 'Kode Produk', 'Harga Jual', 'Harga Modal', 'Stok', 'Fee Reseller (%)', 'Fee Affilator (%)'], $delimiter);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->product_nama,
                    $product->product_kode,
                    $product->product_harga,
                    $product->product_harga_modal ?? '',
                    $product->product_stok ?? '',
                    $product->reseller_fee_percent ?? '',
                    $product->affiliator_fee_percent ?? '',
                ], $delimiter);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilkan halaman upload CSV.
     */
    public function getImport()
    {
        return view('catalog::pages.product.import');
    }

    /**
     * Import produk dari CSV: insert baru atau update jika sudah ada.
     * Match by product_kode → product_nama.
     */
    public function postImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            return redirect()->back()->with('error', 'Gagal membuka file CSV.');
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read delimiter from config (default: ;)
        $delimiter = config('website.csv_delimiter', ';');

        // Read header
        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);

            return redirect()->back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        $header = array_map('strtolower', array_map('trim', $header));
        $headerMap = $this->mapHeader($header);

        $added = 0;
        $updated = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNum++;
                if (count($row) < count($headerMap)) {
                    $errors[] = "Baris {$rowNum}: kolom kurang dari header.";

                    continue;
                }

                $data = $this->mapRowToFields($row, $headerMap);

                if (empty($data['product_nama'])) {
                    $errors[] = "Baris {$rowNum}: nama produk kosong.";

                    continue;
                }

                $product = $this->findProduct($data);

                if ($product) {
                    $clean = array_filter($data, fn ($v) => $v !== null);
                    // Force numeric fields to int so Eloquent dirty-check triggers
                    foreach (['product_harga', 'product_harga_modal', 'product_harga_grosir', 'product_berat', 'product_panjang', 'product_lebar', 'product_tinggi', 'product_stok', 'product_stok_minimum', 'sort_order'] as $intField) {
                        if (isset($clean[$intField])) {
                            $clean[$intField] = (int) $clean[$intField];
                        }
                    }
                    foreach (['reseller_fee_percent', 'affiliator_fee_percent'] as $decField) {
                        if (isset($clean[$decField])) {
                            $clean[$decField] = (float) $clean[$decField];
                        }
                    }
                    $product->update($clean);
                    $updated++;
                } else {
                    $data['product_status'] = 'active';
                    $data['is_active'] = 1;
                    Product::create($data);
                    $added++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            return redirect()->back()->with('error', 'Gagal import: '.$e->getMessage());
        }

        fclose($handle);

        $summary = "Import selesai: {$added} ditambahkan, {$updated} diperbarui.";
        if ($errors !== []) {
            $summary .= ' '.count($errors).' baris errors (lihat flash message).';
        }

        return redirect()->route('catalog-product.getTable')
            ->with('success', $summary)
            ->with('import_errors', $errors);
    }

    private function mapHeader(array $header): array
    {
        $map = [
            'nama produk' => 'product_nama',
            'kode produk' => 'product_kode',
            'harga jual' => 'product_harga',
            'harga modal' => 'product_harga_modal',
            'stok' => 'product_stok',
            'stock' => 'product_stok',
            'fee reseller (%)' => 'reseller_fee_percent',
            'fee reseller' => 'reseller_fee_percent',
            'fee affilator (%)' => 'affiliator_fee_percent',
            'fee affilator' => 'affiliator_fee_percent',
            'fee affiliator (%)' => 'affiliator_fee_percent',
            'fee affiliator' => 'affiliator_fee_percent',
        ];

        $result = [];
        foreach ($header as $idx => $col) {
            $normalized = Str::lower(trim($col));
            $result[$idx] = $map[$normalized] ?? $normalized;
        }

        return $result;
    }

    private function mapRowToFields(array $row, array $headerMap): array
    {
        $fields = [];
        foreach ($headerMap as $idx => $field) {
            $value = trim($row[$idx] ?? '');
            $fields[$field] = $value === '' ? null : $value;
        }

        return $fields;
    }

    private function findProduct(array $data): ?Product
    {
        if (! empty($data['product_kode'])) {
            $product = Product::where('product_kode', $data['product_kode'])->first();
            if ($product) {
                return $product;
            }
        }

        if (! empty($data['product_nama'])) {
            $product = Product::where('product_nama', $data['product_nama'])->first();
            if ($product) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Multiple select tanpa "[]" mengirim satu nilai string — paksa ke array
     * agar rule validasi tag_ids (array) dan sync tags bekerja.
     */
    private function normalizeTagIds(GeneralRequest $request): void
    {
        if (! $request->has('tag_ids')) {
            return;
        }

        $request->merge([
            'tag_ids' => array_values(array_filter((array) $request->input('tag_ids'))),
        ]);
    }

    /**
     * Kolom boolean NOT NULL — string kosong diubah middleware jadi null
     * dan lolos rule "nullable|boolean", padahal DB menolak null. Paksa ke 0/1.
     */
    private function normalizeBooleans(GeneralRequest $request): void
    {
        foreach (['is_featured', 'is_active'] as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => (int) filter_var($request->input($field), FILTER_VALIDATE_BOOLEAN),
                ]);
            }
        }
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
