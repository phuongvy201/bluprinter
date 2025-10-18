<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostTag;
use Illuminate\Http\Request;

class PostTagController extends Controller
{
    /**
     * Display a listing of post tags
     */
    public function index()
    {
        $tags = PostTag::withCount('posts')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.post-tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new post tag
     */
    public function create()
    {
        return view('admin.post-tags.create');
    }

    /**
     * Store a newly created post tag
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        $data = $request->all();
        $data['slug'] = PostTag::generateSlug($request->name);

        PostTag::create($data);

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Post tag created successfully!');
    }

    /**
     * Show the form for editing the specified post tag
     */
    public function edit(PostTag $postTag)
    {
        return view('admin.post-tags.edit', compact('postTag'));
    }

    /**
     * Update the specified post tag
     */
    public function update(Request $request, PostTag $postTag)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        $data = $request->all();

        // Update slug if name changed
        if ($request->name !== $postTag->name) {
            $data['slug'] = PostTag::generateSlug($request->name, $postTag->id);
        }

        $postTag->update($data);

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Post tag updated successfully!');
    }

    /**
     * Remove the specified post tag
     */
    public function destroy(PostTag $postTag)
    {
        // Check if tag is used in posts
        if ($postTag->posts()->count() > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete tag '{$postTag->name}' because it is used in {$postTag->posts()->count()} post(s).");
        }

        $postTag->delete();

        return redirect()->route('admin.post-tags.index')
            ->with('success', 'Post tag deleted successfully!');
    }
}
