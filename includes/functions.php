<?php
require_once 'config.php';

// User Authentication Functions
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function loginUser($pdo, $email, $password) {
    // Check login attempts
    $attempts = getLoginAttempts($pdo, $email);
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        return ['success' => false, 'message' => 'Account temporarily locked due to too many failed attempts'];
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();


    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Clear login attempts
        clearLoginAttempts($pdo, $email);
        
        return ['success' => true, 'user' => $user];
    } else {
        // Record failed attempt
        recordLoginAttempt($pdo, $email);
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
}

function getLoginAttempts($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$email, LOGIN_BLOCK_TIME]);
    return $stmt->fetchColumn();
}

function recordLoginAttempt($pdo, $email) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, NOW())");
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR']]);
}

function clearLoginAttempts($pdo, $email) {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
}

// Product Functions
function getProducts($pdo, $filters = [], $page = 1, $per_page = 12) {
    $where_conditions = [];
    $params = [];
    $offset = ($page - 1) * $per_page; // <-- Add this line

    $sql = "SELECT p.*, c.name as category_name, 
            AVG(r.rating) as average_rating, 
            COUNT(r.id) as review_count,
            CASE WHEN p.discount_percentage > 0 
                 THEN p.price * (1 - p.discount_percentage / 100) 
                 ELSE p.price END as sale_price
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN reviews r ON p.id = r.product_id 
            WHERE p.is_active = 1";

    if (!empty($filters['category'])) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['search'])) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['min_price'])) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $filters['min_price'];
    }

    if (!empty($filters['max_price'])) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $filters['max_price'];
    }

    if (!empty($where_conditions)) {
        $sql .= " AND " . implode(" AND ", $where_conditions);
    }

    $sql .= " GROUP BY p.id";

    // Sorting
    $sort_options = [
        'name_asc' => 'p.name ASC',
        'name_desc' => 'p.name DESC',
        'price_asc' => 'sale_price ASC',
        'price_desc' => 'sale_price DESC',
        'rating' => 'average_rating DESC',
        'newest' => 'p.created_at DESC'
    ];

    $sort = $filters['sort'] ?? 'newest';
    if (isset($sort_options[$sort])) {
        $sql .= " ORDER BY " . $sort_options[$sort];
    }

    // Pagination
    $offset = ($page - 1) * $per_page;
   $sql .= " LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductById($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name,
        AVG(r.rating) as average_rating,
        COUNT(r.id) as review_count,
        CASE WHEN p.discount_percentage > 0 
             THEN p.price * (1 - p.discount_percentage / 100) 
             ELSE p.price END as sale_price
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN reviews r ON p.id = r.product_id 
        WHERE p.id = ? AND p.is_active = 1
        GROUP BY p.id
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

function getFeaturedProducts($pdo, $limit = 8) {
    $sql = "
        SELECT p.*, c.name as category_name,
        AVG(r.rating) as average_rating,
        COUNT(r.id) as review_count,
        CASE WHEN p.discount_percentage > 0 
             THEN p.price * (1 - p.discount_percentage / 100) 
             ELSE p.price END as sale_price
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN reviews r ON p.id = r.product_id 
        WHERE p.is_featured = 1 AND p.is_active = 1
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT " . (int)$limit . "
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getProductImages($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

// Cart Functions
function addToCart($pdo, $user_id, $product_id, $quantity = 1) {
    // Check if product exists and has stock
    $product = getProductById($pdo, $product_id);
    if (!$product || $product['stock_quantity'] < $quantity) {
        return false;
    }

    // Check if item already in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $new_quantity = $existing['quantity'] + $quantity;
        if ($new_quantity > $product['stock_quantity']) {
            return false;
        }
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_quantity, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    return true;
}

function getCartItems($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.main_image, p.stock_quantity,
        p.discount_percentage,
        CASE WHEN p.discount_percentage > 0 
             THEN p.price * (1 - p.discount_percentage / 100) 
             ELSE p.price END as sale_price
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.is_active = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function updateCartQuantity($pdo, $user_id, $product_id, $quantity) {
    if ($quantity <= 0) {
        return removeFromCart($pdo, $user_id, $product_id);
    }

    $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$quantity, $user_id, $product_id]);
}

function removeFromCart($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

function clearCart($pdo, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// Wishlist Functions
function addToWishlist($pdo, $user_id, $product_id) {
    try {
        // Check if already in wishlist
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetch()) {
            return false; // Already in wishlist
        }
        
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$user_id, $product_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function removeFromWishlist($pdo, $user_id, $product_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$user_id, $product_id]);
    } catch (PDOException $e) {
        return false;
    }
}

function isInWishlist($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch() !== false;
}

function getWishlistItems($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT w.*, p.name, p.price, p.main_image, p.discount_percentage, p.stock_quantity,
        CASE WHEN p.discount_percentage > 0 
             THEN p.price * (1 - p.discount_percentage / 100) 
             ELSE p.price END as sale_price
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? AND p.is_active = 1
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Order Functions
function createOrder($pdo, $user_id, $cart_items, $shipping_address, $payment_method = 'fake') {
    try {
        $pdo->beginTransaction();

        // Calculate totals
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['sale_price'] * $item['quantity'];
        }
        $tax = $subtotal * 0.08; // 8% tax
        $shipping = $subtotal > 50 ? 0 : 9.99; // Free shipping over $50
        $total = $subtotal + $tax + $shipping;

        // Ensure payment_method is a string
        if (is_array($payment_method)) {
            $payment_method = isset($payment_method['method']) ? $payment_method['method'] : 'unknown';
        }

        // Allow status override (default 'pending')
        $order_status = isset($GLOBALS['order_status']) ? $GLOBALS['order_status'] : 'pending';

        // Create order
        $order_number = 'GL' . date('Ymd') . rand(1000, 9999);
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, subtotal, tax_amount, shipping_amount, total_amount, 
                               shipping_address, payment_method, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $order_number, $subtotal, $tax, $shipping, $total, 
                       json_encode($shipping_address), $payment_method, $order_status]);

        $order_id = $pdo->lastInsertId();

        // Create order items
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], 
                           $item['sale_price'], $item['sale_price'] * $item['quantity']]);

            // Update product stock
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // Clear cart
        clearCart($pdo, $user_id);

        $pdo->commit();
        return ['success' => true, 'order_id' => $order_id, 'order_number' => $order_number];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



function getOrdersByUser($pdo, $user_id, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT :per_page OFFSET :offset
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', (int)$per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getOrderById($pdo, $order_id, $user_id = null) {
    $sql = "SELECT * FROM orders WHERE id = ?";
    $params = [$order_id];
    
    if ($user_id) {
        $sql .= " AND user_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function getOrderItems($pdo, $order_id) {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.main_image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Utility Functions
function generateStarRating($rating, $max_stars = 5) {
    $rating = round($rating * 2) / 2; // Round to nearest 0.5
    $stars = '';
    
    for ($i = 1; $i <= $max_stars; $i++) {
        if ($i <= $rating) {
            $stars .= '<span class="star filled">★</span>';
        } elseif ($i - 0.5 <= $rating) {
            $stars .= '<span class="star half">★</span>';
        } else {
            $stars .= '<span class="star empty">☆</span>';
        }
    }
    
    return $stars;
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

    // Notification Functions
    function createNotification($pdo, $user_id, $title, $message, $type = 'general') {
        // Only columns: user_id, message, is_read, created_at
        // Combine title and type into message if needed
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $full_message = $title;
        if (!empty($message)) {
            $full_message .= ': ' . $message;
        }
        if (!empty($type)) {
            $full_message .= ' [' . $type . ']';
        }
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
        return $stmt->execute([$user_id, $full_message]);
    }
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}


require_once __DIR__ . '/sendEmail.php';

function uploadFile($file, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Ensure the upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Upload failed'];
    }
}

function generateQRCode($data) {
    // This would typically use a QR code library
    // For demo purposes, we'll return a placeholder
    return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($data);
}


function getDefaultAddress($pdo, $user_id, $type = 'shipping') {
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND type = ? AND is_default = 1 LIMIT 1");
    $stmt->execute([$user_id, $type]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>