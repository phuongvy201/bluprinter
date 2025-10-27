<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\PromoCodeMail;

class PromoCodeController extends Controller
{
    public function create()
    {
        $title = 'Promo Code';
        $coupons = [
            [
                'title' => 'Halloween',
                'percent' => 5,
                'min_order' => 50,
                'code' => 'HALLOWEEN5',
                'expires_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'title' => 'National Cat Day',
                'percent' => 5,
                'min_order' => 50,
                'code' => 'CATDAY5',
                'expires_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Welcome New User',
                'percent' => 10,
                'min_order' => 0,
                'code' => 'WELCOME10',
                'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ],
        ];
        return view('promo.code', compact('title', 'coupons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'interest' => 'nullable|string|max:2000',
            'message' => 'nullable|string|max:5000',
        ]);

        $to = config('support.promo_to') ?? env('SUPPORT_PROMO_TO') ?? (config('mail.from.address'));

        try {
            Mail::to($to)->send(new PromoCodeMail($data));
        } catch (\Throwable $e) {
            Log::error('Promo code request email failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to submit your request. Please try again later.');
        }

        return redirect()->route('promo.code.create')->with('success', 'Thanks! We will email you a promo code shortly.');
    }
}
