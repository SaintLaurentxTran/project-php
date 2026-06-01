-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for shopeefake
CREATE DATABASE IF NOT EXISTS `shopeefake` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `shopeefake`;

-- Dumping structure for table shopeefake.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.categories: ~11 rows (approximately)
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

-- Dumping structure for table shopeefake.email_verifications
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `user_id` int NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.email_verifications: ~0 rows (approximately)

-- Dumping structure for table shopeefake.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.password_resets: ~0 rows (approximately)

-- Dumping structure for table shopeefake.products
CREATE TABLE IF NOT EXISTS `products` (
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.products: ~5 rows (approximately)
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `old_price`, `discount_percent`, `stock`, `sold_count`, `city`, `description`, `thumb_url`, `is_flash_sale`, `created_at`) VALUES
	(1, 1, 'Áo thun Cotton Organic thoáng khí 2024', 199000, 299000, 34, 450, 12400, 'TP. Hồ Chí Minh', 'Áo thun cotton organic, thấm hút tốt, form basic.', 'uploads/aothun1.jpg', 1, '2026-06-01 15:48:33'),
	(2, 1, 'Quần Jeans Slimfit co giãn 4 chiều', 350000, 450000, 22, 318, 3400, 'Hà Nội', 'Jeans denim co giãn, dáng slimfit.', 'uploads/quanjeans.jpg', 0, '2026-06-01 15:48:33'),
	(3, 1, 'Kính mát UV400 phong cách unisex', 299000, 399000, 25, 210, 672, 'TP. Hồ Chí Minh', 'Kính mát chống tia UV, nhẹ và bền.', 'uploads/kinhmatuv400.jpg', 1, '2026-06-01 15:48:33'),
	(4, 2, 'Tai nghe Bluetooth chống ồn Edifier X5 Pro', 1850000, 2500000, 26, 160, 2300, 'Đà Nẵng', 'Tai nghe chống ồn chủ động, pin lâu.', 'uploads/edifierx5pro.jpg', 1, '2026-06-01 15:48:33'),
	(5, 4, 'Chăn ga gối cotton mềm mịn', 650000, 790000, 18, 120, 980, 'Đà Nẵng', 'Bộ chăn ga gối cotton, thoáng mát.', 'uploads/changagoicotton.jpg', 0, '2026-06-01 15:48:33');

-- Dumping structure for table shopeefake.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.product_images: ~2 rows (approximately)
INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `sort_order`) VALUES
	(1, 1, 'uploads/aothun1.jpg', 1),
	(2, 1, 'uploads/aothun3.jpg', 1);

-- Dumping structure for table shopeefake.users
CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.users: ~2 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `avatar`, `phone`, `address`, `remember_token`, `email_verified_at`, `created_at`, `updated_at`) VALUES
	(1, 'Tí Vua', 'admin@shopeefake.com', '$2y$12$/S3I/h/6XNFxmt80LfKC8OuxJXU1vQzwodG8tKSOkNjOBT0DRwLIu', 'admin', 1, 'public/uploads/avatars/avatar_1_1780304978.jpg', '', '', NULL, NULL, '2026-06-01 15:48:33', '2026-06-01 16:11:30'),
	(2, 'Lão Gà Khô', 'user@shopeefake.com', '$2y$12$zDB10aMZB8s77HQl1HpurusRhrdCf9Nz3d0NnQkXDNw7rgRdjC3g2', 'user', 1, NULL, '', '', NULL, NULL, '2026-06-01 15:48:33', '2026-06-01 16:11:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
