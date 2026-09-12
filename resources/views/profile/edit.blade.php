@extends('layouts.app')

@section('title', 'Profile')

@section('content')
@php
    $user = $user ?? auth()->user();
@endphp
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <header class="section-heading section-heading--catalog mb-8">
            <p class="section-heading__eyebrow">Account</p>
            <h1 class="section-heading__title">Your <span class="gradient-text">profile</span></h1>
            <p class="section-heading__sub">Update your details, password, and account security.</p>
            <span class="section-heading__accent"></span>
        </header>

        @if(Route::has('customer.profile.index'))
            <div class="mb-6">
                <a href="{{ route('customer.profile.index') }}" class="inline-flex items-center text-sm font-semibold text-[#005366] hover:text-[#003d4d]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to my profile
                </a>
            </div>
        @endif

        @if(session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800" role="status">
                Saved.
            </div>
        @endif

        <div class="max-w-xl space-y-6">
            <div class="commerce-card">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="commerce-card">
                @include('profile.partials.update-password-form')
            </div>

            <div class="commerce-card border-red-200">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
