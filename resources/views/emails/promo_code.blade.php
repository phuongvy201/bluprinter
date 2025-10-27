<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Promo Code Request</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;line-height:1.6;color:#333;margin:0;padding:0;background:#f4f4f4}
    .container{max-width:600px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#f97316,#f59e0b);color:#fff;padding:28px;text-align:center}
    .header h1{margin:0;font-size:22px;font-weight:800}
    .badge{display:inline-block;margin-top:8px;padding:6px 12px;border-radius:999px;background:#fff;color:#c2410c;font-size:12px;font-weight:700}
    .content{padding:24px 28px}
    .section{margin:18px 0}
    .section h3{margin:0 0 10px;font-size:15px;color:#c2410c;border-bottom:2px solid #fb923c;padding-bottom:6px}
    .info-box{background:#f8f9fa;border-left:4px solid #f59e0b;padding:14px;border-radius:6px}
    .row{padding:6px 0;border-bottom:1px solid #eee}
    .row:last-child{border-bottom:none}
    .label{width:140px;display:inline-block;color:#666;font-weight:600}
    .footer{background:#f8f9fa;padding:16px 22px;text-align:center;color:#666;font-size:13px}
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>🎟️ New Promo Code Request</h1>
    <div class="badge">{{ config('app.name') }}</div>
  </div>
  <div class="content">
    <div class="section">
      <h3>Requester</h3>
      <div class="info-box">
        <div class="row"><span class="label">Name:</span><span>{{ $data['name'] ?? '' }}</span></div>
        <div class="row"><span class="label">Email:</span><span>{{ $data['email'] ?? '' }}</span></div>
      </div>
    </div>
    @if(!empty($data['interest']) || !empty($data['message']))
    <div class="section">
      <h3>Details</h3>
      <div class="info-box">
        @if(!empty($data['interest']))
        <div class="row"><span class="label">Interest:</span><span>{{ $data['interest'] }}</span></div>
        @endif
        @if(!empty($data['message']))
        <div class="row"><span class="label">Message:</span><span>{{ $data['message'] }}</span></div>
        @endif
      </div>
    </div>
    @endif
  </div>
  <div class="footer">
    <p style="margin:0 0 6px 0;"><strong>{{ config('app.name') }}</strong></p>
    <p style="margin:0;font-size:12px;color:#999">Automated notification. © {{ date('Y') }} {{ config('app.name') }}.</p>
  </div>
</div>
</body>
</html>
