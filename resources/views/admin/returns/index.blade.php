@extends('layouts.admin')

@section('title', 'Return Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Return / Refund Requests</h1>
            <p class="text-sm text-gray-600">Manage customer return, refund, or exchange requests.</p>
        </div>
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold text-gray-900">{{ $requests->total() }}</span>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.returns.index') }}" class="p-4 flex flex-wrap items-center gap-3">
            <div class="w-48">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, email, name"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="w-40">
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-44">
                <select name="resolution" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All resolutions</option>
                    @foreach(['refund' => 'Refund', 'exchange' => 'Exchange', 'store_credit' => 'Store credit'] as $val => $label)
                        <option value="{{ $val }}" {{ request('resolution') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-32">
                <select name="per_page" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @foreach([20,50,100] as $size)
                        <option value="{{ $size }}" {{ (int)request('per_page', $perPage) === $size ? 'selected' : '' }}>{{ $size }}/page</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                @if(request()->anyFilled(['search','status','resolution','per_page']))
                    <a href="{{ route('admin.returns.index') }}" class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Order</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Resolution</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Created</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                @if($req->order)
                                    <div>#{!! $req->order->order_number !!}</div>
                                    <div class="text-xs text-gray-500">${{ number_format($req->order->total_amount, 2) }}</div>
                                @else
                                    <span class="text-xs text-gray-500 italic">Order missing</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($req->order)
                                    <div>{{ $req->order->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $req->order->customer_email }}</div>
                                @elseif($req->user)
                                    <div>{{ $req->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $req->user->email }}</div>
                                @else
                                    <span class="text-xs text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="font-semibold">{{ ucfirst(str_replace('_',' ', $req->reason ?? 'n/a')) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-50 text-blue-700">
                                    {{ ucfirst(str_replace('_',' ', $req->resolution ?? '')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($req->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($req->status === 'processing') bg-blue-100 text-blue-800
                                    @elseif($req->status === 'approved') bg-green-100 text-green-800
                                    @elseif($req->status === 'completed') bg-emerald-100 text-emerald-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $req->created_at?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.returns.show', $req) }}"
                                   class="inline-flex items-center px-3 py-1.5 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">
                                No return requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection

