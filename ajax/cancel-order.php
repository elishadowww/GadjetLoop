<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to cancel order']);
    exit;
}

if ($_POST) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit;
    }
    
    try {
        // Check if order belongs to user and can be cancelled
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found or cannot be cancelled']);
            exit;
        }
        
        // Get order items to restore stock
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Restore stock for each item
        foreach ($order_items as $item) {
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Create notification
        createNotification($pdo, $user_id, 'Order Cancelled', 
            'Your order #' . $order['order_number'] . ' has been cancelled successfully.', 'order');
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>