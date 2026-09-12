@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Promo Codes</h1>
            <p class="text-sm text-gray-500 mt-1">Manage welcome, thank-you, win-back, VIP and public promo codes.</p>
        </div>
        <a href="{{ route('admin.promo-codes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
            Create promo code
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Code</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Discount</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Audience</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Usage</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Expires</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($promoCodes as $promo)
                        <tr>
                            <td class="px-4 py-3 font-mono font-semibold text-gray-900">{{ $promo->code }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $promo->title }}</td>
                            <td class="px-4 py-3">
                                @if($promo->type === 'percent')
                                    {{ rtrim(rtrim(number_format((float) $promo->value, 2), '0'), '.') }}%
                                @else
                                    ${{ number_format((float) $promo->value, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $promo->audience) }}</td>
                            <td class="px-4 py-3">{{ $promo->usage_count }}{{ $promo->usage_limit ? ' / '.$promo->usage_limit : '' }}</td>
                            <td class="px-4 py-3">{{ $promo->expires_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($promo->is_active && ! $promo->isExpired() && $promo->hasRemainingUses())
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                                @elseif($promo->isSoldOut())
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Sold out</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Inactive</span>
                                @endif
                                @if($promo->show_on_promo_page)
                                    <span class="ml-1 px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">Public</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.promo-codes.edit', $promo) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                                <form action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST" class="inline" onsubmit="return confirm('Delete this promo code?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No promo codes yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($promoCodes->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $promoCodes->links() }}</div>
        @endif
    </div>
</div>
@endsection
