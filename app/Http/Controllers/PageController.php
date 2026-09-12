<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    /**
     * Display the specified page
     */
    public function show(string $slug)
    {
        // Redirect shipping-delivery to dedicated route
        if ($slug === 'shipping-delivery') {
            return redirect()->route('shipping-delivery.index');
        }

        $page = Page::where('slug', $slug)
            ->published()
            ->firstOrFail();

        $page->incrementViews();

        $childPages = $page->children()
            ->published()
            ->get();

        return view($page->resolveTemplateView(), compact('page', 'childPages'));
    }
}
