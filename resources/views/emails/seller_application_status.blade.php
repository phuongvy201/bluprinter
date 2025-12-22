<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Seller Application Status</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2 style="margin-bottom: 12px;">Hi {{ $application->name }},</h2>

    @if($status === 'approved')
        <p>Your seller application has been <strong>approved</strong>. You can log in and start selling.</p>
        @if(!empty($credentials))
            <p><strong>Account details:</strong></p>
            <ul>
                <li><strong>Login email:</strong> {{ $credentials['email'] }}</li>
                <li><strong>Temporary password:</strong> {{ $credentials['password'] }}</li>
            </ul>
            <p>Login here: <a href="{{ route('login') }}">{{ route('login') }}</a></p>
            <p>Please change your password after first login.</p>
        @else
            <p>You can use your existing account to log in: <a href="{{ route('login') }}">{{ route('login') }}</a></p>
        @endif
    @else
        <p>Your seller application has been <strong>reviewed</strong> and was not approved at this time.</p>
        @if($reason)
            <p><strong>Notes:</strong> {{ $reason }}</p>
        @endif
    @endif

    <p style="margin-top: 16px;">Application details:</p>
    <ul>
        <li><strong>Name:</strong> {{ $application->name }}</li>
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
        <p><strong>Notes from you:</strong></p>
        <p style="white-space: pre-wrap;">{{ $application->message }}</p>
    @endif

    <p style="margin-top: 16px;">Thank you for choosing Bluprinter.</p>
</body>
</html>

