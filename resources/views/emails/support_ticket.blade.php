<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Support Ticket</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #005366 0%, #E2150C 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #fff;
            color: #005366;
            margin-top: 10px;
        }
        .content { padding: 24px 30px; }
        .section { margin: 20px 0; }
        .section h3 {
            color: #005366;
            margin: 0 0 12px;
            font-size: 16px;
            border-bottom: 2px solid #E2150C;
            padding-bottom: 6px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #005366;
            padding: 16px;
            border-radius: 6px;
        }
        .row { padding: 8px 0; border-bottom: 1px solid #eee; }
        .row:last-child { border-bottom: none; }
        .label { width: 140px; display: inline-block; color: #666; font-weight: 600; }
        .message {
            white-space: pre-wrap;
            border: 1px solid #e5e7eb;
            background: #fcfcfd;
            padding: 12px;
            border-radius: 8px;
        }
        .footer {
            background: #f8f9fa;
            padding: 18px 24px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .cta {
            display: inline-block;
            padding: 12px 20px;
            background: linear-gradient(135deg, #005366 0%, #E2150C 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
        }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <h1>📩 New Support Ticket</h1>
            <div class="badge">{{ config('app.name') }}</div>
        </div>

        <div class="content">
            <div class="section">
                <h3>🧾 Ticket Information</h3>
                <div class="info-box">
                    <div class="row">
                        <span class="label">Subject:</span>
                        <span>{{ $data['subject'] ?? '' }}</span>
                    </div>
                    <div class="row">
                        <span class="label">Name:</span>
                        <span>{{ $data['name'] ?? '' }}</span>
                    </div>
                    <div class="row">
                        <span class="label">Email:</span>
                        <span>{{ $data['email'] ?? '' }}</span>
                    </div>
                    @if(!empty($data['order_number']))
                    <div class="row">
                        <span class="label">Order No.:</span>
                        <span>{{ $data['order_number'] }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="section">
                <h3>💬 Message</h3>
                <div class="message">{{ $data['message'] ?? '' }}</div>
            </div>

            <div class="section" style="text-align:center;">
                <a href="{{ config('app.url') }}" class="cta">Go to Dashboard</a>
            </div>

            <div class="section" style="background:#fff3cd; padding:14px; border-radius:8px;">
                <p style="margin:0; color:#856404; font-size:14px;">
                    If you need to reply, contact: <a href="mailto:{{ config('mail.from.address') }}" style="color:#E2150C;">{{ config('mail.from.address') }}</a>
                </p>
            </div>
        </div>

        <div class="footer">
            <p style="margin:0 0 8px 0;"><strong>{{ config('app.name') }}</strong></p>
            <p style="margin:0; font-size:12px; color:#999;">This is an automated notification from your site.<br>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
