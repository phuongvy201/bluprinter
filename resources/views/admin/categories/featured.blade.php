@extends('layouts.admin')

@section('title', 'Featured Categories Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Featured Categories Management</h3>
                    <p class="text-muted">Select up to 6 categories to display on the homepage</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update-featured') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            @foreach($categories as $category)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 {{ $category->featured ? 'border-primary' : '' }}">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="featured_categories[]" 
                                                           value="{{ $category->id }}"
                                                           {{ $category->featured ? 'checked' : '' }}
                                                           id="category_{{ $category->id }}">
                                                    <label class="form-check-label fw-bold" for="category_{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            @if($category->image)
                                                <div class="mb-3">
                                                    <img src="{{ $category->image }}" 
                                                         alt="{{ $category->name }}" 
                                                         class="img-fluid rounded" 
                                                         style="max-height: 120px; width: 100%; object-fit: cover;">
                                                </div>
                                            @endif
                                            
                                            <p class="text-muted small mb-3">{{ $category->description }}</p>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <label class="form-label small">Sort Order</label>
                                                    <input type="number" 
                                                           name="sort_order[{{ $category->id }}]" 
                                                           value="{{ $category->sort_order }}" 
                                                           class="form-control form-control-sm"
                                                           min="0">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Products</label>
                                                    <span class="badge bg-info">{{ $category->templates_count ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Featured Categories
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Categories
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="featured_categories[]"]');
    const maxFeatured = 6;
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[name="featured_categories[]"]:checked').length;
            
            if (checkedCount > maxFeatured) {
                alert(`You can only select up to ${maxFeatured} featured categories.`);
                this.checked = false;
                return;
            }
            
            // Update card border based on selection
            const card = this.closest('.card');
            if (this.checked) {
                card.classList.add('border-primary');
            } else {
                card.classList.remove('border-primary');
            }
        });
    });
});
</script>
@endsection
