@extends('layouts.admin')

@section('title', 'Product Templates Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Product Templates</h1>
            <p class="mt-1 text-sm text-gray-600">Manage product templates and base configurations</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.product-templates.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Template
            </a>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
            <!-- Template Image -->
            <div class="h-48 bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center relative overflow-hidden">
                @if($template->media && count($template->media) > 0)
                    @php $firstMedia = $template->media[0]; @endphp
                    @if(is_string($firstMedia))
                        @if(str_contains($firstMedia, '.mp4') || str_contains($firstMedia, '.mov') || str_contains($firstMedia, '.avi'))
                            {{-- Video Thumbnail --}}
                            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-purple-100 to-pink-100">
                                <div class="text-center">
                                    <div class="w-20 h-20 mx-auto mb-3 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-purple-700">Video Template</p>
                                    <p class="text-xs text-purple-600 mt-1">ID: #{{ $template->id }}</p>
                                </div>
                            </div>
                        @else
                            {{-- Image --}}
                            <img src="{{ $firstMedia }}" alt="{{ $template->name }}" class="h-full w-full object-cover">
                            <div class="absolute top-2 right-2 bg-black/50 backdrop-blur-sm text-white text-xs font-semibold px-2 py-1 rounded-lg">
                                #{{ $template->id }}
                            </div>
                        @endif
                    @elseif(is_array($firstMedia) && isset($firstMedia['url']))
                        <img src="{{ $firstMedia['url'] }}" alt="{{ $template->name }}" class="h-full w-full object-cover">
                        <div class="absolute top-2 right-2 bg-black/50 backdrop-blur-sm text-white text-xs font-semibold px-2 py-1 rounded-lg">
                            #{{ $template->id }}
                        </div>
                    @else
                        {{-- Fallback for unknown media type --}}
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-3 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">{{ Str::limit($template->name, 20) }}</p>
                            <p class="text-xs text-gray-600 mt-1">ID: #{{ $template->id }}</p>
                        </div>
                    @endif
                @else
                    {{-- No Media - Show nice icon with template info --}}
                    <div class="text-center px-4">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-xl transform hover:scale-105 transition-transform">
                            <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-gray-800">{{ Str::limit($template->name, 25) }}</p>
                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span class="text-xs font-semibold">ID: {{ $template->id }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Template Info -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $template->name }}</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                            #{{ $template->id }}
                        </span>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $template->category->name }}
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $template->description }}</p>
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-2xl font-bold text-gray-900">${{ number_format($template->base_price, 2) }}</div>
                        @if($template->user)
                        <div class="text-xs text-gray-500 mt-1">
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            by {{ $template->user->name }}
                        </div>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">{{ $template->products_count ?? 0 }} products</div>
                </div>

                <!-- Template Attributes Preview -->
                @if($template->attributes && count($template->attributes) > 0)
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($template->attributes->take(3) as $attribute)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $attribute->attribute_name }}: {{ $attribute->attribute_value }}
                            </span>
                        @endforeach
                        @if($template->attributes->count() > 3)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                +{{ $template->attributes->count() - 3 }} more
                            </span>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions -->
                @php
                    $canEdit = auth()->user()->hasRole('admin') || $template->user_id === auth()->id();
                @endphp
                
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.product-templates.show', $template) }}" 
                       class="inline-flex justify-center items-center px-3 py-2 text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors border border-gray-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View
                    </a>
                    
                    @if($canEdit)
                    <a href="{{ route('admin.product-templates.edit', $template) }}" 
                       class="inline-flex justify-center items-center px-3 py-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    @else
                    <span class="inline-flex justify-center items-center px-3 py-2 text-gray-400 bg-gray-50 rounded-lg border border-gray-200 cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Locked
                    </span>
                    @endif
                    
                    @if($canEdit)
                    <form method="POST" action="{{ route('admin.product-templates.clone', $template->id) }}" onsubmit="return confirm('Clone this template?')">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-3 py-2 text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors border border-green-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Clone
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.product-templates.destroy', $template) }}" onsubmit="return confirm('Are you sure you want to delete this template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors border border-red-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                    @else
                    <span class="inline-flex justify-center items-center px-3 py-2 text-gray-400 bg-gray-50 rounded-lg border border-gray-200 cursor-not-allowed col-span-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Admin Only
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl text-gray-400">📄</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No templates found</h3>
                <p class="text-gray-500 mb-6">Get started by creating a new product template.</p>
                <a href="{{ route('admin.product-templates.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    Add First Template
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
    <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-b-xl">
        <div class="flex-1 flex justify-between sm:hidden">
            @if($templates->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-not-allowed">
                    Previous
                </span>
            @else
                <a href="{{ $templates->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Previous
                </a>
            @endif

            @if($templates->hasMorePages())
                <a href="{{ $templates->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Next
                </a>
            @else
                <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-not-allowed">
                    Next
                </span>
            @endif
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-600">
                    Showing
                    <span class="font-semibold text-gray-900">{{ $templates->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900">{{ $templates->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900">{{ $templates->total() }}</span>
                    results
                </p>
            </div>
            <div>
                {{ $templates->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
