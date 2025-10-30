<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Bluprinter Newsletter</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome-message h2 {
            color: #2d3748;
            font-size: 24px;
            margin: 0 0 15px 0;
        }
        .welcome-message p {
            color: #718096;
            font-size: 16px;
            margin: 0;
        }
        .benefits {
            background: #f7fafc;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
        }
        .benefits h3 {
            color: #2d3748;
            font-size: 18px;
            margin: 0 0 20px 0;
        }
        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .benefit-item:last-child {
            margin-bottom: 0;
        }
        .benefit-icon {
            width: 24px;
            height: 24px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .benefit-text {
            color: #4a5568;
            font-size: 14px;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background: #2d3748;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .footer p {
            margin: 0 0 15px 0;
            font-size: 14px;
            opacity: 0.8;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .unsubscribe {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 20px;
        }
        .unsubscribe a {
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to Bluprinter!</h1>
            <p>You're now part of our exclusive community</p>
        </div>
        
        <div class="content">
            <div class="welcome-message">
                <h2>Thank you for subscribing!</h2>
                <p>We're excited to have you join our community of creative minds and design enthusiasts.</p>
            </div>
            
            <div class="benefits">
                <h3>What you'll get:</h3>
                <div class="benefit-item">
                    <div class="benefit-icon">🎨</div>
                    <div class="benefit-text">Exclusive design tips and tutorials</div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">💎</div>
                    <div class="benefit-text">Early access to new products and features</div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">🎁</div>
                    <div class="benefit-text">Special discounts and promotional offers</div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">📰</div>
                    <div class="benefit-text">Industry news and trend updates</div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">👥</div>
                    <div class="benefit-text">Community highlights and success stories</div>
                </div>
            </div>
            
            <div class="cta-section">
                <a href="{{ route('products.index') }}" class="cta-button">Explore Our Products</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Stay connected with us on social media:</p>
            <p>
                <a href="#">Facebook</a> • 
                <a href="#">Instagram</a> • 
                <a href="#">Twitter</a> • 
                <a href="#">LinkedIn</a>
            </p>
            <div class="unsubscribe">
                <p>You received this email because you subscribed to our newsletter.</p>
                <p>
                    <a href="{{ route('newsletter.unsubscribe', ['email' => $email]) }}">Unsubscribe</a> | 
                    <a href="/page/privacy-policy">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
