@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Seller Application Detail</h1>
            <p class="text-gray-600">Review the submitted information.</p>
        </div>
        <a href="{{ route('admin.seller-applications.index') }}" class="text-sky-600 hover:underline">Back to list</a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
    @endif

    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs uppercase text-gray-500">Name</p>
                <p class="text-gray-900 font-semibold">{{ $application->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Email</p>
                <p class="text-gray-900 font-semibold">{{ $application->email }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Phone</p>
                <p class="text-gray-900 font-semibold">{{ $application->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Store / Brand</p>
                <p class="text-gray-900 font-semibold">{{ $application->store_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Product Categories</p>
                <p class="text-gray-900 font-semibold">{{ $application->product_categories }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Status</p>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                    @if($application->status === 'approved') bg-green-100 text-green-800
                    @elseif($application->status === 'rejected') bg-red-100 text-red-800
                    @else bg-yellow-100 text-yellow-800 @endif">
                    {{ ucfirst($application->status) }}
                </span>
            </div>
        </div>

        <div>
            <p class="text-xs uppercase text-gray-500">Message</p>
            <p class="text-gray-900 whitespace-pre-line">{{ $application->message ?? '—' }}</p>
        </div>

        @if($application->reviewed_by)
            <div class="text-sm text-gray-600">
                Reviewed by user ID: {{ $application->reviewed_by }} |
                @if($application->approved_at) Approved at: {{ $application->approved_at }} @endif
                @if($application->rejected_at) Rejected at: {{ $application->rejected_at }} @endif
            </div>
        @endif
    </div>

    @if($application->status === 'pending')
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6 space-y-3">
            <h2 class="text-lg font-semibold text-gray-900">Review actions</h2>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('admin.seller-applications.approve', $application) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
                        Approve &amp; send email
                    </button>
                </form>
                <form action="{{ route('admin.seller-applications.reject', $application) }}" method="POST" class="inline-flex items-center gap-2">
                    @csrf
                    <input type="text" name="reason" class="border border-gray-200 rounded-lg px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-100" placeholder="Rejection note (optional)">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                        Reject &amp; notify
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

