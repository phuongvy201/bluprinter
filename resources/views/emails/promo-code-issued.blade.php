<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Promo Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background:#f4f4f4; padding:20px;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.08);">
        <div style="background:linear-gradient(135deg,#005366,#003d4d);color:#fff;padding:28px;text-align:center;">
            <h1 style="margin:0;font-size:24px;">Your exclusive promo code</h1>
        </div>
        <div style="padding:28px;">
            @if($recipientName)
                <p>Hi {{ $recipientName }},</p>
            @endif
            <p>{{ $promoCode->description ?: 'Use the code below on your next order.' }}</p>
            <div style="margin:24px 0;padding:20px;border:2px dashed #005366;border-radius:12px;text-align:center;background:#f8fafb;">
                <div style="font-size:28px;font-weight:bold;letter-spacing:2px;color:#005366;">{{ $promoCode->code }}</div>
                <div style="margin-top:8px;font-size:14px;color:#666;">
                    @if($promoCode->type === 'percent')
                        {{ rtrim(rtrim(number_format((float) $promoCode->value, 2), '0'), '.') }}% off
                    @else
                        ${{ number_format((float) $promoCode->value, 2) }} off
                    @endif
                    @if($promoCode->expires_at)
                        · expires {{ $promoCode->expires_at->format('M j, Y') }}
                    @endif
                </div>
            </div>
            <p style="font-size:13px;color:#666;">Enter this code at checkout. Promo codes cannot be combined with volume discounts — choose the best offer for your order.</p>
            <p style="text-align:center;margin-top:24px;">
                <a href="{{ url('/checkout') }}" style="display:inline-block;background:#005366;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:bold;">Shop now</a>
            </p>
        </div>
    </div>
</body>
</html>
