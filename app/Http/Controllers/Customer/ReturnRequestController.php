<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReturnRequestController extends Controller
{
    public function store(Request $request, $orderNumber)
    {
        $user = Auth::user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'resolution' => 'required|string|in:refund,exchange,store_credit',
            'description' => 'nullable|string|max:2000',
            'evidence.*' => 'nullable|image|max:5120',
            'confirm' => 'accepted',
        ]);

        // Upload evidence files
        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('returns', 'public');
                $evidencePaths[] = $path;
            }
        }

        ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'resolution' => $validated['resolution'],
            'description' => $validated['description'] ?? null,
            'evidence_paths' => $evidencePaths,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('customer.orders.show', $order->order_number)
            ->with('success', 'Return/Exchange request submitted successfully.');
    }
}

