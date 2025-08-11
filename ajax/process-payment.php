<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to process payment']);
    exit;
}

if ($_POST) {
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $order_id = intval($_POST['order_id'] ?? 0);
    
    if (empty($payment_method) || $amount <= 0 || $order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid payment data']);
        exit;
    }
    
    try {
        // For demo purposes, we'll simulate payment processing
        $payment_reference = 'PAY_' . time() . '_' . rand(1000, 9999);
        
        // Simulate payment processing delay
        sleep(1);
        
        // Update order payment status
        $stmt = $pdo->prepare("
            UPDATE orders SET 
            payment_status = 'paid', 
            payment_reference = ?,
            updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$payment_reference, $order_id]);
        
        // Create payment notification
        $user_id = $_SESSION['user_id'];
        createNotification($pdo, $user_id, 'Payment Successful', 
            "Your payment of $" . number_format($amount, 2) . " has been processed successfully.", 'payment');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment processed successfully',
            'payment_reference' => $payment_reference
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Payment processing failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>