<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the specified page
     */
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment views
        $page->incrementViews();

        // Get child pages if any
        $childPages = $page->children()
            ->published()
            ->get();

        return view('pages.show', compact('page', 'childPages'));
    }
}
