-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2025 at 02:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gadgetloop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2025-07-22 06:38:52', '2025-07-22 06:38:52'),
(3, 10, 1, 1, '2025-07-24 05:28:05', '2025-07-24 05:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Smartphone', 'Latest smartphones and mobile devices', 'smartphone.jpg', 1, 1, '2025-07-21 05:38:26'),
(2, 'Laptops', 'Laptops and notebooks for work and gaming', 'laptop.jpg', 1, 2, '2025-07-21 05:38:26'),
(3, 'Tablets', 'Tablets and e-readers', 'tabletssss.jpg', 1, 3, '2025-07-21 05:38:26'),
(4, 'Audio', 'Headphones, speakers, and audio accessories', 'audio.jpg', 1, 4, '2025-07-21 05:38:26'),
(5, 'Gaming', 'Gaming consoles, accessories, and peripherals', 'gamingss.jpg', 1, 5, '2025-07-21 05:38:26'),
(6, 'Wearables', 'Smartwatches and fitness trackers', 'wearables.jpg', 1, 6, '2025-07-21 05:38:26'),
(7, 'Accessories', 'Phone cases, chargers, and other accessories', 'accessories.jpg', 1, 7, '2025-07-21 05:38:26'),
(8, 'Smart Home', 'Smart home devices and IoT products', 'smart-home.jpg', 1, 8, '2025-07-21 05:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `minimum_amount`, `maximum_discount`, `usage_limit`, `used_count`, `is_active`, `starts_at`, `expires_at`, `created_at`) VALUES
(1, 'WELCOME10', 'Welcome discount for new customers', 'percentage', 10.00, 50.00, NULL, 100, 0, 1, NULL, '2025-08-20 13:38:26', '2025-07-21 05:38:26'),
(2, 'SAVE50', 'Save $50 on orders over $200', 'fixed', 50.00, 200.00, NULL, 50, 0, 1, NULL, '2025-08-05 13:38:26', '2025-07-21 05:38:26'),
(3, 'TECH20', '20% off on all tech products', 'percentage', 20.00, 100.00, NULL, 200, 0, 1, NULL, '2025-07-28 13:38:26', '2025-07-21 05:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `to_email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `attempt_time`) VALUES
(18, 'elishatpp-sm23@student.tarc.edu.my', '::1', '2025-07-25 02:59:51'),
(19, 'elishatpp-sm23@student.tarc.edu.my', '::1', '2025-07-25 03:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `short_description`, `category_id`, `price`, `discount_percentage`, `stock_quantity`, `low_stock_threshold`, `sku`, `main_image`, `is_featured`, `is_active`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 15 Pro', 'The latest iPhone with advanced camera system and A17 Pro chip', 'Latest iPhone with pro features', 1, 3000.00, 5, 50, 10, 'IP15PRO001', 'iphone-15-pro.png', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:50:51'),
(2, 'Samsung Galaxy S24', 'Flagship Android smartphone with AI features', 'Premium Android smartphone', 1, 3699.00, 10, 30, 10, 'SGS24001', 'galaxy-s24.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:51:19'),
(3, 'MacBook Pro 14\"', 'Powerful laptop with M3 chip for professionals', 'Professional laptop with M3 chip', 2, 1999.00, 0, 25, 10, 'MBP14M3001', 'macbook-pro-14.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(4, 'Dell XPS 13', 'Ultra-portable laptop with premium design', 'Premium ultrabook', 2, 6699.00, 15, 20, 10, 'DXPS13001', 'dell-xps-13.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:52:07'),
(5, 'iPad Air', 'Versatile tablet for work and creativity', 'Powerful and versatile tablet', 3, 3299.00, 8, 40, 10, 'IPADAIR001', 'ipad-air.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:52:40'),
(6, 'Sony WH-1000XM5', 'Premium noise-canceling headphones', 'Industry-leading noise cancellation', 4, 1799.00, 20, 60, 10, 'SWXM5001', 'sony-wh1000xm5.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:51:47'),
(7, 'AirPods Pro 2', 'Advanced wireless earbuds with ANC', 'Premium wireless earbuds', 4, 829.00, 12, 80, 10, 'APP2001', 'airpods-pro-2.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:53:13'),
(8, 'PlayStation 5', 'Next-gen gaming console', 'Latest gaming console from Sony', 5, 3849.00, 0, 15, 10, 'PS5001', 'playstation-5.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:53:43'),
(9, 'Apple Watch Series 9', 'Advanced smartwatch with health features', 'Latest Apple smartwatch', 6, 999.00, 10, 35, 10, 'AWS9001', 'apple-watch-s9.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-24 05:54:17'),
(10, 'Google Nest Hub', 'Smart display for your home', 'Smart home control center', 8, 99.00, 25, 50, 10, 'GNH001', 'nest-hub.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(11, 'Xiaomi 14 Pro', 'Flagship Xiaomi smartphone with Leica camera and Snapdragon 8 Gen 3', 'Flagship Android with Leica camera', 1, 3799.00, 5, 40, 10, 'XM14PRO001', 'xiaomi-14-pro.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(12, 'Realme 12 Pro+', 'Affordable mid-range phone with 200MP camera', 'Mid-range phone with flagship camera', 1, 1499.00, 10, 70, 10, 'RM12P001', 'realme-12-pro-plus.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(13, 'Huawei P60 Pro', 'Powerful photography smartphone with ultra-light sensor', 'Flagship Huawei with pro camera', 1, 4299.00, 15, 25, 10, 'HWP60PRO001', 'huawei-p60-pro.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(14, 'ASUS ROG Zephyrus G14', 'Gaming laptop with AMD Ryzen 9 and RTX 4060 GPU', 'Powerful gaming laptop', 2, 6599.00, 5, 15, 10, 'ASUSG14001', 'rog-zephyrus-g14.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(15, 'Acer Swift X', 'Slim laptop with NVIDIA GPU for creators', 'Portable laptop for creators', 2, 3999.00, 10, 18, 10, 'ACSWX001', 'acer-swift-x.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(16, 'HP Spectre x360', '2-in-1 convertible laptop with OLED display', 'Convertible laptop with touch screen', 2, 5499.00, 8, 12, 10, 'HPSX360001', 'hp-spectre-x360.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(17, 'Samsung Galaxy Tab S9', 'Premium Android tablet with AMOLED display', 'High-performance Android tablet', 3, 3899.00, 12, 22, 10, 'SGTS9001', 'galaxy-tab-s9.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(18, 'Lenovo Tab P12 Pro', 'Tablet for entertainment and productivity', 'All-in-one Android tablet', 3, 2999.00, 15, 30, 10, 'LTP12P001', 'lenovo-tab-p12-pro.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(19, 'JBL Charge 5', 'Portable Bluetooth speaker with powerful sound', 'Loud portable speaker', 4, 799.00, 20, 45, 10, 'JBLCHG5001', 'jbl-charge-5.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(20, 'Anker Soundcore Life Q30', 'Budget noise-canceling headphones with long battery life', 'Affordable ANC headphones', 4, 349.00, 25, 60, 10, 'ANKQ3001', 'soundcore-life-q30.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(21, 'Nintendo Switch OLED', 'Hybrid console with improved display and battery life', 'Portable and docked gaming console', 5, 1599.00, 5, 18, 10, 'NSWOLED001', 'switch-oled.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(22, 'Razer Kishi V2', 'Mobile gaming controller for Android and iPhone', 'Compact mobile gaming controller', 5, 399.00, 10, 50, 10, 'RZKISHI001', 'razer-kishi-v2.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(23, 'Logitech G502 X', 'Advanced gaming mouse with adjustable DPI', 'Precision gaming mouse', 5, 299.00, 15, 30, 10, 'LTG502X001', 'logitech-g502x.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(24, 'Xiaomi Smart Band 8', 'Fitness tracker with AMOLED screen and multiple sports modes', 'Affordable fitness tracker', 6, 199.00, 12, 75, 10, 'XMSB8001', 'xiaomi-band-8.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(25, 'Garmin Venu Sq 2', 'Smartwatch with GPS and fitness tracking features', 'Fitness smartwatch with GPS', 6, 1199.00, 10, 28, 10, 'GRMVNSQ2001', 'garmin-venu-sq2.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(26, 'Anker PowerCore 10000mAh', 'Portable power bank with fast charging', 'Fast-charging power bank', 7, 139.00, 10, 100, 10, 'ANKPWR10000', 'anker-powercore.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-26 12:01:13'),
(27, 'Baseus USB-C Hub 6-in-1', 'Multi-port USB-C hub for laptops', 'Essential USB-C accessory', 7, 179.00, 15, 35, 10, 'BASUSBC001', 'baseus-usb-c-hub.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(28, 'Ugreen Fast Charging Cable (1.5m)', 'Durable cable with fast data transfer', 'Reliable fast-charging cable', 7, 39.00, 20, 80, 10, 'UGRCBL001', 'ugreen-cable.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(29, 'Xiaomi Smart Air Purifier 4', 'Air purifier with smart app control and HEPA filter', 'Smart air purifier for home', 8, 599.00, 15, 30, 10, 'XMPRF4001', 'xiaomi-air-purifier.jpg', 1, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(30, 'TP-Link Tapo C200', 'Smart Wi-Fi security camera with pan and tilt', 'Affordable smart home camera', 8, 139.00, 20, 45, 10, 'TPLTPC2001', 'tapo-c200.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26'),
(31, 'Philips Hue Smart Bulb', 'Customizable smart bulb with color control', 'Smart LED bulb for home', 8, 109.00, 12, 55, 10, 'PHSHB001', 'philips-hue.jpg', 0, 1, NULL, NULL, '2025-07-21 05:38:26', '2025-07-21 05:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `role`, `profile_photo`, `is_active`, `is_verified`, `verification_token`, `remember_token`, `password_reset_token`, `password_reset_expires`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@gadgetloop.com', '$2y$10$l6QBisIZAupNWdXebQvf/Oc7pR.TMSj2VXfabuVPJALOdcerUcVKC', NULL, 'admin', NULL, 1, 1, NULL, NULL, NULL, NULL, '2025-07-21 05:38:26', '2025-07-22 11:40:24'),
(2, 'Demo', 'Member', 'member@gadgetloop.com', '$2y$10$439pdBXMvGGaHDzzKG1VCeT/cd/sjCAqlKvAJFXalUkiS5SIlZ3Nu', NULL, 'member', '687f336e7633e.jpeg', 1, 1, NULL, NULL, NULL, NULL, '2025-07-21 05:38:26', '2025-07-22 06:45:02'),
(10, 'Elisha', 'Tiong', 'tiongepp@gmail.com', '$2y$10$fjTkJt10ZNKr2J5te.oT6uNZx6Ew7Zy.eCzqPf41ejA02/I5hz6rO', '', 'member', '6882e699ad478.jpg', 1, 0, NULL, NULL, NULL, NULL, '2025-07-24 04:58:08', '2025-07-25 02:08:46'),
(12, 'Elisha', 'Tiong', 'elishatpp-sm23@student.tarc.edu.my', '$2y$10$vjDsM1JiXT2tD1UmWz1jeO.HF1jge0kwjW1vqlMNuP89UeRP/3Xma', '', 'member', NULL, 1, 0, NULL, NULL, NULL, NULL, '2025-07-25 07:15:24', '2025-07-25 07:15:24');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 2, 1, '2025-07-22 06:44:43'),
(2, 10, 6, '2025-07-24 05:28:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_cart_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_time` (`email`,`attempt_time`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_featured` (`is_featured`),
  ADD KEY `idx_products_active` (`is_active`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product_order` (`user_id`,`product_id`,`order_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_reviews_product` (`product_id`),
  ADD KEY `idx_reviews_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_wishlist_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
