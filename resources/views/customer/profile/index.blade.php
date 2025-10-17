@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-[#005366] to-[#003d4d] px-6 py-8 text-center">
                    <div class="mb-4">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full mx-auto border-4 border-white shadow-lg object-cover">
                        @else
                            <div class="w-24 h-24 rounded-full mx-auto border-4 border-white shadow-lg bg-white flex items-center justify-center">
                                <span class="text-3xl font-bold text-[#005366]">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-1">{{ $user->name }}</h2>
                    <p class="text-blue-100">{{ $user->email }}</p>
                    
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center mt-3 px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Verified
                        </span>
                    @else
                        <span class="inline-flex items-center mt-3 px-3 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-full">
                            Unverified
                        </span>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="p-6">
                    <a href="{{ route('customer.profile.edit') }}" class="w-full flex items-center justify-center px-4 py-3 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors mb-3">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profile
                    </a>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Member since</span>
                            <span class="font-semibold text-gray-900">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        @if($user->phone)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Phone</span>
                                <span class="font-semibold text-gray-900">{{ $user->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Stats -->
            <div class="bg-white rounded-xl shadow-md p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Statistics</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <span class="text-gray-600">Total Orders</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ $stats['total_orders'] }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-gray-600">Total Spent</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">${{ number_format($stats['total_spent'], 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <span class="text-gray-600">Wishlist Items</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ $stats['wishlist_items'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                            <p class="text-gray-900 font-semibold">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                            <p class="text-gray-900 font-semibold">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                            <p class="text-gray-900 font-semibold">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Account Status</label>
                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Address Information</h3>
                </div>
                <div class="p-6">
                    @if($user->address || $user->city || $user->state || $user->postal_code || $user->country)
                        <div class="space-y-3">
                            @if($user->address)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Street Address</label>
                                    <p class="text-gray-900">{{ $user->address }}</p>
                                </div>
                            @endif
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @if($user->city)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">City</label>
                                        <p class="text-gray-900">{{ $user->city }}</p>
                                    </div>
                                @endif
                                @if($user->state)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">State</label>
                                        <p class="text-gray-900">{{ $user->state }}</p>
                                    </div>
                                @endif
                                @if($user->postal_code)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Postal Code</label>
                                        <p class="text-gray-900">{{ $user->postal_code }}</p>
                                    </div>
                                @endif
                                @if($user->country)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Country</label>
                                        <p class="text-gray-900">{{ $user->country }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-gray-500 mb-4">No address information provided</p>
                            <a href="{{ route('customer.profile.edit') }}" class="text-[#005366] hover:text-[#003d4d] font-semibold">
                                Add Address →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('customer.orders.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">My Orders</h4>
                            <p class="text-sm text-gray-600">{{ $stats['total_orders'] }} orders</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('wishlist.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Wishlist</h4>
                            <p class="text-sm text-gray-600">{{ $stats['wishlist_items'] }} items</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('customer.profile.edit') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Settings</h4>
                            <p class="text-sm text-gray-600">Edit profile</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

