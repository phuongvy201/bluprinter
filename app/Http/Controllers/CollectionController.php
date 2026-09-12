<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of collections
     */
    public function index(Request $request)
    {
        $collectionGroups = $this->buildCollectionGroups();
        $groupSlug = $request->get('group');

        $query = Collection::with(['shop'])
            ->withDisplayableProductsCount()
            ->global()
            ->active()
            ->approved()
            ->hasDisplayableProducts();

        if ($groupSlug) {
            $groupIds = $this->collectionIdsForGroup($groupSlug);
            if ($groupIds->isEmpty()) {
                abort(404);
            }
            $query->whereIn('id', $groupIds);
        }

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['manual', 'automatic'])) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'products':
                $query->orderBy('displayable_products_count', 'desc');
                break;
            case 'featured':
            default:
                $query->orderBy('featured', 'desc')
                    ->orderBy('sort_order')
                    ->latest();
                break;
        }

        $collections = $query->paginate(20)->withQueryString();

        $hero = config('catalog.collections_index.hero', []);

        return view('collections.index', compact(
            'collections',
            'collectionGroups',
            'groupSlug',
            'hero'
        ));
    }

    private function buildCollectionGroups()
    {
        $collections = Collection::query()
            ->withDisplayableProductsCount()
            ->global()
            ->active()
            ->approved()
            ->hasDisplayableProducts()
            ->orderBy('name')
            ->get(['id', 'name']);

        $groups = [];

        foreach ($collections as $collection) {
            $key = $this->collectionGroupKey($collection->name);

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'slug' => $key,
                    'label' => $this->collectionGroupLabel($collection->name),
                    'collections_count' => 0,
                    'products_count' => 0,
                ];
            }

            $groups[$key]['collections_count']++;
            $groups[$key]['products_count'] += (int) $collection->displayable_products_count;
        }

        return collect($groups)
            ->sortByDesc('products_count')
            ->filter(function ($group) {
                return $group['collections_count'] > 1
                    || str_contains(strtolower($group['label']), 'collection');
            })
            ->take(6)
            ->values();
    }

    private function collectionIdsForGroup(string $groupSlug)
    {
        return Collection::query()
            ->global()
            ->active()
            ->approved()
            ->hasDisplayableProducts()
            ->get(['id', 'name'])
            ->filter(fn ($collection) => $this->collectionGroupKey($collection->name) === $groupSlug)
            ->pluck('id');
    }

    private function collectionGroupKey(string $name): string
    {
        if (preg_match('/^(.+?\s+collection)\b/i', $name, $matches)) {
            return \Illuminate\Support\Str::slug($matches[1]);
        }

        $prefix = \Illuminate\Support\Str::before($name, ' - ');

        return \Illuminate\Support\Str::slug($prefix !== $name ? $prefix : \Illuminate\Support\Str::words($name, 2, ''));
    }

    private function collectionGroupLabel(string $name): string
    {
        if (preg_match('/^(.+?\s+collection)\b/i', $name, $matches)) {
            return \Illuminate\Support\Str::upper($matches[1]);
        }

        $prefix = \Illuminate\Support\Str::before($name, ' - ');

        return \Illuminate\Support\Str::upper($prefix !== $name ? $prefix : $name);
    }

    /**
     * Display the specified collection
     */
    public function show(Request $request, string $slug)
    {
        $collection = Collection::with(['shop', 'products.template.category'])
            ->where('slug', $slug)
            ->active()
            ->approved()
            ->firstOrFail();

        // Get products in this collection (chỉ lấy đủ điều kiện hiển thị)
        $query = $collection->activeProducts()
            ->availableForDisplay()
            ->with(['template.category', 'shop']);

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('template.category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'default');
        switch ($sortBy) {
            case 'price_low':
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_high':
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            case 'newest':
                $query->orderBy('products.created_at', 'desc');
                break;
            case 'featured':
            case 'default':
            default:
                $query->orderBy('product_collection.sort_order');
                break;
        }

        $products = $query->paginate(20)->withQueryString();

        $filterCategories = Category::query()
            ->whereIn('id', $collection->activeProducts()
                ->availableForDisplay()
                ->join('product_templates', 'products.template_id', '=', 'product_templates.id')
                ->pluck('product_templates.category_id')
                ->unique()
                ->filter())
            ->orderBy('name')
            ->get();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Collections', 'url' => route('collections.index')],
            ['name' => $collection->name, 'url' => null],
        ];

        return view('collections.show', compact(
            'collection',
            'products',
            'filterCategories',
            'breadcrumbs'
        ));
    }
}
