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
            currency: 'USD',
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

    if (typeof gtag === 'function') {
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

        gtag('event', 'purchase', {
            currency: 'USD',
            transaction_id: '{{ $order->order_number }}',
            value: Number('{{ $order->total_amount }}'),
            tax: Number('{{ $order->tax_amount }}'),
            shipping: Number('{{ $order->shipping_cost }}'),
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
            currency: 'USD',
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
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% {
            transform: translate3d(0,0,0);
        }
        40%, 43% {
            transform: translate3d(0, -30px, 0);
        }
        70% {
            transform: translate3d(0, -15px, 0);
        }
        90% {
            transform: translate3d(0, -4px, 0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-scaleIn {
        animation: scaleIn 0.5s ease-out forwards;
    }

    .animate-bounce {
        animation: bounce 1s ease-in-out;
    }

    .gradient-text {
        background: linear-gradient(135deg, #005366 0%, #E2150C 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .success-icon {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Header -->
        <div class="text-center mb-12 animate-fadeInUp">
            <div class="success-icon w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Order 
                <span class="gradient-text">Confirmed!</span>
            </h1>
            <p class="text-lg text-gray-600 mb-6">
                Thank you for your purchase. Your order has been successfully placed.
            </p>
            
            <div class="bg-white rounded-xl shadow-lg p-6 inline-block animate-scaleIn">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Order Number</p>
                    <p class="text-2xl font-bold text-[#005366]">{{ $order->order_number }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Order Details -->
            <div class="animate-fadeInUp" style="animation-delay: 0.2s">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Order Details</h2>
                    
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
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $item->product_name }}</h4>
                                        <p class="text-sm text-gray-600">Qty: {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">${{ number_format($item->total_price, 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>${{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span>${{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span>${{ number_format($order->tax_amount, 2) }}</span>
                            </div>
                            @if($order->tip_amount > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>Tips</span>
                                <span>${{ number_format($order->tip_amount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span>${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status & Next Steps -->
            <div class="animate-fadeInUp" style="animation-delay: 0.4s">
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Order Status</h2>
                    
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
                            <div class="w-8 h-8 {{ $order->payment_status === 'paid' ? 'bg-green-500' : 'bg-gray-300' }} rounded-full flex items-center justify-center mr-4">
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

                <!-- What's Next -->
                <div class="bg-blue-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">What's Next?</h3>
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p>You'll receive an email confirmation shortly</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>We'll process your order within 1-2 business days</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            <p>You'll get tracking information once shipped</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-12 animate-fadeInUp" style="animation-delay: 0.6s">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center px-6 py-3 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Continue Shopping
                </a>
                
                <a href="#" 
                   class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Receipt
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
