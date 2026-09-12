<?php

namespace App\Http\Controllers;

use App\Mail\PromoCodeMail;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PromoCodeController extends Controller
{
    public function __construct(
        protected PromoCodeService $promoCodeService,
    ) {}

    public function create()
    {
        $title = 'Promo Code';
        $coupons = $this->promoCodeService->publicPromoCodes();
        $actionOffers = $this->promoCodeService->actionPromoOffers();

        return view('promo.code', compact('title', 'coupons', 'actionOffers'));
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $promo = $this->promoCodeService->issueWelcomeCode($data['email'], $data['name']);

        if (! $promo) {
            return back()->withInput()->with('error', 'Welcome promo is only available for new customers who have not completed an order yet.');
        }

        $result = $this->promoCodeService->claimAndSendFixedPromo(
            'welcome',
            $data['email'],
            $data['name'],
        );

        if (! ($result['success'] ?? false)) {
            return back()->withInput()->with('error', $result['message'] ?? 'Could not send email. Please try again later.');
        }

        return redirect()->route('promo.code.create')->with('success', 'Check your inbox! Your welcome code ' . ($result['code'] ?? $promo->code) . ' is on its way.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'interest' => 'nullable|string|max:2000',
            'message' => 'nullable|string|max:5000',
        ]);

        $to = config('support.promo_to') ?? env('SUPPORT_PROMO_TO') ?? config('mail.from.address');

        try {
            Mail::to($to)->send(new PromoCodeMail($data));
        } catch (\Throwable $e) {
            Log::error('Promo code request email failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to submit your request. Please try again later.');
        }

        return redirect()->route('promo.code.create')->with('success', 'Thanks! We will email you if a qualifying promo is available.');
    }
}
