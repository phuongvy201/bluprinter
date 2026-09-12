@php
    $fieldClass = 'w-full min-h-12 px-4 py-3 border border-gray-300 rounded-lg text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#005366] focus:border-transparent';
@endphp

<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Update password</h2>
        <p class="mt-2 text-sm text-gray-600">Use a long, unique password to keep your account secure.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-semibold text-gray-900 mb-2">Current password</label>
            <input id="update_password_current_password" name="current_password" type="password" class="{{ $fieldClass }}" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-semibold text-gray-900 mb-2">New password</label>
            <input id="update_password_password" name="password" type="password" class="{{ $fieldClass }}" autocomplete="new-password">
            @error('password', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">Confirm password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="{{ $fieldClass }}" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit" class="btn-outline-petrol">Update password</button>
        </div>
    </form>
</section>
