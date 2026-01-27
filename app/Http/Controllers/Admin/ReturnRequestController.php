<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    private array $statuses = ['pending', 'processing', 'approved', 'declined', 'completed'];

    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('resolution')) {
            $query->where('resolution', $request->resolution);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($oq) use ($search) {
                    $oq->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $allowedPerPage = [20, 50, 100];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        $requests = $query->paginate($perPage)->withQueryString();

        // Mark returns as seen for badge clearing
        session(['returns_seen_at' => now()]);

        return view('admin.returns.index', [
            'requests' => $requests,
            'statuses' => $this->statuses,
            'perPage' => $perPage,
        ]);
    }

    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order', 'user', 'order.items']);

        return view('admin.returns.show', [
            'request' => $returnRequest,
            'statuses' => $this->statuses,
        ]);
    }

    public function update(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', $this->statuses),
            'admin_note' => 'nullable|string|max:5000',
        ]);

        $returnRequest->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ]);

        return redirect()
            ->route('admin.returns.show', $returnRequest)
            ->with('success', 'Return request updated.');
    }
}
