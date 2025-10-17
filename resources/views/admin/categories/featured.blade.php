@extends('layouts.admin')

@section('title', 'Featured Categories Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-5">
            <h3 class="text-lg font-semibold text-gray-900">Featured Categories Management</h3>
            <p class="text-sm text-gray-500 mt-1">Select up to 6 categories to display on the homepage</p>
        </div>
        
        <div class="p-6">
            <form action="{{ route('admin.categories.update-featured') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($categories as $category)
                        <div class="bg-white border-2 rounded-xl transition-all duration-200 hover:shadow-md {{ $category->featured ? 'border-blue-500 shadow-sm' : 'border-gray-200' }}">
                            <div class="p-5">
                                <div class="flex items-center mb-4">
                                    <input type="checkbox" 
                                           name="featured_categories[]" 
                                           value="{{ $category->id }}"
                                           {{ $category->featured ? 'checked' : '' }}
                                           id="category_{{ $category->id }}"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 category-checkbox">
                                    <label for="category_{{ $category->id }}" class="ml-3 text-sm font-bold text-gray-900 cursor-pointer">
                                        {{ $category->name }}
                                    </label>
                                </div>
                                
                                @if($category->image)
                                    <div class="mb-4">
                                        <img src="{{ $category->image }}" 
                                             alt="{{ $category->name }}" 
                                             class="w-full h-32 object-cover rounded-lg">
                                    </div>
                                @endif
                                
                                <p class="text-gray-500 text-xs mb-4 line-clamp-2">{{ $category->description }}</p>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sort Order</label>
                                        <input type="number" 
                                               name="sort_order[{{ $category->id }}]" 
                                               value="{{ $category->sort_order }}" 
                                               class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               min="0">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Products</label>
                                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $category->templates_count ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-8 flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Update Featured Categories
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Categories
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    const maxFeatured = 6;
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
            
            if (checkedCount > maxFeatured) {
                alert(`You can only select up to ${maxFeatured} featured categories.`);
                this.checked = false;
                return;
            }
            
            // Update card border based on selection
            const card = this.closest('div[class*="border-2"]');
            if (card) {
                if (this.checked) {
                    card.classList.remove('border-gray-200');
                    card.classList.add('border-blue-500', 'shadow-sm');
                } else {
                    card.classList.remove('border-blue-500', 'shadow-sm');
                    card.classList.add('border-gray-200');
                }
            }
        });
    });
});
</script>
@endsection
