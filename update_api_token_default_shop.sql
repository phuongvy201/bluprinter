-- ============================================================================
-- Script: Cập Nhật Default Shop Cho API Tokens
-- Mục đích: Gán shop mặc định cho API tokens để tự động assign products
-- ============================================================================

-- ============================================================================
-- 1. XEM DANH SÁCH SHOPS HIỆN CÓ
-- ============================================================================

SELECT 
    id AS shop_id,
    shop_name,
    shop_status,
    user_id AS owner_id,
    total_products,
    created_at
FROM shops
ORDER BY shop_status DESC, shop_name ASC;

-- ============================================================================
-- 2. XEM DANH SÁCH API TOKENS HIỆN CÓ
-- ============================================================================

SELECT 
    id AS token_id,
    name AS token_name,
    default_shop_id,
    is_active,
    last_used_at,
    created_at
FROM api_tokens
ORDER BY is_active DESC, created_at DESC;

-- ============================================================================
-- 3. GÁN DEFAULT SHOP CHO TOKEN CỤ THỂ
-- ============================================================================

-- Ví dụ: Gán shop ID = 5 cho token có ID = 1
-- UPDATE api_tokens SET default_shop_id = 5 WHERE id = 1;

-- Hoặc gán theo tên token:
-- UPDATE api_tokens SET default_shop_id = 5 WHERE name = 'My Production Token';

-- ⚠️ BỎ COMMENT VÀ CHẠY LỆNH PHÍA DƯỚI:

-- UPDATE api_tokens 
-- SET default_shop_id = 5  -- ← Thay đổi shop_id ở đây
-- WHERE id = 1;             -- ← Thay đổi token_id ở đây


-- ============================================================================
-- 4. GÁN DEFAULT SHOP CHO NHIỀU TOKENS CÙNG LÚC
-- ============================================================================

-- Gán tất cả tokens active vào shop ID = 5
-- UPDATE api_tokens 
-- SET default_shop_id = 5 
-- WHERE is_active = 1;

-- Gán tokens theo pattern name
-- UPDATE api_tokens 
-- SET default_shop_id = 5 
-- WHERE name LIKE '%Production%';

-- Gán tokens chưa có default shop
-- UPDATE api_tokens 
-- SET default_shop_id = 5 
-- WHERE default_shop_id IS NULL;


-- ============================================================================
-- 5. XÓA DEFAULT SHOP (SET NULL)
-- ============================================================================

-- Xóa default shop của token cụ thể
-- UPDATE api_tokens SET default_shop_id = NULL WHERE id = 1;

-- Xóa tất cả default shops
-- UPDATE api_tokens SET default_shop_id = NULL;


-- ============================================================================
-- 6. VERIFY RESULTS - Kiểm Tra Kết Quả
-- ============================================================================

SELECT 
    t.id AS token_id,
    t.name AS token_name,
    t.default_shop_id,
    s.shop_name AS default_shop_name,
    s.shop_status,
    t.is_active,
    t.last_used_at
FROM api_tokens t
LEFT JOIN shops s ON t.default_shop_id = s.id
ORDER BY t.is_active DESC, t.created_at DESC;


-- ============================================================================
-- 7. KIỂM TRA PRODUCTS ĐÃ TẠO BỞI API TOKEN
-- ============================================================================

SELECT 
    p.id AS product_id,
    p.name AS product_name,
    p.shop_id,
    s.shop_name,
    p.api_token_id,
    t.name AS token_name,
    p.created_at
FROM products p
LEFT JOIN shops s ON p.shop_id = s.id
LEFT JOIN api_tokens t ON p.api_token_id = t.id
WHERE p.created_by = 'api'
ORDER BY p.created_at DESC
LIMIT 20;


-- ============================================================================
-- 8. STATISTICS - Thống Kê
-- ============================================================================

-- Số lượng products theo shop (từ API)
SELECT 
    s.id AS shop_id,
    s.shop_name,
    COUNT(p.id) AS api_products_count,
    s.total_products AS total_products
FROM shops s
LEFT JOIN products p ON s.id = p.shop_id AND p.created_by = 'api'
GROUP BY s.id, s.shop_name, s.total_products
ORDER BY api_products_count DESC;

-- Số lượng products theo API token
SELECT 
    t.name AS token_name,
    t.default_shop_id,
    COUNT(p.id) AS products_created,
    MAX(p.created_at) AS last_product_created
FROM api_tokens t
LEFT JOIN products p ON t.id = p.api_token_id
GROUP BY t.id, t.name, t.default_shop_id
ORDER BY products_created DESC;


-- ============================================================================
-- 9. EXAMPLES - VÍ DỤ THỰC TẾ
-- ============================================================================

-- Example 1: Multi-Shop Setup
-- Token cho Electronics Shop
-- UPDATE api_tokens SET default_shop_id = 5 WHERE name = 'Electronics API Token';

-- Token cho Fashion Shop  
-- UPDATE api_tokens SET default_shop_id = 8 WHERE name = 'Fashion API Token';

-- Token cho Home & Garden Shop
-- UPDATE api_tokens SET default_shop_id = 12 WHERE name = 'Home API Token';


-- Example 2: Single Shop Setup
-- Tất cả tokens vào shop chính
-- UPDATE api_tokens SET default_shop_id = 1 WHERE is_active = 1;


-- ============================================================================
-- 10. TROUBLESHOOTING - Xử Lý Lỗi
-- ============================================================================

-- Tìm products không có shop (NULL shop_id)
SELECT id, name, shop_id, api_token_id 
FROM products 
WHERE shop_id IS NULL AND created_by = 'api';

-- Tìm products có shop_id không tồn tại
SELECT p.id, p.name, p.shop_id 
FROM products p
LEFT JOIN shops s ON p.shop_id = s.id
WHERE p.created_by = 'api' AND s.id IS NULL;

-- Tìm tokens có default_shop_id không hợp lệ
SELECT t.id, t.name, t.default_shop_id
FROM api_tokens t
LEFT JOIN shops s ON t.default_shop_id = s.id
WHERE t.default_shop_id IS NOT NULL AND s.id IS NULL;


-- ============================================================================
-- NOTES - GHI CHÚ
-- ============================================================================
--
-- Priority Order (Thứ tự ưu tiên khi tạo product):
--   1. shop_id trong API request
--   2. default_shop_id của API token
--   3. shop_id từ template
--   4. Config default shop (config/api.php)
--   5. Fallback về shop ID = 1
--
-- ⚠️ LƯU Ý:
--   - Shop phải có shop_status = 'active' để products hiển thị
--   - Chạy migration trước: php artisan migrate
--   - Clear cache sau khi sửa config: php artisan config:cache
--
-- ============================================================================

