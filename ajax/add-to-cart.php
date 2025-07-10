<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

if ($_POST) {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $user_id = $_SESSION['user_id'];
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        exit;
    }
    
    // Check if product exists and has stock
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }
    
    try {
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $new_quantity = $existing['quantity'] + $quantity;
            if ($new_quantity > $product['stock_quantity']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_quantity, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Item added to cart successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>