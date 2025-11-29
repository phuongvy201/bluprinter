<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            background: #f5f5f5;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #005366;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #005366;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #005366;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
        }
        .section p {
            margin-bottom: 8px;
            font-size: 14px;
        }
        .section .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 100px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead {
            background: #005366;
            color: white;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .items-table th {
            font-weight: bold;
            font-size: 14px;
        }
        .items-table td {
            font-size: 14px;
        }
        .items-table tbody tr:hover {
            background: #f9f9f9;
        }
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .item-image-placeholder {
            width: 60px;
            height: 60px;
            background: #e0e0e0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .totals {
            margin-top: 20px;
            border-top: 2px solid #005366;
            padding-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.final {
            font-size: 20px;
            font-weight: bold;
            color: #005366;
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }
        .status-paid {
            background: #10b981;
            color: white;
        }
        .status-pending {
            background: #f59e0b;
            color: white;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Bluprinter</h1>
            <p>{{ $locale === 'vi' ? 'Hóa đơn đặt hàng' : 'Order Receipt' }}</p>
            <p style="margin-top: 10px; font-size: 16px; font-weight: bold;">{{ $order->order_number }}</p>
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
                        <th>{{ $locale === 'vi' ? 'Số lượng' : 'Quantity' }}</th>
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
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @php
                                    $itemUnitPrice = ($currency ?? 'USD') !== 'USD' && isset($currencyRate) 
                                        ? $item->unit_price * $currencyRate 
                                        : $item->unit_price;
                                    echo \App\Services\CurrencyService::formatPrice($itemUnitPrice, $currency ?? 'USD');
                                @endphp
                            </td>
                            <td>
                                @php
                                    $itemTotalPrice = ($currency ?? 'USD') !== 'USD' && isset($currencyRate) 
                                        ? $item->total_price * $currencyRate 
                                        : $item->total_price;
                                    echo \App\Services\CurrencyService::formatPrice($itemTotalPrice, $currency ?? 'USD');
                                @endphp
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Exchange Rate Display (only show if currency is not USD) -->
        @if(($currency ?? 'USD') !== 'USD' && isset($currencyRate))
        <div class="section" style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p style="font-size: 12px; color: #666; margin-bottom: 5px;">
                <strong>{{ $locale === 'vi' ? 'Tỷ giá hối đoái:' : 'Exchange Rate:' }}</strong>
            </p>
            <p style="font-size: 14px; color: #333; font-weight: bold;">
                1 USD = {{ number_format($currencyRate, 4) }} {{ $currency }}
            </p>
            <p style="font-size: 11px; color: #999; margin-top: 5px;">
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
            <p style="margin-top: 10px;">{{ $locale === 'vi' ? 'Đây là hóa đơn điện tử của bạn.' : 'This is your electronic receipt.' }}</p>
        </div>
    </div>
</body>
</html>

