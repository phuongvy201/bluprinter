@extends('layouts.admin')

@section('title', 'Return Request #'.$request->id)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Return / Refund Request</h1>
            <p class="text-sm text-gray-600">Request ID: #{{ $request->id }} · Order: #{{ $request->order->order_number ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.returns.index') }}" class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Back</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
            <p class="text-green-700 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Request Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm text-gray-700">
                    <div>
                        <dt class="font-semibold text-gray-900">Status</dt>
                        <dd>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($request->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($request->status === 'approved') bg-green-100 text-green-800
                                @elseif($request->status === 'completed') bg-emerald-100 text-emerald-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900">Resolution</dt>
                        <dd class="text-gray-700">{{ ucfirst(str_replace('_',' ', $request->resolution ?? '')) }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900">Reason</dt>
                        <dd class="text-gray-700">{{ ucfirst(str_replace('_',' ', $request->reason ?? '')) }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900">Submitted At</dt>
                        <dd class="text-gray-700">{{ $request->created_at?->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>

                @if($request->description)
                    <div class="mt-4">
                        <dt class="font-semibold text-gray-900 mb-1">Customer Note</dt>
                        <dd class="text-gray-700 whitespace-pre-line">{{ $request->description }}</dd>
                    </div>
                @endif
            </div>

            @if($request->evidence_paths && count($request->evidence_paths) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Evidence</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($request->evidence_paths as $path)
                        <a href="{{ Storage::url($path) }}" target="_blank" class="block">
                            <div class="aspect-video rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                                <img src="{{ Storage::url($path) }}" alt="Evidence" class="w-full h-full object-cover">
                            </div>
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ basename($path) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Order Info</h2>
                @if($request->order)
                    <div class="text-sm text-gray-700 space-y-1">
                        <p><span class="font-semibold">Order:</span> #{{ $request->order->order_number }}</p>
                        <p><span class="font-semibold">Customer:</span> {{ $request->order->customer_name }} ({{ $request->order->customer_email }})</p>
                        <p><span class="font-semibold">Total:</span> ${{ number_format($request->order->total_amount, 2) }}</p>
                        <p><span class="font-semibold">Status:</span> {{ ucfirst($request->order->status) }}</p>
                        <p><span class="font-semibold">Payment:</span> {{ ucfirst($request->order->payment_status) }}</p>
                    </div>
                    @if($request->order->items && $request->order->items->count())
                        <div class="mt-3 border-t border-gray-200 pt-3">
                            <p class="text-sm font-semibold text-gray-900 mb-2">Items</p>
                            <ul class="text-sm text-gray-700 space-y-1">
                                @foreach($request->order->items as $item)
                                    <li>- {{ $item->product_name }} x{{ $item->quantity }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500">Order data not available.</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Update Status</h2>
                <form method="POST" action="{{ route('admin.returns.update', $request) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" {{ $request->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Internal Note</label>
                        <textarea name="admin_note" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Notes for staff only...">{{ old('admin_note', $request->admin_note) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

