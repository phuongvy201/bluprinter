<?php

namespace App\Http\Controllers;

use App\Mail\SellerApplicationMail;
use App\Mail\SellerApplicationReceivedMail;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SellerApplicationController extends Controller
{
    public function create()
    {
        $title = 'Become a Seller';

        return view('seller.apply', compact('title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'store_name' => 'nullable|string|max:255',
            'product_categories' => 'required|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $application = SellerApplication::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'store_name' => $data['store_name'] ?? null,
            'product_categories' => $data['product_categories'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        $recipient = env('SELLER_APPLICATION_TO', config('mail.from.address'));

        try {
            Mail::to($recipient)->send(new SellerApplicationMail($data));
            Mail::to($application->email)->send(new SellerApplicationReceivedMail($application));
        } catch (\Throwable $e) {
            Log::error('Seller application email failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to submit your application right now. Please try again later.');
        }

        return redirect()
            ->route('seller.apply')
            ->with('success', 'Your application was submitted. We will review it and respond within 24-48 hours.');
    }
}
