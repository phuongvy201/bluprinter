-- Tạo bảng wishlists
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned DEFAULT NULL,
    `session_id` varchar(255) DEFAULT NULL,
    `product_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `wishlists_user_id_product_id_index` (`user_id`,`product_id`),
    KEY `wishlists_session_id_product_id_index` (`session_id`,`product_id`),
    KEY `wishlists_product_id_index` (`product_id`),
    UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
    UNIQUE KEY `unique_session_product` (`session_id`,`product_id`),
    CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
