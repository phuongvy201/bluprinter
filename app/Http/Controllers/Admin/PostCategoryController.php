<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostCategoryController extends Controller
{
    /**
     * Display a listing of post categories
     */
    public function index()
    {
        $categories = PostCategory::with(['parent', 'children'])
            ->withCount('posts')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.post-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new post category
     */
    public function create()
    {
        $parentCategories = PostCategory::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.post-categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created post category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:post_categories,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->all();
        $data['slug'] = PostCategory::generateSlug($request->name);

        // Upload image to S3 if provided
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = Storage::disk('s3')->putFileAs('post-categories', $file, $fileName);
            $data['image'] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/post-categories/' . $fileName;
        }

        PostCategory::create($data);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Post category created successfully!');
    }

    /**
     * Show the form for editing the specified post category
     */
    public function edit(PostCategory $postCategory)
    {
        $parentCategories = PostCategory::whereNull('parent_id')
            ->where('id', '!=', $postCategory->id)
            ->orderBy('name')
            ->get();

        return view('admin.post-categories.edit', compact('postCategory', 'parentCategories'));
    }

    /**
     * Update the specified post category
     */
    public function update(Request $request, PostCategory $postCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:post_categories,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->all();

        // Update slug if name changed
        if ($request->name !== $postCategory->name) {
            $data['slug'] = PostCategory::generateSlug($request->name, $postCategory->id);
        }

        // Upload new image to S3 if provided
        if ($request->hasFile('image')) {
            // Delete old image
            if ($postCategory->image) {
                $oldPath = str_replace('https://s3.us-east-1.amazonaws.com/image.bluprinter/', '', $postCategory->image);
                Storage::disk('s3')->delete($oldPath);
            }

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = Storage::disk('s3')->putFileAs('post-categories', $file, $fileName);
            $data['image'] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/post-categories/' . $fileName;
        }

        $postCategory->update($data);

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Post category updated successfully!');
    }

    /**
     * Remove the specified post category
     */
    public function destroy(PostCategory $postCategory)
    {
        // Check if category has posts
        if ($postCategory->posts()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with posts. Please move or delete posts first.');
        }

        // Check if category has children
        if ($postCategory->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        // Delete image from S3
        if ($postCategory->image) {
            $oldPath = str_replace('https://s3.us-east-1.amazonaws.com/image.bluprinter/', '', $postCategory->image);
            Storage::disk('s3')->delete($oldPath);
        }

        $postCategory->delete();

        return redirect()->route('admin.post-categories.index')
            ->with('success', 'Post category deleted successfully!');
    }
}
