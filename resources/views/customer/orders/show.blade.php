@extends('layouts.app')

@section('content')
@php
    $field = 'w-full min-h-12 px-4 py-3 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#005366] focus:border-transparent';
    $latestReturn = ($order->returnRequests ?? collect())->first();
    $returnRoute = \Illuminate\Support\Facades\Route::has('customer.orders.return-request')
        ? route('customer.orders.return-request', $order->order_number)
        : null;
@endphp
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center text-sm font-semibold text-[#005366] hover:text-[#003d4d] mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to orders
        </a>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
        @endif

        <header class="section-heading section-heading--catalog mb-8 text-left md:text-center">
            <p class="section-heading__eyebrow">Order</p>
            <h1 class="section-heading__title">{{ $order->order_number }}</h1>
            <p class="section-heading__sub">Placed {{ $order->created_at->format('M d, Y') }} at {{ $order->created_at->format('h:i A') }}</p>
            <span class="section-heading__accent"></span>
        </header>

        <div class="flex flex-wrap gap-2 justify-center mb-8">
            <span class="px-3 py-1 text-sm font-semibold rounded-full
                @if($order->status == 'pending') bg-amber-50 text-amber-800
                @elseif($order->status == 'processing') bg-blue-50 text-blue-800
                @elseif($order->status == 'completed') bg-green-50 text-green-800
                @elseif($order->status == 'cancelled') bg-red-50 text-red-800
                @else bg-gray-100 text-gray-800 @endif">{{ ucfirst($order->status) }}</span>
            <span class="px-3 py-1 text-sm font-semibold rounded-full
                @if($order->payment_status == 'paid') bg-green-50 text-green-800
                @elseif($order->payment_status == 'pending') bg-amber-50 text-amber-800
                @else bg-gray-100 text-gray-800 @endif">{{ ucfirst($order->payment_status) }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="commerce-card">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Items</h2>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            @php
                                $options = is_array($item->product_options) ? $item->product_options : [];
                                $variant = $options['selected_variant'] ?? null;
                                $variantLabel = is_array($variant['attributes'] ?? null) ? implode(' / ', $variant['attributes']) : ($item->variant_name ?? null);
                                $customs = $item->visibleCustomizations();
                            @endphp
                            <div class="flex items-start gap-4 p-4 border border-gray-200 rounded-xl">
                                <div class="shrink-0">
                                    @include('customer.partials.order-item-image', ['item' => $item, 'size' => 'w-20 h-20'])
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900">{{ $item->product_name }}</h3>
                                    @if($variantLabel)
                                        <p class="text-sm text-gray-600 mt-1">{{ $variantLabel }}</p>
                                    @endif
                                    @if($customs !== [])
                                        <div class="mt-2 text-sm text-gray-600 space-y-1">
                                            @foreach($customs as $key => $value)
                                                @if(is_array($value))
                                                    <p>{{ $key }}: {{ $value['value'] ?? $value['label'] ?? json_encode($value) }}</p>
                                                @else
                                                    <p>{{ $key }}: {{ $value }}</p>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    <p class="mt-2 text-sm text-gray-600">Qty {{ $item->quantity }} · {{ format_price((float) $item->unit_price) }}</p>
                                </div>
                                <p class="font-bold text-[#005366]">{{ format_price((float) $item->total_price) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="commerce-summary">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Summary</h2>
                    <div class="space-y-3 text-gray-600">
                        <div class="flex justify-between"><span>Subtotal</span><span>{{ format_price((float) $order->subtotal) }}</span></div>
                        <div class="flex justify-between"><span>Shipping</span><span>{{ format_price((float) $order->shipping_cost) }}</span></div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-3">
                            <span>Total</span>
                            <span class="text-[#005366]">{{ format_price((float) $order->total_amount) }}</span>
                        </div>
                    </div>
                </div>
                <div class="commerce-card">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Shipping address</h2>
                    <div class="text-gray-600 space-y-1">
                        <p class="font-semibold text-gray-900">{{ $order->customer_name }}</p>
                        <p>{{ $order->shipping_address }}</p>
                        <p>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
                        <p>{{ $order->country }}</p>
                        @if($order->customer_phone)
                            <p class="mt-2">Phone: {{ $order->customer_phone }}</p>
                        @endif
                        <p>Email: {{ $order->customer_email }}</p>
                    </div>
                </div>
                <div class="commerce-card">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Payment</h2>
                    <div class="text-gray-600 space-y-2">
                        <div class="flex justify-between"><span>Method</span><span class="font-semibold text-gray-900">{{ ucfirst((string) $order->payment_method) }}</span></div>
                        <div class="flex justify-between">
                            <span>Status</span>
                            <span class="font-semibold @if($order->payment_status == 'paid') text-green-700 @elseif($order->payment_status == 'pending') text-amber-700 @else text-[#e2150c] @endif">{{ ucfirst($order->payment_status) }}</span>
                        </div>
                        @if($order->payment_transaction_id)
                            <p class="font-mono text-xs break-all">{{ $order->payment_transaction_id }}</p>
                        @endif
                        @if($order->paid_at)
                            <p>Paid {{ $order->paid_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>
                @if($order->tracking_number)
                    <div class="commerce-card">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Tracking</h2>
                        <p class="font-mono bg-gray-50 p-3 rounded-lg border border-gray-200">{{ $order->tracking_number }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($latestReturn)
            <div class="commerce-card mt-6">
                <div class="flex items-center justify-between mb-4 gap-3">
                    <h2 class="text-xl font-bold text-gray-900">Return status</h2>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                        @if($latestReturn->status === 'pending') bg-amber-50 text-amber-800
                        @elseif($latestReturn->status === 'processing') bg-blue-50 text-blue-800
                        @elseif(in_array($latestReturn->status, ['approved', 'completed'])) bg-green-50 text-green-800
                        @else bg-red-50 text-red-800 @endif">{{ ucfirst($latestReturn->status) }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <p class="font-semibold text-gray-900">Resolution</p>
                        <p class="mt-1">{{ ucfirst(str_replace('_', ' ', $latestReturn->resolution ?? '')) }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Reason</p>
                        <p class="mt-1">{{ ucfirst(str_replace('_', ' ', $latestReturn->reason ?? '')) }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Submitted</p>
                        <p class="mt-1">{{ $latestReturn->created_at?->format('M d, Y H:i') }}</p>
                    </div>
                    @if($latestReturn->admin_note)
                        <div>
                            <p class="font-semibold text-gray-900">Store note</p>
                            <p class="mt-1 whitespace-pre-line">{{ $latestReturn->admin_note }}</p>
                        </div>
                    @endif
                </div>
                @if($latestReturn->description)
                    <p class="mt-4 font-semibold text-gray-900">Your message</p>
                    <p class="mt-1 text-gray-700 whitespace-pre-line">{{ $latestReturn->description }}</p>
                @endif
                @if($latestReturn->evidence_paths && count($latestReturn->evidence_paths) > 0)
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($latestReturn->evidence_paths as $path)
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ Storage::url($path) }}" alt="Evidence" class="aspect-video w-full object-cover rounded-lg border border-gray-200">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="commerce-card mt-6">
            <h2 class="text-xl font-bold text-gray-900">Return or exchange</h2>
            <p class="text-sm text-gray-600 mt-1 mb-6">Tell us why you want to return or exchange this order.</p>
            @if(!$returnRoute)
                <div class="mb-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-3">Contact support to submit this request.</div>
            @endif
            <form class="space-y-5" method="POST" action="{{ $returnRoute ?? '#' }}" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2" for="reason">Reason</label>
                    <select id="reason" name="reason" class="{{ $field }}" {{ $returnRoute ? '' : 'disabled' }}>
                        <option value="">Select a reason</option>
                        <option value="product_defect">Product defect</option>
                        <option value="not_as_described">Not as described</option>
                        <option value="wrong_item">Wrong item received</option>
                        <option value="size_issue">Does not fit</option>
                        <option value="changed_mind">Changed my mind</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <p class="block text-sm font-semibold text-gray-900 mb-2">Preferred resolution</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach([
                            ['value' => 'refund', 'label' => 'Refund to original payment'],
                            ['value' => 'exchange', 'label' => 'Exchange for a new product'],
                            ['value' => 'store_credit', 'label' => 'Store credit'],
                        ] as $res)
                            <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-[#005366]">
                                <input type="radio" name="resolution" value="{{ $res['value'] }}" class="mt-1 text-[#005366] focus:ring-[#005366]" {{ $returnRoute ? '' : 'disabled' }}>
                                <span class="text-sm text-gray-700">{{ $res['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2" for="description">Tell us more (optional)</label>
                    <textarea id="description" name="description" rows="4" class="{{ $field }}" placeholder="Describe the issue" {{ $returnRoute ? '' : 'disabled' }}></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2" for="evidence">Upload photos (optional)</label>
                    <input id="evidence" type="file" name="evidence[]" accept="image/*" multiple class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-[#005366] file:text-white" {{ $returnRoute ? '' : 'disabled' }}>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="confirm" class="rounded text-[#005366] focus:ring-[#005366]" {{ $returnRoute ? '' : 'disabled' }}>
                    I confirm this information is accurate.
                </label>
                <button type="{{ $returnRoute ? 'submit' : 'button' }}" class="{{ $returnRoute ? 'btn-cta' : 'btn-cta opacity-50 cursor-not-allowed' }}" {{ $returnRoute ? '' : 'disabled' }}>Submit request</button>
            </form>
        </div>
    </div>
</div>
@endsection
