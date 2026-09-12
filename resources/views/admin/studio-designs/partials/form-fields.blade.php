<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name', $design->name) }}" required class="w-full rounded-lg border-gray-300">
    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
        <input type="text" name="tag" value="{{ old('tag', $design->tag) }}" class="w-full rounded-lg border-gray-300" placeholder="Camping, Retro, Typography…">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Price (USD)</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $design->price) }}" required class="w-full rounded-lg border-gray-300">
        <p class="text-xs text-gray-500 mt-1">Added on top of the garment size price when a customer picks this design.</p>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
    <input type="text" name="image_url" value="{{ old('image_url', $design->image_url) }}" class="w-full rounded-lg border-gray-300" placeholder="Or upload a file below">
    <p class="text-xs text-gray-500 mt-1">The original is stored for print. Customers and this grid only receive a ~480px preview.</p>
    @if($design->preview_url || $design->image_url)
        <img src="{{ $design->preview_url ?: $design->image_url }}" alt="" class="mt-2 h-24 object-contain" draggable="false">
    @endif
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Upload image</label>
    <input type="file" name="image_file" accept="image/*" class="w-full text-sm">
    <p class="text-xs text-gray-500 mt-1">PNG, JPG, or WEBP up to 100 MB. Customers still see a ~480px preview only.</p>
    @error('image_file')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $design->sort_order ?? 0) }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div class="flex items-end pb-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $design->is_active))>
            Active in library
        </label>
    </div>
</div>
