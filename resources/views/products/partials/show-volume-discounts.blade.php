{{-- Buy More Save More — expects $volumeDiscountTiers from product show settings --}}
@if(!empty($volumeDiscountTiers) && count($volumeDiscountTiers) > 0)
    <div class="product-show-volume" data-volume-discounts>
        <div class="product-show-volume__head">
            <h3 class="product-show-volume__title">Buy More, Save More</h3>
            <p class="product-show-volume__note">Mix and match different products in your order — these volume discounts apply across items in your cart, not just this product.</p>
        </div>
        <div class="product-show-volume__grid">
            @foreach($volumeDiscountTiers as $index => $tier)
                <button
                    type="button"
                    class="product-show-volume__tier{{ ($tier['is_popular'] ?? false) ? ' product-show-volume__tier--popular' : '' }}"
                    data-volume-tier="{{ $index }}"
                    data-min-quantity="{{ (int) ($tier['min_quantity'] ?? 0) }}"
                    data-discount-percent="{{ (int) ($tier['discount_percent'] ?? 0) }}"
                    onclick="selectVolumeTier({{ $index }})"
                >
                    @if($tier['is_popular'] ?? false)
                        <span class="product-show-volume__popular-badge">Popular</span>
                    @endif
                    <span class="product-show-volume__qty">Buy {{ (int) ($tier['min_quantity'] ?? 0) }}</span>
                    <span class="product-show-volume__off">{{ (int) ($tier['discount_percent'] ?? 0) }}% OFF</span>
                </button>
            @endforeach
        </div>
    </div>
@endif
