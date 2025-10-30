@extends('layouts.app')

@section('title', 'Unsubscribe from Newsletter - Bluprinter')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Unsubscribe from Newsletter</h2>
                <p class="text-gray-600 mb-6">We're sorry to see you go!</p>
            </div>

            <div id="unsubscribe-content">
                <div class="text-center">
                    <p class="text-gray-600 mb-4">Are you sure you want to unsubscribe from our newsletter?</p>
                    <p class="text-sm text-gray-500 mb-6">You'll no longer receive updates about new products, exclusive offers, and design tips.</p>
                    
                    <div class="flex space-x-4">
                        <button id="confirm-unsubscribe" 
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Yes, Unsubscribe
                        </button>
                        <button id="cancel-unsubscribe" 
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div id="unsubscribe-result" class="hidden text-center">
                <div id="success-message" class="hidden">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Successfully Unsubscribed</h3>
                    <p class="text-gray-600 mb-4">You have been unsubscribed from our newsletter.</p>
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-500 font-medium">Return to Homepage</a>
                </div>

                <div id="error-message" class="hidden">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Error</h3>
                    <p class="text-gray-600 mb-4" id="error-text">Something went wrong. Please try again later.</p>
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-500 font-medium">Return to Homepage</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const email = '{{ $email }}';
    const confirmBtn = document.getElementById('confirm-unsubscribe');
    const cancelBtn = document.getElementById('cancel-unsubscribe');
    const content = document.getElementById('unsubscribe-content');
    const result = document.getElementById('unsubscribe-result');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');

    confirmBtn.addEventListener('click', function() {
        // Show loading state
        confirmBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Unsubscribing...';
        confirmBtn.disabled = true;

        // Make API call
        fetch(`/newsletter/unsubscribe/${email}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            content.classList.add('hidden');
            result.classList.remove('hidden');
            
            if (data.success) {
                successMessage.classList.remove('hidden');
            } else {
                errorMessage.classList.remove('hidden');
                errorText.textContent = data.message || 'Something went wrong. Please try again later.';
            }
        })
        .catch(error => {
            console.error('Unsubscribe error:', error);
            content.classList.add('hidden');
            result.classList.remove('hidden');
            errorMessage.classList.remove('hidden');
            errorText.textContent = 'Something went wrong. Please try again later.';
        });
    });

    cancelBtn.addEventListener('click', function() {
        window.location.href = '{{ route("home") }}';
    });
});
</script>
@endsection
