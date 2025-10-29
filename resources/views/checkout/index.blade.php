@extends('layouts.app')

@section('title', 'Checkout - Bluprinter')

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD&intent=capture&components=buttons"></script>

<!-- Stripe JS SDK -->
<script src="https://js.stripe.com/v3/"></script>

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    * {
        font-family: 'Inter', sans-serif;
    }

function buildCheckoutCustomizationInputs(customizations) {
    var html = '';
    if (!customizations) return html;
    Object.keys(customizations).forEach(function(k){
        var v = customizations[k] || {};
        var value = v && v.value ? String(v.value).replace(/"/g, '&quot;') : '';
        html += '<div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-center">'
             + '<div class="sm:col-span-2"><span class="text-sm text-gray-600">' + k + '</span></div>'
             + '<div class="sm:col-span-3">'
             + '<input type="text" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 checkout-customization-input" data-label="' + k + '" value="' + value + '" oninput="updateCheckoutModalTotal()" title="' + value + '" />'
             + '</div>'
             + '</div>';
    });
    return html;
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


    .product-item {
        @apply transition-all duration-300 rounded-xl hover:shadow-lg hover:transform hover:translate-x-1;
    }

    .step-indicator {
        @apply flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white font-semibold text-sm shadow-md;
    }

    .step-indicator.active {
        @apply bg-gradient-to-r from-blue-500 to-purple-600 shadow-lg;
    }

    .step-indicator.completed {
        @apply bg-green-500 shadow-lg;
    }


    .floating-label {
        @apply relative;
    }

    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label {
        @apply -translate-y-5 scale-90 text-blue-600;
    }

    .floating-label label {
        @apply absolute left-3 top-3 transition-all duration-200 pointer-events-none text-gray-500;
    }

    /* Main containers */
    .checkout-container {
        @apply rounded-2xl;
    }

    .order-summary-container {
        @apply rounded-2xl;
    }

    /* LianLian Pay iframe styles */
    #llpay-card-element {
        @apply min-h-[280px] border-2 border-gray-200 rounded-xl bg-white relative overflow-hidden shadow-sm;
    }

    #llpay-card-element iframe {
        @apply w-full h-[280px] border-0 block visible opacity-100 absolute top-0 left-0 z-[1000] bg-white;
    }

    #lianlian-loading-placeholder {
        @apply absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center z-[999];
    }

    /* PayPal button styles */
    #paypal-button-container {
        @apply min-h-[200px] rounded-xl relative;
    }

    #paypal-button {
        @apply min-h-[120px] flex items-center justify-center;
    }

    #paypal-button button {
        @apply rounded-lg min-h-[48px];
    }
    
    /* Stripe card element styles */
    #stripe-card-container {
        @apply min-h-[200px] rounded-xl relative;
    }
    
    #stripe-card-element {
        @apply min-h-[50px] p-3;
    }
    
    .StripeElement {
        @apply bg-white p-4 rounded-lg border border-gray-200 shadow-sm;
    }
    
    .StripeElement--focus {
        @apply border-blue-500 shadow-lg ring-2 ring-blue-200;
    }
    
    .StripeElement--invalid {
        @apply border-red-500 shadow-lg ring-2 ring-red-200;
    }

    /* Ensure submit button is always visible */
    button[type="submit"] {
        position: relative !important;
        z-index: 10 !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Prevent text overflow in customizations */
    .customization-value {
        word-break: break-all;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
        display: inline-block;
    }

    /* Tip selection styling */
    .tip-option {
        transition: all 0.2s ease;
        position: relative;
    }

    .tip-option:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .tip-option.selected {
        border: 3px solid #10b981 !important;
        background-color: #f0fdf4 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.4) !important;
        transform: translateY(-2px) !important;
    }

    .tip-option.selected span {
        color: #059669 !important;
        font-weight: 700 !important;
    }

    /* Force override Tailwind */
    button.tip-option.selected {
        border: 3px solid #10b981 !important;
        background-color: #f0fdf4 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.4) !important;
    }

    .tip-option.selected::before {
        content: '✓';
        position: absolute;
        top: -3px;
        right: -3px;
        background: #10b981;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        z-index: 20;
        border: 2px solid white;
    }
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Steps -->
        <div class="mb-12 animate-fadeInUp">
            <div class="flex items-center justify-center space-x-8">
                <div class="flex items-center">
                    <div class="step-indicator completed">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="ml-4 font-semibold text-gray-700 text-lg">Cart</span>
                </div>
                <div class="w-16 h-2 bg-gradient-to-r from-green-500 to-blue-500 rounded-full shadow-sm"></div>
                <div class="flex items-center">
                    <div class="step-indicator active">2</div>
                    <span class="ml-4 font-bold text-gray-900 text-lg">Checkout</span>
                </div>
                <div class="w-16 h-2 bg-gray-300 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 text-white font-semibold text-sm flex items-center justify-center shadow-md">3</div>
                    <span class="ml-4 font-medium text-gray-500 text-lg">Complete</span>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-12 animate-fadeInUp">
            <div class="inline-block p-6 bg-white rounded-2xl shadow-lg mb-6">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold text-gray-900 mb-3">
                    Complete Your 
                    <span class="gradient-text">Order</span>
                </h1>
                <p class="text-xl text-gray-600">Secure checkout with multiple payment options</p>
            </div>
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
                        <input type="hidden" id="tip_amount" name="tip_amount" value="0">
                        
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
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5" required
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
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5" required
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
                                       class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5"
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
                                          class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5 resize-vertical min-h-[100px]" rows="3" required
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
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5" required
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
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5"
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
                                           class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5" required
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
                                <select id="country" name="country" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5 cursor-pointer" required>
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
                                          class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md focus:shadow-lg focus:-translate-y-0.5 resize-vertical min-h-[100px]" rows="3"
                                          placeholder="Any special instructions for your order..."></textarea>
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
                                    <label for="payment_lianlian" class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-blue-500 hover:shadow-xl transition-all duration-300 payment-option bg-white">
                                        <input type="radio" id="payment_lianlian" name="payment_method" value="lianlian_pay" class="w-6 h-6 text-blue-600 border-gray-300 focus:ring-blue-500 mr-5" checked>
                                        <div class="flex items-center flex-1">
                                            <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl p-4 mr-5 shadow-lg">
                                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <span class="font-bold text-gray-900 text-xl">LianLian Pay</span>
                                                    <span class="px-3 py-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-bold rounded-full shadow-md">RECOMMENDED</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2">Credit Card & Digital Wallet with 3D Secure</p>
                                                <div class="flex items-center mt-3 text-sm text-blue-600 bg-blue-50 px-3 py-2 rounded-lg">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium">3DS authentication may be required</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <!-- LianLian Pay Form -->
                                    <div id="lianlian-pay-info" class="hidden mt-4 p-6 border-2 border-orange-200 rounded-xl bg-gradient-to-r from-orange-50 to-red-50">
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
                                </div>

                                <!-- PayPal -->
                                <div class="relative">
                                    <label for="payment_paypal" class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-blue-500 hover:shadow-xl transition-all duration-300 payment-option bg-white">
                                        <input type="radio" id="payment_paypal" name="payment_method" value="paypal" class="w-6 h-6 text-blue-600 border-gray-300 focus:ring-blue-500 mr-5">
                                        <div class="flex items-center flex-1">
                                            <div class="bg-blue-600 rounded-2xl p-4 mr-5 shadow-lg">
                                                <img src="https://www.paypalobjects.com/webstatic/icon/pp258.png" 
                                                     alt="PayPal" class="h-10 w-10">
                                            </div>
                                            <div class="flex-1">
                                                <span class="font-bold text-gray-900 text-xl">PayPal</span>
                                                <p class="text-sm text-gray-600 mt-2">Safe & secure payment platform</p>
                                                <div class="flex items-center mt-3 text-sm text-green-600 bg-green-50 px-3 py-2 rounded-lg">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium">Fast and reliable checkout</span>
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
                                
                                <!-- Stripe -->
                                <div class="relative">
                                    <label for="payment_stripe" class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-blue-500 hover:shadow-xl transition-all duration-300 payment-option bg-white">
                                        <input type="radio" id="payment_stripe" name="payment_method" value="stripe" class="w-6 h-6 text-blue-600 border-gray-300 focus:ring-blue-500 mr-5">
                                        <div class="flex items-center flex-1">
                                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-4 mr-5 shadow-lg">
                                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.274 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.386-2.061 1.386-1.705 0-3.888-.921-5.811-1.758L4.443 24c2.254.893 5.18 1.758 7.83 1.758 2.532 0 4.633-.624 6.123-1.844 1.543-1.271 2.346-3.116 2.346-5.342 0-3.896-2.467-5.76-6.476-7.219z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <span class="font-bold text-gray-900 text-xl">Credit Card (Stripe)</span>
                                                    <span class="px-3 py-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-bold rounded-full shadow-md">SECURE</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2">Direct credit card processing</p>
                                                <div class="flex items-center mt-3 text-sm text-green-600 bg-green-50 px-3 py-2 rounded-lg">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium">PCI-DSS compliant & 3D Secure</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <!-- Stripe Card Element -->
                                    <div id="stripe-card-container" class="hidden mt-4 p-6 border-2 border-purple-200 rounded-xl bg-gradient-to-r from-purple-50 to-blue-50">
                                        <div class="flex items-center mb-4">
                                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="font-bold text-purple-900 text-lg">💳 Credit Card Details</h4>
                                        </div>
                                        
                                        <!-- Card Type Logos -->
                                        <div class="flex gap-3 mb-4 justify-center">
                                            <div class="w-12 h-8 bg-gradient-to-r from-blue-600 to-blue-800 rounded text-white text-xs font-bold flex items-center justify-center">VISA</div>
                                            <div class="w-12 h-8 bg-gradient-to-r from-red-500 to-red-700 rounded text-white text-xs font-bold flex items-center justify-center">MC</div>
                                            <div class="w-12 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded text-white text-xs font-bold flex items-center justify-center">AMEX</div>
                                            <div class="w-12 h-8 bg-gradient-to-r from-orange-400 to-orange-600 rounded text-white text-xs font-bold flex items-center justify-center">DISC</div>
                                        </div>

                                        <!-- Stripe Card Element Container -->
                                        <div id="stripe-card-element" class="p-4 border-2 border-gray-200 rounded-xl bg-white">
                                            <!-- Stripe Elements will be inserted here -->
                                        </div>
                                        <div id="stripe-card-errors" class="text-red-500 text-sm mt-2" role="alert"></div>

                                        <!-- Security Notice -->
                                        <div class="mt-4 p-3 bg-white/60 rounded-lg border border-purple-200">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <div class="text-sm text-purple-800">
                                                    <p class="font-semibold mb-1">🔒 100% Secure Payment</p>
                                                    <p>Your payment information is encrypted and processed securely by Stripe. We never store your card details.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 pt-6 border-t-2 border-gray-100 relative z-10">
                            <button type="submit" 
                                    class="w-full py-5 px-6 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-bold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group cursor-pointer">
                                <span class="flex items-center justify-center relative z-20">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Secure Checkout
                                    <svg class="w-6 h-6 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </span>
                                <!-- Shimmer effect -->
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-20 -translate-x-full group-hover:translate-x-full transition-transform duration-700 z-10"></div>
                            </button>
                            
                            <!-- Security Information -->
                            <div class="mt-6 p-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-xl">
                                <div class="flex items-center justify-center text-white">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-lg font-bold mb-1">🔒 100% Secure Checkout</h3>
                                        <p class="text-green-100 text-sm">Your information is protected with 256-bit SSL encryption</p>
                                    </div>
                                </div>
                            </div>
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
                    <div class="space-y-3 mb-6">
                        @foreach($products as $item)
                            <div class="product-item p-3 bg-white border border-gray-200 rounded-xl hover:shadow-sm transition flex gap-3" data-checkout-cart-item-id="{{ $item['cart_item']->id }}">
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
                                <div class="shrink-0">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" 
                                             alt="{{ $item['product']->name }}"
                                             class="w-14 h-14 object-cover rounded-lg">
                                    @else
                                        <div class="w-14 h-14 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-semibold text-gray-900 text-sm truncate">{{ Str::limit($item['product']->name, 42) }}</h3>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button onclick="openCheckoutEditCartModal({{ $item['cart_item']->id }})" class="p-1.5 text-gray-400 hover:text-blue-600" title="Edit item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <p class="font-semibold text-gray-900">${{ number_format($item['total'], 2) }}</p>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Qty: {{ $item['quantity'] }}</p>

                                    @php $sv = $item['cart_item']->selected_variant; @endphp
                                    @if($sv && is_array($sv) && isset($sv['attributes']) && is_array($sv['attributes']))
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($sv['attributes'] as $k => $v)
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-[10px] rounded">{{ $k }}: {{ $v }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($item['cart_item']->customizations && count($item['cart_item']->customizations) > 0)
                                        @php $cid = $item['cart_item']->id; @endphp
                                        <div class="mt-2">
                                            <ul id="cust-list-{{ $cid }}" class="space-y-0.5">
                                                @foreach($item['cart_item']->customizations as $k => $c)
                                                    <li class="text-[11px] text-gray-700 {{ $loop->iteration > 3 ? 'hidden more-'.$cid : '' }}">
                                                        <span class="text-gray-500">{{ $k }}:</span>
                                                        <span class="font-medium customization-value" title="{{ $c['value'] }}">
                                                            @if(strlen($c['value']) > 50)
                                                                {{ Str::limit($c['value'], 50) }}
                                                            @else
                                                                {{ $c['value'] }}
                                                            @endif
                                                        </span>
                                                        @if(isset($c['price']) && $c['price']>0)
                                                            <span class="text-green-600">(+${{ number_format($c['price'],2) }})</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if(count($item['cart_item']->customizations) > 3)
                                                <button type="button" class="mt-1 text-[11px] text-blue-600 hover:underline" onclick="toggleCheckoutCustList({{ $cid }})" id="cust-toggle-{{ $cid }}">View more</button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Tip Selection -->
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-700">Love your items? Please support our designers. Thank you! ❤️</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <button type="button" onclick="selectTip(0)" class="tip-option p-3 border-2 border-gray-200 rounded-lg text-center hover:border-blue-500 transition-all duration-200" data-tip="0">
                                <span class="text-sm font-medium text-gray-700">No tips</span>
                            </button>
                            <button type="button" onclick="selectTip(5)" class="tip-option p-3 border-2 border-gray-200 rounded-lg text-center hover:border-green-500 transition-all duration-200" data-tip="5">
                                <span class="text-sm font-medium text-gray-700">$5.00</span>
                            </button>
                            <button type="button" onclick="selectTip(3)" class="tip-option p-3 border-2 border-gray-200 rounded-lg text-center hover:border-green-500 transition-all duration-200" data-tip="3">
                                <span class="text-sm font-medium text-gray-700">$3.00</span>
                            </button>
                            <button type="button" onclick="selectTip(3.15)" class="tip-option p-3 border-2 border-gray-200 rounded-lg text-center hover:border-green-500 transition-all duration-200" data-tip="3.15">
                                <span class="text-sm font-medium text-gray-700">$3.15</span>
                            </button>
                        </div>
                        
                        <div class="flex gap-2">
                            <button type="button" onclick="selectTip('custom')" class="tip-option flex-1 p-3 border-2 border-gray-200 rounded-lg text-center hover:border-green-500 transition-all duration-200" data-tip="custom">
                                <span class="text-sm font-medium text-gray-700">Other</span>
                            </button>
                            <input type="number" id="custom-tip-amount" placeholder="Custom amount" min="0" step="0.01" class="hidden w-32 px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:border-green-500 focus:outline-none" onchange="updateCustomTip(this.value)">
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        
                        <!-- Delivery Information -->
                        <div class="border-t pt-3">
                            <div class="flex justify-between items-center text-gray-600 mb-3">
                                <span id="delivery-country-text">Deliver to United States</span>
                                <button onclick="openDeliveryModal()" class="text-orange-500 hover:text-orange-600 text-sm font-medium">
                                    Change
                                </button>
                            </div>
                            
                            <div class="text-xs text-gray-500">
                                <div class="flex justify-between">
                                    <span>Zone:</span>
                                    <span id="delivery-zone-text">United States</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span class="shipping-cost-display">${{ number_format($shippingCost, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-gray-600" id="tip-line" style="display: none;">
                            <span>Tips</span>
                            <span class="tip-amount-display">$0.00</span>
                        </div>
                        
                        <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-3 mt-3">
                            <span>Total</span>
                            <span class="text-blue-600 total-display">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Security Badge -->
                    <div class="mt-6 p-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-xl text-white">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h3 class="font-bold text-xl mb-2">🔒 100% Secure Checkout</h3>
                            <div class="space-y-1 text-green-100 text-sm">
                                <div class="flex items-center justify-center">
                                    <span class="mr-2">🔒</span>
                                    <span>SSL Encrypted</span>
                                </div>
                                <div class="flex items-center justify-center">
                                    <span class="mr-2">🛡️</span>
                                    <span>PCI Compliant</span>
                                </div>
                                <div class="flex items-center justify-center">
                                    <span class="mr-2">💳</span>
                                    <span>Safe Payments</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Edit Cart Modal -->
<div id="checkoutEditCartModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center z-10">
            <h2 class="text-2xl font-bold text-gray-900">Edit Item</h2>
            <button onclick="closeCheckoutEditCartModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="checkoutEditCartModalContent" class="p-6"></div>
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
    
    // Toast notification function - define early
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
    
    // Check if this is a 3DS return
    const threeDSInfo = sessionStorage.getItem('lianlian_3ds_info');
    const pending3DS = sessionStorage.getItem('pending_3ds_transaction');
    
    if (threeDSInfo || pending3DS) {
        console.log('🔄 3DS Return Detected', {
            threeDSInfo: threeDSInfo ? JSON.parse(threeDSInfo) : null,
            pending3DS: pending3DS ? JSON.parse(pending3DS) : null,
        });
        
        // Clear 3DS session data
        sessionStorage.removeItem('lianlian_3ds_info');
        sessionStorage.removeItem('pending_3ds_transaction');
        
        // Show success message
        showToast('success', '3DS Authentication Complete', 'Your payment has been processed successfully');
        
        // Redirect to success page after a delay
        setTimeout(() => {
            window.location.href = '{{ route("checkout.lianlian.success") }}';
        }, 3000);
        
        return; // Exit early for 3DS return
    }
    
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
    
    // Unified order processing function
    const processUnifiedOrder = async (orderData) => {
        try {
            console.log('📦 Creating unified order with data:', orderData);

            // Validate required fields
            const requiredFields = ['customer_name', 'customer_email', 'shipping_address', 'city', 'postal_code', 'country'];
            const missingFields = requiredFields.filter(field => !orderData[field]);

            if (missingFields.length > 0) {
                throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
            }

            // Create order using unified endpoint
            const response = await fetch('{{ route("checkout.process") }}', {
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

            if (!response.ok) {
                if (response.status === 422) {
                    const errorData = await response.json();
                    throw new Error(`Validation failed: ${errorData.message || Object.values(errorData.errors || {}).flat().join(', ')}`);
                } else {
                    throw new Error(`Order processing failed: ${response.status} ${response.statusText}`);
                }
            }

            const responseData = await response.json();
            console.log('📋 Unified order response:', responseData);

            if (responseData.success) {
                // Handle different payment method responses
                if (orderData.payment_method === 'lianlian_pay') {
                    await handleLianLianResponse(responseData);
                } else if (orderData.payment_method === 'paypal') {
                    await handlePayPalResponse(responseData);
                } else if (orderData.payment_method === 'stripe') {
                    await handleStripeResponse(responseData);
                } else {
                    // Generic success handling
                    await handleGenericSuccess(responseData);
                }
            } else {
                throw new Error(responseData.message || 'Order processing failed');
            }

        } catch (error) {
            console.error('❌ Unified order processing error:', error);
            showToast('error', 'Order Error', error.message);
            throw error;
        }
    };

    // Helper functions for payment responses
    const handleLianLianResponse = async (responseData) => {
        console.log('🔄 Handling LianLian response:', responseData);
        
        if (responseData.payment_completed === true || responseData.payment_status === 'paid') {
            console.log('✅ LianLian Payment Completed');
            showToast('success', 'Payment Successful!', 'Your payment has been processed successfully');
            
            // Clear cart from localStorage
            localStorage.removeItem('cart');
            
            // Redirect to success page
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("home") }}';
                }
            }, 2000);
        } else if (responseData.requires_3ds === true && responseData.redirect_url) {
            console.log('🔐 3DS Authentication Required');
            await handle3DSRedirect(responseData.redirect_url, responseData.transaction_id);
        } else if (responseData.payment_url) {
            // Redirect to payment URL
            window.location.href = responseData.payment_url;
        } else {
            // Payment pending
            console.log('⏳ LianLian Payment Pending');
            showToast('info', 'Payment is processing...', 'Your payment is being processed');
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("checkout.index") }}';
                }
            }, 2000);
        }
    };

    const handlePayPalResponse = async (responseData) => {
        console.log('🔄 Handling PayPal response:', responseData);
        
        if (responseData.payment_completed === true || responseData.payment_status === 'paid') {
            console.log('✅ PayPal Payment Completed');
            showToast('success', 'Payment Successful!', 'Your payment has been processed successfully');
            
            // Clear cart from localStorage
            localStorage.removeItem('cart');
            
            // Redirect to success page
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("home") }}';
                }
            }, 2000);
        } else if (responseData.payment_url) {
            // Redirect to payment URL
            window.location.href = responseData.payment_url;
        } else {
            // Payment pending
            console.log('⏳ PayPal Payment Pending');
            showToast('info', 'Payment is processing...', 'Your payment is being processed');
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("checkout.index") }}';
                }
            }, 2000);
        }
    };

    const handleStripeResponse = async (responseData) => {
        console.log('🔄 Handling Stripe response:', responseData);
        
        if (responseData.payment_completed === true || responseData.payment_status === 'paid') {
            console.log('✅ Stripe Payment Completed');
            showToast('success', 'Payment Successful!', 'Your payment has been processed successfully');
            
            // Clear cart from localStorage
            localStorage.removeItem('cart');
            
            // Redirect to success page
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("home") }}';
                }
            }, 2000);
        } else if (responseData.payment_url) {
            // Redirect to payment URL
            window.location.href = responseData.payment_url;
        } else {
            // Payment pending
            console.log('⏳ Stripe Payment Pending');
            showToast('info', 'Payment is processing...', 'Your payment is being processed');
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("checkout.index") }}';
                }
            }, 2000);
        }
    };

    const handleGenericSuccess = async (responseData) => {
        console.log('🔄 Handling generic success:', responseData);
        
        if (responseData.payment_completed === true || responseData.payment_status === 'paid') {
            console.log('✅ Generic Payment Completed');
            showToast('success', 'Payment Successful!', 'Your payment has been processed successfully');
            
            // Clear cart from localStorage
            localStorage.removeItem('cart');
            
            // Redirect to success page
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("home") }}';
                }
            }, 2000);
        } else if (responseData.payment_url) {
            // Redirect to payment URL
            window.location.href = responseData.payment_url;
        } else {
            // Payment pending
            console.log('⏳ Generic Payment Pending');
            showToast('info', 'Payment is processing...', 'Your payment is being processed');
            setTimeout(() => {
                if (responseData.order_number) {
                    window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                } else {
                    window.location.href = '{{ route("checkout.index") }}';
                }
            }, 2000);
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
            const tip = parseFloat(document.getElementById('tip_amount')?.value || 0);
            const total = subtotal + tax + shipping + tip;
            
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
                            showToast('error', 'Missing information', 'Please fill in all information: ' + missingFields.join(', '));
                            throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
                        }
                        
                        // Validate email format
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(requiredFields.customer_email)) {
                            showToast('error', 'Invalid Email', 'Please enter a valid email address');
                            throw new Error('Invalid email format');
                        }
                        
                        console.log('✅ Form validation passed, creating PayPal order...');
                        
                        // Calculate total amount for PayPal
                        const subtotal = parseFloat('{{ $subtotal }}');
                        const tax = parseFloat('{{ $taxAmount }}');
                        const shipping = parseFloat('{{ $shippingCost }}');
                        const tip = parseFloat(document.getElementById('tip_amount')?.value || 0);
                        const total = subtotal + tax + shipping + tip;
                        
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
                            tip_amount: parseFloat(document.getElementById('tip_amount')?.value || 0),
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
                                <span class="text-sm font-medium">Please fill in all required fields: ${validation.missingFields.join(', ')}</span>
                            </div>
                        `;
                    } else if (!validation.isEmailValid) {
                        warningMsg.innerHTML = `
                            <div class="flex items-center text-yellow-800">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium">Please enter a valid email address</span>
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
    
    // Stripe integration
    let stripeInstance = null;
    let stripeCardElement = null;
    let stripePaymentIntent = null;
    
    // Payment method change handler
    const handlePaymentMethodChange = function() {
        const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
        const lianLianInfo = document.getElementById('lianlian-pay-info');
        const paypalContainer = document.getElementById('paypal-button-container');
        const stripeContainer = document.getElementById('stripe-card-container');
        
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
            console.log('🔧 LianLian Pay selected');
            // Show LianLian Pay form
            if (lianLianInfo) {
                lianLianInfo.classList.remove('hidden');
            }
            // Hide others
            if (paypalContainer) {
                paypalContainer.classList.add('hidden');
            }
            if (stripeContainer) {
                stripeContainer.classList.add('hidden');
            }
            // Initialize LianLian iframe if not already done
            if (!lianLianCardInstance && !iframeToken) {
                initializeLianLianIframe();
            }
        } else if (selectedRadio && selectedRadio.value === 'paypal') {
            console.log('🔧 PayPal selected - initializing buttons');
            // Hide others
            if (lianLianInfo) {
                lianLianInfo.classList.add('hidden');
            }
            if (stripeContainer) {
                stripeContainer.classList.add('hidden');
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
        } else if (selectedRadio && selectedRadio.value === 'stripe') {
            console.log('🔧 Stripe selected - initializing card element');
            // Hide others
            if (lianLianInfo) {
                lianLianInfo.classList.add('hidden');
            }
            if (paypalContainer) {
                paypalContainer.classList.add('hidden');
            }
            // Show Stripe container
            if (stripeContainer) {
                stripeContainer.classList.remove('hidden');
                // Initialize Stripe Elements if not already done
                if (!stripeCardElement) {
                    initializeStripeElements();
                }
            }
        } else {
            console.log('🔧 Hiding all payment methods...');
            // Hide all containers
            if (lianLianInfo) {
                lianLianInfo.classList.add('hidden');
            }
            if (paypalContainer) {
                paypalContainer.classList.add('hidden');
            }
            if (stripeContainer) {
                stripeContainer.classList.add('hidden');
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
        
        // Handle payment method change (will show LianLian form and initialize)
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
    
    // Initialize Stripe Elements
    const initializeStripeElements = async () => {
        try {
            console.log('🚀 Initializing Stripe Elements...');
            
            if (!window.Stripe) {
                throw new Error('Stripe.js not loaded');
            }
            
            // Initialize Stripe with publishable key
            const stripeKey = '{{ config("services.stripe.key") }}';
            if (!stripeKey) {
                throw new Error('Stripe publishable key not configured');
            }
            
            stripeInstance = window.Stripe(stripeKey);
            
            // Create Elements instance
            const elements = stripeInstance.elements();
            
            // Create card element with styling
            stripeCardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                        iconColor: '#666EE8',
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a',
                    },
                },
                hidePostalCode: true,
            });
            
            // Mount the card element
            stripeCardElement.mount('#stripe-card-element');
            
            // Handle real-time validation errors
            stripeCardElement.on('change', function(event) {
                const displayError = document.getElementById('stripe-card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
            
            console.log('✅ Stripe Elements initialized successfully');
            
        } catch (error) {
            console.error('❌ Stripe Elements initialization error:', error);
            showToast('error', 'Payment Error', 'Failed to initialize Stripe: ' + error.message);
        }
    };
    
    // Handle Stripe Payment
    const handleStripePayment = async () => {
        try {
            console.log('🚀 Processing Stripe payment...');
            showLoading(true);
            
            if (!stripeInstance || !stripeCardElement) {
                throw new Error('Stripe not initialized. Please refresh and try again.');
            }
            
            // Calculate total amount
            const subtotal = parseFloat('{{ $subtotal }}');
            const tax = parseFloat('{{ $taxAmount }}');
            const shipping = parseFloat('{{ $shippingCost }}');
            const tip = parseFloat(document.getElementById('tip_amount')?.value || 0);
            const total = subtotal + tax + shipping + tip;
            
            // Create Payment Intent on server
            console.log('📝 Creating Payment Intent...');
            const intentResponse = await fetch('{{ route("payment.stripe.create-intent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    amount: total,
                    currency: 'usd',
                }),
            });
            
            const intentData = await intentResponse.json();
            
            if (!intentData.success) {
                throw new Error(intentData.message || 'Failed to create payment intent');
            }
            
            const clientSecret = intentData.clientSecret;
            console.log('✅ Payment Intent created');
            
            // Get billing details from form
            const checkoutForm = document.getElementById('checkout-form');
            const billingDetails = {
                name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
                email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
                phone: checkoutForm.querySelector('[name="customer_phone"]')?.value?.trim() || '',
                address: {
                    line1: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
                    city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
                    state: checkoutForm.querySelector('[name="state"]')?.value?.trim() || '',
                    postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
                    country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || '',
                },
            };
            
            // Confirm the payment with Stripe
            console.log('💳 Confirming payment with Stripe...');
            const { error, paymentIntent } = await stripeInstance.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: stripeCardElement,
                    billing_details: billingDetails,
                },
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            if (paymentIntent.status === 'succeeded') {
                console.log('✅ Payment succeeded:', paymentIntent.id);
                
                // Process order on server
                await processStripeOrder(paymentIntent.id);
            } else {
                throw new Error('Payment was not successful. Status: ' + paymentIntent.status);
            }
            
        } catch (error) {
            console.error('❌ Stripe payment error:', error);
            showToast('error', 'Payment Error', error.message);
            showLoading(false);
        }
    };
    
    // Process Stripe Order
    const processStripeOrder = async (paymentIntentId) => {
        try {
            console.log('📦 Creating order with payment intent:', paymentIntentId);
            
            const checkoutForm = document.getElementById('checkout-form');
            const orderData = {
                payment_intent_id: paymentIntentId,
                customer_name: checkoutForm.querySelector('[name="customer_name"]')?.value?.trim() || '',
                customer_email: checkoutForm.querySelector('[name="customer_email"]')?.value?.trim() || '',
                customer_phone: checkoutForm.querySelector('[name="customer_phone"]')?.value?.trim() || '',
                shipping_address: checkoutForm.querySelector('[name="shipping_address"]')?.value?.trim() || '',
                city: checkoutForm.querySelector('[name="city"]')?.value?.trim() || '',
                state: checkoutForm.querySelector('[name="state"]')?.value?.trim() || '',
                postal_code: checkoutForm.querySelector('[name="postal_code"]')?.value?.trim() || '',
                country: checkoutForm.querySelector('[name="country"]')?.value?.trim() || '',
                notes: checkoutForm.querySelector('[name="notes"]')?.value?.trim() || '',
                tip_amount: parseFloat(document.getElementById('tip_amount')?.value || 0),
                payment_method: 'stripe'
            };
            
            // Use unified order processing
            await processUnifiedOrder(orderData);
            
        } catch (error) {
            console.error('❌ Order processing error:', error);
            showToast('error', 'Order Error', error.message);
            showLoading(false);
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
        } else if (selectedPaymentMethod === 'stripe') {
            handleStripePayment();
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
                tip_amount: parseFloat(document.getElementById('tip_amount')?.value || 0),
            };
            
            // Validate order data
            const requiredFields = ['customer_name', 'customer_email', 'shipping_address', 'city', 'postal_code', 'country'];
            const missingFields = requiredFields.filter(field => !orderData[field]);
            
            if (missingFields.length > 0) {
                throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
            }
            
            // Add card token to order data
            orderData.card_token = cardToken;
            
            // Create order using unified processing
            console.log('📦 Creating order with unified processing...');
            await processUnifiedOrder(orderData);
            
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
        initializeTipSelection();
    }, 500);
    
    // Initialize tip selection on page load
    function initializeTipSelection() {
        // Check if there's a default tip amount and select it
        const tipAmount = parseFloat('{{ $tipAmount ?? 0 }}');
        if (tipAmount > 0) {
            const tipButton = document.querySelector(`[data-tip="${tipAmount}"]`);
            if (tipButton) {
                tipButton.classList.add('selected');
                selectedTipAmount = tipAmount;
                updateTotal();
            }
        }
        
        // Debug: Log all tip options (remove in production)
        // console.log('Available tip options:', document.querySelectorAll('.tip-option'));
    }
    
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

    // Tip selection functionality
    let selectedTipAmount = 0;
    
    window.selectTip = function(amount) {
        console.log('Selecting tip:', amount);
        
        // Remove selected class from all tip options
        document.querySelectorAll('.tip-option').forEach(btn => {
            btn.classList.remove('selected');
            
            // Clear inline styles
            btn.style.border = '';
            btn.style.backgroundColor = '';
            btn.style.boxShadow = '';
            btn.style.transform = '';
            
            console.log('Removed selected from:', btn);
        });
        
        // Add selected class to clicked button
        const clickedButton = event.target.closest('.tip-option');
        if (clickedButton) {
            clickedButton.classList.add('selected');
            
            // Force inline styles as backup
            clickedButton.style.border = '3px solid #10b981';
            clickedButton.style.backgroundColor = '#f0fdf4';
            clickedButton.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.4)';
            clickedButton.style.transform = 'translateY(-2px)';
            
            console.log('Added selected to:', clickedButton);
        }
        
        if (amount === 'custom') {
            // Show custom tip input
            const customInput = document.getElementById('custom-tip-amount');
            customInput.classList.remove('hidden');
            customInput.focus();
            selectedTipAmount = 0;
        } else {
            // Hide custom tip input
            const customInput = document.getElementById('custom-tip-amount');
            customInput.classList.add('hidden');
            selectedTipAmount = parseFloat(amount) || 0;
            updateTotal();
        }
    };
    
    window.updateCustomTip = function(value) {
        selectedTipAmount = parseFloat(value) || 0;
        
        // Add selected class to custom tip button when user types
        if (selectedTipAmount > 0) {
            document.querySelectorAll('.tip-option').forEach(btn => {
                btn.classList.remove('selected');
                
                // Clear inline styles
                btn.style.border = '';
                btn.style.backgroundColor = '';
                btn.style.boxShadow = '';
                btn.style.transform = '';
            });
            
            const customButton = document.querySelector('[data-tip="custom"]');
            customButton.classList.add('selected');
            
            // Force inline styles for custom button
            customButton.style.border = '3px solid #10b981';
            customButton.style.backgroundColor = '#f0fdf4';
            customButton.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.4)';
            customButton.style.transform = 'translateY(-2px)';
        }
        
        updateTotal();
    };
    
    function updateTotal() {
        const subtotal = parseFloat('{{ $subtotal }}');
        const shipping = parseFloat('{{ $shippingCost }}');
        const tip = selectedTipAmount;
        const total = subtotal + shipping + tip;
        
        // Update tip line visibility
        const tipLine = document.getElementById('tip-line');
        const tipAmountDisplay = document.querySelector('.tip-amount-display');
        const totalDisplay = document.querySelector('.total-display');
        
        if (tip > 0) {
            tipLine.style.display = 'flex';
            tipAmountDisplay.textContent = '$' + tip.toFixed(2);
        } else {
            tipLine.style.display = 'none';
        }
        
        // Update total
        totalDisplay.textContent = '$' + total.toFixed(2);
        
        // Store tip amount for form submission
        const tipInput = document.getElementById('tip_amount');
        if (tipInput) {
            tipInput.value = tip;
        }
    }
});
</script>

@php
    $checkoutItems = [];
    foreach ($products as $item) {
        $checkoutItems[] = [
            'id' => $item['cart_item']->id,
            'quantity' => $item['cart_item']->quantity,
            'price' => (float) $item['cart_item']->price,
            'product' => [
                'name' => $item['product']->name,
                'variants' => $item['product']->variants,
                'media' => $item['product']->media ?? $item['product']->getEffectiveMedia(),
                'base_price' => (float) ($item['product']->base_price ?? 0),
                'price' => (float) ($item['product']->price ?? 0),
                'template' => $item['product']->template ? ['base_price' => (float) $item['product']->template->base_price] : null,
            ],
            'selected_variant' => $item['cart_item']->selected_variant,
            'customizations' => $item['cart_item']->customizations,
        ];
    }
@endphp

<!-- Build checkoutItemsData from server-side products for modal editing -->
<script>
const checkoutItemsData = @json($checkoutItems);
const checkoutCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function openCheckoutEditCartModal(cartItemId) {
    const ci = checkoutItemsData.find(i => i.id === cartItemId);
    if (!ci) { alert('Cart item not found'); return; }
    const modal = document.getElementById('checkoutEditCartModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const content = document.getElementById('checkoutEditCartModalContent');
    content.innerHTML = buildCheckoutEditContent(ci);
    window.__checkoutEditingCtx = {
        id: cartItemId,
        item: ci,
        variants: (ci.product && ci.product.variants) ? ci.product.variants : [],
        originalCustomizations: ci.customizations || {}
    };
}

function closeCheckoutEditCartModal() {
    const modal = document.getElementById('checkoutEditCartModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function buildCheckoutEditContent(ci) {
    const product = ci.product;
    const variants = product.variants || [];
    const selectedVariant = ci.selected_variant || {};
    const customizations = ci.customizations || {};
    const img = getCheckoutProductImage(product);
    const total = (parseFloat(ci.price) * ci.quantity).toFixed(2);
    return `
        <div class="space-y-6">
            <div class="flex gap-4">
                <img src="${img}" alt="${product.name}" class="w-24 h-24 object-cover rounded-lg">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">${product.name}</h3>
                    <p class="text-gray-600">$${parseFloat(ci.price).toFixed(2)} each</p>
                </div>
            </div>
            ${variants.length ? `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Variants</label>
                <div class="space-y-2">${buildCheckoutVariantOptions(variants, selectedVariant)}</div>
            </div>` : ''}
            ${Object.keys(customizations).length ? `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Customizations</label>
                <div class="space-y-3">
                    ${buildCheckoutCustomizationInputs(customizations)}
                </div>
            </div>` : ''}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <div class="flex items-center gap-3">
                    <button onclick="updateCheckoutModalQty(${ci.id}, ${ci.quantity - 1})" class="w-10 h-10 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors" ${ci.quantity<=1?'disabled':''}>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <span class="text-xl font-semibold" id="checkoutModalQty${ci.id}">${ci.quantity}</span>
                    <button onclick="updateCheckoutModalQty(${ci.id}, ${ci.quantity + 1})" class="w-10 h-10 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
            </div>
            <div class="border-t pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-[#005366]" id="checkoutModalTotal${ci.id}">$${total}</span>
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button onclick="saveCheckoutCartChanges(${ci.id})" class="flex-1 bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-3 rounded-xl transition-colors">Save Changes</button>
                <button onclick="closeCheckoutEditCartModal()" class="px-6 py-3 border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-xl transition-colors">Cancel</button>
            </div>
        </div>`;
}

function buildCheckoutVariantOptions(variants, selectedVariant) {
    const groups = {};
    variants.forEach(v => { if (v.attributes) Object.keys(v.attributes).forEach(k => { groups[k] = groups[k]||new Set(); groups[k].add(v.attributes[k]); }); });
    return Object.keys(groups).map(k => {
        const values = Array.from(groups[k]);
        const sel = selectedVariant && selectedVariant.attributes ? selectedVariant.attributes[k] : '';
        return `
        <div>
            <label class="block text-sm text-gray-600 mb-1">${k.charAt(0).toUpperCase()+k.slice(1)}</label>
            <select class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-[#005366] focus:outline-none" id="checkout-variant-${k}" onchange="updateCheckoutModalTotal()">
                ${values.map(v => `<option value="${v}" ${v===sel?'selected':''}>${v}</option>`).join('')}
            </select>
        </div>`;
    }).join('');
}

function buildCheckoutCustomizationInputs(customizations) {
    var html = '';
    if (!customizations) return html;
    Object.keys(customizations).forEach(function(k){
        var v = customizations[k] || {};
        var value = v && v.value ? String(v.value).replace(/"/g, '&quot;') : '';
        html += '<div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-center">'
             + '<div class="sm:col-span-2"><span class="text-sm text-gray-600">' + k + '</span></div>'
             + '<div class="sm:col-span-3">'
             + '<input type="text" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 checkout-customization-input" data-label="' + k + '" value="' + value + '" oninput="updateCheckoutModalTotal()" title="' + value + '" />'
             + '</div>'
             + '</div>';
    });
    return html;
}

function getCheckoutProductImage(product) {
    const media = product && product.media && product.media.length ? product.media[0] : null;
    if (!media) return '/images/placeholder.jpg';
    if (typeof media === 'string') return media;
    if (media.url) return media.url; if (media.path) return media.path; return '/images/placeholder.jpg';
}

function updateCheckoutModalQty(id, newQty) {
    if (newQty < 1) return; const el = document.getElementById('checkoutModalQty'+id); if (el) el.textContent = newQty; updateCheckoutModalTotal();
}

function updateCheckoutModalTotal() {
    const ctx = window.__checkoutEditingCtx; if (!ctx) return; const id = ctx.id; const item = ctx.item; const qty = parseInt(document.getElementById('checkoutModalQty'+id)?.textContent || '1');
    // selected variant
    const attrs = {}; (ctx.variants||[]).forEach(v=>{ if(v.attributes){ Object.keys(v.attributes).forEach(k=>{ const sel=document.getElementById('checkout-variant-'+k); if(sel) attrs[k]=sel.value;});}});
    const match = (ctx.variants||[]).find(v=>v.attributes && Object.keys(attrs).every(k=>String(v.attributes[k])===String(attrs[k])));
    let unitPrice = 0;
    if (match && match.price!=null && match.price!=='') { const pv=parseFloat(match.price); if(!isNaN(pv)) unitPrice=pv; }
    if (!unitPrice) {
        const p=item.product||{}; const candidates=[p.price,p.base_price,(p.template||{}).base_price,item.price]; for(const c of candidates){ const v=parseFloat(c); if(!isNaN(v)){ unitPrice=v; break; } }
    }
    // customizations keep original price
    const customMap={}; document.querySelectorAll('.checkout-customization-input').forEach(inp=>{ const label=inp.dataset.label; const value=inp.value||''; const orig=ctx.originalCustomizations&&ctx.originalCustomizations[label]; const price=orig&&orig.price?parseFloat(orig.price)||0:0; if(value.trim()!==''){ customMap[label]={value:value.trim(),price}; }});
    let custTotal=0; Object.values(customMap).forEach(c=>{ custTotal+=parseFloat(c.price)||0; });
    const total = (unitPrice + custTotal) * qty; const td=document.getElementById('checkoutModalTotal'+id); if (td) td.textContent='$'+total.toFixed(2);
}

function saveCheckoutCartChanges(cartItemId) {
    const ctx = window.__checkoutEditingCtx; if (!ctx || ctx.id!==cartItemId) return; const item=ctx.item; const qty=parseInt(document.getElementById('checkoutModalQty'+cartItemId)?.textContent||'1');
    const attrs={}; (ctx.variants||[]).forEach(v=>{ if(v.attributes){ Object.keys(v.attributes).forEach(k=>{ const sel=document.getElementById('checkout-variant-'+k); if(sel) attrs[k]=sel.value;});}});
    const match=(ctx.variants||[]).find(v=>v.attributes && Object.keys(attrs).every(k=>String(v.attributes[k])===String(attrs[k])));
    const selectedVariant = match ? { id: match.id, attributes: match.attributes, price: match.price } : (Object.keys(attrs).length ? { attributes: attrs } : null);
    const customizations={}; document.querySelectorAll('.checkout-customization-input').forEach(inp=>{ const label=inp.dataset.label; const value=inp.value||''; const orig=ctx.originalCustomizations&&ctx.originalCustomizations[label]; const price=orig&&orig.price?parseFloat(orig.price)||0:0; if(value.trim()!==''){ customizations[label]={ value:value.trim(), price }; }});
    let unitPrice=0; if (selectedVariant && selectedVariant.price!=null && selectedVariant.price!==''){ const v=parseFloat(selectedVariant.price); if(!isNaN(v)) unitPrice=v; }
    if (!unitPrice){ const p=item.product||{}; const candidates=[p.price,p.base_price,(p.template||{}).base_price,item.price]; for(const c of candidates){ const v=parseFloat(c); if(!isNaN(v)){ unitPrice=v; break; } } }
    Object.values(customizations).forEach(c=>{ unitPrice += parseFloat(c.price)||0; });
    fetch(`/api/cart/update/${cartItemId}`, {
        method:'PUT', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN': checkoutCsrfToken },
        body: JSON.stringify({ quantity: qty, selected_variant: selectedVariant, customizations: customizations, price: unitPrice })
    }).then(r=>r.json()).then(data=>{ if(data.success){ window.location.reload(); } else { alert('Failed to update cart item'); }}).catch(err=>{ console.error(err); alert('An error occurred'); });
}

// Delivery Modal Functions
function openDeliveryModal() {
    const modal = document.getElementById('deliveryModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeliveryModal() {
    const modal = document.getElementById('deliveryModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function selectCountry(countryCode, countryName, shippingCost) {
    // Update display
    document.getElementById('delivery-country-text').textContent = `Deliver to ${countryName}`;
    document.getElementById('delivery-zone-text').textContent = countryName;
    
    // Close modal
    closeDeliveryModal();
    
    // Update shipping cost based on country (use API calculation)
    updateCheckoutShippingForCountry(countryCode, countryName);
}

async function updateCheckoutShippingForCountry(countryCode, countryName) {
    // Show loading state
    const shippingCostElement = document.querySelector('.shipping-cost-display');
    if (shippingCostElement) {
        shippingCostElement.innerHTML = '<span class="text-gray-500">Calculating...</span>';
    }
    
    try {
        // Calculate actual shipping cost via API
        const response = await fetch('/checkout/calculate-shipping', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ country: countryCode })
        });
        
        const data = await response.json();
        
        if (data.success && data.shipping) {
            const newShipping = parseFloat(data.shipping.total_shipping);
            const subtotal = parseFloat('{{ $subtotal }}');
            const qualifiesForFreeShipping = subtotal >= 100;
            const actualShipping = qualifiesForFreeShipping ? 0 : newShipping;
            const newTotal = subtotal + actualShipping;
            
            // Update shipping cost display
            if (shippingCostElement) {
                if (qualifiesForFreeShipping) {
                    shippingCostElement.innerHTML = '<span class="text-green-600">FREE</span>';
                } else {
                    shippingCostElement.innerHTML = `$${newShipping.toFixed(2)}`;
                }
            }
            
            // Update total
            const totalElement = document.querySelector('.total-display');
            if (totalElement) {
                totalElement.textContent = `$${newTotal.toFixed(2)}`;
            }
            
            console.log('Checkout shipping updated:', {
                country: countryCode,
                originalShipping: newShipping,
                actualShipping: actualShipping,
                qualifiesForFreeShipping: qualifiesForFreeShipping
            });
        } else {
            // Fallback: show error message
            if (shippingCostElement) {
                shippingCostElement.innerHTML = '<span class="text-red-500">Error calculating</span>';
            }
            
            console.error('Checkout shipping calculation failed:', data.message);
        }
    } catch (error) {
        console.error('Checkout shipping calculation error:', error);
        
        // Show error message
        if (shippingCostElement) {
            shippingCostElement.innerHTML = '<span class="text-red-500">Error calculating</span>';
        }
    }
}

// Close modal on backdrop click
document.getElementById('deliveryModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeliveryModal();
});
</script>

<!-- Modal for editing cart items -->
<div id="checkoutEditCartModal" class="hidden fixed top-0 left-0 w-full h-full bg-gray-500 bg-opacity-75 items-center justify-center">
    <div id="checkoutEditCartModalContent" class="bg-white rounded-lg shadow-lg p-6 w-1/2"></div>
</div>

<!-- Delivery Country Modal -->
<div id="deliveryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Select Delivery Country</h2>
            <button onclick="closeDeliveryModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @php
                    // Sort zones to put US first
                    $sortedZones = $shippingZones->sortBy(function($zone) {
                        $firstCountry = $zone->countries[0] ?? 'US';
                        return $firstCountry === 'US' ? 0 : 1;
                    });
                @endphp
                @foreach($sortedZones as $zone)
                    @php
                        $firstCountry = $zone->countries[0] ?? 'US';
                        $countryFlags = [
                            'US' => '🇺🇸', 'GB' => '🇬🇧', 'CA' => '🇨🇦', 'AU' => '🇦🇺',
                            'DE' => '🇩🇪', 'FR' => '🇫🇷', 'IT' => '🇮🇹', 'ES' => '🇪🇸',
                            'JP' => '🇯🇵', 'KR' => '🇰🇷', 'CN' => '🇨🇳', 'IN' => '🇮🇳',
                            'BR' => '🇧🇷', 'MX' => '🇲🇽', 'RU' => '🇷🇺', 'ZA' => '🇿🇦'
                        ];
                        $flag = $countryFlags[$firstCountry] ?? '🌍';
                        
                        // Calculate actual shipping cost for this zone based on cart items
                        $calculator = new \App\Services\ShippingCalculator();
                        $cartItemsForCalc = collect($products)->map(function ($item) {
                            return [
                                'product_id' => $item['product']->id,
                                'quantity' => $item['quantity'],
                                'price' => $item['product']->base_price,
                            ];
                        });
                        $shippingResult = $calculator->calculateShipping($cartItemsForCalc, $firstCountry);
                        $shippingCost = $shippingResult['success'] ? $shippingResult['total_shipping'] : 0;
                        $rateName = $shippingResult['success'] && !empty($shippingResult['items']) ? $shippingResult['items'][0]['shipping_rate_name'] : 'Standard shipping';
                    @endphp
                    <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors" 
                         onclick="selectCountry('{{ $firstCountry }}', '{{ $zone->name }}', {{ $shippingCost }})">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">{{ $flag }}</span>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $zone->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $firstCountry }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">${{ number_format($shippingCost, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $rateName }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection