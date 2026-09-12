@extends('layouts.app')

@section('content')
@php
    $currentStatus = request('status');
    $statBase = 'block rounded-xl border border-gray-200 bg-white p-4 hover:bg-gray-50';
    $statActive = 'bg-[#f7f7f7]';
    $inputClass = 'w-full min-h-12 pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-base bg-white focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent';
    $from = $orders->firstItem();
    $to = $orders->lastItem();
    $total = $orders->total();
@endphp
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <header class="mb-8">
            <h1 class="text-[28px] font-bold text-gray-900 leading-tight">Your orders</h1>
            <p class="mt-2 text-gray-600">Track shipments and manage purchases.</p>
        </header>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('customer.orders.index') }}" class="{{ $statBase }} {{ $currentStatus ? '' : $statActive }}">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-sm text-gray-600 mt-1">All</p>
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'processing']) }}" class="{{ $statBase }} {{ $currentStatus == 'processing' ? $statActive : '' }}">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['processing'] }}</p>
                <p class="text-sm text-gray-600 mt-1">Processing</p>
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" class="{{ $statBase }} {{ $currentStatus == 'completed' ? $statActive : '' }}">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                <p class="text-sm text-gray-600 mt-1">Completed</p>
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'cancelled']) }}" class="{{ $statBase }} {{ $currentStatus == 'cancelled' ? $statActive : '' }}">
                <p class="text-2xl font-bold {{ $stats['cancelled'] > 0 ? 'text-[#e2150c]' : 'text-gray-900' }}">{{ $stats['cancelled'] }}</p>
                <p class="text-sm text-gray-600 mt-1">Cancelled</p>
            </a>
        </div>

        <form method="GET" action="{{ route('customer.orders.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <label for="order-search" class="sr-only">Search orders</label>
                <input id="order-search" type="text" name="search" value="{{ $search ?? '' }}" placeholder="Order number, name, or email" class="{{ $inputClass }}">
            </div>
            <button type="submit" class="btn-outline-petrol">Search</button>
            @if($search || $status)
                <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center justify-center text-sm font-semibold text-gray-600 hover:text-gray-900 px-2">Clear</a>
            @endif
        </form>

        @if($orders->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                @foreach($orders as $order)
                    <article class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-4 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-semibold text-gray-900">{{ $order->order_number }}</h2>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                    @if($order->status == 'pending') bg-amber-50 text-amber-800
                                    @elseif($order->status == 'processing') bg-blue-50 text-blue-800
                                    @elseif($order->status == 'completed') bg-green-50 text-green-800
                                    @elseif($order->status == 'cancelled') bg-red-50 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">{{ ucfirst($order->status) }}</span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                    @if($order->payment_status == 'paid') bg-green-50 text-green-800
                                    @elseif($order->payment_status == 'pending') bg-amber-50 text-amber-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">{{ ucfirst($order->payment_status) }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $order->created_at->format('M d, Y') }} · {{ $order->items->count() }} item{{ $order->items->count() === 1 ? '' : 's' }}</p>
                        </div>
                        <p class="text-base font-semibold text-gray-900 sm:text-right">{{ format_price((float) $order->total_amount) }}</p>
                        <a href="{{ route('customer.orders.show', $order->order_number) }}" class="btn-outline-petrol btn-outline-petrol--compact shrink-0">View order</a>
                    </article>
                @endforeach
            </div>

            <p class="mt-4 text-sm text-gray-600">
                Showing {{ $from }}–{{ $to }} of {{ $total }} orders
            </p>
            @if($orders->hasPages())
                <div class="mt-4 catalog-pagination">
                    {{ $orders->onEachSide(1)->links('vendor.pagination.catalog') }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-xl border border-gray-200 px-6 py-12 text-center">
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ ($search || $status) ? 'No orders found' : 'No orders yet' }}</h2>
                <p class="text-gray-600 mb-6">
                    @if($search || $status)
                        No orders match these filters.
                    @else
                        You have not placed an order yet.
                    @endif
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    @if($search || $status)
                        <a href="{{ route('customer.orders.index') }}" class="btn-outline-petrol">Clear filters</a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn-cta">Start shopping</a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
