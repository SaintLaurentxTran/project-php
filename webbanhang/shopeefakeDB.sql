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

-- Dumping structure for table shopeefake.cart
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.cart: ~0 rows (approximately)

-- Dumping structure for table shopeefake.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.categories: ~9 rows (approximately)
INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
	(1, 'Thời Trang & Phụ Kiện', NULL),
	(2, 'Thiết Bị Điện Tử', NULL),
	(3, 'Nhà Cửa & Đời Sống', NULL),
	(4, 'Thời Trang', 'checkroom'),
	(6, 'Làm Đẹp', 'face'),
	(7, 'Nhà Cửa', 'home'),
	(8, 'Thể Thao', 'fitness_center'),
	(9, 'Sức Khỏe', 'medical_services'),
	(11, 'Đồ Chơi', 'toys');

-- Dumping structure for table shopeefake.email_verifications
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `user_id` int NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.email_verifications: ~13 rows (approximately)
INSERT INTO `email_verifications` (`user_id`, `token`, `expires_at`, `used`) VALUES
	(4, 'e07fb3d9e7fdeb5df3756516885a404d37b61ff0ae9ea74b0fa3f618db0d8d4d', '2026-06-11 09:41:16', 1),
	(7, '351091073283b918af0d19c482a5fab146418ab9b04239ecf40e451a52cf01e0', '2026-06-10 10:06:30', 1),
	(8, '5cc3a403860372ac20986a4697f8d9a11189ee87c7c5a50aeda679f1e25e79bd', '2026-06-10 10:10:58', 0),
	(9, '598e36eb367e7ea390fc27bc872c0f11c86ba205160b5b05dd936da56d5fe017', '2026-06-10 10:11:48', 0),
	(11, '226812d552d0853381b4dffdaead92ba0a0aa71487f4b9fdce803243053b6a72', '2026-06-10 10:18:46', 1),
	(12, '5532e7dcfb04a6cff6cf64889da4cb20ffa0e23ba2104756466e6a0fec064b03', '2026-06-10 10:21:29', 0),
	(14, '8f9d4eea117371d3c65365d37b5f271461df9a51fff1676ba22db4ade3e479a8', '2026-06-10 10:28:31', 0),
	(15, '29ee3d563519d200773c6750281f1bb9dce53de4d4d220ad52a3c772f430acc4', '2026-06-10 10:29:56', 1),
	(16, '0df45f4630cf085df69581d1ab0ad1ce4d8e9ea8b05384a46856244a605dc486', '2026-06-10 10:59:41', 0),
	(17, '211c11e4dcbe57811f192334d026609e0ce11667fe30165bf5ecfc68b7d61fbb', '2026-06-10 11:01:29', 0),
	(18, '2a4eeb260ff686f97c707da0a391d821779ab84cb4553ce2821bddb9fdc835a2', '2026-06-10 11:08:47', 1),
	(19, '68ad62a6d1c85939820799057361dcbceba80650528cfe55064ecb66730a307e', '2026-06-10 11:11:36', 1),
	(20, 'f952dc6f7909ef248b343c809b406d08c91c68f592d4cc7452aeaf43646c565e', '2026-06-10 11:21:19', 0),
	(22, 'ccb822cba3dc853e1c6118d77e1b5370e5e715f1086e9b6395bc16a50b3f6de4', '2026-06-10 17:22:28', 1),
	(23, 'e4c9d593b7672ceb619bdd4374d5a1003bc8ee0b4e0069e056369bede993e437', '2026-06-10 17:38:31', 1);

-- Dumping structure for table shopeefake.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `order_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_fee` int NOT NULL DEFAULT '0',
  `voucher_amount` int NOT NULL DEFAULT '0',
  `coins_used` int NOT NULL DEFAULT '0',
  `total_amount` int NOT NULL DEFAULT '0',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.orders: ~4 rows (approximately)
INSERT INTO `orders` (`id`, `user_id`, `order_code`, `customer_name`, `customer_phone`, `customer_address`, `payment_method`, `shipping_fee`, `voucher_amount`, `coins_used`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
	(1, 2, 'ORD1717257110', 'Lão Gà Khô', '0987654321', '123 Đường ABC, Quận 1, TP. Hồ Chí Minh', 'COD', 30000, 0, 0, 229000, 'pending', '2026-06-01 16:15:00', '2026-06-01 16:15:00'),
	(2, 2, 'ORD1717259999', 'Lão Gà Khô', '0987654321', '123 Đường ABC, Quận 1, TP. Hồ Chí Minh', 'Online', 30000, 50000, 0, 330000, 'completed', '2026-06-02 10:00:00', '2026-06-02 14:30:00'),
	(3, 1, 'SF1780900459220', 'Nguyễn Văn A', '090 123 4567', '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh', 'shopeepay', 32000, 50000, 0, 281000, 'pending', '2026-06-08 13:34:19', '2026-06-08 13:34:19'),
	(4, NULL, 'SF1780903888468', 'cc nè', '090 123 4567', '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh', 'cod', 32000, 50000, 0, 632000, 'pending', '2026-06-08 14:31:28', '2026-06-08 14:31:28');

-- Dumping structure for table shopeefake.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `qty` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.order_items: ~3 rows (approximately)
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `qty`) VALUES
	(1, 1, 1, 'Áo thun Cotton Organic thoáng khí 2024', 199000, 1),
	(2, 2, 2, 'Quần Jeans Slimfit co giãn 4 chiều', 350000, 1),
	(3, 3, 3, 'Kính mát UV400 phong cách unisex', 299000, 1),
	(4, 4, 5, 'Chăn ga gối cotton mềm mịn', 650000, 1);

-- Dumping structure for table shopeefake.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.password_resets: ~2 rows (approximately)
INSERT INTO `password_resets` (`email`, `token`, `expires_at`, `used`) VALUES
	('horewo7079@bncinema.com', '7b9702cf410fa1916e47467a50fc64f4ca57b8c134b953ebcf5578cd8e79c421', '2026-06-10 18:32:03', 0),
	('xomexes234@brixozu.com', 'fd4610eefa666b9669d841047db6a0719ea63c9a35b076a38cba8a684fc05488', '2026-06-10 18:34:19', 1);

-- Dumping structure for table shopeefake.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `old_price` int DEFAULT NULL,
  `discount_percent` int NOT NULL DEFAULT '0',
  `stock` int NOT NULL DEFAULT '0',
  `sold_count` int NOT NULL DEFAULT '0',
  `city` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TP. Hồ Chí Minh',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.products: ~6 rows (approximately)
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `old_price`, `discount_percent`, `stock`, `sold_count`, `city`, `description`, `thumb_url`, `is_flash_sale`, `created_at`) VALUES
	(1, 1, 'Áo thun Cotton Organic thoáng khí', 209300, 299000, 30, 8, 12400, 'TP. Huế', 'Áo thun cotton organic, thấm hút tốt, form basic.', 'uploads/aothun1.jpg', 1, '2026-06-01 15:48:33'),
	(2, 1, 'Quần Jeans Slimfit co giãn 4 chiều', 350000, 450000, 22, 318, 3400, 'Hà Nội', 'Jeans denim co giãn, dáng slimfit.', 'uploads/quanjeans.jpg', 0, '2026-06-01 15:48:33'),
	(3, 1, 'Kính mát UV400 phong cách unisex', 299000, 399000, 25, 209, 672, 'TP. Hồ Chí Minh', 'Kính mát chống tia UV, nhẹ và bền.', 'uploads/kinhmatuv400.jpg', 1, '2026-06-01 15:48:33'),
	(4, 2, 'Edifier X5 Pro', 1850000, 2500000, 26, 160, 2300, 'Đà Nẵng', 'Tai nghe chống ồn chủ động, pin lâu.', 'uploads/edifierx5pro.jpg', 1, '2026-06-01 15:48:33'),
	(5, 4, 'Chăn ga gối cotton mềm mịn', 650000, 790000, 18, 119, 980, 'Đà Nẵng', 'Bộ chăn ga gối cotton, thoáng mát.', 'uploads/changagoicotton.jpg', 0, '2026-06-01 15:48:33'),
	(15, 11, 'Mô hình Labubu Candy Series', 467500, 550000, 15, 50, 0, 'TP. Huế', 'Phiên bản kẹo ngọt cực xinh', 'uploads/labubu_candy.png', 1, '2026-06-15 00:23:04');

-- Dumping structure for table shopeefake.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table shopeefake.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `avatar`, `phone`, `address`, `remember_token`, `email_verified_at`, `created_at`, `updated_at`) VALUES
	(1, 'Tí Vua', 'admin@shopeefake.com', '$2y$12$/S3I/h/6XNFxmt80LfKC8OuxJXU1vQzwodG8tKSOkNjOBT0DRwLIu', 'admin', 1, 'public/uploads/avatars/avatar_1_1780304978.jpg', '0912345678', 'Hà Nội, Việt Nam', NULL, '2026-06-10 11:10:41', '2026-06-01 15:48:33', '2026-06-10 11:10:41'),
	(2, 'Lão Gà Khô', 'user@shopeefake.com', '$2y$12$zDB10aMZB8s77HQl1HpurusRhrdCf9Nz3d0NnQkXDNw7rgRdjC3g2', 'user', 1, NULL, '0987654321', 'TP. Hồ Chí Minh, Việt Nam', NULL, '2026-06-10 11:11:39', '2026-06-01 15:48:33', '2026-06-10 11:11:39'),
	(23, 'user02', 'xomexes234@brixozu.com', '$2y$12$5Fa15hk3uLTlEvC/ioR3qeK4kxEAlTL/sCxkdkPWc5BOprz.vRN7m', 'user', 1, NULL, NULL, NULL, NULL, '2026-06-10 17:33:50', '2026-06-10 17:33:31', '2026-06-10 17:35:31');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
