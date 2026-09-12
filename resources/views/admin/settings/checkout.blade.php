@extends('layouts.admin')

@section('title', 'Checkout Settings')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Checkout settings</h1>
        <p class="mt-1 text-sm text-gray-600">Choose which payment methods appear on the storefront checkout page.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.checkout.update') }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
            <h2 class="text-lg font-semibold text-gray-900">Payment methods</h2>
            <p class="text-sm text-gray-600">Disabled methods are hidden from checkout and rejected on the server.</p>

            @php
                $methods = old('payment_methods', $settings);
            @endphp

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="payment_methods[stripe]" value="0">
                <input type="checkbox" name="payment_methods[stripe]" value="1"
                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                       {{ !empty($methods['stripe']) ? 'checked' : '' }}>
                <span>
                    <span class="block font-medium text-gray-900">Credit card (Stripe)</span>
                    <span class="block text-sm text-gray-500 mt-1">Direct card payments via Stripe Elements.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="payment_methods[paypal]" value="0">
                <input type="checkbox" name="payment_methods[paypal]" value="1"
                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                       {{ !empty($methods['paypal']) ? 'checked' : '' }}>
                <span>
                    <span class="block font-medium text-gray-900">PayPal</span>
                    <span class="block text-sm text-gray-500 mt-1">PayPal Smart Buttons checkout.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="payment_methods[lianlian]" value="0">
                <input type="checkbox" name="payment_methods[lianlian]" value="1"
                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                       {{ !empty($methods['lianlian']) ? 'checked' : '' }}>
                <span>
                    <span class="block font-medium text-gray-900">LianLian Pay</span>
                    <span class="block text-sm text-gray-500 mt-1">LianLian iframe card checkout with 3DS.</span>
                </span>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                Save settings
            </button>
        </div>
    </form>
</div>
@endsection
