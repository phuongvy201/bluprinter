<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\Category;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    /**
     * Display a listing of shipping rates.
     */
    public function index(Request $request)
    {
        $query = ShippingRate::with(['shippingZone', 'category']);

        // Filter by zone
        if ($request->filled('zone_id')) {
            $query->where('shipping_zone_id', $request->zone_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            if ($request->category_id === 'null') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $request->category_id);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $rates = $query->ordered()->paginate(20);
        $zones = ShippingZone::ordered()->get();
        $categories = $this->getCategoriesHierarchy();

        return view('admin.shipping-rates.index', compact('rates', 'zones', 'categories'));
    }

    /**
     * Show the form for creating a new shipping rate.
     */
    public function create()
    {
        $zones = ShippingZone::active()->ordered()->get();
        $categories = $this->getCategoriesHierarchy();

        return view('admin.shipping-rates.create', compact('zones', 'categories'));
    }

    /**
     * Get categories in hierarchical format for dropdown
     */
    protected function getCategoriesHierarchy()
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $flatCategories = [];

        foreach ($categories as $category) {
            $flatCategories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'level' => 0
            ];

            // Add children
            if ($category->children->count() > 0) {
                foreach ($category->children->sortBy('name') as $child) {
                    $flatCategories[] = [
                        'id' => $child->id,
                        'name' => $child->name,
                        'level' => 1,
                        'parent' => $category->name
                    ];
                }
            }
        }

        return collect($flatCategories);
    }

    /**
     * Store a newly created shipping rate in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_zone_id' => 'required|exists:shipping_zones,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'first_item_cost' => 'required|numeric|min:0',
            'additional_item_cost' => 'required|numeric|min:0',
            'min_items' => 'nullable|integer|min:1',
            'max_items' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_order_value' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ShippingRate::create($validated);

        return redirect()->route('admin.shipping-rates.index')
            ->with('success', 'Shipping rate created successfully!');
    }

    /**
     * Display the specified shipping rate.
     */
    public function show(ShippingRate $shippingRate)
    {
        $shippingRate->load(['shippingZone', 'category']);

        return view('admin.shipping-rates.show', compact('shippingRate'));
    }

    /**
     * Show the form for editing the specified shipping rate.
     */
    public function edit(ShippingRate $shippingRate)
    {
        $zones = ShippingZone::active()->ordered()->get();
        $categories = $this->getCategoriesHierarchy();

        return view('admin.shipping-rates.edit', compact('shippingRate', 'zones', 'categories'));
    }

    /**
     * Update the specified shipping rate in storage.
     */
    public function update(Request $request, ShippingRate $shippingRate)
    {
        $validated = $request->validate([
            'shipping_zone_id' => 'required|exists:shipping_zones,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'first_item_cost' => 'required|numeric|min:0',
            'additional_item_cost' => 'required|numeric|min:0',
            'min_items' => 'nullable|integer|min:1',
            'max_items' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_order_value' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $shippingRate->update($validated);

        return redirect()->route('admin.shipping-rates.index')
            ->with('success', 'Shipping rate updated successfully!');
    }

    /**
     * Remove the specified shipping rate from storage.
     */
    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();

        return redirect()->route('admin.shipping-rates.index')
            ->with('success', 'Shipping rate deleted successfully!');
    }
}
