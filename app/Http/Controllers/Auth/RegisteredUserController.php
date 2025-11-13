<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TikTokEventsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $this->validateRecaptcha($request);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $tikTok = app(TikTokEventsService::class);
        if ($tikTok->enabled()) {
            $tikTok->track(
                'CompleteRegistration',
                [
                    'value' => 0,
                    'currency' => 'USD',
                    'content_type' => 'user',
                    'content_id' => (string) $user->id,
                    'content_name' => 'Account Registration',
                ],
                $request,
                [
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'external_id' => $user->id,
                ]
            );
        }

        // Redirect to home with message to verify email (NOT dashboard)
        return redirect()->intended(route('home', absolute: false))
            ->with('success', 'Registration successful! Please check your email to verify your account.');
    }

    protected function validateRecaptcha(Request $request): void
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (!$secretKey) {
            return;
        }

        $token = $request->input('g-recaptcha-response');
        if (!$token) {
            throw ValidationException::withMessages([
                'captcha' => __('Vui lòng hoàn tất kiểm tra bảo mật.'),
            ]);
        }

        try {
            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]
            );

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                throw ValidationException::withMessages([
                    'captcha' => __('Xác minh bảo mật không thành công, vui lòng thử lại.'),
                ]);
            }
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'captcha' => __('Không thể xác minh bảo mật, vui lòng thử lại.'),
            ]);
        }
    }
}
