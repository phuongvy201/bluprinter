@extends('layouts.app')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <header class="section-heading section-heading--catalog mb-8">
            <p class="section-heading__eyebrow">Account</p>
            <h1 class="section-heading__title">My <span class="gradient-text">profile</span></h1>
            <p class="section-heading__sub">Your details, orders, and wishlist in one place.</p>
            <span class="section-heading__accent"></span>
        </header>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800" role="status">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-6">
                <div class="commerce-card overflow-hidden p-0">
                    <div class="bg-[#005366] px-6 py-8 text-center">
                        <div class="mb-4">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full mx-auto border-4 border-white object-cover">
                            @else
                                <div class="w-24 h-24 rounded-full mx-auto border-4 border-white bg-white flex items-center justify-center">
                                    <span class="text-3xl font-bold text-[#005366]">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <h2 class="text-2xl font-bold text-white">{{ $user->name }}</h2>
                        <p class="mt-1 text-white/80">{{ $user->email }}</p>
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center mt-3 px-3 py-1 bg-green-50 text-green-800 text-xs font-semibold rounded-full">Verified</span>
                        @else
                            <span class="inline-flex items-center mt-3 px-3 py-1 bg-amber-50 text-amber-800 text-xs font-semibold rounded-full">Unverified</span>
                        @endif
                    </div>
                    <div class="p-6">
                        <a href="{{ route('customer.profile.edit') }}" class="btn-cta btn-cta--block">Edit profile</a>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Member since</span>
                                <span class="font-semibold text-gray-900">{{ $user->created_at->format('M Y') }}</span>
                            </div>
                            @if($user->phone)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Phone</span>
                                    <span class="font-semibold text-gray-900">{{ $user->phone }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="commerce-card">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Account statistics</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <span class="text-gray-600">Total orders</span>
                            <span class="text-xl font-bold text-gray-900">{{ $stats['total_orders'] }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <span class="text-gray-600">Total spent</span>
                            <span class="text-xl font-bold text-gray-900">{{ format_price((float) $stats['total_spent']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Wishlist items</span>
                            <span class="text-xl font-bold text-gray-900">{{ $stats['wishlist_items'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="commerce-card p-0 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-[#f7f7f7]">
                        <h3 class="text-xl font-bold text-gray-900">Personal information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">Full name</p>
                            <p class="text-gray-900 font-semibold">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">Email</p>
                            <p class="text-gray-900 font-semibold">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">Phone</p>
                            <p class="text-gray-900 font-semibold">{{ $user->phone ?: 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">Account status</p>
                            <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-800 text-sm font-medium rounded-full">Active</span>
                        </div>
                    </div>
                </div>

                <div class="commerce-card p-0 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-[#f7f7f7]">
                        <h3 class="text-xl font-bold text-gray-900">Address</h3>
                    </div>
                    <div class="p-6">
                        @if($user->address || $user->city || $user->state || $user->postal_code || $user->country)
                            <div class="space-y-3">
                                @if($user->address)
                                    <div>
                                        <p class="text-sm font-semibold text-gray-600 mb-1">Street</p>
                                        <p class="text-gray-900">{{ $user->address }}</p>
                                    </div>
                                @endif
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @if($user->city)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 mb-1">City</p>
                                            <p class="text-gray-900">{{ $user->city }}</p>
                                        </div>
                                    @endif
                                    @if($user->state)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 mb-1">State</p>
                                            <p class="text-gray-900">{{ $user->state }}</p>
                                        </div>
                                    @endif
                                    @if($user->postal_code)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 mb-1">Postal code</p>
                                            <p class="text-gray-900">{{ $user->postal_code }}</p>
                                        </div>
                                    @endif
                                    @if($user->country)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-600 mb-1">Country</p>
                                            <p class="text-gray-900">{{ $user->country }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-600 mb-4">No address on file yet.</p>
                                <a href="{{ route('customer.profile.edit') }}" class="font-semibold text-[#005366] hover:text-[#003d4d]">Add address</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('customer.orders.index') }}" class="commerce-card hover:shadow-lg">
                        <h4 class="text-lg font-bold text-gray-900">My orders</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $stats['total_orders'] }} orders</p>
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="commerce-card hover:shadow-lg">
                        <h4 class="text-lg font-bold text-gray-900">Wishlist</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $stats['wishlist_items'] }} items</p>
                    </a>
                    <a href="{{ route('customer.profile.edit') }}" class="commerce-card hover:shadow-lg">
                        <h4 class="text-lg font-bold text-gray-900">Settings</h4>
                        <p class="text-sm text-gray-600 mt-1">Edit profile</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
