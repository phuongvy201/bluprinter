@props([
    'href',
    'active' => false,
    'danger' => false,
])

@php
    $state = $danger
        ? ($active ? 'bg-red-50 text-red-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900')
        : ($active ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900');
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'flex items-center px-3 py-2 text-sm rounded-lg transition-colors '.$state]) }}
   @click="sidebarOpen = false">
    {{ $slot }}
</a>
