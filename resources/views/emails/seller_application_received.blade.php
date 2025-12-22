<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Seller Application Received</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2 style="margin-bottom: 12px;">Hi {{ $application->name }},</h2>

    <p>We’ve received your seller application. Our team will review it within 24-48 hours.</p>
    <p>If approved, we’ll send your seller account details to this email address so you can log in and start selling.</p>

    <p style="margin-top: 16px;">Application summary:</p>
    <ul>
        <li><strong>Email:</strong> {{ $application->email }}</li>
        @if($application->phone)
            <li><strong>Phone:</strong> {{ $application->phone }}</li>
        @endif
        @if($application->store_name)
            <li><strong>Store / Brand:</strong> {{ $application->store_name }}</li>
        @endif
        <li><strong>Main categories:</strong> {{ $application->product_categories }}</li>
    </ul>

    @if($application->message)
        <p><strong>Your notes:</strong></p>
        <p style="white-space: pre-wrap;">{{ $application->message }}</p>
    @endif

    <p style="margin-top: 16px;">Thank you for choosing Bluprinter.</p>
</body>
</html>

