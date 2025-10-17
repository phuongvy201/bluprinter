# 🎨 Bluprinter - Print on Demand Platform

Print-on-Demand platform built with Laravel 11, allowing users to create and sell custom products.

---

## 🚀 API Documentation

### 📌 Quick Access Links

| Resource                     | URL                                                                     | Description            |
| ---------------------------- | ----------------------------------------------------------------------- | ---------------------- |
| 🔑 **API Token Dashboard**   | http://localhost:8000/api-token.html                                    | View API token & stats |
| 📖 **Swagger Documentation** | http://localhost:8000/api-docs.html                                     | Interactive API docs   |
| 📥 **Postman Collection**    | http://localhost:8000/Bluprinter_API_Collection.postman_collection.json | Download & import      |

### 📚 Documentation Files

| File                                                                   | Description                                  |
| ---------------------------------------------------------------------- | -------------------------------------------- |
| **[API_QUICK_START.md](API_QUICK_START.md)**                           | ⚡ Quick start guide - Test API trong 3 bước |
| **[API_DOCUMENTATION_VIETNAMESE.md](API_DOCUMENTATION_VIETNAMESE.md)** | 📚 Hướng dẫn chi tiết bằng tiếng Việt        |
| **[POSTMAN_STEP_BY_STEP.md](POSTMAN_STEP_BY_STEP.md)**                 | 🎯 Hướng dẫn test với Postman từng bước      |
| **[API_PRODUCT_DOCUMENTATION.md](API_PRODUCT_DOCUMENTATION.md)**       | 📋 Technical documentation (English)         |

---

## 🔌 API Endpoints

### Create Product (AI Integration)

```http
POST /api/products/create
Content-Type: multipart/form-data
X-API-Token: bluprinter_xxxxx...
Accept: application/json

Body:
- name: string (required)
- description: string (required)
- template_id: integer (required) [1=T-Shirt, 2=Hoodie]
- images[]: file[] (required, max 8 images)
- video: file (optional)
- price: decimal (optional)
- shop_id: integer (optional)
```

### Get Product

```http
GET /api/products/{id}
```

### List Products

```http
GET /api/products?page=1&per_page=15
```

---

## 🔑 Authentication

Add these headers to all API requests:

```bash
X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
Accept: application/json
```

⚠️ **Note:** No `Bearer` prefix needed!

---

## ⚡ Quick Test

```bash
# Test with cURL
curl -X POST http://localhost:8000/api/products/create \
  -H "X-API-Token: bluprinter_xxxxx..." \
  -H "Accept: application/json" \
  -F "name=AI T-Shirt" \
  -F "description=Beautiful design" \
  -F "template_id=1" \
  -F "images[]=@image1.jpg" \
  -F "images[]=@image2.jpg"
```

Expected response:

```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "product_id": 123,
        "url": "http://localhost:8000/products/ai-t-shirt",
        "media": ["https://s3.amazonaws.com/..."],
        "created_at": "2025-10-16T10:30:00Z"
    }
}
```

---

## 🛠️ Setup

### Requirements

-   PHP 8.2+
-   MySQL 8.0+
-   Composer
-   Node.js & NPM
-   AWS S3 (for media storage)

### Installation

```bash
# Clone repository
git clone https://github.com/your-repo/bluprinter.git
cd bluprinter

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start server
php artisan serve
```

### AWS S3 Configuration

Add to `.env`:

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
AWS_URL=https://s3.amazonaws.com/your_bucket
```

---

## 📦 Features

-   ✅ User Authentication & Authorization (Spatie Permissions)
-   ✅ Multi-Shop System
-   ✅ Product Management with Templates
-   ✅ Shopping Cart & Checkout
-   ✅ Wishlist System
-   ✅ Order Management
-   ✅ Dynamic Shipping Calculator (by country, category, quantity)
-   ✅ PayPal Integration
-   ✅ **API for AI Product Creation**
-   ✅ **AWS S3 Media Upload**
-   ✅ Email Verification
-   ✅ Password Reset
-   ✅ Responsive Design (Tailwind CSS)

---

## 📊 Tech Stack

-   **Backend:** Laravel 11
-   **Frontend:** Blade Templates + Alpine.js
-   **Styling:** Tailwind CSS
-   **Database:** MySQL
-   **Storage:** AWS S3
-   **Authentication:** Laravel Breeze + Spatie Permissions
-   **Payment:** PayPal SDK
-   **API Documentation:** Swagger UI

---

## 🔐 Security

-   CSRF Protection (API routes excluded)
-   XSS Protection
-   SQL Injection Protection (Eloquent ORM)
-   API Token Authentication
-   File Upload Validation
-   Role-Based Access Control (RBAC)

---

## 📄 License

The Bluprinter platform is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📞 Support

For support, email support@bluprinter.com or visit our documentation.

---

**Made with ❤️ by Bluprinter Team**

Last Updated: 2025-10-16
