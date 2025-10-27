<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BulkOrderMail;

class BulkOrderController extends Controller
{
    public function create()
    {
        $title = 'Bulk Order Quote';
        return view('bulk.order', compact('title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'products' => 'required|string|max:2000',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'design' => 'nullable|file|max:5120', // 5MB
        ]);

        $to = config('support.bulk_order_to') ?? env('SUPPORT_BULK_ORDER_TO') ?? (config('mail.from.address'));

        try {
            Mail::to($to)->send(new BulkOrderMail($data, $request->file('design')));
        } catch (\Throwable $e) {
            Log::error('Bulk order email failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to submit your bulk order request. Please try again later.');
        }

        return redirect()->route('bulk.order.create')->with('success', 'Your request has been submitted. We will contact you shortly.');
    }
}
