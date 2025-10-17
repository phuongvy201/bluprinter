<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderTrackingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        // Get orders for products from user's shops
        $query = Order::with(['user', 'items.product.shop'])
            ->whereHas('items.product.shop', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by tracking status
        if ($request->has('tracking_status') && $request->tracking_status) {
            if ($request->tracking_status === 'with_tracking') {
                $query->whereNotNull('tracking_number');
            } elseif ($request->tracking_status === 'without_tracking') {
                $query->whereNull('tracking_number');
            }
        }

        $orders = $query->paginate(20);

        // Statistics for seller's products only
        $sellerOrders = Order::whereHas('items.product.shop', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        $stats = [
            'total_orders' => $sellerOrders->count(),
            'pending_orders' => $sellerOrders->where('status', 'pending')->count(),
            'processing_orders' => $sellerOrders->where('status', 'processing')->count(),
            'completed_orders' => $sellerOrders->where('status', 'delivered')->count(),
            'total_revenue' => $sellerOrders->where('payment_status', 'paid')->sum('total_amount'),
            'today_orders' => $sellerOrders->whereDate('created_at', today())->count(),
        ];

        return view('seller.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $user = Auth::user();

        // Check if order contains seller's products
        $hasSellerProducts = $order->items()
            ->whereHas('product.shop', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->exists();

        if (!$hasSellerProducts) {
            abort(403, 'You can only view orders containing your products.');
        }

        $order->load(['user', 'items.product.shop']);

        // Filter items to show only seller's products
        $sellerItems = $order->items()->whereHas('product.shop', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->get();

        return view('seller.orders.show', compact('order', 'sellerItems'));
    }

    public function update(Request $request, Order $order)
    {
        $user = Auth::user();

        // Check if order contains seller's products
        $hasSellerProducts = $order->items()
            ->whereHas('product.shop', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->exists();

        if (!$hasSellerProducts) {
            abort(403, 'You can only update orders containing your products.');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Get old values for comparison
        $oldTrackingNumber = $order->tracking_number;
        $oldStatus = $order->status;
        $newTrackingNumber = $request->tracking_number;
        $newStatus = $request->status;

        // Only update status, tracking_number and notes, not payment_status (admin only)
        $order->update([
            'status' => $newStatus,
            'tracking_number' => $newTrackingNumber,
            'notes' => $request->notes
        ]);

        // Send email notification if tracking number was added or status changed
        $shouldSendEmail = false;
        $emailReason = '';

        \Log::info('Order Update Debug', [
            'old_tracking' => $oldTrackingNumber,
            'new_tracking' => $newTrackingNumber,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'customer_email' => $order->customer_email
        ]);

        if ($newTrackingNumber && !$oldTrackingNumber) {
            // Tracking number was added for the first time
            $shouldSendEmail = true;
            $emailReason = 'tracking_added';
            \Log::info('Email will be sent: tracking_added');
        } elseif ($newStatus !== $oldStatus) {
            // Status was changed
            $shouldSendEmail = true;
            $emailReason = 'status_changed';
            \Log::info('Email will be sent: status_changed');
        } else {
            \Log::info('No email will be sent - no changes detected');
        }

        if ($shouldSendEmail) {
            try {
                \Log::info('Attempting to send email', [
                    'to' => $order->customer_email,
                    'reason' => $emailReason,
                    'tracking' => $newTrackingNumber,
                    'status' => $newStatus
                ]);

                Mail::to($order->customer_email)->send(
                    new OrderTrackingNotification($order, $newTrackingNumber, $newStatus)
                );

                \Log::info('Email sent successfully');

                $message = 'Order updated successfully!';
                if ($emailReason === 'tracking_added') {
                    $message .= ' Customer has been notified via email about the tracking number.';
                } elseif ($emailReason === 'status_changed') {
                    $message .= ' Customer has been notified via email about the status update.';
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send order notification email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'to' => $order->customer_email
                ]);
                $message = 'Order updated successfully! However, there was an issue sending the email notification.';
            }
        } else {
            $message = 'Order updated successfully!';
        }

        return redirect()->back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['user', 'items.product.shop'])
            ->whereHas('items.product.shop', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->get();

        // Generate CSV
        $filename = 'seller_orders_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Status',
                'Payment Status',
                'Total Amount',
                'Created At'
            ]);

            // CSV data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->customer_phone,
                    $order->status,
                    $order->payment_status,
                    $order->total_amount,
                    $order->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
