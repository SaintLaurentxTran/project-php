-- ============================================================
-- 1. KHỞI TẠO VÀ LỰA CHỌN DATABASE "SHOPEEFAKE"
-- ============================================================
CREATE DATABASE IF NOT EXISTS `shopeefake` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shopeefake`;

-- Thiết lập môi trường tương thích Laragon
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET foreign_key_checks = 0;
SET autocommit = 0;
START TRANSACTION;

-- ============================================================
-- 2. TẠO CẤU TRÚC CÁC BẢNG (NẾU CHƯA CÓ)
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `icon` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `old_price` int(11) DEFAULT NULL,
  `discount_percent` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sold_count` int(11) NOT NULL DEFAULT 0,
  `city` varchar(120) NOT NULL DEFAULT 'TP. Hồ Chí Minh',
  `description` text NOT NULL,
  `thumb_url` text NOT NULL,
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dọn sạch dữ liệu cũ để tránh trùng lặp hoặc lỗi ID
TRUNCATE TABLE `product_images`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;

-- ============================================================
-- 3. ĐỔ DỮ LIỆU MẪU CHO DANH MỤC
-- ============================================================
INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Thời Trang & Phụ Kiện', NULL),
(2, 'Thiết Bị Điện Tử', NULL),
(3, 'Nhà Cửa & Đời Sống', NULL),
(4, 'Thời Trang', 'checkroom'),
(5, 'Điện Tử', 'devices'),
(6, 'Làm Đẹp', 'face'),
(7, 'Nhà Cửa', 'home'),
(8, 'Thể Thao', 'fitness_center'),
(9, 'Sức Khỏe', 'medical_services'),
(10, 'Bách Hóa', 'shopping_basket'),
(11, 'Đồ Chơi', 'toys');

-- ============================================================
-- 4. ĐỔ DỮ LIỆU MẪU SẢN PHẨM (CHỈ DÙNG ĐƯỜNG DẪN TRONG UPLOADS/)
-- ============================================================
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `old_price`, `discount_percent`, `stock`, `sold_count`, `city`, `description`, `thumb_url`, `is_flash_sale`, `created_at`) VALUES
(1, 1, 'Áo thun Cotton Organic thoáng khí 2024', 199000, 299000, 34, 450, 12400, 'TP. Hồ Chí Minh', 'Áo thun cotton organic, thấm hút tốt, form basic.', 'uploads/aothun1.jpg', 1, NOW()),
(2, 1, 'Quần Jeans Slimfit co giãn 4 chiều', 350000, 450000, 22, 318, 3400, 'Hà Nội', 'Jeans denim co giãn, dáng slimfit.', 'uploads/quanjeans.jpg', 0, NOW()),
(3, 1, 'Kính mát UV400 phong cách unisex', 299000, 399000, 25, 210, 672, 'TP. Hồ Chí Minh', 'Kính mát chống tia UV, nhẹ và bền.', 'uploads/kinhmatuv400.jpg', 1, NOW()),
(4, 2, 'Tai nghe Bluetooth chống ồn Edifier X5 Pro', 1850000, 2500000, 26, 160, 2300, 'Đà Nẵng', 'Tai nghe chống ồn chủ động, pin lâu.', 'uploads/edifierx5pro.jpg', 1, NOW()),
(5, 4, 'Chăn ga gối cotton mềm mịn', 650000, 790000, 18, 120, 980, 'Đà Nẵng', 'Bộ chăn ga gối cotton, thoáng mát.', 'uploads/changagoicotton.jpg', 0, NOW());

-- Sử dụng mật khẩu plaintext '123456', UserModel sẽ tự động mã hóa lại khi đăng nhập thành công
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
('System Admin', 'admin@shopeefake.com', '123456', 'admin', 1, NOW(), NOW()),
('Demo User', 'user@shopeefake.com', '123456', 'user', 1, NOW(), NOW());

-- ============================================================
-- 5. ĐỔ DỮ LIỆU MẪU ALBUM ẢNH PHỤ (CHỈ DÙNG ĐƯỜNG DẪN UPLOADS/)
-- ============================================================
INSERT INTO `product_images` (`product_id`, `image_url`, `sort_order`) VALUES
(1, 'uploads/aothun1.jpg', 1),
(1, 'uploads/aothun3.jpg', 1);

-- ============================================================
-- 6. TẠO RÀNG BUỘC KHÓA NGOẠI AN TOÀN
-- ============================================================
ALTER TABLE `products` ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `product_images` ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
SET foreign_key_checks = 1;