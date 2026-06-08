-- ============================================================
-- SHOPEEFAKE FULL DATABASE (BẢN HOÀN CHỈNH ĐÃ ĐƯỢC TỐI ƯU VÀ SỬA LỖI)
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `shopeefake` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shopeefake`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET autocommit = 0;
START TRANSACTION;

-- Xóa bảng cũ nếu tồn tại để tránh xung đột cấu trúc
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `email_verifications`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `categories`;

-- ============================================================
-- 2. TẠO CẤU TRÚC CÁC BẢNG (TABLES STRUCTURE)
-- ============================================================

CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `old_price` int DEFAULT NULL,
  `discount_percent` int NOT NULL DEFAULT '0',
  `stock` int NOT NULL DEFAULT '0',
  `sold_count` int NOT NULL DEFAULT '0',
  `city` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TP. Hồ Chí Minh',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verifications` (
  `user_id` int NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `order_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_fee` int NOT NULL DEFAULT '0',
  `voucher_amount` int NOT NULL DEFAULT '0',
  `coins_used` int NOT NULL DEFAULT '0',
  `total_amount` int NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `qty` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. CHÈN DỮ LIỆU MẪU (DATA SEEDING)
-- ============================================================

-- Dữ liệu bảng categories (Đã sửa lỗi font)
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

-- Dữ liệu bảng users (Đã sửa lỗi font)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `avatar`, `phone`, `address`, `remember_token`, `email_verified_at`, `created_at`, `updated_at`) VALUES
  (1, 'Tí Vua', 'admin@shopeefake.com', '$2y$12$/S3I/h/6XNFxmt80LfKC8OuxJXU1vQzwodG8tKSOkNjOBT0DRwLIu', 'admin', 1, 'public/uploads/avatars/avatar_1_1780304978.jpg', '0912345678', 'Hà Nội, Việt Nam', NULL, NULL, '2026-06-01 15:48:33', '2026-06-01 16:11:30'),
  (2, 'Lão Gà Khô', 'user@shopeefake.com', '$2y$12$zDB10aMZB8s77HQl1HpurusRhrdCf9Nz3d0NnQkXDNw7rgRdjC3g2', 'user', 1, NULL, '0987654321', 'TP. Hồ Chí Minh, Việt Nam', NULL, NULL, '2026-06-01 15:48:33', '2026-06-01 16:11:00');

-- Dữ liệu bảng products (Đã sửa lỗi font)
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `old_price`, `discount_percent`, `stock`, `sold_count`, `city`, `description`, `thumb_url`, `is_flash_sale`, `created_at`) VALUES
  (1, 1, 'Áo thun Cotton Organic thoáng khí 2024', 199000, 299000, 34, 450, 12400, 'TP. Hồ Chí Minh', 'Áo thun cotton organic, thấm hút tốt, form basic.', 'uploads/aothun1.jpg', 1, '2026-06-01 15:48:33'),
  (2, 1, 'Quần Jeans Slimfit co giãn 4 chiều', 350000, 450000, 22, 318, 3400, 'Hà Nội', 'Jeans denim co giãn, dáng slimfit.', 'uploads/quanjeans.jpg', 0, '2026-06-01 15:48:33'),
  (3, 1, 'Kính mát UV400 phong cách unisex', 299000, 399000, 25, 210, 672, 'TP. Hồ Chí Minh', 'Kính mát chống tia UV, nhẹ và bền.', 'uploads/kinhmatuv400.jpg', 1, '2026-06-01 15:48:33'),
  (4, 2, 'Tai nghe Bluetooth chống ồn Edifier X5 Pro', 1850000, 2500000, 26, 160, 2300, 'Đà Nẵng', 'Tai nghe chống ồn chủ động, pin lâu.', 'uploads/edifierx5pro.jpg', 1, '2026-06-01 15:48:33'),
  (5, 4, 'Chăn ga gối cotton mềm mịn', 650000, 790000, 18, 120, 980, 'Đà Nẵng', 'Bộ chăn ga gối cotton, thoáng mát.', 'uploads/changagoicotton.jpg', 0, '2026-06-01 15:48:33');

-- Dữ liệu bảng product_images
INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `sort_order`) VALUES
  (1, 1, 'uploads/aothun1.jpg', 1),
  (2, 1, 'uploads/aothun3.jpg', 1);

-- BỔ SUNG: Dữ liệu mẫu bảng orders (Đơn hàng) để hoàn thiện luồng
INSERT INTO `orders` (`id`, `user_id`, `order_code`, `customer_name`, `customer_phone`, `customer_address`, `payment_method`, `shipping_fee`, `voucher_amount`, `coins_used`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
  (1, 2, 'ORD1717257110', 'Lão Gà Khô', '0987654321', '123 Đường ABC, Quận 1, TP. Hồ Chí Minh', 'COD', 30000, 0, 0, 229000, 'pending', '2026-06-01 16:15:00', '2026-06-01 16:15:00'),
  (2, 2, 'ORD1717259999', 'Lão Gà Khô', '0987654321', '123 Đường ABC, Quận 1, TP. Hồ Chí Minh', 'Online', 30000, 50000, 0, 330000, 'completed', '2026-06-02 10:00:00', '2026-06-02 14:30:00');

-- BỔ SUNG: Dữ liệu mẫu bảng order_items (Chi tiết đơn hàng)
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `qty`) VALUES
  (1, 1, 1, 'Áo thun Cotton Organic thoáng khí 2024', 199000, 1),
  (2, 2, 2, 'Quần Jeans Slimfit co giãn 4 chiều', 350000, 1);


-- ============================================================
-- 4. KẾT THÚC TRANSACTION VÀ KHÔI PHỤC THIẾT LẬP
-- ============================================================
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
SET autocommit = 1;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;