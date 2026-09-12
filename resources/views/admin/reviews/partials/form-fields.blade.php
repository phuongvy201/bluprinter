@php
    $isEdit = !empty($review);
    $formAction = $isEdit ? route('admin.reviews.update', $review) : route('admin.reviews.store');
@endphp

<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isEdit ? 'Edit Review' : 'Add Review' }}</h1>
            <p class="mt-1 text-sm text-gray-600">Manual review with photos for product pages.</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to list</a>
    </div>

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <ul class="text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div>
            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
            <select name="product_id" id="product_id" required class="w-full rounded-lg border-gray-300">
                <option value="">Select product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id', $review?->product_id) == $product->id)>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Customer name</label>
                <input type="text" name="customer_name" id="customer_name" required
                       value="{{ old('customer_name', $review?->customer_name) }}"
                       class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Customer email</label>
                <input type="email" name="customer_email" id="customer_email"
                       value="{{ old('customer_email', $review?->customer_email) }}"
                       class="w-full rounded-lg border-gray-300">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <select name="rating" id="rating" required class="w-full rounded-lg border-gray-300">
                    @for($star = 5; $star >= 1; $star--)
                        <option value="{{ $star }}" @selected((int) old('rating', $review?->rating ?? 5) === $star)>{{ $star }} stars</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="review_date" class="block text-sm font-medium text-gray-700 mb-1">Review date</label>
                <input type="date" name="review_date" id="review_date"
                       value="{{ old('review_date', optional($review?->created_at)->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-300">
            </div>
            <div class="flex items-end gap-6 pb-1">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_verified_purchase" value="1"
                           @checked(old('is_verified_purchase', $review?->is_verified_purchase))>
                    Verified purchase
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_approved" value="1"
                           @checked(old('is_approved', $review?->is_approved ?? true))>
                    Approved
                </label>
            </div>
        </div>

        <div>
            <label for="review_text" class="block text-sm font-medium text-gray-700 mb-1">Review text</label>
            <textarea name="review_text" id="review_text" rows="5"
                      class="w-full rounded-lg border-gray-300">{{ old('review_text', $review?->review_text) }}</textarea>
        </div>

        @if($isEdit && !empty($review->images))
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">Current photos</p>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                    @foreach($review->images as $image)
                        <label class="relative block">
                            <img src="{{ $image }}" alt="" class="w-full h-20 object-cover rounded-lg border border-gray-200">
                            <input type="checkbox" name="existing_images[]" value="{{ $image }}" checked
                                   class="absolute top-2 left-2 rounded border-gray-300">
                            <span class="absolute bottom-1 left-1 text-[10px] bg-black/60 text-white px-1 rounded">Keep</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Uncheck to remove photo when saving.</p>
            </div>
        @endif

        <div>
            <label for="images" class="block text-sm font-medium text-gray-700 mb-1">Upload photos</label>
            <input type="file" name="images[]" id="images" accept="image/*" multiple
                   class="w-full text-sm text-gray-600">
            <p class="text-xs text-gray-500 mt-1">Up to 6 images, max 5MB each.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.reviews.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</div>
