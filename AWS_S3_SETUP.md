# AWS S3 Setup Guide

## Hướng dẫn cấu hình AWS S3 cho tính năng import

### 1. Cài đặt AWS SDK

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### 2. Cấu hình AWS S3 trong file `.env`

Thêm các dòng sau vào file `.env`:

```env
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket-name.s3.ap-southeast-1.amazonaws.com
AWS_USE_PATH_STYLE_ENDPOINT=false

# Set default filesystem disk to S3 (optional)
FILESYSTEM_DISK=s3
```

### 3. Lấy AWS Credentials

#### Bước 1: Đăng nhập AWS Console

-   Truy cập: https://console.aws.amazon.com/
-   Đăng nhập với tài khoản của bạn

#### Bước 2: Tạo IAM User

1. Vào **IAM** → **Users** → **Add users**
2. Đặt tên user (ví dụ: `bluprinter-app`)
3. Chọn **Access key - Programmatic access**
4. Gắn policy: **AmazonS3FullAccess**
5. Hoàn tất và lưu lại:
    - Access Key ID
    - Secret Access Key

#### Bước 3: Tạo S3 Bucket

1. Vào **S3** → **Create bucket**
2. Đặt tên bucket (ví dụ: `bluprinter-media`)
3. Chọn Region (ví dụ: `ap-southeast-1` - Singapore)
4. **Uncheck** "Block all public access" (để file có thể truy cập công khai)
5. Create bucket

#### Bước 4: Cấu hình CORS cho bucket

1. Vào bucket vừa tạo → **Permissions** → **CORS**
2. Thêm cấu hình sau:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": []
    }
]
```

#### Bước 5: Cấu hình Bucket Policy (để file public)

1. Vào **Permissions** → **Bucket policy**
2. Thêm policy sau (thay `your-bucket-name`):

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::your-bucket-name/*"
        }
    ]
}
```

### 4. Test cấu hình

Chạy lệnh sau trong Laravel Tinker:

```bash
php artisan tinker
```

```php
// Test upload file
Storage::disk('s3')->put('test.txt', 'Hello S3!', 'public');

// Get URL
$url = Storage::disk('s3')->url('test.txt');
echo $url;

// Test download
$exists = Storage::disk('s3')->exists('test.txt');
var_dump($exists);
```

### 5. Cấu trúc thư mục trên S3

Sau khi import, media sẽ được lưu theo cấu trúc:

```
your-bucket/
├── products/
│   ├── images/
│   │   ├── abc123...xyz.jpg
│   │   ├── def456...uvw.png
│   │   └── ...
│   └── videos/
│       ├── ghi789...rst.mp4
│       └── ...
```

### 6. Lưu ý quan trọng

✅ **Security:**

-   Không commit AWS credentials vào Git
-   Sử dụng IAM user với quyền tối thiểu cần thiết
-   Rotate access keys định kỳ

✅ **Cost:**

-   S3 tính phí theo dung lượng lưu trữ và băng thông
-   Xem pricing: https://aws.amazon.com/s3/pricing/

✅ **Performance:**

-   Upload file lớn có thể mất thời gian
-   Import batch sẽ chậm hơn khi có nhiều media
-   Nên sử dụng queue cho import lớn (tùy chọn)

### 7. Troubleshooting

**Lỗi: "Class 'League\Flysystem\AwsS3V3\AwsS3V3Adapter' not found"**

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

**Lỗi: "Invalid credentials"**

-   Kiểm tra lại AWS_ACCESS_KEY_ID và AWS_SECRET_ACCESS_KEY trong .env
-   Chạy: `php artisan config:clear`

**Lỗi: "Access Denied"**

-   Kiểm tra IAM user có policy S3 đúng chưa
-   Kiểm tra Bucket Policy

**Lỗi: "403 Forbidden" khi truy cập URL**

-   Kiểm tra "Block all public access" đã tắt chưa
-   Kiểm tra Bucket Policy đã cho phép GetObject chưa

### 8. Chuyển đổi từ local sang S3

Nếu bạn đang dùng local storage và muốn chuyển sang S3:

```bash
# Đổi default disk
# .env
FILESYSTEM_DISK=s3

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### 9. Alternative: Sử dụng DigitalOcean Spaces hoặc MinIO

Nếu không muốn dùng AWS S3, bạn có thể dùng:

-   **DigitalOcean Spaces** (tương thích S3 API, rẻ hơn)
-   **MinIO** (self-hosted, miễn phí)

Config tương tự, chỉ cần đổi endpoint và credentials.

---

## Sử dụng trong Import

Sau khi setup xong, tính năng import sẽ tự động:

1. ✅ Download hình ảnh/video từ URLs trong file CSV
2. ✅ Upload lên AWS S3
3. ✅ Lưu S3 URLs vào database
4. ✅ Báo lỗi nếu upload thất bại

Không cần làm gì thêm! 🎉
