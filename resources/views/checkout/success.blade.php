@extends('layouts.app')

@section('title', 'Order Confirmation - Bluprinter')

@section('content')
<script>
// Track Facebook Pixel Purchase event
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        // Collect product IDs from order items
        const productIds = [
            @foreach($order->items as $item)
                '{{ $item->product_id }}'{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];
        
        // Track Purchase event
        fbq('track', 'Purchase', {
            content_ids: productIds,
            content_type: 'product',
            value: '{{ $order->total_amount }}',
            currency: '{{ $currency ?? "USD" }}',
            transaction_id: '{{ $order->order_number }}',
            num_items: {{ $order->items->count() }}
        });
        
        console.log('✅ Facebook Pixel: Purchase tracked', {
            order: '{{ $order->order_number }}',
            total: '{{ $order->total_amount }}',
            items: {{ $order->items->count() }}
        });
        
        // Clear cart from localStorage after successful purchase
        localStorage.removeItem('cart');
        
        // Dispatch cart updated event to update header
        window.dispatchEvent(new CustomEvent('cartUpdated'));
    }

    // Event tracking được xử lý bởi GTM thông qua dataLayer
    if (typeof dataLayer !== 'undefined') {
        @php
            $gaItems = $order->items->map(function($item, $index) {
                return [
                    'item_id' => (string) $item->product_id,
                    'item_name' => $item->product_name,
                    'price' => (float) $item->unit_price,
                    'quantity' => (int) $item->quantity,
                    'index' => $index + 1
                ];
            })->values()->toArray();
        @endphp
        const gaItems = @json($gaItems);

        dataLayer.push({
            'event': 'purchase',
            'currency': '{{ $currency ?? "USD" }}',
            'transaction_id': '{{ $order->order_number }}',
            'value': Number('{{ $order->total_amount }}'),
            'tax': Number('{{ $order->tax_amount }}'),
            'shipping': Number('{{ $order->shipping_cost }}'),
            items: gaItems
        });

        console.log('✅ Google Tag: purchase tracked', {
            order: '{{ $order->order_number }}',
            total: '{{ $order->total_amount }}',
            items: gaItems.length
        });
    }

    if (typeof window !== 'undefined' && window.ttq) {
        const tiktokOrderContents = {!! $order->items->map(function($item) {
            return [
                'content_id' => (string) $item->product_id,
                'content_type' => 'product',
                'content_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->unit_price,
            ];
        })->values()->toJson(JSON_UNESCAPED_UNICODE) !!};

        const tiktokOrderValue = Number('{{ $order->total_amount }}') || 0;
        const tiktokPayloadBase = {
            contents: Array.isArray(tiktokOrderContents) ? tiktokOrderContents : [],
            value: tiktokOrderValue,
            currency: '{{ $currency ?? "USD" }}',
            order_id: '{{ $order->order_number }}'
        };

        const paymentMethod = {!! json_encode($order->payment_method ?? null) !!};
        if (paymentMethod) {
            tiktokPayloadBase.payment_method = paymentMethod;
        }

        try {
            window.ttq.track('PlaceAnOrder', tiktokPayloadBase);
            window.ttq.track('Purchase', Object.assign({}, tiktokPayloadBase));
        } catch (error) {
            console.error('TikTok Purchase tracking error:', error);
        }
    }
});
</script>

<section class="commerce-page" aria-labelledby="success-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="catalog-breadcrumb__current">Order Confirmation</span>
        </nav>

        <div class="commerce-success-hero">
            <div class="commerce-success-icon">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <header class="section-heading section-heading--catalog">
                <p class="section-heading__eyebrow">Thank you</p>
                <h1 id="success-heading" class="section-heading__title">
                    Order <span class="gradient-text">Confirmed</span>
                </h1>
                <p class="section-heading__sub">Thank you for your purchase. Your order has been successfully placed.</p>
            </header>

            <div class="commerce-order-chip">
                <p class="text-sm text-gray-600 mb-2">Order Number</p>
                <p class="text-2xl font-bold text-[#005366]">{{ $order->order_number }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <div class="commerce-card">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Order Details</h2>
                    
                    <!-- Customer Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Customer Information</h3>
                        <div class="space-y-2 text-gray-600">
                            <p><span class="font-medium">Name:</span> {{ $order->customer_name }}</p>
                            <p><span class="font-medium">Email:</span> {{ $order->customer_email }}</p>
                            @if($order->customer_phone)
                                <p><span class="font-medium">Phone:</span> {{ $order->customer_phone }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Shipping Address</h3>
                        <div class="text-gray-600">
                            <p>{{ $order->shipping_address }}</p>
                            <p>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
                            <p>{{ $order->country }}</p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Order Items</h3>
                        <div class="space-y-3">
                            @foreach($order->items as $item)
                                @php
                                    $product = $item->product;
                                    $imageUrl = data_get($item->product_options, 'customizations._studio.image');
                                    
                                    if (!$imageUrl && $product) {
                                        $media = $product->getEffectiveMedia();
                                        if ($media && count($media) > 0) {
                                            if (is_string($media[0])) {
                                                $imageUrl = $media[0];
                                            } elseif (is_array($media[0])) {
                                                $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 commerce-line-clamp-2">{{ $item->product_name }}</h4>
                                        <p class="text-sm text-gray-600">
                                            Qty: {{ $item->quantity }} × 
                                            {{ \App\Services\CurrencyService::formatPrice($item->unit_price, $currency ?? 'USD') }}
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-semibold text-gray-900">
                                            {{ \App\Services\CurrencyService::formatPrice($item->total_price, $currency ?? 'USD') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="space-y-2">
                            <!-- Exchange Rate Display (only show if currency is not USD) -->
                            @if(($currency ?? 'USD') !== 'USD' && isset($currencyRate))
                            <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg border border-gray-200 mb-3">
                                <div class="flex justify-between items-center">
                                    <span>Exchange Rate:</span>
                                    <span class="font-medium">1 USD = {{ number_format($currencyRate, 4) }} {{ $currency }}</span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1">
                                    Prices converted from USD
                                </div>
                            </div>
                            @endif
                            
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ \App\Services\CurrencyService::formatPrice($convertedSubtotal ?? $order->subtotal, $currency ?? 'USD') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span>{{ \App\Services\CurrencyService::formatPrice($convertedShipping ?? $order->shipping_cost, $currency ?? 'USD') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span>{{ \App\Services\CurrencyService::formatPrice($convertedTax ?? $order->tax_amount, $currency ?? 'USD') }}</span>
                            </div>
                            @if($order->tip_amount > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>Tips</span>
                                <span>{{ \App\Services\CurrencyService::formatPrice($convertedTip ?? $order->tip_amount, $currency ?? 'USD') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span class="text-[#005366]">{{ \App\Services\CurrencyService::formatPrice($convertedTotal ?? $order->total_amount, $currency ?? 'USD') }}</span>
                            </div>
                        </div>
                    </div>
            </div>

            <div>
                <div class="commerce-card mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Order Status</h2>
                    
                    <!-- Status Timeline -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Order Placed</p>
                                <p class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-8 h-8 {{ $order->payment_status === 'paid' ? 'bg-green-600' : 'bg-amber-500' }} rounded-full flex items-center justify-center mr-4">
                                @if($order->payment_status === 'paid')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Payment {{ $order->payment_status === 'paid' ? 'Completed' : 'Pending' }}</p>
                                <p class="text-sm text-gray-600">
                                    @if($order->payment_status === 'paid')
                                        Payment received successfully
                                    @else
                                        Awaiting payment confirmation
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Processing</p>
                                <p class="text-sm text-gray-600">We'll start preparing your order</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Shipped</p>
                                <p class="text-sm text-gray-600">Your order is on its way</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Delivered</p>
                                <p class="text-sm text-gray-600">Enjoy your new products!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="commerce-info-panel">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">What's Next?</h3>
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#005366] mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p>You'll receive an email confirmation shortly</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#005366] mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>We'll process your order within 1-2 business days</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-[#005366] mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            <p>You'll get tracking information once shipped</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-12">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" class="btn-outline-petrol inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Continue Shopping
                </a>

                <a href="{{ route('checkout.receipt', $order->order_number) }}" class="btn-cta inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Receipt
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
