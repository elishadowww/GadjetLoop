<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_POST) {
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    try {
        // Check if product has orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $order_count = $stmt->fetchColumn();
        
        if ($order_count > 0) {
            // Don't actually delete, just deactivate
            $stmt = $pdo->prepare("UPDATE products SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Product deactivated (has existing orders)']);
        } else {
            // Get product images to delete files
            $stmt = $pdo->prepare("SELECT main_image FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $additional_images = $stmt->fetchAll();
            
            // Delete product from database
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            
            // Delete image files
            if ($product['main_image'] && file_exists('../uploads/products/' . $product['main_image'])) {
                unlink('../uploads/products/' . $product['main_image']);
            }
            
            foreach ($additional_images as $image) {
                if (file_exists('../uploads/products/' . $image['image_path'])) {
                    unlink('../uploads/products/' . $image['image_path']);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>