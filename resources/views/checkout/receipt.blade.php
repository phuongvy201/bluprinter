<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --petrol: #005366;
            --petrol-dark: #003d4d;
            --brand-red: #e2150c;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            --bg-page: #f9fafb;
            --bg-surface: #ffffff;
            --border: #e5e7eb;
            --border-strong: #d1d5db;
            --success: #16a34a;
            --warning: #d97706;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
            padding: 24px;
            background: var(--bg-page);
            font-size: 16px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-surface);
            padding: 40px;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid var(--petrol);
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .header h1 {
            color: var(--petrol);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .header p {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .header .order-number {
            margin-top: 12px;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        .section {
            margin-bottom: 24px;
        }
        .section h2 {
            color: var(--petrol);
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
        }
        .section p {
            margin-bottom: 8px;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        .section .label {
            font-weight: 600;
            color: var(--text-primary);
            display: inline-block;
            min-width: 100px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        .items-table thead {
            background: var(--petrol);
            color: white;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .items-table th {
            font-weight: 600;
            font-size: 0.875rem;
        }
        .items-table td {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        .items-table tbody tr:hover {
            background: var(--bg-page);
        }
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .item-image-placeholder {
            width: 60px;
            height: 60px;
            background: var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .totals {
            margin-top: 24px;
            border-top: 2px solid var(--petrol);
            padding-top: 24px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        .total-row.final {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--petrol);
            border-top: 1px solid var(--border);
            padding-top: 16px;
            margin-top: 8px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 4px;
        }
        .status-paid {
            background: #dcfce7;
            color: var(--success);
        }
        .status-pending {
            background: #fef3c7;
            color: var(--warning);
        }
        .exchange-note {
            background: var(--bg-page);
            padding: 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                padding: 20px;
            }
        }
        @media (max-width: 640px) {
            body { padding: 16px; }
            .receipt-container { padding: 24px 16px; }
            .order-info { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Bluprinter</h1>
            <p>{{ $locale === 'vi' ? 'Hóa đơn đặt hàng' : 'Order Receipt' }}</p>
            <p class="order-number">{{ $order->order_number }}</p>
        </div>

        <div class="order-info">
            <div class="section">
                <h2>{{ $locale === 'vi' ? 'Thông tin khách hàng' : 'Customer Information' }}</h2>
                <p><span class="label">{{ $locale === 'vi' ? 'Tên:' : 'Name:' }}</span> {{ $order->customer_name }}</p>
                <p><span class="label">{{ $locale === 'vi' ? 'Email:' : 'Email:' }}</span> {{ $order->customer_email }}</p>
                @if($order->customer_phone)
                    <p><span class="label">{{ $locale === 'vi' ? 'Điện thoại:' : 'Phone:' }}</span> {{ $order->customer_phone }}</p>
                @endif
            </div>

            <div class="section">
                <h2>{{ $locale === 'vi' ? 'Thông tin giao hàng' : 'Shipping Information' }}</h2>
                <p>{{ $order->shipping_address }}</p>
                <p>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
                <p>{{ $order->country }}</p>
            </div>
        </div>

        <div class="section">
            <h2>{{ $locale === 'vi' ? 'Chi tiết đơn hàng' : 'Order Details' }}</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>{{ $locale === 'vi' ? 'Hình ảnh' : 'Image' }}</th>
                        <th>{{ $locale === 'vi' ? 'Sản phẩm' : 'Product' }}</th>
                        <th>{{ $locale === 'vi' ? 'Số lượng' : 'Qty' }}</th>
                        <th>{{ $locale === 'vi' ? 'Đơn giá' : 'Unit Price' }}</th>
                        <th>{{ $locale === 'vi' ? 'Tổng' : 'Total' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $product = $item->product;
                            $imageUrl = null;
                            
                            if ($product) {
                                $media = $product->getEffectiveMedia();
                                if ($media && count($media) > 0) {
                                    if (is_string($media[0])) {
                                        $imageUrl = $media[0];
                                    } elseif (is_array($media[0])) {
                                        $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="item-image">
                                @else
                                    <div class="item-image-placeholder">📦</div>
                                @endif
                            </td>
                            <td style="color: #111827; font-weight: 500;">{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                {{ \App\Services\CurrencyService::formatPrice($item->unit_price, $currency ?? 'USD') }}
                            </td>
                            <td>
                                {{ \App\Services\CurrencyService::formatPrice($item->total_price, $currency ?? 'USD') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(($currency ?? 'USD') !== 'USD' && isset($currencyRate))
        <div class="exchange-note">
            <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 4px;">
                <strong>{{ $locale === 'vi' ? 'Tỷ giá hối đoái:' : 'Exchange Rate:' }}</strong>
            </p>
            <p style="font-size: 0.875rem; color: #111827; font-weight: 600;">
                1 USD = {{ number_format($currencyRate, 4) }} {{ $currency }}
            </p>
            <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 4px;">
                {{ $locale === 'vi' ? 'Giá được chuyển đổi từ USD' : 'Prices converted from USD' }}
            </p>
        </div>
        @endif

        <div class="totals">
            <div class="total-row">
                <span>{{ $locale === 'vi' ? 'Tạm tính:' : 'Subtotal:' }}</span>
                <span>{{ \App\Services\CurrencyService::formatPrice($convertedSubtotal ?? $order->subtotal, $currency ?? 'USD') }}</span>
            </div>
            <div class="total-row">
                <span>{{ $locale === 'vi' ? 'Phí vận chuyển:' : 'Shipping:' }}</span>
                <span>{{ \App\Services\CurrencyService::formatPrice($convertedShipping ?? $order->shipping_cost, $currency ?? 'USD') }}</span>
            </div>
            <div class="total-row">
                <span>{{ $locale === 'vi' ? 'Thuế:' : 'Tax:' }}</span>
                <span>{{ \App\Services\CurrencyService::formatPrice($convertedTax ?? $order->tax_amount, $currency ?? 'USD') }}</span>
            </div>
            @if($order->tip_amount > 0)
                <div class="total-row">
                    <span>{{ $locale === 'vi' ? 'Tiền tip:' : 'Tips:' }}</span>
                    <span>{{ \App\Services\CurrencyService::formatPrice($convertedTip ?? $order->tip_amount, $currency ?? 'USD') }}</span>
                </div>
            @endif
            <div class="total-row final">
                <span>{{ $locale === 'vi' ? 'Tổng cộng:' : 'Total:' }}</span>
                <span>{{ \App\Services\CurrencyService::formatPrice($convertedTotal ?? $order->total_amount, $currency ?? 'USD') }}</span>
            </div>
        </div>

        <div class="section">
            <h2>{{ $locale === 'vi' ? 'Trạng thái thanh toán' : 'Payment Status' }}</h2>
            <p><span class="label">{{ $locale === 'vi' ? 'Phương thức:' : 'Method:' }}</span> {{ strtoupper($order->payment_method) }}</p>
            <p><span class="label">{{ $locale === 'vi' ? 'Trạng thái:' : 'Status:' }}</span>
                <span class="status-badge {{ $order->payment_status === 'paid' ? 'status-paid' : 'status-pending' }}">
                    {{ $order->payment_status === 'paid' ? ($locale === 'vi' ? 'Đã thanh toán' : 'Paid') : ($locale === 'vi' ? 'Chờ thanh toán' : 'Pending') }}
                </span>
            </p>
            <p><span class="label">{{ $locale === 'vi' ? 'Ngày đặt:' : 'Order Date:' }}</span> {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
        </div>

        @if($order->notes)
            <div class="section">
                <h2>{{ $locale === 'vi' ? 'Ghi chú' : 'Notes' }}</h2>
                <p>{{ $order->notes }}</p>
            </div>
        @endif

        <div class="footer">
            <p>{{ $locale === 'vi' ? 'Cảm ơn bạn đã mua sắm tại Bluprinter!' : 'Thank you for shopping at Bluprinter!' }}</p>
            <p style="margin-top: 8px;">{{ $locale === 'vi' ? 'Đây là hóa đơn điện tử của bạn.' : 'This is your electronic receipt.' }}</p>
        </div>
    </div>
</body>
</html>
