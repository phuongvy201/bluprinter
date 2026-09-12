@extends('layouts.app')

@section('content')
@php
    $field = 'w-full min-h-12 px-4 py-3 border border-gray-300 rounded-lg text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#005366] focus:border-transparent';
@endphp
<div class="bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <header class="section-heading section-heading--catalog mb-8">
            <p class="section-heading__eyebrow">Account</p>
            <h1 class="section-heading__title">Edit <span class="gradient-text">profile</span></h1>
            <p class="section-heading__sub">Update your personal details, address, and password.</p>
            <span class="section-heading__accent"></span>
        </header>

        <div class="mb-6">
            <a href="{{ route('customer.profile.index') }}" class="inline-flex items-center text-sm font-semibold text-[#005366] hover:text-[#003d4d]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to my profile
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-semibold">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif

        <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="customer-profile-update">
            @csrf
            @method('PUT')

            <div class="commerce-card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-[#f7f7f7]">
                    <h2 class="text-xl font-bold text-gray-900">Personal information</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-3" for="avatar">Profile picture</label>
                        <div class="flex items-center gap-6">
                            <div class="shrink-0" id="avatar-preview-wrap">
                                @if($user->avatar)
                                    <img id="avatar-preview" src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                @else
                                    <div id="avatar-preview" class="w-24 h-24 rounded-full bg-[#005366] flex items-center justify-center border-4 border-gray-200">
                                        <span class="text-3xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/jpg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#005366] file:text-white hover:file:bg-[#003d4d]">
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG or WEBP. Max size 5MB.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="name">Full name</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="email">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $field }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2" for="phone">Phone number</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $field }}">
                    </div>
                </div>
            </div>

            <div class="commerce-card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-[#f7f7f7]">
                    <h2 class="text-xl font-bold text-gray-900">Address</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2" for="address">Street address</label>
                        <input id="address" type="text" name="address" value="{{ old('address', $user->address) }}" class="{{ $field }}">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="city">City</label>
                            <input id="city" type="text" name="city" value="{{ old('city', $user->city) }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="state">State / province</label>
                            <input id="state" type="text" name="state" value="{{ old('state', $user->state) }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="postal_code">Postal code</label>
                            <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="{{ $field }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2" for="country">Country</label>
                        <input id="country" type="text" name="country" value="{{ old('country', $user->country) }}" class="{{ $field }}">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('customer.profile.index') }}" class="btn-outline-petrol">Cancel</a>
                <button type="submit" class="btn-cta">Save changes</button>
            </div>
        </form>

        <div class="commerce-card p-0 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-[#f7f7f7]">
                <h2 class="text-xl font-bold text-gray-900">Change password</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('customer.profile.password') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2" for="current_password">Current password</label>
                        <input id="current_password" type="password" name="current_password" required class="{{ $field }}">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="password">New password</label>
                            <input id="password" type="password" name="password" required class="{{ $field }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2" for="password_confirmation">Confirm new password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required class="{{ $field }}">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-outline-petrol">Update password</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="commerce-card border-red-200 mt-6">
            <h2 class="text-xl font-bold text-[#e2150c]">Delete account</h2>
            <p class="text-gray-600 mt-2 mb-4">Once deleted, your account cannot be recovered.</p>
            <button type="button" class="btn-cta" onclick="document.getElementById('delete-account-dialog').showModal()">Delete account</button>
            <dialog id="delete-account-dialog" class="w-full max-w-md rounded-2xl border border-gray-100 p-0 shadow-2xl backdrop:bg-black/50">
                <form action="{{ route('customer.profile.destroy') }}" method="POST" class="p-6">
                    @csrf
                    @method('DELETE')
                    <h3 class="text-xl font-bold text-gray-900">Delete this account?</h3>
                    <p class="text-sm text-gray-600 mt-2 mb-6">Enter your password to confirm.</p>
                    <label class="block text-sm font-semibold text-gray-900 mb-2" for="delete_password">Password</label>
                    <input id="delete_password" type="password" name="password" required class="{{ $field }}">
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="btn-outline-petrol" onclick="document.getElementById('delete-account-dialog').close()">Cancel</button>
                        <button type="submit" class="btn-cta">Delete account</button>
                    </div>
                </form>
            </dialog>
        </div>
    </div>
</div>

<script>
document.getElementById('avatar')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (ev) {
        const wrap = document.getElementById('avatar-preview-wrap');
        if (wrap) {
            wrap.innerHTML = '<img id="avatar-preview" src="' + ev.target.result + '" alt="Preview" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">';
        }
    };
    reader.readAsDataURL(file);
});

document.getElementById('customer-profile-update')?.addEventListener('submit', function () {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4';
    overlay.innerHTML = '<div class="bg-white rounded-2xl p-8 shadow-2xl max-w-sm text-center border border-gray-100"><p class="font-semibold text-gray-900">Saving profile…</p></div>';
    document.body.appendChild(overlay);
});
</script>
@endsection
