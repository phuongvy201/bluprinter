<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $parentPages = Page::published()
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view('admin.pages.create', compact('parentPages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|date',
            'template' => 'required|string',
            'show_in_menu' => 'boolean',
            'menu_title' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:pages,id',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Page::generateSlug($validated['title']);
        $validated['show_in_menu'] = $request->has('show_in_menu');

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $page = Page::create($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully!');
    }

    public function edit(Page $page)
    {
        $parentPages = Page::published()
            ->whereNull('parent_id')
            ->where('id', '!=', $page->id)
            ->orderBy('title')
            ->get();

        return view('admin.pages.edit', compact('page', 'parentPages'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|date',
            'template' => 'required|string',
            'show_in_menu' => 'boolean',
            'menu_title' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:pages,id',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        if ($validated['title'] !== $page->title) {
            $validated['slug'] = Page::generateSlug($validated['title'], $page->id);
        }

        $validated['show_in_menu'] = $request->has('show_in_menu');

        if ($request->hasFile('featured_image')) {
            if ($page->featured_image) {
                Storage::disk('public')->delete($page->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $page->update($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully!');
    }

    public function destroy(Page $page)
    {
        if ($page->featured_image) {
            Storage::disk('public')->delete($page->featured_image);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully!');
    }

    /**
     * Upload image for TinyMCE editor
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('pages/content', 'public');
            $url = Storage::url($path);

            return response()->json([
                'location' => $url
            ]);
        }

        return response()->json([
            'error' => 'No file uploaded'
        ], 400);
    }
}
