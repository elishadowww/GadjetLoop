<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gadgetloop');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'GadgetLoop');
define('SITE_URL', 'http://localhost/GadgetLoop');
define('UPLOAD_PATH', 'uploads/');
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOGIN_BLOCK_TIME', 900); // 15 minutes

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// Payment configuration
define('PAYMENT_GATEWAY', 'fake'); // For demo purposes

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>