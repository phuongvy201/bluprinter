# Hướng dẫn cấu hình AWS S3 cho Upload File Custom

## 1. Tạo AWS Account

-   Truy cập: https://aws.amazon.com
-   Đăng ký tài khoản AWS (cần thẻ tín dụng)

## 2. Tạo S3 Bucket

1. Đăng nhập AWS Console
2. Tìm kiếm "S3" trong thanh tìm kiếm
3. Click "Create bucket"
4. Cấu hình:
    - **Bucket name**: `bluprinter-custom-files` (hoặc tên khác)
    - **Region**: Chọn region gần nhất (VD: `ap-southeast-1` cho Singapore)
    - **Block Public Access**: Bỏ chọn "Block all public access" (để file có thể truy cập công khai)
    - Click "Create bucket"

## 3. Cấu hình CORS cho Bucket

1. Vào bucket vừa tạo
2. Chọn tab "Permissions"
3. Scroll xuống "Cross-origin resource sharing (CORS)"
4. Click "Edit" và thêm:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": ["ETag"]
    }
]
```

## 4. Tạo IAM User

1. Tìm kiếm "IAM" trong AWS Console
2. Click "Users" > "Add users"
3. **User name**: `bluprinter-uploader`
4. **Access type**: Chọn "Programmatic access"
5. Click "Next: Permissions"
6. Chọn "Attach existing policies directly"
7. Tìm và chọn: `AmazonS3FullAccess`
8. Click "Next" > "Next" > "Create user"
9. **LƯU Ý**: Lưu lại `Access Key ID` và `Secret Access Key` (chỉ hiển thị 1 lần)

## 5. Cấu hình Laravel (.env)

Thêm vào file `.env`:

```env
AWS_ACCESS_KEY_ID=your_access_key_id_here
AWS_SECRET_ACCESS_KEY=your_secret_access_key_here
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=bluprinter-custom-files
AWS_URL=https://bluprinter-custom-files.s3.ap-southeast-1.amazonaws.com
```

## 6. Cài đặt AWS SDK cho Laravel

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

## 7. Test Upload

1. Truy cập trang sản phẩm có `allow_customization = true`
2. Thử upload file
3. Kiểm tra trong S3 bucket xem file đã được upload chưa

## 8. Quản lý File

-   **Xóa file tự động**: File sẽ tự động xóa sau 24h nếu không được dùng trong đơn hàng
-   **Dọn dẹp thủ công**: Chạy command:
    ```bash
    php artisan custom-files:cleanup
    ```

## 9. API Endpoints

-   **Upload**: `POST /api/custom-files/upload`
-   **Get Files**: `GET /api/custom-files/files?product_id=123`
-   **Delete**: `DELETE /api/custom-files/{fileId}`
-   **Extend Expiration**: `POST /api/custom-files/{fileId}/extend`
-   **Get Info**: `GET /api/custom-files/upload-info`

## 10. Giới hạn Upload

-   **Max file size**: 10MB
-   **Max files**: 5 files mỗi lần upload
-   **Allowed types**:
    -   Images: JPG, PNG, GIF, WebP, SVG
    -   Videos: MP4, AVI, MOV, WMV
    -   Documents: PDF, DOC, DOCX, TXT

## 11. Bảo mật

-   File chỉ có thể xóa bởi người upload (user_id hoặc session_id)
-   File tự động expire sau 24h
-   Validation file type và size trước khi upload
-   Kiểm tra malicious content trong file

## 12. Chi phí AWS S3

-   **Storage**: ~$0.023/GB/tháng
-   **Request**: ~$0.005/1000 PUT requests
-   **Data transfer**: Free cho 100GB/tháng đầu
-   Ước tính: ~$5-10/tháng cho 1000 files upload/tháng

## 13. Alternative: MinIO (Self-hosted S3)

Nếu muốn tự host S3-compatible storage:

```bash
docker run -p 9000:9000 -p 9001:9001 \
  -e "MINIO_ROOT_USER=admin" \
  -e "MINIO_ROOT_PASSWORD=password123" \
  minio/minio server /data --console-address ":9001"
```

Sau đó cấu hình `.env`:

```env
AWS_ENDPOINT=http://localhost:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```
