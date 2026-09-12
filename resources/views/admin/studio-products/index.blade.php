@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Studio garments</h1>
            <p class="text-sm text-gray-500 mt-1">Upload mockup photos and draw the print box. Customers drop their design into that area on Create Your Own.</p>
        </div>
        <a href="{{ route('admin.studio-products.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            Add garment type
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Garment</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Size prices</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Mockups</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Order</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($studioProducts as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $item->name ?: 'Untitled garment' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->product ? 'Fulfillment: '.$item->product->name : 'No catalog SKU linked' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->category ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                @php $sizePrices = $item->normalizedSizePrices(); @endphp
                                @if($sizePrices)
                                    {{ collect($sizePrices)->map(fn ($price, $size) => $size.' $'.number_format((float) $price, 2))->implode(', ') }}
                                @else
                                    ${{ number_format((float) $item->price, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $item->mockups_count }}</td>
                            <td class="px-4 py-3">{{ $item->sort_order }}</td>
                            <td class="px-4 py-3">
                                @if($item->is_active)
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Hidden</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.studio-products.edit', $item) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                                <form action="{{ route('admin.studio-products.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Remove this studio product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No garment types yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($studioProducts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $studioProducts->links() }}</div>
        @endif
    </div>
</div>
@endsection
