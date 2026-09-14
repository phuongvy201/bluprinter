@props([
    'href',
    'active' => false,
    'danger' => false,
    'keywords' => '',
    'label' => null,
])

@php
    $resolvedLabel = $label ?? trim(preg_replace('/\s+/u', ' ', strip_tags($slot->toHtml())));
    // Strip trailing badge numbers for clean pin labels
    $pinLabel = trim(preg_replace('/\s+\d+$/', '', $resolvedLabel));
    $searchText = strtolower(trim($pinLabel.' '.$keywords));
    $base = $danger
        ? ($active
            ? 'bg-red-50 text-red-700 font-semibold'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900')
        : ($active
            ? 'bg-blue-50 text-blue-700 font-semibold'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900');
@endphp

<div class="relative group/link"
     data-sidebar-link-wrap
     x-show="linkVisible(@js($searchText))"
     x-cloak>
    <a href="{{ $href }}"
       data-sidebar-link
       data-search="{{ e($searchText) }}"
       data-label="{{ e($pinLabel) }}"
       data-href="{{ e($href) }}"
       {{ $attributes->merge([
           'class' => 'relative flex items-center gap-2 pl-3 pr-8 py-2 text-sm rounded-lg transition-colors leading-snug '.$base.
               ($active ? ' admin-nav-link--active' : ''),
       ]) }}
       :class="matchClass(@js($searchText))"
       @click="$store.adminUi.sidebarOpen = false">
        @if($active)
            <span class="absolute left-0 top-1.5 bottom-1.5 w-[3px] rounded-r-full {{ $danger ? 'bg-red-500' : 'bg-blue-600' }}" aria-hidden="true"></span>
        @endif
        <span class="min-w-0 flex-1 truncate">{{ $slot }}</span>
    </a>
    <button type="button"
            class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1 rounded opacity-0 group-hover/link:opacity-100 focus:opacity-100 transition-opacity
                   {{ $active ? 'text-blue-500 hover:text-blue-700' : 'text-gray-300 hover:text-amber-500' }}"
            :class="isPinned(@js($href)) ? 'opacity-100 text-amber-500' : ''"
            @click.prevent.stop="togglePin({ href: @js($href), label: @js($pinLabel), search: @js($searchText) })"
            :title="isPinned(@js($href)) ? 'Unpin from favorites' : 'Pin to favorites'"
            :aria-pressed="isPinned(@js($href)) ? 'true' : 'false'">
        <svg class="w-3.5 h-3.5" :fill="isPinned(@js($href)) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        <span class="sr-only">Pin</span>
    </button>
</div>
