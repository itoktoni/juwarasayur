<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Tag;

/**
 * Storefront toko online: katalog publik & detail produk.
 */
class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $categorySlug = trim((string) $request->input('category', ''));
        $tagSlug = trim((string) $request->input('tag', ''));
        $brandSlug = trim((string) $request->input('brand', ''));
        $sort = $request->input('sort', 'latest');
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(6, min(48, $perPage));

        $products = Product::query()
            ->where('is_active', true)
            ->where('product_status', 'active')
            ->with(['has_brand', 'has_category', 'has_tags'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('product_nama', 'like', "%{$q}%")
                        ->orWhere('product_deskripsi', 'like', "%{$q}%")
                        ->orWhere('product_kode', 'like', "%{$q}%")
                        ->orWhere('product_sku', 'like', "%{$q}%");
                });
            })
            ->when($categorySlug !== '', function ($query) use ($categorySlug) {
                $category = Category::where('category_slug', $categorySlug)->first();
                if ($category) {
                    $ids = $this->categoryWithDescendants($category);
                    $query->whereIn('product_id_category', $ids);
                } else {
                    $query->whereRaw('1=0');
                }
            })
            ->when($tagSlug !== '', function ($query) use ($tagSlug) {
                $query->whereHas('has_tags', function ($w) use ($tagSlug) {
                    $w->where('catalog_tags.tag_slug', $tagSlug);
                });
            })
            ->when($brandSlug !== '', function ($query) use ($brandSlug) {
                $query->whereHas('has_brand', function ($w) use ($brandSlug) {
                    $w->where('catalog_brands.brand_slug', $brandSlug);
                });
            })
            ->when($sort === 'price_asc', fn ($q) => $q->orderBy('product_harga', 'asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderBy('product_harga', 'desc'))
            ->when($sort === 'name_asc', fn ($q) => $q->orderBy('product_nama', 'asc'))
            ->when(! in_array($sort, ['price_asc', 'price_desc', 'name_asc']), fn ($q) => $q->orderByDesc('id'))
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('category_nama')->get();
        $brands = Brand::where('is_active', true)->orderBy('sort_order')->orderBy('brand_nama')->get();
        $tags = Tag::where('is_active', true)->orderBy('sort_order')->orderBy('tag_nama')->get();

        $activeCategory = $categorySlug !== '' ? Category::where('category_slug', $categorySlug)->first() : null;
        $activeTag = $tagSlug !== '' ? Tag::where('tag_slug', $tagSlug)->first() : null;
        $activeBrand = $brandSlug !== '' ? Brand::where('brand_slug', $brandSlug)->first() : null;

        return view('ecommerce::pages.shop.index', compact('products', 'categories', 'brands', 'tags', 'activeCategory', 'activeTag', 'activeBrand', 'q', 'sort', 'perPage', 'categorySlug', 'tagSlug', 'brandSlug'));
    }

    public function show(string $slug): View
    {
        $product = Product::where('product_slug', $slug)
            ->where('is_active', true)
            ->with(['has_brand', 'has_category', 'has_satuan', 'has_tags'])
            ->firstOrFail();

        $related = Product::where('is_active', true)
            ->where('product_status', 'active')
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('product_id_category', $product->product_id_category)
                    ->orWhere('product_id_brand', $product->product_id_brand);
            })
            ->with(['has_brand', 'has_category'])
            ->orderByDesc('is_featured')
            ->limit(8)
            ->get();

        return view('ecommerce::pages.shop.show', compact('product', 'related'));
    }

    private function categoryWithDescendants(Category $category): array
    {
        $ids = [$category->id];
        $stack = [$category->id];

        while (! empty($stack)) {
            $parentId = array_pop($stack);
            $children = Category::where('parent_id', $parentId)->pluck('id')->all();
            foreach ($children as $cid) {
                $ids[] = $cid;
                $stack[] = $cid;
            }
        }

        return $ids;
    }
}
