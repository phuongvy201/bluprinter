<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter and send welcome promo code.
     */
    public function subscribe(Request $request, PromoCodeService $promoCodeService)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim((string) $request->email));
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $alreadySubscribed = NewsletterSubscription::isSubscribed($email);

        try {
            if (! $alreadySubscribed) {
                $subscription = NewsletterSubscription::subscribe($email, $ipAddress, $userAgent);

                Log::info('Newsletter subscription successful', [
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'subscription_id' => $subscription->id,
                ]);
            }

            $promoResult = $promoCodeService->claimNewsletterPromo($email);
            $demoCode = config('promo.fixed_codes.newsletter', 'NEWSLETTER10');

            if ($promoResult['success']) {
                $message = $alreadySubscribed
                    ? 'You are already subscribed. ' . ($promoResult['message'] ?? 'Your promo code is on its way.')
                    : 'Thank you for subscribing! Check your email for your exclusive promo code.';

                if (! empty($promoResult['already_sent'])) {
                    $message = 'You are already subscribed. We previously sent your promo code — please check your inbox.';
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'code' => $promoResult['code'] ?? $demoCode,
                ]);
            }

            Log::warning('Newsletter promo email failed', [
                'email' => $email,
                'message' => $promoResult['message'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => true,
                'message' => $alreadySubscribed
                    ? 'You are already subscribed. Email delivery failed — use code ' . $demoCode . ' at checkout.'
                    : 'You are subscribed! Email delivery failed — use code ' . $demoCode . ' at checkout.',
                'code' => $demoCode,
            ]);
        } catch (\Throwable $e) {
            Log::error('Newsletter subscription failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    /**
     * Show unsubscribe page
     */
    public function showUnsubscribe(Request $request, $email)
    {
        return view('newsletter.unsubscribe', compact('email'));
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request, $email)
    {
        try {
            $subscription = NewsletterSubscription::where('email', $email)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found or already unsubscribed.',
                ], 404);
            }

            $subscription->unsubscribe();

            Log::info('Newsletter unsubscription successful', [
                'email' => $email,
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'You have been successfully unsubscribed from our newsletter.',
            ]);
        } catch (\Exception $e) {
            Log::error('Newsletter unsubscription failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get subscription status
     */
    public function status(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email parameter is required.',
            ], 400);
        }

        $isSubscribed = NewsletterSubscription::isSubscribed($email);

        return response()->json([
            'success' => true,
            'subscribed' => $isSubscribed,
        ]);
    }
}
