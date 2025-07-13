-- GadgetLoop Database Schema
-- Run this SQL to create the database structure

CREATE DATABASE IF NOT EXISTS gadgetloop;
USE gadgetloop;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'member') DEFAULT 'member',
    profile_photo VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    remember_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    discount_percentage INT DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    sku VARCHAR(100) UNIQUE,
    main_image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    meta_title VARCHAR(200),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product images table
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address JSON,
    billing_address JSON,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_reference VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_product_order (user_id, product_id, order_id)
);

-- Coupons table
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(200),
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2),
    usage_limit INT,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    starts_at DATETIME,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupon usage table
CREATE TABLE coupon_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Login attempts table (for security)
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempt_time)
);

-- Admin activity log
CREATE TABLE admin_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Email queue table
CREATE TABLE email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL
);

-- Insert default admin user
INSERT INTO users (first_name, last_name, email, password, role, is_active, is_verified) VALUES
('Admin', 'User', 'admin@gadgetloop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE, TRUE),
('Demo', 'Member', 'member@gadgetloop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', TRUE, TRUE);

-- Insert sample categories
INSERT INTO categories (name, description, image, sort_order) VALUES
('Smartphones', 'Latest smartphones and mobile devices', 'smartphones.jpg', 1),
('Laptops', 'Laptops and notebooks for work and gaming', 'laptops.jpg', 2),
('Tablets', 'Tablets and e-readers', 'tablets.jpg', 3),
('Audio', 'Headphones, speakers, and audio accessories', 'audio.jpg', 4),
('Gaming', 'Gaming consoles, accessories, and peripherals', 'gaming.jpg', 5),
('Wearables', 'Smartwatches and fitness trackers', 'wearables.jpg', 6),
('Accessories', 'Phone cases, chargers, and other accessories', 'accessories.jpg', 7),
('Smart Home', 'Smart home devices and IoT products', 'smart-home.jpg', 8);

-- Insert sample products
INSERT INTO products (name, description, short_description, category_id, price, discount_percentage, stock_quantity, sku, main_image, is_featured) VALUES
('iPhone 15 Pro', 'The latest iPhone with advanced camera system and A17 Pro chip', 'Latest iPhone with pro features', 1, 999.00, 5, 50, 'IP15PRO001', 'iphone-15-pro.jpg', TRUE),
('Samsung Galaxy S24', 'Flagship Android smartphone with AI features', 'Premium Android smartphone', 1, 899.00, 10, 30, 'SGS24001', 'galaxy-s24.jpg', TRUE),
('MacBook Pro 14"', 'Powerful laptop with M3 chip for professionals', 'Professional laptop with M3 chip', 2, 1999.00, 0, 25, 'MBP14M3001', 'macbook-pro-14.jpg', TRUE),
('Dell XPS 13', 'Ultra-portable laptop with premium design', 'Premium ultrabook', 2, 1299.00, 15, 20, 'DXPS13001', 'dell-xps-13.jpg', FALSE),
('iPad Air', 'Versatile tablet for work and creativity', 'Powerful and versatile tablet', 3, 599.00, 8, 40, 'IPADAIR001', 'ipad-air.jpg', TRUE),
('Sony WH-1000XM5', 'Premium noise-canceling headphones', 'Industry-leading noise cancellation', 4, 399.00, 20, 60, 'SWXM5001', 'sony-wh1000xm5.jpg', TRUE),
('AirPods Pro 2', 'Advanced wireless earbuds with ANC', 'Premium wireless earbuds', 4, 249.00, 12, 80, 'APP2001', 'airpods-pro-2.jpg', FALSE),
('PlayStation 5', 'Next-gen gaming console', 'Latest gaming console from Sony', 5, 499.00, 0, 15, 'PS5001', 'playstation-5.jpg', TRUE),
('Apple Watch Series 9', 'Advanced smartwatch with health features', 'Latest Apple smartwatch', 6, 399.00, 10, 35, 'AWS9001', 'apple-watch-s9.jpg', TRUE),
('Google Nest Hub', 'Smart display for your home', 'Smart home control center', 8, 99.00, 25, 50, 'GNH001', 'nest-hub.jpg', FALSE);
('Xiaomi 14 Pro', 'Flagship Xiaomi smartphone with Leica camera and Snapdragon 8 Gen 3', 'Flagship Android with Leica camera', 1, 3799.00, 5, 40, 'XM14PRO001', 'xiaomi-14-pro.jpg', TRUE),
('Realme 12 Pro+', 'Affordable mid-range phone with 200MP camera', 'Mid-range phone with flagship camera', 1, 1499.00, 10, 70, 'RM12P001', 'realme-12-pro-plus.jpg', FALSE),
('Huawei P60 Pro', 'Powerful photography smartphone with ultra-light sensor', 'Flagship Huawei with pro camera', 1, 4299.00, 15, 25, 'HWP60PRO001', 'huawei-p60-pro.jpg', TRUE),
('ASUS ROG Zephyrus G14', 'Gaming laptop with AMD Ryzen 9 and RTX 4060 GPU', 'Powerful gaming laptop', 2, 6599.00, 5, 15, 'ASUSG14001', 'rog-zephyrus-g14.jpg', TRUE),
('Acer Swift X', 'Slim laptop with NVIDIA GPU for creators', 'Portable laptop for creators', 2, 3999.00, 10, 18, 'ACSWX001', 'acer-swift-x.jpg', FALSE),
('HP Spectre x360', '2-in-1 convertible laptop with OLED display', 'Convertible laptop with touch screen', 2, 5499.00, 8, 12, 'HPSX360001', 'hp-spectre-x360.jpg', TRUE),
('Samsung Galaxy Tab S9', 'Premium Android tablet with AMOLED display', 'High-performance Android tablet', 3, 3899.00, 12, 22, 'SGTS9001', 'galaxy-tab-s9.jpg', TRUE),
('Lenovo Tab P12 Pro', 'Tablet for entertainment and productivity', 'All-in-one Android tablet', 3, 2999.00, 15, 30, 'LTP12P001', 'lenovo-tab-p12-pro.jpg', FALSE),
('JBL Charge 5', 'Portable Bluetooth speaker with powerful sound', 'Loud portable speaker', 4, 799.00, 20, 45, 'JBLCHG5001', 'jbl-charge-5.jpg', FALSE),
('Anker Soundcore Life Q30', 'Budget noise-canceling headphones with long battery life', 'Affordable ANC headphones', 4, 349.00, 25, 60, 'ANKQ3001', 'soundcore-life-q30.jpg', FALSE),
('Nintendo Switch OLED', 'Hybrid console with improved display and battery life', 'Portable and docked gaming console', 5, 1599.00, 5, 18, 'NSWOLED001', 'switch-oled.jpg', TRUE),
('Razer Kishi V2', 'Mobile gaming controller for Android and iPhone', 'Compact mobile gaming controller', 5, 399.00, 10, 50, 'RZKISHI001', 'razer-kishi-v2.jpg', FALSE),
('Logitech G502 X', 'Advanced gaming mouse with adjustable DPI', 'Precision gaming mouse', 5, 299.00, 15, 30, 'LTG502X001', 'logitech-g502x.jpg', FALSE),
('Xiaomi Smart Band 8', 'Fitness tracker with AMOLED screen and multiple sports modes', 'Affordable fitness tracker', 6, 199.00, 12, 75, 'XMSB8001', 'xiaomi-band-8.jpg', FALSE),
('Garmin Venu Sq 2', 'Smartwatch with GPS and fitness tracking features', 'Fitness smartwatch with GPS', 6, 1199.00, 10, 28, 'GRMVNSQ2001', 'garmin-venu-sq2.jpg', FALSE),
('Anker PowerCore 10000mAh', 'Portable power bank with fast charging', 'Fast-charging power bank', 7, 139.00, 10, 100, 'ANKPWR10000', 'anker-powercore.jpg', FALSE),
('Baseus USB-C Hub 6-in-1', 'Multi-port USB-C hub for laptops', 'Essential USB-C accessory', 7, 179.00, 15, 35, 'BASUSBC001', 'baseus-usb-c-hub.jpg', FALSE),
('Ugreen Fast Charging Cable (1.5m)', 'Durable cable with fast data transfer', 'Reliable fast-charging cable', 7, 39.00, 20, 80, 'UGRCBL001', 'ugreen-cable.jpg', FALSE),
('Xiaomi Smart Air Purifier 4', 'Air purifier with smart app control and HEPA filter', 'Smart air purifier for home', 8, 599.00, 15, 30, 'XMPRF4001', 'xiaomi-air-purifier.jpg', TRUE),
('TP-Link Tapo C200', 'Smart Wi-Fi security camera with pan and tilt', 'Affordable smart home camera', 8, 139.00, 20, 45, 'TPLTPC2001', 'tapo-c200.jpg', FALSE),
('Philips Hue Smart Bulb', 'Customizable smart bulb with color control', 'Smart LED bulb for home', 8, 109.00, 12, 55, 'PHSHB001', 'philips-hue.jpg', FALSE);

-- Insert sample coupons
INSERT INTO coupons (code, description, discount_type, discount_value, minimum_amount, usage_limit, is_active, expires_at) VALUES
('WELCOME10', 'Welcome discount for new customers', 'percentage', 10.00, 50.00, 100, TRUE, DATE_ADD(NOW(), INTERVAL 30 DAY)),
('SAVE50', 'Save $50 on orders over $200', 'fixed', 50.00, 200.00, 50, TRUE, DATE_ADD(NOW(), INTERVAL 15 DAY)),
('TECH20', '20% off on all tech products', 'percentage', 20.00, 100.00, 200, TRUE, DATE_ADD(NOW(), INTERVAL 7 DAY));

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_reviews_product ON reviews(product_id);
CREATE INDEX idx_reviews_user ON reviews(user_id);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_wishlist_user ON wishlist(user_id);