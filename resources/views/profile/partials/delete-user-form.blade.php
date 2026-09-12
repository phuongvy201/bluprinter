@php
    $fieldClass = 'w-full min-h-12 px-4 py-3 border border-gray-300 rounded-lg text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e2150c] focus:border-transparent';
    $showDeleteErrors = $errors->userDeletion->isNotEmpty();
@endphp

<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-[#e2150c]">Delete account</h2>
        <p class="mt-2 text-sm text-gray-600">Once deleted, your account and data cannot be recovered. Download anything you need to keep first.</p>
    </header>

    <button type="button" class="btn-cta" onclick="document.getElementById('confirm-user-deletion').showModal()">
        Delete account
    </button>

    <dialog id="confirm-user-deletion" class="w-full max-w-md rounded-2xl border border-gray-100 p-0 shadow-2xl backdrop:bg-black/50">
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold text-gray-900">Delete this account?</h2>
            <p class="mt-2 text-sm text-gray-600">This cannot be undone. Enter your password to confirm.</p>

            <div class="mt-6">
                <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">Password</label>
                <input id="password" name="password" type="password" class="{{ $fieldClass }}" placeholder="Password" required>
                @error('password', 'userDeletion')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <button type="button" class="btn-outline-petrol" onclick="document.getElementById('confirm-user-deletion').close()">
                    Cancel
                </button>
                <button type="submit" class="btn-cta">Delete account</button>
            </div>
        </form>
    </dialog>
</section>

@if($showDeleteErrors)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dialog = document.getElementById('confirm-user-deletion');
            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
            }
        });
    </script>
@endif
