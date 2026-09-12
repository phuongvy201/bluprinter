@extends('layouts.app')

@section('content')
@php
    $field = 'w-full min-h-12 px-4 py-3 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#005366] focus:border-transparent';
@endphp
<div class="bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if(!isset($order))
            <header class="section-heading section-heading--catalog mb-8">
                <p class="section-heading__eyebrow">Orders</p>
                <h1 class="section-heading__title">Track your <span class="gradient-text">order</span></h1>
                <p class="section-heading__sub">Enter the order number and email from your confirmation.</p>
                <span class="section-heading__accent"></span>
            </header>

            <div class="commerce-card max-w-lg mx-auto">
                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('orders.track') }}" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2" for="order_number">Order number</label>
                        <input id="order_number" type="text" name="order_number" value="{{ request('order_number') }}" placeholder="e.g. BLU20241015-001" class="{{ $field }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2" for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ request('email') }}" placeholder="you@email.com" class="{{ $field }}" required>
                    </div>
                    <button type="submit" class="btn-cta btn-cta--block">Track order</button>
                </form>
                <p class="mt-6 text-center text-sm text-gray-600">Find the order number in your confirmation email.</p>
            </div>
        @else
            <header class="section-heading section-heading--catalog mb-8">
                <p class="section-heading__eyebrow">Tracking</p>
                <h1 class="section-heading__title">{{ $order->order_number }}</h1>
                <p class="section-heading__sub">Placed {{ $order->created_at->format('M d, Y') }}</p>
                <span class="section-heading__accent"></span>
            </header>

            <div class="flex justify-center mb-6">
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    @if($order->status == 'pending') bg-amber-50 text-amber-800
                    @elseif($order->status == 'processing') bg-blue-50 text-blue-800
                    @elseif($order->status == 'completed') bg-green-50 text-green-800
                    @elseif($order->status == 'cancelled') bg-red-50 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">{{ ucfirst($order->status) }}</span>
            </div>

            <div class="commerce-card mb-6">
                <ol class="space-y-6 relative">
                    <li class="relative pl-12">
                        <span class="absolute left-0 w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-sm">1</span>
                        <p class="font-semibold text-gray-900">Order placed</p>
                        <p class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </li>
                    <li class="relative pl-12">
                        <span class="absolute left-0 w-8 h-8 rounded-full {{ $order->payment_status == 'paid' ? 'bg-green-600 text-white' : 'bg-gray-300 text-white' }} flex items-center justify-center text-sm">2</span>
                        <p class="font-semibold text-gray-900">Payment {{ $order->payment_status }}</p>
                        @if($order->paid_at)
                            <p class="text-sm text-gray-600">{{ $order->paid_at->format('M d, Y h:i A') }}</p>
                        @endif
                    </li>
                    <li class="relative pl-12">
                        <span class="absolute left-0 w-8 h-8 rounded-full {{ in_array($order->status, ['processing', 'completed']) ? 'bg-green-600 text-white' : 'bg-gray-300 text-white' }} flex items-center justify-center text-sm">3</span>
                        <p class="font-semibold text-gray-900">Processing</p>
                        <p class="text-sm text-gray-600">Your order is being prepared</p>
                    </li>
                    <li class="relative pl-12">
                        <span class="absolute left-0 w-8 h-8 rounded-full {{ $order->tracking_number ? 'bg-green-600 text-white' : 'bg-gray-300 text-white' }} flex items-center justify-center text-sm">4</span>
                        <p class="font-semibold text-gray-900">Shipped</p>
                        <p class="text-sm text-gray-600">{{ $order->tracking_number ? 'Tracking: '.$order->tracking_number : 'Waiting for shipment' }}</p>
                    </li>
                    <li class="relative pl-12">
                        <span class="absolute left-0 w-8 h-8 rounded-full {{ $order->status == 'completed' ? 'bg-green-600 text-white' : 'bg-gray-300 text-white' }} flex items-center justify-center text-sm">5</span>
                        <p class="font-semibold text-gray-900">Delivered</p>
                        <p class="text-sm text-gray-600">{{ $order->status == 'completed' ? 'Order completed' : 'Pending delivery' }}</p>
                    </li>
                </ol>
            </div>

            <div class="commerce-card mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Items</h2>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl">
                            @include('customer.partials.order-item-image', ['item' => $item, 'size' => 'w-16 h-16'])
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900">{{ $item->product_name }}</p>
                                <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                            </div>
                            <p class="font-bold text-[#005366]">{{ format_price((float) $item->total_price) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('orders.track') }}" class="btn-outline-petrol">Track another order</a>
            </div>
        @endif
    </div>
</div>
@endsection
