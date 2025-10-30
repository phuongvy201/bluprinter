@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Orders</h1>
            <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Track and manage your orders</p>
        </div>

        <!-- Order Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <!-- Total Orders -->
            <a href="{{ route('customer.orders.index') }}" 
               class="group bg-white rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 {{ !request('status') ? 'ring-2 ring-[#005366] bg-gradient-to-br from-[#005366]/5 to-[#005366]/10' : 'hover:bg-gray-50' }}">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#005366]/10 rounded-xl flex items-center justify-center group-hover:bg-[#005366]/20 transition-colors">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    @if(!request('status'))
                        <div class="w-2 h-2 bg-[#005366] rounded-full"></div>
                    @endif
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-[#005366] mb-1">{{ $stats['total'] }}</div>
                <div class="text-xs sm:text-sm font-medium text-gray-600">Total Orders</div>
            </a>
            
            <!-- Pending Orders -->
            <a href="{{ route('customer.orders.index', ['status' => 'pending']) }}" 
               class="group bg-white rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 {{ request('status') == 'pending' ? 'ring-2 ring-yellow-500 bg-gradient-to-br from-yellow-50 to-yellow-100' : 'hover:bg-gray-50' }}">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    @if(request('status') == 'pending')
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                    @endif
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-yellow-600 mb-1">{{ $stats['pending'] }}</div>
                <div class="text-xs sm:text-sm font-medium text-gray-600">Pending</div>
            </a>
            
            <!-- Processing Orders -->
            <a href="{{ route('customer.orders.index', ['status' => 'processing']) }}" 
               class="group bg-white rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 {{ request('status') == 'processing' ? 'ring-2 ring-blue-500 bg-gradient-to-br from-blue-50 to-blue-100' : 'hover:bg-gray-50' }}">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    @if(request('status') == 'processing')
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    @endif
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-blue-600 mb-1">{{ $stats['processing'] }}</div>
                <div class="text-xs sm:text-sm font-medium text-gray-600">Processing</div>
            </a>
            
            <!-- Completed Orders -->
            <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" 
               class="group bg-white rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 {{ request('status') == 'completed' ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-green-100' : 'hover:bg-gray-50' }}">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    @if(request('status') == 'completed')
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    @endif
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-green-600 mb-1">{{ $stats['completed'] }}</div>
                <div class="text-xs sm:text-sm font-medium text-gray-600">Completed</div>
            </a>
            
            <!-- Cancelled Orders -->
            <a href="{{ route('customer.orders.index', ['status' => 'cancelled']) }}" 
               class="group bg-white rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-lg transition-all duration-300 {{ request('status') == 'cancelled' ? 'ring-2 ring-red-500 bg-gradient-to-br from-red-50 to-red-100' : 'hover:bg-gray-50' }}">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-xl flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    @if(request('status') == 'cancelled')
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    @endif
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-red-600 mb-1">{{ $stats['cancelled'] }}</div>
                <div class="text-xs sm:text-sm font-medium text-gray-600">Cancelled</div>
            </a>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Search & Filter</h3>
                @if($search || $status)
                    <a href="{{ route('customer.orders.index') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear Filters
                    </a>
                @endif
            </div>
            
            <form method="GET" action="{{ route('customer.orders.index') }}" class="flex flex-col lg:flex-row gap-3 sm:gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? '' }}"
                           placeholder="Search by order number, customer name, or email..." 
                           class="w-full pl-10 pr-3 py-2.5 sm:py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white text-sm sm:text-base">
                </div>
                
                <button type="submit" 
                        class="inline-flex items-center justify-center px-5 sm:px-6 py-2.5 sm:py-3 bg-[#005366] text-white font-medium rounded-xl hover:bg-[#003d4d] transition-all duration-200 shadow-sm hover:shadow-md text-sm sm:text-base">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search Orders
                </button>
            </form>
            
            @if($search || $status)
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($search)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#005366]/10 text-[#005366]">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search: "{{ $search }}"
                        </span>
                    @endif
                    @if($status)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            Status: {{ ucfirst($status) }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Orders List -->
        @if($orders->count() > 0)
            <div class="space-y-6">
                @foreach($orders as $order)
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden group">
                        <div class="p-6">
                            <!-- Order Header -->
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                                <!-- Order Info -->
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <h3 class="text-xl font-bold text-gray-900">
                                            Order #{{ $order->order_number }}
                                        </h3>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @if($order->status == 'pending') bg-yellow-100 text-yellow-800 border border-yellow-200
                                                @elseif($order->status == 'processing') bg-blue-100 text-blue-800 border border-blue-200
                                                @elseif($order->status == 'completed') bg-green-100 text-green-800 border border-green-200
                                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800 border border-red-200
                                                @else bg-gray-100 text-gray-800 border border-gray-200
                                                @endif">
                                                <div class="w-2 h-2 rounded-full mr-2
                                                    @if($order->status == 'pending') bg-yellow-500
                                                    @elseif($order->status == 'processing') bg-blue-500
                                                    @elseif($order->status == 'completed') bg-green-500
                                                    @elseif($order->status == 'cancelled') bg-red-500
                                                    @else bg-gray-500
                                                    @endif"></div>
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                @if($order->payment_status == 'paid') bg-green-100 text-green-800 border border-green-200
                                                @elseif($order->payment_status == 'pending') bg-yellow-100 text-yellow-800 border border-yellow-200
                                                @else bg-gray-100 text-gray-800 border border-gray-200
                                                @endif">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="font-medium">{{ $order->created_at->format('M d, Y') }}</span>
                                            <span class="text-gray-400 mx-2">•</span>
                                            <span>{{ $order->created_at->format('h:i A') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            <span class="font-medium">{{ $order->items->count() }} item{{ $order->items->count() > 1 ? 's' : '' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Total & Action -->
                                <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-end lg:items-end gap-4">
                                    <div class="text-right">
                                        <div class="text-3xl font-bold text-[#005366] mb-1">
                                            ${{ number_format($order->total_amount, 2) }}
                                        </div>
                                        <div class="text-sm text-gray-500">Total Amount</div>
                                    </div>

                                    <a href="{{ route('customer.orders.show', $order->order_number) }}" 
                                       class="inline-flex items-center px-6 py-3 bg-[#005366] text-white font-medium rounded-xl hover:bg-[#003d4d] transition-all duration-200 shadow-sm hover:shadow-md group-hover:scale-105">
                                        <span>View Details</span>
                                        <svg class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Order Items Preview -->
                            @if($order->items->count() > 0)
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-semibold text-gray-700">Order Items</h4>
                                        <span class="text-xs text-gray-500">{{ $order->items->count() }} item{{ $order->items->count() > 1 ? 's' : '' }}</span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3 overflow-x-auto pb-2">
                                        @foreach($order->items->take(4) as $item)
                                            <div class="flex-shrink-0 group">
                                                <div class="relative">
                                                    @php
                                                        $productMedia = $item->product ? $item->product->getEffectiveMedia() : [];
                                                        $productImageUrl = null;
                                                        if (!empty($productMedia)) {
                                                            if (is_string($productMedia[0])) {
                                                                $productImageUrl = $productMedia[0];
                                                            } elseif (is_array($productMedia[0])) {
                                                                $productImageUrl = $productMedia[0]['url'] ?? $productMedia[0]['path'] ?? reset($productMedia[0]) ?? null;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($productImageUrl)
                                                        <img src="{{ $productImageUrl }}" 
                                                             alt="{{ $item->product_name }}"
                                                             class="w-16 h-16 object-cover rounded-lg border border-gray-200 group-hover:scale-105 transition-transform duration-200"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <!-- Fallback if image fails -->
                                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center border border-gray-200" style="display: none;">
                                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center border border-gray-200">
                                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Quantity Badge -->
                                                    @if($item->quantity > 1)
                                                        <div class="absolute -top-1 -right-1 bg-[#005366] text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                                            {{ $item->quantity }}
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Product Name (on hover) -->
                                                <div class="mt-2 text-center">
                                                    <p class="text-xs text-gray-600 truncate max-w-16" title="{{ $item->product_name }}">
                                                        {{ Str::limit($item->product_name, 12) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        @if($order->items->count() > 4)
                                            <div class="flex-shrink-0 flex items-center justify-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                                                    <div class="text-center">
                                                        <div class="text-lg font-bold text-gray-500">+{{ $order->items->count() - 4 }}</div>
                                                        <div class="text-xs text-gray-400">more</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-32 h-32 mx-auto mb-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-900 mb-3">
                    @if($search || $status)
                        No Orders Found
                    @else
                        No Orders Yet
                    @endif
                </h3>
                
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    @if($search || $status)
                        No orders match your search criteria. Try adjusting your filters or search terms.
                    @else
                        You haven't placed any orders yet. Start shopping to see your orders here!
                    @endif
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @if($search || $status)
                        <a href="{{ route('customer.orders.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear Filters
                        </a>
                    @endif
                    
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center px-8 py-3 bg-[#005366] text-white font-medium rounded-xl hover:bg-[#003d4d] transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                        </svg>
                        Start Shopping
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

