# Email Verification Setup Guide

## ✅ Đã Hoàn Thành

### 1. User Model

-   ✅ Implement `MustVerifyEmail` interface
-   ✅ Email verification đã được bật

### 2. Registration Controller

-   ✅ Redirect về trang home thay vì dashboard
-   ✅ Hiển thị thông báo yêu cầu verify email
-   ✅ Gửi email verification tự động khi đăng ký

### 3. Header Component

-   ✅ Ẩn link Dashboard với user thường (chỉ hiển thị với admin/seller)
-   ✅ Hiển thị link "Verify Email" nếu chưa verify
-   ✅ Thêm warning icon cho email chưa verify

### 4. Layout App

-   ✅ Thêm banner thông báo verify email (màu cam/đỏ)
-   ✅ Nút "Resend Email" trực tiếp trên banner
-   ✅ Link trực tiếp đến trang verification

### 5. Email Verification Routes

-   ✅ `/verify-email` - Trang yêu cầu verify
-   ✅ `/verify-email/{id}/{hash}` - Link verify trong email
-   ✅ `/email/verification-notification` - Resend verification email

## 🔧 Cấu Hình Email (Cần Thiết Lập)

### Bước 1: Cấu hình `.env`

Thêm/cập nhật các dòng sau trong file `.env`:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Bước 2: Tạo App Password (Gmail)

1. Truy cập: https://myaccount.google.com/apppasswords
2. Tạo App Password mới
3. Copy password và paste vào `MAIL_PASSWORD`

### Bước 3: Các Email Provider Khác

#### **Mailtrap (Testing)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

#### **SendGrid**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

#### **Mailgun**

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
```

### Bước 4: Test Email Configuration

Chạy lệnh sau để test:

```bash
php artisan tinker
```

Trong tinker:

```php
Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

## 📋 Flow Hoạt Động

### 1. Đăng Ký Mới

```
User Register
    ↓
Tạo Account
    ↓
Gửi Email Verification (tự động)
    ↓
Redirect về Home với thông báo
    ↓
Hiển thị Banner "Please verify email"
```

### 2. Verify Email

```
User Click Link trong Email
    ↓
Verify Email Controller
    ↓
Update email_verified_at
    ↓
Redirect về Home
    ↓
Banner biến mất
```

### 3. Resend Email

```
User Click "Resend Email"
    ↓
Gửi lại Verification Email
    ↓
Hiển thị thông báo thành công
```

## 🎨 UI/UX Features

### Top Header

-   Link "Verify Email" (màu cam) nếu chưa verify
-   Link "Dashboard" chỉ hiển thị với admin/seller

### Banner Verification

-   Màu gradient cam-đỏ
-   Icon cảnh báo
-   Text: "Please verify your email address to access all features"
-   2 Buttons:
    -   "Click here to verify" → Đến trang verification
    -   "Resend Email" → Gửi lại email

### Desktop Dropdown Menu

-   Hiển thị "Verify Email" với icon warning
-   Màu cam để nổi bật

### Verification Page

-   Design đẹp với gradient background
-   Icon email center
-   Nút "Resend Verification Email" lớn
-   Nút "Log Out"
-   Help text bên dưới

## 🔐 Security Features

-   ✅ Signed URLs cho verification links
-   ✅ Rate limiting (6 requests/minute)
-   ✅ Throttle protection
-   ✅ Email hash verification

## 📱 Responsive Design

-   ✅ Banner responsive trên mobile/tablet
-   ✅ Text điều chỉnh theo màn hình
-   ✅ Buttons stack trên mobile

## 🧪 Testing

### Test Cases:

1. Đăng ký user mới
2. Kiểm tra email nhận được
3. Click link verify trong email
4. Kiểm tra banner biến mất
5. Test resend email
6. Test với email đã verify
7. Test với email chưa verify

## 🚀 Production Checklist

-   [ ] Cấu hình email provider production
-   [ ] Test email delivery
-   [ ] Kiểm tra spam folder
-   [ ] Set up email monitoring
-   [ ] Configure email templates
-   [ ] Add email branding/logo
-   [ ] Test all verification flows
-   [ ] Monitor email bounce rate

## 📧 Email Template Customization

Nếu muốn customize email template, tạo file:

```bash
php artisan vendor:publish --tag=laravel-notifications
```

Sau đó edit:

```
resources/views/vendor/notifications/email.blade.php
```

## 🎯 Next Steps

1. **Cấu hình Email Provider** (bắt buộc)
2. **Test Email Verification Flow**
3. **Customize Email Template** (tùy chọn)
4. **Add Email Logo** (khuyến nghị)
5. **Monitor Email Delivery** (production)

## 💡 Tips

1. Sử dụng Mailtrap cho development
2. Sử dụng SendGrid/Mailgun cho production
3. Always test với real email trước khi deploy
4. Monitor email bounce và spam reports
5. Thêm DKIM và SPF records cho domain
