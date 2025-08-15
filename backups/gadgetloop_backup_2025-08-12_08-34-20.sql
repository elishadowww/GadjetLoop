-- GadgetLoop Database Backup
-- Generated on: 2025-08-12 08:34:20
-- Backup Type: full

-- Table structure for `admin_activity_log`
DROP TABLE IF EXISTS `admin_activity_log`;
CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `cart`
INSERT INTO `cart` VALUES ('4', '1', '21', '1', '2025-07-26 21:25:12', '2025-07-26 21:25:12');

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
INSERT INTO `categories` VALUES ('1', 'Smartphones', 'Latest smartphones and mobile devices', 'smartphone.jpg', '1', '1', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('2', 'Laptops', 'Laptops and notebooks for work and gaming', 'laptop.jpg', '1', '2', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('3', 'Tablets', 'Tablets and e-readers', 'tabletssss.jpg', '1', '3', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('4', 'Audio', 'Headphones, speakers, and audio accessories', 'audio.jpg', '1', '4', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('5', 'Gaming', 'Gaming consoles, accessories, and peripherals', 'gamingss.jpg', '1', '5', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('6', 'Wearables', 'Smartwatches and fitness trackers', 'wearables.jpg', '1', '6', '2025-07-21 13:38:26');
INSERT INTO `categories` VALUES ('7', 'Accessories', 'Phone cases, chargers, and other accessories', 'accessories.jpg', '1', '7', '2025-07-21 13:38:26');
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
INSERT INTO `coupons` VALUES ('2', 'SAVE50', 'Save $50 on orders over $200', 'fixed', '50.00', '200.00', NULL, '50', '0', '1', NULL, '2025-08-05 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `coupons` VALUES ('3', 'TECH20', '20% off on all tech products', 'percentage', '20.00', '100.00', NULL, '200', '0', '1', NULL, '2025-07-28 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `coupons` VALUES ('4', '2025YAY', 'Enjoy 2025', 'percentage', '20.00', '1.00', '20.00', '1', '0', '1', NULL, '2025-12-31 23:59:00', '2025-07-26 20:30:16');

-- Table structure for `email_queue`
DROP TABLE IF EXISTS `email_queue`;
CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `login_attempts`
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_time` (`email`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `login_attempts`
INSERT INTO `login_attempts` VALUES ('18', 'elishatpp-sm23@student.tarc.edu.my', '::1', '2025-07-25 10:59:51');
INSERT INTO `login_attempts` VALUES ('19', 'elishatpp-sm23@student.tarc.edu.my', '::1', '2025-07-25 11:01:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `notifications`
INSERT INTO `notifications` VALUES ('5', '10', 'Order Confirmed: Your order #GL202508124952 has been confirmed and is being processed. [order]', '0', NULL, '2025-08-12 13:34:53');

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `orders`
INSERT INTO `orders` VALUES ('1', '2', 'GL202508105324', 'pending', '3100.20', '248.02', '0.00', '0.00', '3348.22', '{\"first_name\":\"Demo\",\"last_name\":\"Member\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"DK\",\"phone\":\"0123456789\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-10 21:32:04', '2025-08-10 21:32:04');
INSERT INTO `orders` VALUES ('2', '2', 'GL202508102890', 'delivered', '2850.00', '228.00', '0.00', '0.00', '3078.00', '{\"first_name\":\"Demo\",\"last_name\":\"Member\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"JP\",\"phone\":\"0123456789\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-10 21:36:54', '2025-08-11 21:06:20');
INSERT INTO `orders` VALUES ('3', '2', 'GL202508112200', 'pending', '3329.10', '266.33', '0.00', '0.00', '3595.43', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"SG\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:27:01', '2025-08-11 22:27:01');
INSERT INTO `orders` VALUES ('4', '2', 'GL202508118843', 'shipped', '1999.00', '159.92', '0.00', '0.00', '2158.92', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"KR\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:29:34', '2025-08-12 13:03:53');
INSERT INTO `orders` VALUES ('5', '2', 'GL202508115100', 'delivered', '3035.08', '242.81', '0.00', '0.00', '3277.89', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"asdasd\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"US\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:40:53', '2025-08-12 13:02:52');
INSERT INTO `orders` VALUES ('6', '2', 'GL202508119437', 'cancelled', '2850.00', '228.00', '0.00', '0.00', '3078.00', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"erftghjk\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"FI\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 22:57:20', '2025-08-12 09:51:53');
INSERT INTO `orders` VALUES ('7', '2', 'GL202508113564', 'cancelled', '3849.00', '307.92', '0.00', '0.00', '4156.92', '{\"first_name\":\"Demo\",\"last_name\":\"1\",\"address\":\"erftghjk\",\"city\":\"asdasd\",\"state\":\"asdasd\",\"zip_code\":\"21345\",\"country\":\"JP\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-11 23:00:07', '2025-08-11 23:09:40');
INSERT INTO `orders` VALUES ('8', '10', 'GL202508124952', 'delivered', '5018.72', '401.50', '0.00', '0.00', '5420.22', '{\"first_name\":\"Elisha\",\"last_name\":\"Tiong\",\"address\":\"Km3, Inanam\",\"city\":\"Kota Kinabalu\",\"state\":\"Sabah\",\"zip_code\":\"88400\",\"country\":\"CN\",\"phone\":\"0164361141\"}', NULL, 'Array', 'pending', NULL, NULL, '2025-08-12 13:34:53', '2025-08-12 13:38:52');

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `products`
INSERT INTO `products` VALUES ('1', 'iPhone 15 Pro', 'The latest iPhone with advanced camera system and A17 Pro chip', 'Latest iPhone with pro features', '1', '3000.00', '5', '47', '10', 'IP15PRO001', 'iphone-15-pro.png', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-12 13:34:53');
INSERT INTO `products` VALUES ('2', 'Samsung Galaxy S24', 'Flagship Android smartphone with AI features', 'Premium Android smartphone', '1', '3699.00', '10', '29', '10', 'SGS24001', 'galaxy-s24.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-11 22:27:01');
INSERT INTO `products` VALUES ('3', 'MacBook Pro 14\"', 'Powerful laptop with M3 chip for professionals', 'Professional laptop with M3 chip', '2', '1999.00', '0', '24', '10', 'MBP14M3001', 'macbook-pro-14.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-11 22:29:34');
INSERT INTO `products` VALUES ('4', 'Dell XPS 13', 'Ultra-portable laptop with premium design', 'Premium ultrabook', '2', '6699.00', '15', '20', '10', 'DXPS13001', 'dell-xps-13.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-26 21:24:45');
INSERT INTO `products` VALUES ('5', 'iPad Air', 'Versatile tablet for work and creativity', 'Powerful and versatile tablet', '3', '3299.00', '8', '39', '10', 'IPADAIR001', 'ipad-air.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-11 22:40:53');
INSERT INTO `products` VALUES ('6', 'Sony WH-1000XM5', 'Premium noise-canceling headphones', 'Industry-leading noise cancellation', '4', '1799.00', '20', '59', '10', 'SWXM5001', 'sony-wh1000xm5.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-12 13:34:53');
INSERT INTO `products` VALUES ('7', 'AirPods Pro 2', 'Advanced wireless earbuds with ANC', 'Premium wireless earbuds', '4', '829.00', '12', '79', '10', 'APP2001', 'airpods-pro-2.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-12 13:34:53');
INSERT INTO `products` VALUES ('8', 'PlayStation 5', 'Next-gen gaming console', 'Latest gaming console from Sony', '5', '3849.00', '0', '15', '10', 'PS5001', 'playstation-5.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-11 23:09:40');
INSERT INTO `products` VALUES ('9', 'Apple Watch Series 9', 'Advanced smartwatch with health features', 'Latest Apple smartwatch', '6', '999.00', '10', '35', '10', 'AWS9001', 'apple-watch-s9.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-24 13:54:17');
INSERT INTO `products` VALUES ('10', 'Google Nest Hub', 'Smart display for your home', 'Smart home control center', '8', '99.00', '25', '50', '10', 'GNH001', 'nest-hub.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-26 21:26:03');
INSERT INTO `products` VALUES ('11', 'Xiaomi 14 Pro', 'Flagship Xiaomi smartphone with Leica camera and Snapdragon 8 Gen 3', 'Flagship Android with Leica camera', '1', '3799.00', '5', '40', '10', 'XM14PRO001', 'xiaomi-14-pro.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('12', 'Realme 12 Pro+', 'Affordable mid-range phone with 200MP camera', 'Mid-range phone with flagship camera', '1', '1499.00', '10', '70', '10', 'RM12P001', 'realme-12-pro-plus.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-12 13:37:02');
INSERT INTO `products` VALUES ('13', 'Huawei P60 Pro', 'Powerful photography smartphone with ultra-light sensor', 'Flagship Huawei with pro camera', '1', '4299.00', '15', '25', '10', 'HWP60PRO001', 'huawei-p60-pro.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('14', 'ASUS ROG Zephyrus G14', 'Gaming laptop with AMD Ryzen 9 and RTX 4060 GPU', 'Powerful gaming laptop', '2', '6599.00', '5', '15', '10', 'ASUSG14001', 'rog-zephyrus-g14.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('15', 'Acer Swift X', 'Slim laptop with NVIDIA GPU for creators', 'Portable laptop for creators', '2', '3999.00', '10', '18', '10', 'ACSWX001', 'acer-swift-x.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('16', 'HP Spectre x360', '2-in-1 convertible laptop with OLED display', 'Convertible laptop with touch screen', '2', '5499.00', '8', '12', '10', 'HPSX360001', 'hp-spectre-x360.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('17', 'Samsung Galaxy Tab S9', 'Premium Android tablet with AMOLED display', 'High-performance Android tablet', '3', '3899.00', '12', '22', '10', 'SGTS9001', 'galaxy-tab-s9.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('18', 'Lenovo Tab P12 Pro', 'Tablet for entertainment and productivity', 'All-in-one Android tablet', '3', '2999.00', '15', '30', '10', 'LTP12P001', 'lenovo-tab-p12-pro.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('19', 'JBL Charge 5', 'Portable Bluetooth speaker with powerful sound', 'Loud portable speaker', '4', '799.00', '20', '45', '10', 'JBLCHG5001', 'jbl-charge-5.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('20', 'Anker Soundcore Life Q30', 'Budget noise-canceling headphones with long battery life', 'Affordable ANC headphones', '4', '349.00', '25', '60', '10', 'ANKQ3001', 'soundcore-life-q30.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('21', 'Nintendo Switch OLED', 'Hybrid console with improved display and battery life', 'Portable and docked gaming console', '5', '1599.00', '5', '18', '10', 'NSWOLED001', 'switch-oled.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('22', 'Razer Kishi V2', 'Mobile gaming controller for Android and iPhone', 'Compact mobile gaming controller', '5', '399.00', '10', '50', '10', 'RZKISHI001', 'razer-kishi-v2.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('23', 'Logitech G502 X', 'Advanced gaming mouse with adjustable DPI', 'Precision gaming mouse', '5', '299.00', '15', '30', '10', 'LTG502X001', 'logitech-g502x.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('24', 'Xiaomi Smart Band 8', 'Fitness tracker with AMOLED screen and multiple sports modes', 'Affordable fitness tracker', '6', '199.00', '12', '75', '10', 'XMSB8001', 'xiaomi-band-8.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('25', 'Garmin Venu Sq 2', 'Smartwatch with GPS and fitness tracking features', 'Fitness smartwatch with GPS', '6', '1199.00', '10', '28', '10', 'GRMVNSQ2001', 'garmin-venu-sq2.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('26', 'Anker PowerCore 10000mAh', 'Portable power bank with fast charging', 'Fast-charging power bank', '7', '139.00', '10', '98', '10', 'ANKPWR10000', 'anker-powercore.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-08-10 21:32:04');
INSERT INTO `products` VALUES ('27', 'Baseus USB-C Hub 6-in-1', 'Multi-port USB-C hub for laptops', 'Essential USB-C accessory', '7', '179.00', '15', '35', '10', 'BASUSBC001', 'baseus-usb-c-hub.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('28', 'Ugreen Fast Charging Cable (1.5m)', 'Durable cable with fast data transfer', 'Reliable fast-charging cable', '7', '39.00', '20', '80', '10', 'UGRCBL001', 'ugreen-cable.jpg', '0', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
INSERT INTO `products` VALUES ('29', 'Xiaomi Smart Air Purifier 4', 'Air purifier with smart app control and HEPA filter', 'Smart air purifier for home', '8', '599.00', '15', '30', '10', 'XMPRF4001', 'xiaomi-air-purifier.jpg', '1', '1', NULL, NULL, '2025-07-21 13:38:26', '2025-07-21 13:38:26');
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
INSERT INTO `reviews` VALUES ('1', '1', '2', '2', '5', 'I like it &lt;3', 'Receive in nice condition', '1', '1', '2025-08-11 21:15:28', '2025-08-12 13:36:44');
INSERT INTO `reviews` VALUES ('2', '5', '2', '5', '4', 'I love gadget Loop', 'OMG SO PRETTY WOWWWWW', '1', '1', '2025-08-12 13:38:12', '2025-08-12 13:38:12');
INSERT INTO `reviews` VALUES ('3', '1', '10', '8', '4', 'Package okok ja', 'sadfhjkuytre', '1', '1', '2025-08-12 13:39:44', '2025-08-12 13:39:44');

-- Table structure for `user_addresses`
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_addresses_user` (`user_id`),
  KEY `idx_user_addresses_type` (`type`),
  KEY `idx_user_addresses_default` (`is_default`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('1', 'Admin', 'User', 'admin@gadgetloop.com', '$2y$10$l6QBisIZAupNWdXebQvf/Oc7pR.TMSj2VXfabuVPJALOdcerUcVKC', NULL, 'admin', NULL, '1', '1', NULL, NULL, NULL, NULL, '2025-07-21 13:38:26', '2025-07-22 19:40:24');
INSERT INTO `users` VALUES ('2', 'Demo', '1', 'member@gadgetloop.com', '$2y$10$439pdBXMvGGaHDzzKG1VCeT/cd/sjCAqlKvAJFXalUkiS5SIlZ3Nu', '0164361141', 'member', '689a9e2f624d3.jpeg', '1', '1', NULL, NULL, NULL, NULL, '2025-07-21 13:38:26', '2025-08-12 09:51:43');
INSERT INTO `users` VALUES ('10', 'Elisha', 'Tiong', 'tiongepp@gmail.com', '$2y$10$fjTkJt10ZNKr2J5te.oT6uNZx6Ew7Zy.eCzqPf41ejA02/I5hz6rO', '', 'member', '689acbd0eb28d.jpeg', '1', '1', NULL, NULL, NULL, NULL, '2025-07-24 12:58:08', '2025-08-12 13:06:24');
INSERT INTO `users` VALUES ('12', 'Elisha', 'Tiong', 'elishatpp-sm23@student.tarc.edu.my', '$2y$10$vjDsM1JiXT2tD1UmWz1jeO.HF1jge0kwjW1vqlMNuP89UeRP/3Xma', '', 'member', NULL, '1', '1', NULL, NULL, NULL, NULL, '2025-07-25 15:15:24', '2025-07-26 20:07:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `wishlist`
INSERT INTO `wishlist` VALUES ('3', '1', '21', '2025-07-26 21:25:14');
INSERT INTO `wishlist` VALUES ('5', '10', '1', '2025-08-12 13:26:09');
INSERT INTO `wishlist` VALUES ('6', '10', '2', '2025-08-12 13:31:57');
INSERT INTO `wishlist` VALUES ('7', '10', '6', '2025-08-12 13:32:05');

