@props([
    'id',
    'label',
    'active' => false,
    'icon' => 'folder',
])

@php
    $icons = [
        'overview' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'products' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'studio' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        'sales' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'shipping' => 'M8 17a2 2 0 104 0m5 0a2 2 0 104 0M3 7h11v10H3V7zm11 3h4l3 3v4h-7v-7z',
        'content' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
        'blog' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'tools' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'pinned' => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
        'folder' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
    ];
    $iconPath = $icons[$icon] ?? $icons['folder'];
@endphp

<div class="mb-1" data-sidebar-group="{{ $id }}" x-show="groupVisible('{{ $id }}')" x-cloak>
    <button type="button"
            @click="toggleGroup('{{ $id }}')"
            :title="sidebarCollapsed ? @js($label) : null"
            class="group/btn relative w-full flex items-center gap-2.5 px-2.5 py-2.5 text-[11px] font-bold tracking-wider uppercase rounded-lg transition-all
                   {{ $active ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}
                   {{ $active ? 'admin-nav-group--active' : '' }}">
        @if($active)
            <span class="absolute left-0 top-1.5 bottom-1.5 w-[3px] rounded-r-full bg-blue-600" aria-hidden="true"></span>
        @endif
        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500 group-hover/btn:bg-gray-200 group-hover/btn:text-gray-700' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
            </svg>
        </span>
        <span class="flex-1 text-left truncate" x-show="!$store.adminUi.sidebarCollapsed" x-cloak>{{ $label }}</span>
        <svg class="w-4 h-4 text-current transition-transform duration-200 shrink-0" x-show="!$store.adminUi.sidebarCollapsed" x-cloak :class="isGroupOpen('{{ $id }}') ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="isGroupOpen('{{ $id }}') && !$store.adminUi.sidebarCollapsed" x-cloak class="mt-0.5 mb-2 space-y-0.5 pl-3 border-l border-gray-100 ml-5">
        {{ $slot }}
    </div>
</div>
