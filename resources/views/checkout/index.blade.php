@extends('layouts.app')

@section('title', 'Checkout - Bluprinter')

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD&intent=capture&components=buttons"></script>

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    * {
        font-family: 'Inter', sans-serif;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200px 0;
        }
        100% {
            background-position: calc(200px + 100%) 0;
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    .animate-slideInLeft {
        animation: slideInLeft 0.8s ease-out forwards;
    }

    .animate-slideInRight {
        animation: slideInRight 0.8s ease-out forwards;
    }

    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .form-input {
        @apply w-full px-4 py-3 border-2 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200;
        background: #fafafa;
        border: 2px solid #d1d5db;
        border-radius: 12px;
    }

    .form-input:focus {
        background: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        border-color: #3b82f6;
        outline: none;
    }

    .form-input:hover {
        border-color: #9ca3af;
        background: #f9fafb;
    }

    .form-input::placeholder {
        color: #9ca3af;
        font-weight: 400;
    }

    textarea.form-input {
        resize: vertical;
        min-height: 80px;
    }

    select.form-input {
        cursor: pointer;
    }

    select.form-input option {
        padding: 8px;
    }

    .form-label {
        @apply block text-sm font-medium text-gray-700 mb-2;
    }

    /* Payment option styling for new radio button interface */
    .payment-option {
        transition: all 0.3s ease;
    }

    .payment-option:hover {
        @apply border-blue-500 shadow-lg;
    }

    /* Radio button checked state styling - using sibling selector */
    input[type="radio"]:checked + * {
        @apply border-blue-500 bg-blue-50;
    }

    /* Style the label when radio is checked */
    label.payment-option:has(input[type="radio"]:checked) {
        @apply border-blue-500 bg-blue-50;
    }

    /* Fallback for browsers that don't support :has() */
    .payment-option input[type="radio"]:checked {
        @apply text-blue-600 border-blue-600;
    }
    
    /* Alternative approach using JavaScript classes */
    .payment-option.selected {
        @apply border-blue-500 bg-blue-50;
    }

    .checkout-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
    }

    .checkout-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .checkout-btn:hover::before {
        left: 100%;
    }

    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .product-item {
        transition: all 0.3s ease;
        border-radius: 12px;
    }

    .product-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .step-indicator {
        @apply flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white font-semibold text-sm;
    }

    .step-indicator.active {
        @apply bg-gradient-to-r from-blue-500 to-purple-600;
    }

    .step-indicator.completed {
        @apply bg-green-500;
    }

    .security-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 12px;
    }

    .floating-label {
        position: relative;
    }

    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label {
        transform: translateY(-20px) scale(0.85);
        color: #667eea;
    }

    .floating-label label {
        position: absolute;
        left: 12px;
        top: 12px;
        transition: all 0.2s ease;
        pointer-events: none;
        color: #6b7280;
    }

    /* Main containers */
    .checkout-container {
        border-radius: 20px;
    }

    .order-summary-container {
        border-radius: 20px;
    }

    /* LianLian Pay iframe styles */
    #llpay-card-element {
        min-height: 280px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        position: relative;
        overflow: hidden;
    }

    #llpay-card-element iframe {
        width: 100% !important;
        height: 280px !important;
        border: none !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1000 !important;
        background: white !important;
    }

    #lianlian-loading-placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        z-index: 999;
    }

    /* PayPal button styles */
    #paypal-button-container {
        min-height: 200px;
        border-radius: 12px;
        position: relative;
    }

    #paypal-button {
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #paypal-button button {
        border-radius: 8px !important;
        min-height: 48px !important;
    }
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Steps -->
        <div class="mb-8 animate-fadeInUp">
            <div class="flex items-center justify-center space-x-8">
                <div class="flex items-center">
                    <div class="step-indicator completed">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium text-gray-700">Cart</span>
                </div>
                <div class="w-12 h-1 bg-blue-500 rounded"></div>
                <div class="flex items-center">
                    <div class="step-indicator active">2</div>
                    <span class="ml-3 font-semibold text-gray-900">Checkout</span>
                </div>
                <div class="w-12 h-1 bg-gray-300 rounded"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gray-300 text-white font-semibold text-sm flex items-center justify-center">3</div>
                    <span class="ml-3 font-medium text-gray-500">Complete</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-10 animate-fadeInUp">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">
                Complete Your 
                <span class="gradient-text">Order</span>
            </h1>
            <p class="text-lg text-gray-600">Secure checkout with multiple payment options</p>
        </div>

        <div class="flex flex-col lg:grid lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="order-2 lg:order-1 lg:col-span-2 animate-slideInLeft">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden checkout-container">
                    <!-- Modern Header with Gradient -->
                    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 p-6">
                        <div class="flex items-center text-white">
                            <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Shipping Information</h2>
                                <p class="text-blue-100 text-sm">Please provide your delivery details</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8">
                    
                    <form id="checkout-form" method="POST" action="{{ route('checkout.process') }}" class="space-y-8">
                        @csrf
                        
                        <!-- Contact Information -->
                        <div class="space-y-5">
                            <div class="flex items-center space-x-3 pb-3 border-b-2 border-gray-100">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Contact Details</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="relative">
                                    <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Full Name *
                                        </span>
                                    </label>
                                    <input type="text" id="customer_name" name="customer_name" 
                                           class="form-input" required
                                           value="{{ auth()->user() ? auth()->user()->name : '' }}"
                                           placeholder="John Doe">
                                    @error('customer_name')
                                        <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <div class="relative">
                                    <label for="customer_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            Email Address *
                                        </span>
                                    </label>
                                    <input type="email" id="customer_email" name="customer_email" 
                                           class="form-input" required
                                           value="{{ auth()->user() ? auth()->user()->email : '' }}"
                                           placeholder="john@example.com">
                                    @error('customer_email')
                                        <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="relative">
                                <label for="customer_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        Phone Number
                                    </span>
                                </label>
                                <input type="tel" id="customer_phone" name="customer_phone" 
                                       class="form-input"
                                       placeholder="+1 (555) 123-4567">
                                @error('customer_phone')
                                    <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="space-y-5">
                            <div class="flex items-center space-x-3 pb-3 border-b-2 border-gray-100">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Delivery Address</h3>
                            </div>
                            
                            <div class="relative">
                                <label for="shipping_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                        Street Address *
                                    </span>
                                </label>
                                <textarea id="shipping_address" name="shipping_address" 
                                          class="form-input" rows="3" required
                                          placeholder="Street address, apartment, suite, unit, etc."></textarea>
                                @error('shipping_address')
                                    <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div class="relative">
                                    <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            City *
                                        </span>
                                    </label>
                                    <input type="text" id="city" name="city" 
                                           class="form-input" required
                                           placeholder="New York">
                                    @error('city')
                                        <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <div class="relative">
                                    <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                            </svg>
                                            State/Province
                                        </span>
                                    </label>
                                    <input type="text" id="state" name="state" 
                                           class="form-input"
                                           placeholder="NY">
                                    @error('state')
                                        <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                
                                <div class="relative">
                                    <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            Postal Code *
                                        </span>
                                    </label>
                                    <input type="text" id="postal_code" name="postal_code" 
                                           class="form-input" required
                                           placeholder="10001">
                                    @error('postal_code')
                                        <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <div class="relative">
                                <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Country *
                                    </span>
                                </label>
                                <select id="country" name="country" class="form-input" required>
                                    <option value="">Select Country</option>
                                    <option value="US">🇺🇸 United States</option>
                                    <option value="GB">🇬🇧 United Kingdom</option>
                                </select>
                                @error('country')
                                    <p class="text-red-500 text-xs mt-1.5 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="space-y-5">
                            <div class="flex items-center space-x-3 pb-3 border-b-2 border-gray-100">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Payment Method</h3>
                            </div>
                            <div class="space-y-4">
                                <!-- LianLian Pay -->
                                <div class="relative">
                                    <label for="payment_lianlian" class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:shadow-lg transition-all duration-300 payment-option">
                                        <input type="radio" id="payment_lianlian" name="payment_method" value="lianlian_pay" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 mr-4" checked>
                                        <div class="flex items-center flex-1">
                                            <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-xl p-3 mr-4 shadow-md">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-gray-900 text-lg">LianLian Pay</span>
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">RECOMMENDED</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1">Credit Card & Digital Wallet with 3D Secure</p>
                                                <div class="flex items-center mt-2 text-xs text-blue-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>3DS authentication may be required</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- PayPal -->
                                <div class="relative">
                                    <label for="payment_paypal" class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:shadow-lg transition-all duration-300 payment-option">
                                        <input type="radio" id="payment_paypal" name="payment_method" value="paypal" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 mr-4">
                                        <div class="flex items-center flex-1">
                                            <div class="bg-blue-600 rounded-xl p-3 mr-4 shadow-md">
                                                <img src="https://www.paypalobjects.com/webstatic/icon/pp258.png" 
                                                     alt="PayPal" class="h-8 w-8">
                                            </div>
                                            <div class="flex-1">
                                                <span class="font-bold text-gray-900 text-lg">PayPal</span>
                                                <p class="text-sm text-gray-600 mt-1">Safe & secure payment platform</p>
                                                <div class="flex items-center mt-2 text-xs text-green-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Fast and reliable checkout</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <!-- PayPal Button Container -->
                                <div id="paypal-button-container" class="hidden mt-4 p-6 border-2 border-blue-200 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center text-white mr-3">
                                            <img src="https://www.paypalobjects.com/webstatic/icon/pp258.png" 
                                                 alt="PayPal" class="h-6 w-6">
                                        </div>
                                        <h4 class="font-bold text-blue-900 text-lg">💳 PayPal Checkout</h4>
                                    </div>
                                    <!-- PayPal button will be rendered here -->
                                    <div id="paypal-button" class="min-h-[120px] flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="animate-spin w-8 h-8 border-4 border-blue-200 border-t-blue-500 rounded-full mx-auto mb-3"></div>
                                            <p class="text-blue-600 text-sm">Loading PayPal...</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Stripe - Disabled -->
                                <div class="relative opacity-50">
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-not-allowed payment-option">
                                        <input type="radio" name="payment_method" value="stripe" class="w-5 h-5 text-gray-400 border-gray-300 mr-4" disabled>
                                        <div class="flex items-center flex-1">
                                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-3 mr-4 shadow-md">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.274 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.386-2.061 1.386-1.705 0-3.888-.921-5.811-1.758L4.443 24c2.254.893 5.18 1.758 7.83 1.758 2.532 0 4.633-.624 6.123-1.844 1.543-1.271 2.346-3.116 2.346-5.342 0-3.896-2.467-5.76-6.476-7.219z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-gray-900 text-lg">Credit Card (Stripe)</span>
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">COMING SOON</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1">Direct credit card processing</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="space-y-5">
                            <div class="flex items-center space-x-3 pb-3 border-b-2 border-gray-100">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Additional Notes</h3>
                            </div>
                            <div class="relative">
                                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        Order Notes (Optional)
                                    </span>
                                </label>
                                <textarea id="notes" name="notes" 
                                          class="form-input" rows="3"
                                          placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>

                        <!-- LianLian Pay iframe Integration -->
                        <div id="lianlian-pay-info" class="hidden mt-6 p-6 border-2 border-orange-200 rounded-xl bg-gradient-to-r from-orange-50 to-red-50">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-orange-900 text-lg">💳 LianLian Pay Secure Checkout</h4>
                            </div>
                            
                            <!-- Card Type Logos -->
                            <div class="flex gap-3 mb-4 justify-center">
                                <div class="w-12 h-8 bg-gradient-to-r from-blue-600 to-blue-800 rounded text-white text-xs font-bold flex items-center justify-center">VISA</div>
                                <div class="w-12 h-8 bg-gradient-to-r from-red-500 to-red-700 rounded text-white text-xs font-bold flex items-center justify-center">MC</div>
                                <div class="w-12 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded text-white text-xs font-bold flex items-center justify-center">AMEX</div>
                            </div>

                            <!-- LianLian Pay iframe Container -->
                            <div id="llpay-card-element" class="min-h-[280px] border-2 border-gray-200 rounded-xl bg-white relative overflow-hidden">
                                <div id="lianlian-loading-placeholder" class="absolute inset-0 flex items-center justify-center bg-gray-50">
                                    <div class="text-center">
                                        <div class="animate-spin w-8 h-8 border-4 border-orange-200 border-t-orange-500 rounded-full mx-auto mb-3"></div>
                                        <p class="text-gray-600 text-sm">Loading secure payment form...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div class="mt-4 p-3 bg-white/60 rounded-lg border border-orange-200">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm text-orange-800">
                                        <p class="font-semibold mb-1">🔒 Secure Payment</p>
                                        <p>Your card information is encrypted with 256-bit SSL. 3D Secure authentication may be required for additional security.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 pt-6 border-t-2 border-gray-100">
                            <button type="submit" 
                                    class="checkout-btn w-full py-5 px-6 text-white rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl transition-all duration-300">
                                <span class="flex items-center justify-center relative z-10">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Secure Checkout
                                    <svg class="w-6 h-6 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </span>
                            </button>
                            <p class="text-center text-sm text-gray-500 mt-4">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                Your information is protected with 256-bit SSL encryption
                            </p>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-1 lg:order-2 animate-slideInRight">
                <div class="bg-white rounded-2xl shadow-lg p-6 lg:sticky lg:top-8 order-summary-container">
                    <div class="flex items-center mb-6">
                        <div class="w-8 h-8 rounded-lg bg-purple-500 flex items-center justify-center text-white mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Order Summary</h2>
                    </div>
                    
                    <!-- Products -->
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        @foreach($products as $item)
                            <div class="product-item flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                @php
                                    $media = $item['product']->getEffectiveMedia();
                                    $imageUrl = null;
                                    if ($media && count($media) > 0) {
                                        if (is_string($media[0])) {
                                            $imageUrl = $media[0];
                                        } elseif (is_array($media[0])) {
                                            $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
                                        }
                                    }
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $item['product']->name }}"
                                         class="w-12 h-12 object-cover rounded-lg">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 text-sm truncate">{{ Str::limit($item['product']->name, 30) }}</h3>
                                    <p class="text-xs text-gray-600">Qty: {{ $item['quantity'] }}</p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">${{ number_format($item['total'], 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Totals -->
                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span class="shipping-cost-display">${{ number_format($shippingCost, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-3 mt-3">
                            <span>Total</span>
                            <span class="text-blue-600 total-display">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Security Badge -->
                    <div class="mt-6 p-4 security-badge rounded-lg text-white">
                        <div class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="font-semibold">100% Secure Checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Clear any cached data
console.log('🔄 Loading checkout script...', new Date().toISOString());

// Track Facebook Pixel InitiateCheckout event
document.addEventListener('DOMContentLoaded', function() {
    // Get cart data from localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length > 0 && typeof fbq !== 'undefined') {
        // Calculate cart total and collect product IDs
        let cartTotal = 0;
        const productIds = [];
        
        cart.forEach(item => {
            const price = parseFloat(item.price) || 0;
            const quantity = parseInt(item.quantity) || 1;
            cartTotal += price * quantity;
            productIds.push(item.id);
        });
        
        // Track InitiateCheckout event
        fbq('track', 'InitiateCheckout', {
            content_ids: productIds,
            content_type: 'product',
            value: cartTotal.toFixed(2),
            currency: 'USD',
            num_items: cart.length
        });
        
        console.log('✅ Facebook Pixel: InitiateCheckout tracked', {
            items: cart.length,
            total: cartTotal.toFixed(2),
            ids: productIds
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('📅 DOM Content Loaded at:', new Date().toISOString());
    console.log('📱 User Agent:', navigator.userAgent);
    console.log('📱 Is Mobile:', /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
    console.log('🔒 Current protocol:', window.location.protocol);
    console.log('🔒 Current origin:', window.location.origin);
    console.log('🔒 Full URL:', window.location.href);
    
    const form = document.getElementById('checkout-form');
    
    // Check if form exists
    if (!form) {
        console.error('❌ Checkout form not found!');
        return;
    }
    
    console.log('✅ Checkout form found and ready');
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const paymentOptions = document.querySelectorAll('.payment-option');
    
    // LianLian Pay integration
    let isRedirecting3DS = false;
    let lianLianCardInstance = null;
    let iframeToken = null;
    
    // PayPal integration
    let paypalButtonsInitialized = false;
    let paypalSDKLoadAttempts = 0;
    const MAX_PAYPAL_SDK_ATTEMPTS = 50; // 5 seconds max
    
    // Toast notification function
    const showToast = (type, title, message) => {
        Swal.fire({
            icon: type,
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
    };
    
    // Loading state function
    const showLoading = (loading) => {
        if (loading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            `;
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center relative z-10">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Secure Checkout
                    <svg class="w-6 h-6 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </span>
            `;
        }
    };
    
    // Initialize LianLian Pay iframe
    const initializeLianLianIframe = async () => {
        try {
            console.log('🚀 Initializing LianLian Pay iframe...');
            
            // Get token first
            const tokenUrl = new URL('/payment/lianlian/token', window.location.origin);
            console.log('📡 Token URL:', tokenUrl.toString());
            
            const tokenResponse = await fetch(tokenUrl.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                },
                credentials: 'same-origin',
                mode: 'same-origin'
            });
            
            if (!tokenResponse.ok) {
                throw new Error(`Token request failed: ${tokenResponse.status} ${tokenResponse.statusText}`);
            }
            
            const tokenData = await tokenResponse.json();
            console.log('🎫 Token response:', tokenData);
            
            if (!tokenData.success) {
                throw new Error(tokenData.message || 'Failed to get payment token');
            }
            
            iframeToken = tokenData.token;
            console.log('✅ Token received:', iframeToken);
            
            // Load LianLian SDK
            if (!window.LLP) {
                const script = document.createElement('script');
                script.src = 'https://secure-checkout.lianlianpay.com/v2/llpay.min.js';
                script.async = true;
                
                await new Promise((resolve, reject) => {
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
                
                if (!window.LLP) {
                    throw new Error('LianLian Pay SDK failed to load');
                }
            }
            
            // Set language
            window.LLP.setLanguage('en-US');
            
            // Create card element
            const elements = window.LLP.elements();
            lianLianCardInstance = elements.create('card', {
                token: iframeToken,
                style: {
                    base: {
                        backgroundColor: '#f8f8f8',
                        borderColor: '#f1f1f1',
                        color: '#bcbcbc',
                        fontWeight: '400',
                        fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
                        fontSize: '14px',
                        fontSmoothing: 'antialiased',
                        floatLabelSize: '12px',
                        floatLabelColor: '#333333',
                        floatLabelWeight: '100',
                    },
                },
                merchantUrl: window.location.origin,
            });
            
            // Mount the card
            lianLianCardInstance.mount('#llpay-card-element');
            
            // Hide loading placeholder after mount
            setTimeout(() => {
                const loadingPlaceholder = document.getElementById('lianlian-loading-placeholder');
                if (loadingPlaceholder) {
                    loadingPlaceholder.style.display = 'none';
                }
                console.log('✅ LianLian Pay iframe initialized successfully');
            }, 2000);
            
        } catch (error) {
            console.error('❌ LianLian Pay iframe initialization error:', error);
            showToast('error', 'Payment Error', 'Failed to initialize payment form: ' + error.message);
            
            // Show error in iframe container
            const iframeContainer = document.getElementById('llpay-card-element');
            if (iframeContainer) {
                iframeContainer.innerHTML = `
                    <div class="flex items-center justify-center h-full text-red-500">
                        <div class="text-center">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">Failed to load payment form</p>
                        </div>
                    </div>
                `;
            }
        }
    };
    
    // Initialize PayPal buttons
    const initializePayPalButtons = async () => {
        try {
            console.log('🚀 Initializing PayPal buttons...');
            
            if (paypalButtonsInitialized) {
                console.log('⚠️ PayPal buttons already initialized');
                return;
            }
            
            if (!window.paypal) {
                console.error('❌ PayPal SDK not loaded. Cannot initialize buttons.');
                showToast('error', 'PayPal Error', 'PayPal SDK is not loaded. Please refresh the page and try again.');
                return;
            }
            
            const paypalButtonContainer = document.getElementById('paypal-button');
            if (!paypalButtonContainer) {
                console.error('❌ PayPal button container not found');
                return;
            }
            
            // Clear any existing content
            paypalButtonContainer.innerHTML = '';
            
            // Calculate total amount
            const subtotal = parseFloat('{{ $subtotal }}');
            const tax = parseFloat('{{ $taxAmount }}');
            const shipping = parseFloat('{{ $shippingCost }}');
            const total = subtotal + tax + shipping;
            
            // Get order data from form
            const checkoutForm = document.getElementById('checkout-form');
            const orderData = {
                customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
                customer_email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
                customer_phone: checkoutForm.querySelector('[name="customer_phone"]')?.value?.trim() || '',
                shipping_address: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
                city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
                state: checkoutForm.querySelector('[name="state"]')?.value?.trim() || '',
                postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
                country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || '',
                notes: checkoutForm.querySelector('[name="notes"]')?.value?.trim() || '',
            };
            
            // Render PayPal buttons
            window.paypal.Buttons({
                style: {
                    color: 'blue',
                    shape: 'rect',
                    label: 'paypal',
                    height: 48
                },
                
                // Set up the transaction
                createOrder: async function(data, actions) {
                    console.log('📝 Creating PayPal order for SDK...');
                    
                    try {
                        // Validate form data before creating PayPal order
                        const checkoutForm = document.getElementById('checkout-form');
                        const requiredFields = {
                            customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
                            customer_email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
                            shipping_address: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
                            city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
                            postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
                            country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || ''
                        };
                        
                        // Check for missing required fields
                        const missingFields = [];
                        Object.entries(requiredFields).forEach(([field, value]) => {
                            if (!value) {
                                missingFields.push(field.replace('_', ' '));
                            }
                        });
                        
                        if (missingFields.length > 0) {
                            showToast('error', 'Thiếu thông tin', 'Vui lòng điền đầy đủ thông tin: ' + missingFields.join(', '));
                            throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
                        }
                        
                        // Validate email format
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(requiredFields.customer_email)) {
                            showToast('error', 'Email không hợp lệ', 'Vui lòng nhập địa chỉ email hợp lệ');
                            throw new Error('Invalid email format');
                        }
                        
                        console.log('✅ Form validation passed, creating PayPal order...');
                        
                        // Calculate total amount for PayPal
                        const subtotal = parseFloat('{{ $subtotal }}');
                        const tax = parseFloat('{{ $taxAmount }}');
                        const shipping = parseFloat('{{ $shippingCost }}');
                        const total = subtotal + tax + shipping;
                        
                        // Create order on PayPal side using actions.order.create()
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: total.toFixed(2),
                                    currency_code: 'USD'
                                },
                                description: 'Order from Bluprinter',
                                custom_id: 'order-' + Date.now()
                            }]
                        });
                        
                    } catch (error) {
                        console.error('❌ Error creating PayPal order:', error);
                        if (!error.message.includes('Missing required fields') && !error.message.includes('Invalid email')) {
                            showToast('error', 'PayPal Error', 'Failed to create PayPal order: ' + error.message);
                        }
                        throw error;
                    }
                },
                
                // Finalize the transaction
                onApprove: async function(data, actions) {
                    console.log('✅ PayPal payment approved:', data);
                    
                    try {
                        showLoading(true);
                        
                        // Capture the payment details
                        const details = await actions.order.capture();
                        console.log('💰 Payment captured:', details);
                        
                        // Get fresh order data from form at the time of approval
                        const checkoutForm = document.getElementById('checkout-form');
                        const currentOrderData = {
                            customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
                            customer_email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
                            customer_phone: checkoutForm.querySelector('[name="customer_phone"]')?.value?.trim() || '',
                            shipping_address: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
                            city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
                            state: checkoutForm.querySelector('[name="state"]')?.value?.trim() || '',
                            postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
                            country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || '',
                            notes: checkoutForm.querySelector('[name="notes"]')?.value?.trim() || '',
                        };
                        
                        console.log('📋 Sending order data:', currentOrderData);
                        
                        // Validate required fields before sending
                        const requiredFields = ['customer_name', 'customer_email', 'shipping_address', 'city', 'postal_code', 'country'];
                        const missingFields = requiredFields.filter(field => !currentOrderData[field]);
                        
                        if (missingFields.length > 0) {
                            throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
                        }
                        
                        // Now create order on our server and process with PayPal order ID
                        const response = await fetch('{{ route("checkout.process") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                ...currentOrderData,
                                payment_method: 'paypal',
                                paypal_order_id: data.orderID,
                                paypal_payer_id: data.payerID
                            }),
                            credentials: 'same-origin',
                            mode: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            // Handle validation errors (422) specifically
                            if (response.status === 422) {
                                const errorData = await response.json();
                                console.error('❌ Validation Error:', errorData);
                                throw new Error(`Validation failed: ${errorData.message || Object.values(errorData.errors || {}).flat().join(', ')}`);
                            } else {
                                throw new Error(`Order processing failed: ${response.status} ${response.statusText}`);
                            }
                        }
                        
                        const responseData = await response.json();
                        console.log('📋 Order processed:', responseData);
                        
                        if (responseData.success) {
                            showToast('success', 'Payment successful!', 'Your payment has been processed successfully');
                            
                            // Redirect to success page
                            setTimeout(() => {
                                // Since the current flow redirects, we'll wait for redirect or handle success
                                if (responseData.order_number) {
                                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                                } else {
                                    window.location.href = '{{ route("checkout.index") }}';
                                }
                            }, 2000);
                        } else {
                            throw new Error(responseData.message || 'Order processing failed');
                        }
                        
                    } catch (error) {
                        console.error('❌ Error processing payment:', error);
                        showToast('error', 'Payment Error', 'Payment processing failed: ' + error.message);
                    } finally {
                        showLoading(false);
                    }
                },
                
                onError: function(err) {
                    console.error('❌ PayPal error:', err);
                    showToast('error', 'PayPal Error', 'An error occurred during payment: ' + (err.message || 'Unknown error'));
                    showLoading(false);
                },
                
                onCancel: function(data) {
                    console.log('⚠️ PayPal payment cancelled:', data);
                    showToast('info', 'Payment Cancelled', 'Payment was cancelled by user');
                    showLoading(false);
                }
                
            }).render('#paypal-button');
            
            paypalButtonsInitialized = true;
            console.log('✅ PayPal buttons initialized successfully');
            
            // Update button state after rendering
            setTimeout(() => updatePayPalButtonState(), 100);
            
        } catch (error) {
            console.error('❌ PayPal buttons initialization error:', error);
            showToast('error', 'PayPal Error', 'Failed to initialize PayPal: ' + error.message);
            
            const paypalButtonContainer = document.getElementById('paypal-button');
            if (paypalButtonContainer) {
                paypalButtonContainer.innerHTML = `
                    <div class="flex items-center justify-center h-full text-red-500">
                        <div class="text-center">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">Failed to load PayPal</p>
                        </div>
                    </div>
                `;
            }
        }
    };
    
    // Function to validate form fields
    const validateCheckoutForm = function() {
        const checkoutForm = document.getElementById('checkout-form');
        if (!checkoutForm) return false;
        
        const requiredFields = {
            customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
            customer_email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
            shipping_address: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
            city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
            postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
            country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || ''
        };
        
        // Check for missing required fields
        const missingFields = [];
        Object.entries(requiredFields).forEach(([field, value]) => {
            if (!value) {
                missingFields.push(field.replace('_', ' '));
            }
        });
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isEmailValid = emailRegex.test(requiredFields.customer_email);
        
        return {
            isValid: missingFields.length === 0 && isEmailValid && requiredFields.customer_email,
            missingFields: missingFields,
            isEmailValid: isEmailValid
        };
    };
    
    // Function to update PayPal button state
    const updatePayPalButtonState = function() {
        const paypalContainer = document.getElementById('paypal-button-container');
        const validation = validateCheckoutForm();
        
        if (paypalContainer) {
            const paypalButtons = paypalContainer.querySelector('#paypal-button');
            
            if (!validation.isValid) {
                // Add disabled styling
                paypalContainer.classList.add('opacity-50', 'pointer-events-none');
                
                // Add warning message if not already present
                let warningMsg = paypalContainer.querySelector('.validation-warning');
                if (!warningMsg) {
                    warningMsg = document.createElement('div');
                    warningMsg.className = 'validation-warning mt-3 p-3 bg-yellow-100 border border-yellow-300 rounded-lg';
                    
                    if (validation.missingFields.length > 0) {
                        warningMsg.innerHTML = `
                            <div class="flex items-center text-yellow-800">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium">Vui lòng điền đầy đủ thông tin: ${validation.missingFields.join(', ')}</span>
                            </div>
                        `;
                    } else if (!validation.isEmailValid) {
                        warningMsg.innerHTML = `
                            <div class="flex items-center text-yellow-800">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium">Vui lòng nhập địa chỉ email hợp lệ</span>
                            </div>
                        `;
                    }
                    
                    paypalContainer.appendChild(warningMsg);
                }
            } else {
                // Remove disabled styling
                paypalContainer.classList.remove('opacity-50', 'pointer-events-none');
                
                // Remove warning message
                const warningMsg = paypalContainer.querySelector('.validation-warning');
                if (warningMsg) {
                    warningMsg.remove();
                }
            }
        }
    };
    
    // Payment method change handler
    const handlePaymentMethodChange = function() {
        const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
        const lianLianInfo = document.getElementById('lianlian-pay-info');
        const paypalContainer = document.getElementById('paypal-button-container');
        
        // Remove selected class from all payment options
        paymentOptions.forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selected class to the parent label of checked radio
        if (selectedRadio) {
            const label = selectedRadio.closest('.payment-option');
            if (label) {
                label.classList.add('selected');
            }
        }
        
        console.log('💳 Payment method changed:', selectedRadio ? selectedRadio.value : 'none');
        
        if (selectedRadio && selectedRadio.value === 'lianlian_pay') {
            console.log('🔧 LianLian Pay selected - initializing iframe');
            // Hide PayPal container
            if (paypalContainer) {
                paypalContainer.classList.add('hidden');
            }
            // Show LianLian Pay
            if (lianLianInfo) {
                lianLianInfo.classList.remove('hidden');
                // Initialize iframe if not already done
                if (!lianLianCardInstance && !iframeToken) {
                    initializeLianLianIframe();
                }
            }
        } else if (selectedRadio && selectedRadio.value === 'paypal') {
            console.log('🔧 PayPal selected - initializing buttons');
            // Hide LianLian Pay
            if (lianLianInfo) {
                lianLianInfo.classList.add('hidden');
            }
            // Show PayPal container
            if (paypalContainer) {
                paypalContainer.classList.remove('hidden');
                // Initialize PayPal buttons if not already done
                if (!paypalButtonsInitialized && window.paypal) {
                    initializePayPalButtons();
                }
                // Update PayPal button state based on form validation
                setTimeout(() => updatePayPalButtonState(), 100);
            }
        } else {
            console.log('🔧 Hiding payment methods...');
            // Hide both containers
            if (lianLianInfo) {
                lianLianInfo.classList.add('hidden');
            }
            if (paypalContainer) {
                paypalContainer.classList.add('hidden');
            }
        }
    };

    // Listen for radio button changes
    document.addEventListener('change', function(e) {
        if (e.target.name === 'payment_method' && e.target.type === 'radio') {
            console.log('💳 Radio button changed:', e.target.value, 'checked:', e.target.checked);
            handlePaymentMethodChange();
        }
    });
    
    // Set default selection to LianLian Pay
    const defaultPaymentRadio = document.querySelector('input[value="lianlian_pay"]');
    if (defaultPaymentRadio) {
        console.log('🎯 Setting default to LianLian Pay');
        // Ensure it's checked
        defaultPaymentRadio.checked = true;
        
        // Show LianLian Pay info since it's the default
        handlePaymentMethodChange();
        
        console.log('✅ Default payment method set to LianLian Pay');
    }
    
    // Initialize PayPal SDK when ready
    const initializePayPalSDK = () => {
        paypalSDKLoadAttempts++;
        
        if (window.paypal) {
            console.log('✅ PayPal SDK loaded successfully');
            return true;
        } else if (paypalSDKLoadAttempts >= MAX_PAYPAL_SDK_ATTEMPTS) {
            console.error('❌ PayPal SDK failed to load after', paypalSDKLoadAttempts, 'attempts');
            console.log('🔍 Checking PayPal configuration...');
            
            // Check if client ID is configured
            const paypalScript = document.querySelector('script[src*="paypal.com/sdk/js"]');
            if (paypalScript && paypalScript.src.includes('client-id=')) {
                const clientIdMatch = paypalScript.src.match(/client-id=([^&]+)/);
                if (clientIdMatch && clientIdMatch[1] && !clientIdMatch[1].includes('null') && clientIdMatch[1] !== '') {
                    console.log('⚠️ PayPal SDK script loaded but window.paypal not available. This might be a network issue.');
                } else {
                    console.error('❌ PayPal Client ID not configured properly');
                }
            } else {
                console.error('❌ PayPal script not found or malformed');
            }
            return false;
        } else {
            console.log('⏳ Waiting for PayPal SDK to load...', `(${paypalSDKLoadAttempts}/${MAX_PAYPAL_SDK_ATTEMPTS})`);
            setTimeout(initializePayPalSDK, 100);
            return false;
        }
    };
    
    // Start checking for PayPal SDK
    initializePayPalSDK();
    
    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if payment method is selected
        const selectedPaymentRadio = form.querySelector('input[name="payment_method"]:checked');
        if (!selectedPaymentRadio) {
            showToast('error', 'Payment Error', 'Please select a payment method');
            return;
        }
        
        const selectedPaymentMethod = selectedPaymentRadio.value;
        console.log('💳 Selected payment method:', selectedPaymentMethod);
        
        // Validate form data before proceeding
        const requiredFields = ['customer_name', 'customer_email', 'shipping_address', 'city', 'postal_code', 'country'];
        const missingFields = [];
        
        requiredFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field || !field.value.trim()) {
                missingFields.push(fieldName);
            }
        });
        
        if (missingFields.length > 0) {
            showToast('error', 'Form Error', 'Please fill in all required fields: ' + missingFields.join(', '));
            return;
        }
        
        if (selectedPaymentMethod === 'lianlian_pay') {
            handleLianLianPayment();
        } else if (selectedPaymentMethod === 'paypal') {
            // PayPal is handled by the SDK, just show info
            showToast('info', 'PayPal Checkout', 'Please use the PayPal button below to complete your payment');
            return;
        } else {
            handleRegularPayment();
        }
    });
    
    // Handle LianLian Pay payment using iframe
    const handleLianLianPayment = async () => {
        try {
            console.log('🚀 Processing LianLian Pay payment via iframe...');
            showLoading(true);
            
            if (!lianLianCardInstance || !window.LLP) {
                throw new Error('Payment form not initialized. Please refresh and try again.');
            }
            
            // Validate card information using SDK
            const validateResult = await window.LLP.getValidateResult();
            if (!validateResult || !validateResult.validateResult) {
                showToast('error', 'Payment Error', 'Please fill in all card information correctly.');
                showLoading(false);
                return;
            }
            
            // Get card token from SDK
            const paymentResult = await window.LLP.confirmPay();
            console.log('Payment result:', paymentResult);
            
            if (!paymentResult || !paymentResult.data) {
                throw new Error('Failed to process card. Please check your information and try again.');
            }
            
            const cardToken = paymentResult.data;
            console.log('✅ Card token generated:', cardToken);
            
            // Process payment with server
            await processLianLianPayment(cardToken);
            
        } catch (error) {
            console.error('❌ LianLian Pay payment error:', error);
            showToast('error', 'Payment Error', error.message);
            showLoading(false);
        }
    };
    
    // Process payment with server using card token
    const processLianLianPayment = async (cardToken) => {
        try {
            console.log('📤 Processing payment with server...');
            
            // Get form data for order creation
            const checkoutForm = document.getElementById('checkout-form');
            const orderData = {
                customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
                customer_email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
                customer_phone: checkoutForm.querySelector('[name="customer_phone"]')?.value?.trim() || '',
                shipping_address: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
                city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
                state: checkoutForm.querySelector('[name="state"]')?.value?.trim() || '',
                postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
                country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || '',
                payment_method: 'lianlian_pay',
                notes: checkoutForm.querySelector('[name="notes"]')?.value?.trim() || '',
            };
            
            // Validate order data
            const requiredFields = ['customer_name', 'customer_email', 'shipping_address', 'city', 'postal_code', 'country'];
            const missingFields = requiredFields.filter(field => !orderData[field]);
            
            if (missingFields.length > 0) {
                throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
            }
            
            // Create order first
            console.log('📦 Creating order...');
            const checkoutUrl = new URL('/checkout/process', window.location.origin);
            
            const orderResponse = await fetch(checkoutUrl.toString(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(orderData),
                credentials: 'same-origin',
                mode: 'same-origin'
            });
            
            if (!orderResponse.ok) {
                const errorText = await orderResponse.text();
                throw new Error(`Order creation failed: ${orderResponse.status} ${orderResponse.statusText}`);
            }
            
            const orderResult = await orderResponse.json();
            console.log('📦 Order creation result:', orderResult);
            
            if (!orderResult.success) {
                throw new Error(orderResult.message || 'Failed to create order');
            }
            
            // Process payment with card token
            const paymentData = {
                card_token: cardToken,
                payment_method: 'lianlian_pay',
                order_id: orderResult.order_id,
                amount: '{{ $total }}',
            };
            
            console.log('📤 Sending payment data:', paymentData);
            
            const paymentResponse = await fetch('{{ route("payment.lianlian.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(paymentData)
            });
            
            const responseData = await paymentResponse.json();
            console.log('📥 Payment response:', responseData);
            
            if (responseData.success) {
                // Check if 3DS authentication is required
                if (responseData.requires_3ds === true && responseData.redirect_url) {
                    console.log('🔐 3DS Authentication Required');
                    await handle3DSRedirect(responseData.redirect_url, responseData.transaction_id);
                    return;
                }
                
                // Check if payment is completed immediately
                if (responseData.payment_completed === true || responseData.payment_status === 'paid') {
                    console.log('✅ Payment Completed Immediately');
                    showToast('success', 'Payment successful! Redirecting...', 'Payment completed successfully');
                    setTimeout(() => {
                        if (responseData.order_number) {
                            window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                        } else {
                            window.location.href = '{{ route("checkout.index") }}';
                        }
                    }, 2000);
                } else {
                    // Payment pending
                    console.log('⏳ Payment Pending');
                    showToast('info', 'Payment is processing...', 'Your payment is being processed');
                    setTimeout(() => {
                        if (responseData.order_number) {
                            window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                        } else {
                            window.location.href = '{{ route("checkout.index") }}';
                        }
                    }, 2000);
                }
            } else {
                const errorMessage = responseData.message || 'Payment failed';
                const errorCode = responseData.return_code || 'Unknown error';
                showToast('error', 'Payment Error', `Payment failed (${errorCode}): ${errorMessage}`);
            }
            
        } catch (error) {
            console.error('❌ Server payment error:', error);
            showToast('error', 'Payment Error', 'Payment processing failed: ' + error.message);
        } finally {
            showLoading(false);
        }
    };
    
    
    // Handle regular payment (PayPal) - improved for mobile
    const handleRegularPayment = async () => {
        try {
            console.log('🔄 Processing PayPal payment...');
            showLoading(true);
            
            // Double-check form validation before submitting
            const requiredFields = ['customer_name', 'customer_email', 'shipping_address', 'city', 'postal_code', 'country'];
            const missingFields = [];
            
            requiredFields.forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (!field || !field.value.trim()) {
                    missingFields.push(fieldName);
                }
            });
            
            if (missingFields.length > 0) {
                showLoading(false);
                showToast('error', 'Form Error', 'Please fill in all required fields: ' + missingFields.join(', '));
                return;
            }
            
            // Submit form after validation
            console.log('✅ Form validated, submitting to PayPal...');
            setTimeout(() => {
                form.submit();
            }, 300);
            
        } catch (error) {
            console.error('❌ PayPal payment error:', error);
            showLoading(false);
            showToast('error', 'Payment Error', 'Failed to process payment: ' + error.message);
        }
    };
    
    // Handle 3DS redirect
    const handle3DSRedirect = async (redirectUrl, transactionId) => {
        const { value: confirmRedirect } = await Swal.fire({
            title: '3DS Authentication Required',
            html: `
                <div style="text-align: left; margin: 20px 0;">
                    <p style="margin-bottom: 15px; color: #333;">
                        <i class="fas fa-shield-alt" style="color: #007bff; margin-right: 8px;"></i>
                        Your bank requires additional verification for this payment.
                    </p>
                    <p style="margin-bottom: 15px; color: #666;">
                        You will be redirected to your bank's secure page to complete the verification process.
                    </p>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;">
                        <p style="margin: 0; font-weight: 500; color: #495057;">
                            <i class="fas fa-info-circle" style="color: #007bff; margin-right: 5px;"></i>
                            Please complete the verification and you will be redirected back automatically.
                        </p>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-external-link-alt"></i> Continue to Bank',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            width: '500px'
        });
        
        if (confirmRedirect) {
            sessionStorage.setItem('pending_3ds_transaction', JSON.stringify({
                transaction_id: transactionId,
                timestamp: Date.now(),
                redirect_url: redirectUrl
            }));
            
            isRedirecting3DS = true;
            showToast('info', 'Redirecting to 3DS Authentication...', 'Please wait while we redirect you to your bank');
            
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1500);
        }
    };
    
    // Calculate shipping when country changes
    const countrySelect = document.getElementById('country');
    if (countrySelect) {
        countrySelect.addEventListener('change', async function() {
            const country = this.value;
            
            if (!country) {
                return;
            }
            
            try {
                // Ensure HTTPS for shipping calculation
                const shippingUrl = new URL('{{ route('checkout.calculate-shipping') }}', window.location.origin);
                console.log('🚚 Shipping calculation URL:', shippingUrl.toString());
                
                const response = await fetch(shippingUrl.toString(), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ country: country }),
                    credentials: 'same-origin',
                    mode: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success && data.shipping) {
                    // Update shipping cost display
                    const shippingCostElement = document.querySelector('.shipping-cost-display');
                    const totalElement = document.querySelector('.total-display');
                    
                    if (shippingCostElement) {
                        const newShipping = parseFloat(data.shipping.total_shipping);
                        shippingCostElement.textContent = '$' + newShipping.toFixed(2);
                        
                        // Recalculate total
                        const subtotal = parseFloat('{{ $subtotal }}');
                        const tax = parseFloat('{{ $taxAmount }}');
                        const newTotal = subtotal + tax + newShipping;
                        
                        if (totalElement) {
                            totalElement.textContent = '$' + newTotal.toFixed(2);
                        }
                        
                        // showToast('success', 'Shipping Updated', 
                        //     `Shipping to ${data.shipping.zone_name}: $${newShipping.toFixed(2)}`);
                    }
                } else {
                    // showToast('error', 'Shipping Error', data.message || 'Could not calculate shipping');
                }
            } catch (error) {
                console.error('Shipping calculation error:', error);
                // showToast('error', 'Error', 'Could not calculate shipping cost');
            }
        });
    }
    
    // Auto-detect country
    fetch('https://ipapi.co/json/')
        .then(response => response.json())
        .then(data => {
            if (data.country_code) {
                const countrySelect = document.getElementById('country');
                // Only allow US and GB, fallback to US if detected country is not available
                let selectedCountry = data.country_code;
                if (data.country_code !== 'US' && data.country_code !== 'GB') {
                    selectedCountry = 'US'; // Default to US if country not available
                }
                
                const option = countrySelect.querySelector(`option[value="${selectedCountry}"]`);
                if (option) {
                    option.selected = true;
                    // Trigger shipping calculation for detected country
                    countrySelect.dispatchEvent(new Event('change'));
                }
            }
        })
        .catch(error => console.log('Could not detect country'));
    
    // Add event listeners for form field changes to update PayPal button state
    const formFieldsToWatch = [
        'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'city', 'state', 'postal_code', 'country'
    ];
    
    formFieldsToWatch.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('input', updatePayPalButtonState);
            field.addEventListener('change', updatePayPalButtonState);
            field.addEventListener('blur', updatePayPalButtonState);
        }
    });
    
    // Initial validation when page loads
    setTimeout(() => {
        updatePayPalButtonState();
    }, 500);
    
    // Cleanup iframe on page unload
    window.addEventListener('beforeunload', () => {
        if (lianLianCardInstance) {
            try {
                lianLianCardInstance.unmount();
            } catch (e) {
                console.log('Error unmounting LianLian Pay:', e);
            }
        }
    });
});
</script>
@endsection