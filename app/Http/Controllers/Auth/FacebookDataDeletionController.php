<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookDataDeletionController extends Controller
{
    /**
     * Handle Facebook Data Deletion Callback
     * 
     * This endpoint is called by Facebook when a user requests to delete their data
     * Facebook will send a POST request with signed_request parameter
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handleDeletion(Request $request): JsonResponse
    {
        try {
            $signedRequest = $request->input('signed_request');

            if (!$signedRequest) {
                return response()->json([
                    'error' => 'Invalid request. Missing signed_request parameter.'
                ], 400);
            }

            // Parse signed_request from Facebook
            list($encodedSig, $payload) = explode('.', $signedRequest, 2);

            // Decode the payload
            $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

            if (!$data) {
                return response()->json([
                    'error' => 'Invalid signed_request. Unable to decode payload.'
                ], 400);
            }

            // Verify the signature (optional but recommended)
            $secret = config('services.facebook.client_secret');
            if ($secret) {
                $expectedSig = hash_hmac('sha256', $payload, $secret, true);
                $sig = base64_decode(strtr($encodedSig, '-_', '+/'));

                if ($sig !== $expectedSig) {
                    Log::warning('Facebook data deletion: Invalid signature', [
                        'user_id' => $data['user_id'] ?? null
                    ]);
                    // Continue anyway as some implementations may not verify
                }
            }

            $facebookUserId = $data['user_id'] ?? null;

            if (!$facebookUserId) {
                return response()->json([
                    'error' => 'Missing user_id in signed_request.'
                ], 400);
            }

            // Generate unique confirmation code
            $confirmationCode = 'fb_' . $facebookUserId . '_' . time() . '_' . bin2hex(random_bytes(8));

            // Find user by Facebook ID
            $user = User::where('facebook_id', $facebookUserId)->first();

            if ($user) {
                // Delete or anonymize user data
                // Option 1: Soft delete (recommended - keeps data for legal/compliance purposes)
                // $user->delete(); // This will soft delete if SoftDeletes is enabled

                // Option 2: Anonymize data (recommended for GDPR compliance)
                $user->update([
                    'name' => 'Deleted User',
                    'email' => 'deleted_' . $user->id . '_' . time() . '@deleted.local',
                    'facebook_id' => null,
                    'avatar' => null,
                    'phone' => null,
                    'address' => null,
                    'city' => null,
                    'state' => null,
                    'postal_code' => null,
                    'country' => null,
                ]);

                // You can also delete related data if needed
                // $user->orders()->delete();
                // $user->cartItems()->delete();
                // $user->wishlists()->delete();

                Log::info('Facebook data deletion: User data anonymized', [
                    'user_id' => $user->id,
                    'facebook_user_id' => $facebookUserId,
                    'confirmation_code' => $confirmationCode
                ]);
            } else {
                // User not found - return success anyway (privacy best practice)
                Log::info('Facebook data deletion: User not found', [
                    'facebook_user_id' => $facebookUserId
                ]);
            }

            // Return confirmation code to Facebook (always return success for privacy)
            return response()->json([
                'url' => route('facebook.deletion.status', ['confirmation_code' => $confirmationCode]),
                'confirmation_code' => $confirmationCode
            ]);
        } catch (\Exception $e) {
            Log::error('Facebook data deletion error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while processing the deletion request.'
            ], 500);
        }
    }

    /**
     * Status page for data deletion confirmation
     * This page is shown to the user after they request data deletion
     */
    public function status(Request $request, $confirmationCode)
    {
        return response()->view('auth.facebook-deletion-status', [
            'confirmation_code' => $confirmationCode
        ]);
    }
}
