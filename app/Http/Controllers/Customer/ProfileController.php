<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\S3Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show customer profile
     */
    public function index()
    {
        $user = auth()->user();

        // Get customer statistics
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'wishlist_items' => $user->wishlists()->count(),
        ];

        return view('customer.profile.index', compact('user', 'stats') + ['title' => 'My Profile']);
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = auth()->user();
        return view('customer.profile.edit', compact('user') + ['title' => 'Edit Profile']);
    }

    /**
     * Update customer profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');

            if (! $avatar->isValid()) {
                return back()
                    ->withInput()
                    ->withErrors(['avatar' => 'Invalid image file. Please try another photo (JPG/PNG/WEBP, max 5MB).']);
            }

            try {
                $uploadedUrl = S3Media::store($avatar, 'avatars');
                if (! $uploadedUrl) {
                    return back()
                        ->withInput()
                        ->withErrors(['avatar' => 'Could not upload avatar. Please try again.']);
                }

                if ($user->avatar) {
                    $this->deleteAvatarFile($user->avatar);
                }

                $validated['avatar'] = $uploadedUrl;
            } catch (\Throwable $e) {
                report($e);

                return back()
                    ->withInput()
                    ->withErrors(['avatar' => 'Failed to upload avatar: ' . $e->getMessage()]);
            }
        }

        $user->update($validated);

        return redirect()->route('customer.profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.'
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('customer.profile.index')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Delete account
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = auth()->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password is incorrect.'
            ]);
        }

        if ($user->avatar) {
            $this->deleteAvatarFile($user->avatar);
        }

        // Logout and delete account
        auth()->logout();
        $user->delete();

        return redirect()->route('home')
            ->with('success', 'Your account has been deleted.');
    }

    protected function deleteAvatarFile(string $avatarUrl): void
    {
        try {
            if (S3Media::isPublicUrl($avatarUrl)) {
                $path = ltrim((string) parse_url($avatarUrl, PHP_URL_PATH), '/');
                // path like image.bluprinter/avatars/xxx.jpg → avatars/xxx.jpg
                if (str_starts_with($path, 'image.bluprinter/')) {
                    $path = substr($path, strlen('image.bluprinter/'));
                }
                if ($path !== '') {
                    Storage::disk('s3')->delete($path);
                }

                return;
            }

            if (str_contains($avatarUrl, '/storage/')) {
                $relative = ltrim((string) parse_url($avatarUrl, PHP_URL_PATH), '/');
                $relative = preg_replace('#^storage/#', '', $relative) ?: '';
                if ($relative !== '') {
                    Storage::disk('public')->delete($relative);
                }
            }
        } catch (\Throwable) {
            // Continue even if deletion fails
        }
    }
}
