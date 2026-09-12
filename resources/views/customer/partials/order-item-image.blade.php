@php
    $size = $size ?? 'w-16 h-16';
    $url = $item->resolveDisplayImage();
@endphp
@if($url !== '')
    <img src="{{ $url }}" alt="{{ $item->product_name }}" class="{{ $size }} object-cover rounded-lg border border-gray-200">
@else
    <div class="{{ $size }} bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
    </div>
@endif
