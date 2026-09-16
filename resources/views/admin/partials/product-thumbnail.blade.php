@php
    $size = $size ?? 'w-12 h-12';
    $url = $product->adminThumbnailUrl();
@endphp
@if ($url)
    <img src="{{ $url }}" alt="{{ $product->name ?? '' }}" class="{{ $size }} rounded-lg object-cover border border-gray-200 shrink-0 bg-gray-50">
@else
    <div class="{{ $size }} rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shrink-0 border border-gray-200">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
    </div>
@endif
