@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
                <p class="text-gray-600 mt-1">Update your personal information and settings</p>
            </div>
            <a href="{{ route('customer.profile.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-red-800">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Profile Form -->
    <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Avatar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Profile Picture</label>
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            @if($user->avatar)
                                <img id="avatar-preview" src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                            @else
                                <div id="avatar-preview" class="w-24 h-24 rounded-full bg-[#005366] flex items-center justify-center border-4 border-gray-200">
                                    <span class="text-3xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/jpg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#005366] file:text-white hover:file:bg-[#003d4d]">
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG or WEBP. Max size 5MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Name & Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Address Information</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Street Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                </div>

                <!-- City, State, Postal Code -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <input type="text" name="city" value="{{ old('city', $user->city) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                        <input type="text" name="state" value="{{ old('state', $user->state) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                </div>

                <!-- Country -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('customer.profile.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors shadow-md hover:shadow-lg">
                Save Changes
            </button>
        </div>
    </form>

    <!-- Change Password -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mt-6">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
        </div>
        <div class="p-6">
            <form action="{{ route('customer.profile.password') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password *</label>
                    <input type="password" name="current_password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Account -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mt-6 border-2 border-red-200">
        <div class="bg-red-50 px-6 py-4 border-b border-red-200">
            <h3 class="text-lg font-semibold text-red-900">Danger Zone</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
            
            <button onclick="document.getElementById('delete-modal').classList.remove('hidden')" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Delete Account
            </button>

            <!-- Delete Confirmation Modal -->
            <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Delete Account?</h3>
                        <p class="text-gray-600 mb-6">This action cannot be undone. All your data will be permanently deleted.</p>
                        
                        <form action="{{ route('customer.profile.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm your password *</label>
                                <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            </div>

                            <div class="flex items-center justify-end space-x-3">
                                <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Yes, Delete My Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview avatar when selected (without compression to avoid upload issues)
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">`;
            
            // Also update header avatar immediately
            const headerAvatar = document.querySelector('.header-user-avatar');
            if (headerAvatar) {
                headerAvatar.innerHTML = `<img src="${e.target.result}" alt="Avatar" class="w-9 h-9 rounded-full object-cover shadow-md">`;
            }
        }
        reader.readAsDataURL(file);
    }
});

// Update header avatar on page load if user has avatar
document.addEventListener('DOMContentLoaded', function() {
    const userAvatar = @json(auth()->user()->avatar);
    if (userAvatar) {
        const headerAvatar = document.querySelector('.header-user-avatar');
        if (headerAvatar) {
            headerAvatar.innerHTML = `<img src="${userAvatar}" alt="Avatar" class="w-9 h-9 rounded-full object-cover shadow-md">`;
        }
    }
});

// Show loading overlay when form is submitted
document.querySelector('form[action="{{ route('customer.profile.update') }}"]').addEventListener('submit', function(e) {
    // Create loading overlay
    const overlay = document.createElement('div');
    overlay.id = 'upload-overlay';
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
    overlay.innerHTML = `
        <div class="bg-white rounded-xl p-8 shadow-2xl max-w-sm mx-4">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-[#005366] mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Uploading...</h3>
                <p class="text-sm text-gray-600">Please wait while we update your profile</p>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
});
</script>
@endsection

