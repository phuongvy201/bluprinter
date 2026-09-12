@props([
    'id',
    'label',
    'active' => false,
])

<div class="mb-0.5">
    <button type="button"
            @click="open = open === '{{ $id }}' ? '' : '{{ $id }}'"
            class="w-full flex items-center justify-between px-3 py-2.5 text-[11px] font-bold tracking-wider uppercase rounded-lg transition-colors {{ $active ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
        <span>{{ $label }}</span>
        <svg class="w-4 h-4 text-current transition-transform duration-200 shrink-0" :class="open === '{{ $id }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="open === '{{ $id }}'" x-cloak class="mt-0.5 mb-2 space-y-0.5 pl-1">
        {{ $slot }}
    </div>
</div>
