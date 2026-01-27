<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the customer's orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $status = $request->get('status');
        $search = $request->get('search');

        // Build query
        $query = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $orders = $query->paginate(10);

        // Get order statistics
        $stats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        return view('customer.orders.index', compact('orders', 'stats', 'status', 'search'));
    }

    /**
     * Display the specified order.
     */
    public function show($orderNumber)
    {
        $user = Auth::user();

        // Find order by order number and ensure it belongs to the current user
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with([
                'items.product',
                'returnRequests' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
            ])
            ->firstOrFail();

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Track order status.
     */
    public function track(Request $request)
    {
        $orderNumber = $request->get('order_number');
        $email = $request->get('email');

        if (!$orderNumber || !$email) {
            return view('customer.orders.track');
        }

        // Find order by order number and email
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_email', $email)
            ->with(['items.product'])
            ->first();

        if (!$order) {
            return view('customer.orders.track')
                ->with('error', 'Order not found. Please check your order number and email.');
        }

        return view('customer.orders.track', compact('order'));
    }

    /**
     * Cancel an order (only if status is pending or processing).
     */
    public function cancel($orderNumber)
    {
        $user = Auth::user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            return redirect()
                ->route('customer.orders.show', $orderNumber)
                ->with('error', 'This order cannot be cancelled.');
        }

        // Update order status
        $order->update([
            'status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes . "\n" : '') .
                "Order cancelled by customer on " . now()->format('Y-m-d H:i:s')
        ]);

        return redirect()
            ->route('customer.orders.show', $orderNumber)
            ->with('success', 'Order has been cancelled successfully.');
    }
}
