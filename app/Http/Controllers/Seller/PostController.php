<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Admin xem tất cả posts, Seller chỉ xem của mình
        $query = Post::with(['category', 'tags', 'shop', 'user']);

        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        // Filter by status (Admin only)
        if ($user->hasRole('admin') && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->latest()->paginate(20)->withQueryString();

        return view('seller.posts.index', compact('posts'));
    }

    /**
     * Approve post (Admin only)
     */
    public function approve(Post $post)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(), // Set published_at nếu chưa có
        ]);

        return back()->with('success', 'Post approved and published successfully!');
    }

    /**
     * Reject post (Admin only)
     */
    public function reject(Post $post)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $post->update(['status' => 'draft']);

        return back()->with('success', 'Post rejected and set to draft.');
    }

    public function create()
    {
        $categories = PostCategory::orderBy('name')->get();
        $tags = PostTag::orderBy('name')->get();
        $shop = auth()->user()->shop;

        return view('seller.posts.create', compact('categories', 'tags', 'shop'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:2048',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|date',
            'type' => 'required|in:article,video,gallery,product_review',
            'featured' => 'boolean',
            'allow_comments' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['shop_id'] = auth()->user()->shop?->id;
        $validated['slug'] = Post::generateSlug($validated['title']);
        $validated['featured'] = $request->has('featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Set published_at to now if not provided and status is published
        if (!isset($validated['published_at']) && $validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        // Set status to pending if seller is publishing
        if ($validated['status'] === 'published' && !auth()->user()->hasRole('admin')) {
            $validated['status'] = 'pending';
        }

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store('posts/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        $post = Post::create($validated);

        // Attach tags
        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

        // Calculate reading time
        $post->updateReadingTime();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully!' . ($validated['status'] === 'pending' ? ' (Pending admin approval)' : ''));
    }

    public function edit(Post $post)
    {
        // Check if user can edit this post
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $categories = PostCategory::orderBy('name')->get();
        $tags = PostTag::orderBy('name')->get();
        $shop = auth()->user()->shop;

        return view('seller.posts.edit', compact('post', 'categories', 'tags', 'shop'));
    }

    public function update(Request $request, Post $post)
    {
        // Check if user can edit this post
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:2048',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'status' => 'required|in:published,draft,scheduled',
            'published_at' => 'nullable|date',
            'type' => 'required|in:article,video,gallery,product_review',
            'featured' => 'boolean',
            'allow_comments' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        if ($validated['title'] !== $post->title) {
            $validated['slug'] = Post::generateSlug($validated['title'], $post->id);
        }

        $validated['featured'] = $request->has('featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Set published_at to now if not provided and status is published
        if (!isset($validated['published_at']) && $validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        // Set status to pending if seller is publishing
        if ($validated['status'] === 'published' && !auth()->user()->hasRole('admin') && $post->status !== 'published') {
            $validated['status'] = 'pending';
        }

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            // Delete old gallery images
            if ($post->gallery) {
                foreach ($post->gallery as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store('posts/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        $post->update($validated);

        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        } else {
            $post->tags()->detach();
        }

        // Update reading time
        $post->updateReadingTime();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully!');
    }

    public function destroy(Post $post)
    {
        // Check if user can delete this post
        if ($post->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        if ($post->gallery) {
            foreach ($post->gallery as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
