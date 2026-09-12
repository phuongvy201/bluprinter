@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Reviews</h1>
            <p class="mt-1 text-sm text-gray-600">Add reviews manually or import from CSV/Excel.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.reviews.import') }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                Import Reviews
            </a>
            <a href="{{ route('admin.reviews.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Add Review
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif
    @if (session('import_errors'))
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-900">
            <p class="font-semibold mb-2">Import warnings:</p>
            <ul class="text-sm space-y-1">
                @foreach(session('import_errors') as $importError)
                    <li>{{ $importError }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-300 text-sm" placeholder="Name, text, product...">
            </div>
            <div>
                <label for="product_id" class="block text-xs font-medium text-gray-500 mb-1">Product</label>
                <select name="product_id" id="product_id" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="approved" class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="approved" id="approved" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All</option>
                    <option value="1" @selected(request('approved') === '1')>Approved</option>
                    <option value="0" @selected(request('approved') === '0')>Hidden</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-800">Filter</button>
                <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Review</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Photos</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900 max-w-[200px]">
                                <div class="font-medium truncate">{{ $review->product?->name }}</div>
                                @if($review->product?->shop)
                                    <div class="text-xs text-gray-500">{{ $review->product->shop->shop_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div>{{ $review->display_name }}</div>
                                @if($review->is_verified_purchase)
                                    <span class="text-xs text-green-600 font-medium">Verified</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-amber-500 font-semibold">{{ $review->rating }}/5</td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $review->review_text }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($review->images))
                                    <div class="flex gap-1">
                                        @foreach(array_slice($review->images, 0, 3) as $image)
                                            <img src="{{ $image }}" alt="" class="w-10 h-10 rounded object-cover border border-gray-200">
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($review->is_approved)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Approved</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Hidden</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $review->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('admin.reviews.edit', $review) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline-block ml-3"
                                      onsubmit="return confirm('Delete this review?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">No reviews found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $reviews->links() }}</div>
        @endif
    </div>
</div>
@endsection
