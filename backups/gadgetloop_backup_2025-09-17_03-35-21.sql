-- GadgetLoop Database Backup
-- Generated on: 2025-09-17 03:35:21
-- Backup Type: full

-- Table structure for `cart`
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_cart_user` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `cart`
INSERT INTO `cart` VALUES ('26', '1', '23', '1', '2025-08-20 10:43:59', '2025-08-20 10:43:59');
INSERT INTO `cart` VALUES ('29', '10', '24', '2', '2025-09-09 14:35:09', '2025-09-09 14:41:59');
INSERT INTO `cart` VALUES ('31', '17', '18', '1', '2025-09-13 22:44:45', '2025-09-13 22:44:45');
INSERT INTO `cart` VALUES ('32', '17', '11', '1', '2025-09-14 21:02:11', '2025-09-14 21:02:11');

-- Table structure for `categories`
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `categories`
INSERT INTO `categories` VALUES ('1', 'Smartphones', 'Latest smartphones and mobile devices', 'smartphone.jpg', '1', '2', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('2', 'Laptops', 'Laptops and notebooks for work and gaming', 'laptop.jpg', '1', '1', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('3', 'Tablets', 'Tablets and e-readers', 'tabletssss.jpg', '1', '3', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('4', 'Audio', 'Headphones, speakers, and audio accessories', 'audio.jpg', '1', '4', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('5', 'Gaming', 'Gaming consoles, accessories, and peripherals', 'gamingss.jpg', '1', '5', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('6', 'Wearables', 'Smartwatches and fitness trackers', 'wearables.jpg', '1', '6', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('7', 'Accessories', 'Phone cases, chargers, and other accessories', 'accessories.jpg', '1', '0', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('8', 'Smart Home', 'Smart home devices and IoT products', 'smart-home.jpg', '1', '8', '2025-07-21 13:38:26');

-- Table structure for `coupon_usage`
DROP TABLE IF EXISTS `coupon_usage`;
CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `coupons`
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `coupons`
INSERT INTO `coupons` VALUES ('1', 'WELCOME10', 'Welcome discount for new customers', 'percentage', '10.00', '50.00', NULL, '100', '0', '1', NULL, '2025-08-20 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `coupons` VALUES ('2', 'SAVE50', 'Save $50 on orders over $200', 'fixed', '50.00', '200.00', NULL, '50', '0', '1', NULL, '2026-08-05 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `coupons` VALUES ('4', '2025YAY', 'Enjoy 2025', 'percentage', '20.00', '1.00', '20.00', '1', '0', '1', NULL, '2025-12-31 23:59:00', '2025-07-26 20:30:16');

-- Table structure for `login_attempts`
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_time` (`email`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `login_attempts`
INSERT INTO `login_attempts` VALUES ('25', 'sdf@sdfsdf.com', '::1', '2025-09-14 21:01:12');
INSERT INTO `login_attempts` VALUES ('27', 'yayarandommm@gmail.com', '::1', '2025-09-14 23:15:15');
INSERT INTO `login_attempts` VALUES ('28', 'yayarandommm@gmail.com', '::1', '2025-09-14 23:15:17');
INSERT INTO `login_attempts` VALUES ('29', 'yayarandommm@gmail.com', '::1', '2025-09-14 23:15:19');
INSERT INTO `login_attempts` VALUES ('30', 'tiongepp@gmail.com', '::1', '2025-09-14 23:16:31');
INSERT INTO `login_attempts` VALUES ('31', 'tiongepp@gmail.com', '::1', '2025-09-14 23:16:37');
INSERT INTO `login_attempts` VALUES ('32', 'yayarandommm@gmail.com', '::1', '2025-09-16 18:17:12');
INSERT INTO `login_attempts` VALUES ('33', 'yayarandommm@gmail.com', '::1', '2025-09-16 18:17:21');
INSERT INTO `login_attempts` VALUES ('44', 'douglaslys-sm23@student.tarc.edu.my', '::1', '2025-09-17 09:28:32');
INSERT INTO `login_attempts` VALUES ('45', 'douglaslys-sm23@student.tarc.edu.my', '::1', '2025-09-17 09:28:35');
INSERT INTO `login_attempts` VALUES ('46', 'douglaslys-sm23@student.tarc.edu.my', '::1', '2025-09-17 09:28:38');

-- Table structure for `notifications`
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `notifications`
INSERT INTO `notifications` VALUES ('5', '10', 'Order Confirmed: Your order #GL202508124952 has been confirmed and is being processed. [order]', '0', NULL, '2025-08-12 13:34:53');
INSERT INTO `notifications` VALUES ('6', '10', 'Order Confirmed: Your order #GL202508125691 has been confirmed and is being processed. [order]', '0', NULL, '2025-08-12 22:08:37');
INSERT INTO `notifications` VALUES ('12', '10', 'Order Confirmed: Your order #GL202509092384 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-09 14:34:13');
INSERT INTO `notifications` VALUES ('13', '17', 'Order Confirmed: Your order #GL202509137865 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-13 22:42:08');
INSERT INTO `notifications` VALUES ('14', '17', 'Order Confirmed: Your order #GL1757774931826 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-13 22:48:51');
INSERT INTO `notifications` VALUES ('15', '17', 'Order Update: Your order has been shipped and is on its way (Order #GL1757774931826) [order]', '0', NULL, '2025-09-13 22:51:25');
INSERT INTO `notifications` VALUES ('16', '17', 'Order Confirmed: Your order #GL1757854950674 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-14 21:02:30');
INSERT INTO `notifications` VALUES ('17', '17', 'Order Confirmed: Your order #GL1757855583693 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-14 21:13:03');
INSERT INTO `notifications` VALUES ('18', '17', 'Order Update: Your order is now being processed (Order #GL1757855466180) [order]', '0', NULL, '2025-09-14 21:14:50');
INSERT INTO `notifications` VALUES ('19', '17', 'Order Update: Your order is now being processed (Order #GL1757855466180) [order]', '0', NULL, '2025-09-14 21:15:21');
INSERT INTO `notifications` VALUES ('20', '17', 'Order Update: Your order has been shipped and is on its way (Order #GL202509137865) [order]', '0', NULL, '2025-09-14 21:17:08');
INSERT INTO `notifications` VALUES ('21', '17', 'Order Update: Your order has been shipped and is on its way (Order #GL202509137865) [order]', '0', NULL, '2025-09-14 21:18:32');
INSERT INTO `notifications` VALUES ('22', '2', 'Order Confirmed: Your order #GL1757861695607 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-14 22:54:55');
INSERT INTO `notifications` VALUES ('23', '2', 'Order Confirmed: Your order #GL1757861859796 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-14 22:57:39');
INSERT INTO `notifications` VALUES ('24', '2', 'Order Confirmed: Your order #GL1757861941405 has been confirmed and is being processed. [order]', '1', '2025-09-14 23:05:44', '2025-09-14 22:59:01');
INSERT INTO `notifications` VALUES ('25', '2', 'Order Confirmed: Your order #GL202509147126 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-14 23:43:46');
INSERT INTO `notifications` VALUES ('26', '2', 'Order Confirmed: Your order #GL1757864838476 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-14 23:47:18');
INSERT INTO `notifications` VALUES ('27', '2', 'Order Cancelled: Your order #GL202509147126 has been cancelled successfully. [order]', '0', NULL, '2025-09-16 18:18:20');
INSERT INTO `notifications` VALUES ('28', '2', 'Order Confirmed: Your order #GL1758071691700 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-17 09:14:51');
INSERT INTO `notifications` VALUES ('29', '2', 'Order Confirmed: Your order #GL1758071892683 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-17 09:18:12');
INSERT INTO `notifications` VALUES ('30', '2', 'Order Confirmed: Your order #GL1758072723796 has been confirmed and is being processed. [order]', '0', NULL, '2025-09-17 09:32:03');
INSERT INTO `notifications` VALUES ('31', '2', 'Order Update: Your order has been shipped and is on its way (Order #GL1758072723796) [order]', '0', NULL, '2025-09-17 09:34:04');

-- Table structure for `order_items`
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `order_items`
INSERT INTO `order_items` VALUES ('1', '1', '26', '2', '125.10', '250.20', '2025-08-10 21:32:04');
INSERT INTO `order_items` VALUES ('2', '1', '1', '1', '2850.00', '2850.00', '2025-08-10 21:32:04');
INSERT INTO `order_items` VALUES ('3', '2', '1', '1', '2850.00', '2850.00', '2025-08-10 21:36:54');
INSERT INTO `order_items` VALUES ('4', '3', '2', '1', '3329.10', '3329.10', '2025-08-11 22:27:01');
INSERT INTO `order_items` VALUES ('5', '4', '3', '1', '1999.00', '1999.00', '2025-08-11 22:29:34');
INSERT INTO `order_items` VALUES ('6', '5', '5', '1', '3035.08', '3035.08', '2025-08-11 22:40:53');
INSERT INTO `order_items` VALUES ('7', '6', '1', '1', '2850.00', '2850.00', '2025-08-11 22:57:20');
INSERT INTO `order_items` VALUES ('8', '7', '8', '1', '3849.00', '3849.00', '2025-08-11 23:00:07');
INSERT INTO `order_items` VALUES ('9', '8', '7', '1', '729.52', '729.52', '2025-08-12 13:34:53');
INSERT INTO `order_items` VALUES ('10', '8', '6', '1', '1439.20', '1439.20', '2025-08-12 13:34:53');
INSERT INTO `order_items` VALUES ('11', '8', '1', '1', '2850.00', '2850.00', '2025-08-12 13:34:53');
INSERT INTO `order_items` VALUES ('12', '9', '1', '1', '2850.00', '2850.00', '2025-08-12 22:08:36');
INSERT INTO `order_items` VALUES ('13', '9', '2', '1', '3329.10', '3329.10', '2025-08-12 22:08:36');
INSERT INTO `order_items` VALUES ('14', '9', '6', '1', '1439.20', '1439.20', '2025-08-12 22:08:36');
INSERT INTO `order_items` VALUES ('15', '10', '22', '1', '359.10', '359.10', '2025-08-18 20:34:40');
INSERT INTO `order_items` VALUES ('16', '11', '28', '1', '31.20', '31.20', '2025-08-18 20:39:05');
INSERT INTO `order_items` VALUES ('17', '12', '4', '1', '5694.15', '5694.15', '2025-08-19 12:39:50');
INSERT INTO `order_items` VALUES ('18', '13', '1', '1', '2700.00', '2700.00', '2025-09-09 14:34:13');
INSERT INTO `order_items` VALUES ('19', '13', '6', '1', '1439.20', '1439.20', '2025-09-09 14:34:13');
INSERT INTO `order_items` VALUES ('20', '13', '9', '1', '899.10', '899.10', '2025-09-09 14:34:13');
INSERT INTO `order_items` VALUES ('21', '14', '8', '1', '3849.00', '3849.00', '2025-09-13 22:42:08');
INSERT INTO `order_items` VALUES ('22', '21', '11', '1', '3799.00', '3609.05', '2025-09-14 21:13:03');
INSERT INTO `order_items` VALUES ('23', '21', '18', '1', '2999.00', '2549.15', '2025-09-14 21:13:03');
INSERT INTO `order_items` VALUES ('24', '22', '24', '1', '199.00', '175.12', '2025-09-14 22:54:55');
INSERT INTO `order_items` VALUES ('25', '23', '3', '1', '1999.00', '1999.00', '2025-09-14 22:57:39');
INSERT INTO `order_items` VALUES ('26', '23', '24', '1', '199.00', '175.12', '2025-09-14 22:57:39');
INSERT INTO `order_items` VALUES ('27', '24', '3', '1', '1999.00', '1999.00', '2025-09-14 22:59:01');
INSERT INTO `order_items` VALUES ('28', '24', '24', '1', '199.00', '175.12', '2025-09-14 22:59:01');
INSERT INTO `order_items` VALUES ('29', '25', '23', '1', '254.15', '254.15', '2025-09-14 23:43:46');
INSERT INTO `order_items` VALUES ('30', '26', '12', '1', '1499.00', '1349.10', '2025-09-14 23:47:18');
INSERT INTO `order_items` VALUES ('31', '27', '6', '1', '1799.00', '1439.20', '2025-09-17 09:14:51');
INSERT INTO `order_items` VALUES ('32', '27', '10', '2', '99.00', '148.50', '2025-09-17 09:14:51');
INSERT INTO `order_items` VALUES ('33', '27', '5', '1', '3299.00', '3035.08', '2025-09-17 09:14:51');
INSERT INTO `order_items` VALUES ('34', '28', '19', '1', '799.00', '639.20', '2025-09-17 09:18:12');
INSERT INTO `order_items` VALUES ('35', '29', '5', '1', '3299.00', '3035.08', '2025-09-17 09:32:03');
INSERT INTO `order_items` VALUES ('36', '29', '28', '1', '39.00', '31.20', '2025-09-17 09:32:03');
INSERT INTO `order_items` VALUES ('37', '29', '26', '1', '140.00', '126.00', '2025-09-17 09:32:03');

-- Table structure for `orders`
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shipping_address`)),
  `billing_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`billing_address`)),
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `orders`
INSERT INTO `orders` VALUES ('1', '2', 'GL202508105324', 'delivered', '3100.20', '248.02', '0.00', '0.00', '3348.22', '{\"first_name\":\"Demo\",\"last_name\":\"Member\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"DK\",\"phone\":\"0123456789\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-10 21:32:04', '2025-09-13 22:53:27');
INSERT INTO `orders` VALUES ('2', '2', 'GL202508102890', 'delivered', '2850.00', '228.00', '0.00', '0.00', '3078.00', '{\"first_name\":\"Demo\",\"last_name\":\"Member\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"JP\",\"phone\":\"0123456789\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-10 21:36:54', '2025-08-11 21:06:20');
INSERT INTO `orders` VALUES ('3', '2', 'GL202508112200', 'shipped', '3329.10', '266.33', '0.00', '0.00', '3595.43', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"SG\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:27:01', '2025-09-13 22:53:24');
INSERT INTO `orders` VALUES ('4', '2', 'GL202508118843', 'shipped', '1999.00', '159.92', '0.00', '0.00', '2158.92', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"KR\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:29:34', '2025-08-12 13:03:53');
INSERT INTO `orders` VALUES ('5', '2', 'GL202508115100', 'delivered', '3035.08', '242.81', '0.00', '0.00', '3277.89', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"US\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:40:53', '2025-08-12 13:02:52');
INSERT INTO `orders` VALUES ('6', '2', 'GL202508119437', 'cancelled', '2850.00', '228.00', '0.00', '0.00', '3078.00', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"erftghjk\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"FI\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:57:20', '2025-08-12 09:51:53');
INSERT INTO `orders` VALUES ('7', '2', 'GL202508113564', 'cancelled', '3849.00', '307.92', '0.00', '0.00', '4156.92', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"erftghjk\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"JP\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 23:00:07', '2025-08-11 23:09:40');
INSERT INTO `orders` VALUES ('8', '10', 'GL202508124952', 'delivered', '5018.72', '401.50', '0.00', '0.00', '5420.22', '{\"first_name\":\"Elisha\",\"last_name\":\"Tiong\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"CN\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-12 13:34:53', '2025-08-12 13:38:52');
INSERT INTO `orders` VALUES ('9', '10', 'GL202508125691', 'shipped', '7618.30', '609.46', '0.00', '0.00', '8227.76', '{\"first_name\":\"Elisha\",\"last_name\":\"Tiong\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"AT\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-12 22:08:36', '2025-08-18 20:12:32');
INSERT INTO `orders` VALUES ('10', '2', 'GL202508184565', 'cancelled', '359.10', '28.73', '0.00', '0.00', '387.83', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"erftghjk\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"AT\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-18 20:34:40', '2025-08-18 20:38:22');
INSERT INTO `orders` VALUES ('11', '2', 'GL202508183264', 'cancelled', '31.20', '2.50', '9.99', '0.00', '43.69', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"DE\",\"phone\":\"0164361141\"}', NULL, 'unknown', 'pending', NULL, NULL, '2025-08-18 20:39:05', '2025-08-19 12:25:34');
INSERT INTO `orders` VALUES ('12', '2', 'GL202508191274', 'shipped', '5694.15', '455.53', '0.00', '0.00', '6149.68', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"JP\",\"phone\":\"0164361141\"}', NULL, 'unknown', 'pending', NULL, NULL, '2025-08-19 12:39:50', '2025-08-19 13:17:14');
INSERT INTO `orders` VALUES ('13', '10', 'GL202509092384', 'delivered', '5038.30', '403.06', '0.00', '0.00', '5441.36', '{\"first_name\":\"Elisha\",\"last_name\":\"Tiong\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"FR\",\"phone\":\"0164361141\"}', NULL, 'unknown', 'pending', NULL, NULL, '2025-09-09 14:34:13', '2025-09-13 22:53:16');
INSERT INTO `orders` VALUES ('14', '17', 'GL202509137865', 'shipped', '3849.00', '307.92', '0.00', '0.00', '4156.92', '{\"first_name\":\"Yuki\",\"last_name\":\"Lai\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"NO\",\"phone\":\"0164361141\"}', NULL, 'unknown', 'pending', NULL, 'sdf', '2025-09-13 22:42:08', '2025-09-14 21:18:32');
INSERT INTO `orders` VALUES ('15', '17', 'GL1757774931826', 'shipped', '2549.15', '203.93', '0.00', '50.00', '2703.08', '{\"first_name\":\"Yuki\",\"last_name\":\"Lai\",\"address\":\"erftghjk\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"UK\",\"phone\":\"0164361141\"}', '{\"first_name\":\"Yuki\",\"last_name\":\"Lai\",\"address\":\"erftghjk\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"UK\",\"phone\":\"0164361141\"}', 'apple_pay', 'paid', NULL, '', '2025-09-13 22:48:51', '2025-09-13 22:51:25');
INSERT INTO `orders` VALUES ('21', '17', 'GL1757855583693', 'processing', '6158.20', '492.66', '0.00', '0.00', '6650.86', '{\"first_name\":\"Yuki\",\"last_name\":\"Lai\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"KR\",\"phone\":\"0164361141\"}', '{\"first_name\":\"Yuki\",\"last_name\":\"Lai\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"KR\",\"phone\":\"0164361141\"}', 'paypal', 'paid', NULL, NULL, '2025-09-14 21:13:03', '2025-09-14 21:13:03');
INSERT INTO `orders` VALUES ('22', '2', 'GL1757861695607', 'processing', '175.12', '14.01', '0.00', '0.00', '189.13', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"UK\",\"phone\":\"0164361141\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"UK\",\"phone\":\"0164361141\"}', 'apple_pay', 'paid', NULL, NULL, '2025-09-14 22:54:55', '2025-09-14 22:54:55');
INSERT INTO `orders` VALUES ('23', '2', 'GL1757861859796', 'processing', '2174.12', '173.93', '0.00', '0.00', '2348.05', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"0164361141\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"0164361141\"}', 'paypal', 'paid', NULL, NULL, '2025-09-14 22:57:39', '2025-09-14 22:57:39');
INSERT INTO `orders` VALUES ('24', '2', 'GL1757861941405', 'processing', '2174.12', '173.93', '0.00', '0.00', '2348.05', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"DK\",\"phone\":\"0164361141\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"DK\",\"phone\":\"0164361141\"}', 'apple_pay', 'paid', NULL, NULL, '2025-09-14 22:59:01', '2025-09-14 22:59:01');
INSERT INTO `orders` VALUES ('25', '2', 'GL202509147126', 'cancelled', '254.15', '20.33', '0.00', '0.00', '274.48', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"21345\",\"country\":\"CA\",\"phone\":\"0164361141\"}', NULL, 'unknown', 'pending', NULL, NULL, '2025-09-14 23:43:46', '2025-09-16 18:18:20');
INSERT INTO `orders` VALUES ('26', '2', 'GL1757864838476', 'processing', '1349.10', '107.93', '0.00', '0.00', '1457.03', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"0164361141\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"0164361141\"}', 'paypal', 'paid', NULL, NULL, '2025-09-14 23:47:18', '2025-09-14 23:47:18');
INSERT INTO `orders` VALUES ('27', '2', 'GL1758071691700', 'processing', '4622.78', '369.82', '0.00', '50.00', '4942.60', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"016436114\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"016436114\"}', 'paypal', 'paid', NULL, NULL, '2025-09-17 09:14:51', '2025-09-17 09:14:51');
INSERT INTO `orders` VALUES ('28', '2', 'GL1758071892683', 'processing', '639.20', '51.14', '0.00', '50.00', '640.34', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"016436114\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"016436114\"}', 'credit_card', 'paid', NULL, NULL, '2025-09-17 09:18:12', '2025-09-17 09:18:12');
INSERT INTO `orders` VALUES ('29', '2', 'GL1758072723796', 'shipped', '3192.28', '255.38', '0.00', '50.00', '3397.66', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"016436114\"}', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"MY\",\"phone\":\"016436114\"}', 'credit_card', 'paid', NULL, '', '2025-09-17 09:32:03', '2025-09-17 09:34:04');

-- Table structure for `password_resets`
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `password_resets`
INSERT INTO `password_resets` VALUES ('1', '10', 'd0f09276ed2c029af90cd3e90f597f77f7d0432943a400d5a37016e4738a25b0', '2025-09-14 18:25:27', '2025-09-14 23:25:27');
INSERT INTO `password_resets` VALUES ('2', '10', '06c31bc85953f1875ecebfd8dd1adbb111b52b160f82304b14026856f25f25bd', '2025-09-14 18:26:29', '2025-09-14 23:26:29');

-- Table structure for `product_images`
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` int(11) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `sku` varchar(100) DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `idx_products_active` (`is_active`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `products`
INSERT INTO `products` VALUES ('1', 'iPhone 15 Pro', 'The latest iPhone with advanced camera system and A17 Pro chip', 'Latest iPhone with pro features', '1', '3000.00', '5', '100', '10', 'IPH  290', '68a4087c44e20.png', '1', '1', '', 'sdfsdf', '2025-07-21 13:38:26', '2025-09-17 09:33:28');
INSERT INTO `products` VALUES ('2', 'Samsung Galaxy S24', 'Flagship Android smartphone with AI features', 'Premium Android smartphone', '1', '3699.00', '10', '28', '10', 'SGS24001', 'galaxy-s24.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:16:39');
INSERT INTO `products` VALUES ('3', 'MacBook Pro 14\"', 'Powerful laptop with M3 chip for professionals', 'Professional laptop with M3 chip', '2', '1999.00', '0', '24', '10', 'MBP14M3001', 'macbook-pro-14.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:16:41');
INSERT INTO `products` VALUES ('4', 'Dell XPS 13', 'Ultra-portable laptop with premium design', 'Premium ultrabook', '2', '6699.00', '15', '19', '10', 'DXPS13001', 'dell-xps-13.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:16:43');
INSERT INTO `products` VALUES ('5', 'iPad Air', 'Versatile tablet for work and creativity', 'Powerful and versatile tablet', '3', '3299.00', '8', '39', '10', 'IPADAIR001', 'ipad-air.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:18:31');
INSERT INTO `products` VALUES ('6', 'Sony WH-1000XM5', 'Premium noise-canceling headphones', 'Industry-leading noise cancellation', '4', '1799.00', '20', '57', '10', 'SWXM5001', 'sony-wh1000xm5.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:17:09');
INSERT INTO `products` VALUES ('7', 'AirPods Pro 2', 'Advanced wireless earbuds with ANC', 'Premium wireless earbuds', '4', '829.00', '12', '79', '10', 'APP2001', 'airpods-pro-2.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:17:11');
INSERT INTO `products` VALUES ('8', 'PlayStation 5', 'Next-gen gaming console', 'Latest gaming console from Sony', '5', '3849.00', '0', '14', '10', 'PS5001', 'playstation-5.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:18:13');
INSERT INTO `products` VALUES ('9', 'Apple Watch Series 9', 'Advanced smartwatch with health features', 'Latest Apple smartwatch', '6', '999.00', '10', '34', '10', 'AWS9001', 'apple-watch-s9.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:18:02');
INSERT INTO `products` VALUES ('10', 'Google Nest Hub', 'Smart display for your home', 'Smart home control center', '8', '99.00', '25', '50', '10', 'GNH001', 'nest-hub.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-26 21:26:03');
INSERT INTO `products` VALUES ('11', 'Xiaomi 14 Pro', 'Flagship Xiaomi smartphone with Leica camera and Snapdragon 8 Gen 3', 'Flagship Android with Leica camera', '1', '3799.00', '5', '40', '10', 'XM14PRO001', 'xiaomi-14-pro.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('12', 'Realme 12 Pro+', 'Affordable mid-range phone with 200MP camera', 'Mid-range phone with flagship camera', '1', '1499.00', '10', '70', '10', 'RM12P001', 'realme-12-pro-plus.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:18:15');
INSERT INTO `products` VALUES ('13', 'Huawei P60 Pro', 'Powerful photography smartphone with ultra-light sensor', 'Flagship Huawei with pro camera', '1', '4299.00', '15', '25', '10', 'HWP60PRO001', 'huawei-p60-pro.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-20 09:11:56');
INSERT INTO `products` VALUES ('14', 'ASUS ROG Zephyrus G14', 'Gaming laptop with AMD Ryzen 9 and RTX 4060 GPU', 'Powerful gaming laptop', '2', '6599.00', '5', '15', '10', 'ASUSG14001', 'rog-zephyrus-g14.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('15', 'Acer Swift X', 'Slim laptop with NVIDIA GPU for creators', 'Portable laptop for creators', '2', '3999.00', '10', '18', '10', 'ACSWX001', 'acer-swift-x.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:17:28');
INSERT INTO `products` VALUES ('16', 'HP Spectre x360', '2-in-1 convertible laptop with OLED display', 'Convertible laptop with touch screen', '2', '5499.00', '8', '12', '10', 'HPSX360001', 'hp-spectre-x360.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('17', 'Samsung Galaxy Tab S9', 'Premium Android tablet with AMOLED display', 'High-performance Android tablet', '3', '3899.00', '12', '22', '10', 'SGTS9001', 'galaxy-tab-s9.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('18', 'Lenovo Tab P12 Pro', 'Tablet for entertainment and productivity', 'All-in-one Android tablet', '3', '2999.00', '15', '30', '10', 'LTP12P001', 'lenovo-tab-p12-pro.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-19 13:02:38');
INSERT INTO `products` VALUES ('19', 'JBL Charge 5', 'Portable Bluetooth speaker with powerful sound', 'Loud portable speaker', '4', '799.00', '20', '45', '10', 'JBLCHG5001', 'jbl-charge-5.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('20', 'Anker Soundcore Life Q30', 'Budget noise-canceling headphones with long battery life', 'Affordable ANC headphones', '4', '349.00', '25', '60', '10', 'ANKQ3001', 'soundcore-life-q30.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('21', 'Nintendo Switch OLED', 'Hybrid console with improved display and battery life', 'Portable and docked gaming console', '5', '1599.00', '5', '18', '10', 'NSWOLED001', 'switch-oled.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('22', 'Razer Kishi V2', 'Mobile gaming controller for Android and iPhone', 'Compact mobile gaming controller', '5', '399.00', '10', '50', '10', 'RZKISHI001', 'razer-kishi-v2.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-18 20:38:22');
INSERT INTO `products` VALUES ('23', 'Logitech G502 X', 'Advanced gaming mouse with adjustable DPI', 'Precision gaming mouse', '5', '299.00', '15', '30', '10', 'LTG502X001', 'logitech-g502x.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 18:18:20');
INSERT INTO `products` VALUES ('24', 'Xiaomi Smart Band 8', 'Fitness tracker with AMOLED screen and multiple sports modes', 'Affordable fitness tracker', '6', '199.00', '12', '75', '10', 'XMSB8001', 'xiaomi-band-8.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:17:24');
INSERT INTO `products` VALUES ('25', 'Garmin Venu Sq 2', 'Smartwatch with GPS and fitness tracking features', 'Fitness smartwatch with GPS', '6', '1199.00', '10', '28', '10', 'GRMVNSQ2001', 'garmin-venu-sq2.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:17:06');
INSERT INTO `products` VALUES ('26', 'Anker PowerCore 10000mAh', 'Portable power bank with fast charging', 'Fast-charging power bank', '7', '140.00', '10', '98', '10', 'ANKPWR10000', 'anker-powercore.jpg', '0', '1', '', '', '2025-07-21 13:38:26', '2025-09-16 21:16:28');
INSERT INTO `products` VALUES ('27', 'Baseus USB-C Hub 6-in-1', 'Multi-port USB-C hub for laptops', 'Essential USB-C accessory', '7', '179.00', '15', '35', '10', 'BASUSBC001', 'baseus-usb-c-hub.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('28', 'Ugreen Fast Charging Cable (1.5m)', 'Durable cable with fast data transfer', 'Reliable fast-charging cable', '7', '39.00', '20', '80', '10', 'UGRCBL001', 'ugreen-cable.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-19 12:25:34');
INSERT INTO `products` VALUES ('29', 'Xiaomi Smart Air Purifier 4', 'Air purifier with smart app control and HEPA filter', 'Smart air purifier for home', '8', '599.00', '15', '30', '10', 'XMPRF4001', 'xiaomi-air-purifier.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-09-16 21:16:24');
INSERT INTO `products` VALUES ('30', 'TP-Link Tapo C200', 'Smart Wi-Fi security camera with pan and tilt', 'Affordable smart home camera', '8', '139.00', '20', '45', '10', 'TPLTPC2001', 'tapo-c200.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('31', 'Philips Hue Smart Bulb', 'Customizable smart bulb with color control', 'Smart LED bulb for home', '8', '109.00', '12', '55', '10', 'PHSHB001', 'philips-hue.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');

-- Table structure for `reviews`
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product_order` (`user_id`,`product_id`,`order_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_reviews_product` (`product_id`),
  KEY `idx_reviews_user` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `reviews`
INSERT INTO `reviews` VALUES ('2', '5', '2', '5', '4', 'I love gadget Loop', 'OMG SO PRETTY WOWWWWW', '1', '1', '2025-08-12 13:38:12', '2025-08-12 13:38:12');

-- Table structure for `user_addresses`
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('shipping','billing') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(200) NOT NULL,
  `address_line_2` varchar(200) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `profile_photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('1', 'Admin', 'User', 'admin@gadgetloop.com', '$2y$10$l6QBisIZAupNWdXebQvf/Oc7pR.TMSj2VXfabuVPJALOdcerUcVKC', NULL, 'admin', NULL, '1', '1', NULL, NULL, NULL, NULL, '2025-07-21 13:38:26', '2025-07-22 19:40:24');
INSERT INTO `users` VALUES ('2', 'Demo', '1', 'member@gadgetloop.com', '$2y$10$QaqtOV/2hQ6CgxBgB9EhX.pBY8PNIVNW7nzC2tLEo/pFU1UUEIvYy', '016436114', 'member', '68ca0ab97efbd.png', '1', '1', NULL, NULL, NULL, NULL, '2025-07-21 13:38:26', '2025-09-17 09:28:01');
INSERT INTO `users` VALUES ('10', 'Elisha', 'Tiong', 'tiongepp@gmail.com', '$2y$10$fjTkJt10ZNKr2J5te.oT6uNZx6Ew7Zy.eCzqPf41ejA02/I5hz6rO', '', 'member', '689acbd0eb28d.jpeg', '1', '1', NULL, NULL, NULL, NULL, '2025-07-24 12:58:08', '2025-08-12 13:06:24');
INSERT INTO `users` VALUES ('16', 'Yaya', '1', 'elishatpp-sm23@student.tarc.edu.my', '$2y$10$82Kc5ugRIo9.HbIqAKcyVOnngydAFIVRAcE.wf0oOy1LC0JXDKDcO', '0164361141', 'member', '68a53415e9998.jpg', '1', '1', NULL, NULL, NULL, NULL, '2025-08-20 10:25:58', '2025-08-20 10:33:57');
INSERT INTO `users` VALUES ('17', 'Yuki', 'Lai', 'yayarandommm@gmail.com', '$2y$10$MtLxd5LtfHJ6BhhYK0bZaun.WjmDkrQTkycwRmVTUo.FTaP9Vp1T6', '', 'member', '68c6bf95eb8e8.jpg', '1', '1', NULL, NULL, NULL, NULL, '2025-09-13 21:20:41', '2025-09-16 21:39:46');
INSERT INTO `users` VALUES ('18', 'Douglas', 'Lai', 'douglaslys-sm23@student.tarc.edu.my', '$2y$10$ohTbtVvcMpFRVnIiWxEQpumq7wX.OtlxXa34gyMXqJt5xbgCox87.', '0178163645', 'member', '68ca0e51c4ef7.png', '1', '1', NULL, NULL, NULL, NULL, '2025-09-17 09:23:56', '2025-09-17 09:26:41');

-- Table structure for `wishlist`
DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_wishlist_user` (`user_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `wishlist`
INSERT INTO `wishlist` VALUES ('3', '1', '21', '2025-07-26 21:25:14');
INSERT INTO `wishlist` VALUES ('10', '2', '11', '2025-09-17 09:11:31');
INSERT INTO `wishlist` VALUES ('11', '18', '17', '2025-09-17 09:26:58');

