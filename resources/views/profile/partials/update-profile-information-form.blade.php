@php
    $fieldClass = 'w-full min-h-12 px-4 py-3 border border-gray-300 rounded-lg text-base text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#005366] focus:border-transparent';
@endphp

<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Profile information</h2>
        <p class="mt-2 text-sm text-gray-600">Update your name and email address.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Name</label>
            <input id="name" name="name" type="text" class="{{ $fieldClass }}" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
            <input id="email" name="email" type="email" class="{{ $fieldClass }}" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-3 text-sm text-gray-600">
                    Your email address is unverified.
                    <button type="submit" form="send-verification" class="font-semibold text-[#005366] hover:text-[#003d4d] underline">
                        Resend the verification email
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-green-700">A new verification link has been sent to your email address.</p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-cta">Save changes</button>
        </div>
    </form>
</section>
