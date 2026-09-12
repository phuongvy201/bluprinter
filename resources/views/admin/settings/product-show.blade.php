@extends('layouts.admin')

@section('title', 'Product Page Settings')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Product page settings</h1>
        <p class="mt-1 text-sm text-gray-600">Configure Buy More Save More tiers, sale end date, and virtual social proof stats.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.product-show.update') }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Sale</h2>
            <div>
                <label for="sale_ends_date" class="block text-sm font-medium text-gray-700 mb-1">Sale ends date</label>
                <input type="date" name="sale_ends_date" id="sale_ends_date"
                       value="{{ old('sale_ends_date', $settings['sale_ends_date'] ?? '') }}"
                       class="w-full max-w-xs rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">Shown next to the discount badge on product pages.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Buy More, Save More</h2>
                <button type="button" id="add-tier-btn" class="text-sm font-medium text-blue-600 hover:text-blue-800">Add tier</button>
            </div>
            <div id="volume-tiers" class="space-y-3">
                @php $tiers = old('volume_discounts', $settings['volume_discounts'] ?? []); @endphp
                @forelse($tiers as $index => $tier)
                    <div class="tier-row grid grid-cols-1 sm:grid-cols-4 gap-3 items-end border border-gray-100 rounded-lg p-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Min quantity</label>
                            <input type="number" name="volume_discounts[{{ $index }}][min_quantity]" min="2" max="999"
                                   value="{{ $tier['min_quantity'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Discount %</label>
                            <input type="number" name="volume_discounts[{{ $index }}][discount_percent]" min="1" max="90"
                                   value="{{ $tier['discount_percent'] ?? '' }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex items-center gap-2 h-10">
                            <input type="hidden" name="volume_discounts[{{ $index }}][is_popular]" value="0">
                            <input type="checkbox" name="volume_discounts[{{ $index }}][is_popular]" value="1"
                                   {{ ($tier['is_popular'] ?? false) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600">
                            <label class="text-sm text-gray-700">Popular badge</label>
                        </div>
                        <button type="button" class="remove-tier text-sm text-red-600 hover:text-red-800 h-10">Remove</button>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No tiers yet. Click “Add tier” to create volume discounts.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Virtual stats (social proof)</h2>
            <p class="text-sm text-gray-600">Used for views and in-cart counts on product pages. Formula: base + (product ID × multiplier).</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Views base</label>
                    <input type="number" name="virtual_stats[views_base]" min="0"
                           value="{{ old('virtual_stats.views_base', $settings['virtual_stats']['views_base'] ?? 800) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Views multiplier</label>
                    <input type="number" name="virtual_stats[views_multiplier]" min="0"
                           value="{{ old('virtual_stats.views_multiplier', $settings['virtual_stats']['views_multiplier'] ?? 37) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">In-cart base</label>
                    <input type="number" name="virtual_stats[in_cart_base]" min="0"
                           value="{{ old('virtual_stats.in_cart_base', $settings['virtual_stats']['in_cart_base'] ?? 40) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">In-cart multiplier</label>
                    <input type="number" name="virtual_stats[in_cart_multiplier]" min="0"
                           value="{{ old('virtual_stats.in_cart_multiplier', $settings['virtual_stats']['in_cart_multiplier'] ?? 1) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
                Save settings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('volume-tiers');
    var addBtn = document.getElementById('add-tier-btn');
    if (!container || !addBtn) return;

    addBtn.addEventListener('click', function () {
        var index = container.querySelectorAll('.tier-row').length;
        var row = document.createElement('div');
        row.className = 'tier-row grid grid-cols-1 sm:grid-cols-4 gap-3 items-end border border-gray-100 rounded-lg p-3';
        row.innerHTML = `
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Min quantity</label>
                <input type="number" name="volume_discounts[${index}][min_quantity]" min="2" max="999" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Discount %</label>
                <input type="number" name="volume_discounts[${index}][discount_percent]" min="1" max="90" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="flex items-center gap-2 h-10">
                <input type="hidden" name="volume_discounts[${index}][is_popular]" value="0">
                <input type="checkbox" name="volume_discounts[${index}][is_popular]" value="1" class="rounded border-gray-300 text-blue-600">
                <label class="text-sm text-gray-700">Popular badge</label>
            </div>
            <button type="button" class="remove-tier text-sm text-red-600 hover:text-red-800 h-10">Remove</button>
        `;
        container.appendChild(row);
    });

    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-tier')) {
            e.target.closest('.tier-row').remove();
        }
    });
});
</script>
@endsection
