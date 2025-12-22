<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Seller Application</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2 style="margin-bottom: 16px;">New Seller Application</h2>

    <p style="margin: 4px 0;"><strong>Name:</strong> {{ $data['name'] }}</p>
    <p style="margin: 4px 0;"><strong>Email:</strong> {{ $data['email'] }}</p>
    @if(!empty($data['phone']))
        <p style="margin: 4px 0;"><strong>Phone:</strong> {{ $data['phone'] }}</p>
    @endif
    @if(!empty($data['store_name']))
        <p style="margin: 4px 0;"><strong>Store / Brand:</strong> {{ $data['store_name'] }}</p>
    @endif
    @if(!empty($data['website']))
        <p style="margin: 4px 0;"><strong>Website:</strong> {{ $data['website'] }}</p>
    @endif
    <p style="margin: 4px 0;"><strong>Product categories:</strong> {{ $data['product_categories'] }}</p>
    @if(!empty($data['marketplaces']))
        <p style="margin: 4px 0;"><strong>Selling on marketplaces:</strong> {{ $data['marketplaces'] }}</p>
    @endif
    @if(!empty($data['experience']))
        <p style="margin: 4px 0;"><strong>Experience:</strong> {{ $data['experience'] }}</p>
    @endif

    @if(!empty($data['message']))
        <div style="margin-top: 12px;">
            <strong>Notes:</strong>
            <p style="white-space: pre-wrap; margin-top: 4px;">{{ $data['message'] }}</p>
        </div>
    @endif
</body>
</html>

