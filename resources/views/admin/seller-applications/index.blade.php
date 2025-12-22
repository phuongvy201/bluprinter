@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Seller Applications</h1>
            <p class="text-gray-600">Review and approve/reject seller signups.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 rounded bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
    @endif

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Store</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Categories</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($applications as $app)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $app->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $app->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $app->store_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $app->product_categories }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($app->status === 'approved') bg-green-100 text-green-800
                                    @elseif($app->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $app->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 space-x-2">
                                <a href="{{ route('admin.seller-applications.show', $app) }}" class="text-sky-600 hover:underline">View</a>
                                @if($app->status === 'pending')
                                    <form action="{{ route('admin.seller-applications.approve', $app) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:underline">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.seller-applications.reject', $app) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="reason" value="">
                                        <button type="submit" class="text-red-600 hover:underline">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">No applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection

