<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to apply coupon']);
    exit;
}

if ($_POST) {
    $coupon_code = strtoupper(sanitizeInput($_POST['coupon_code'] ?? ''));
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (empty($coupon_code) || $subtotal <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid coupon code or order amount']);
        exit;
    }
    
    try {
        // Get coupon details
        $stmt = $pdo->prepare("
            SELECT * FROM coupons 
            WHERE code = ? AND is_active = 1 
            AND (starts_at IS NULL OR starts_at <= NOW()) 
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$coupon_code]);
        $coupon = $stmt->fetch();
        
        if (!$coupon) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
            exit;
        }
        
        // Check minimum amount
        if ($subtotal < $coupon['minimum_amount']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Minimum order amount of $' . number_format($coupon['minimum_amount'], 2) . ' required'
            ]);
            exit;
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
            echo json_encode(['success' => false, 'message' => 'Coupon usage limit reached']);
            exit;
        }
        
        // Check if user already used this coupon
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
        $stmt->execute([$coupon['id'], $user_id]);
        $user_usage = $stmt->fetchColumn();
        
        if ($user_usage > 0) {
            echo json_encode(['success' => false, 'message' => 'You have already used this coupon']);
            exit;
        }
        
        // Calculate discount
        $discount_amount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount_amount = ($subtotal * $coupon['discount_value']) / 100;
            if ($coupon['maximum_discount'] && $discount_amount > $coupon['maximum_discount']) {
                $discount_amount = $coupon['maximum_discount'];
            }
        } else {
            $discount_amount = $coupon['discount_value'];
        }
        
        // Ensure discount doesn't exceed subtotal
        $discount_amount = min($discount_amount, $subtotal);
        
        // Calculate new total (subtotal - discount + tax + shipping)
        $tax = ($subtotal - $discount_amount) * 0.08; // 8% tax on discounted amount
        $shipping = ($subtotal - $discount_amount) > 50 ? 0 : 9.99; // Free shipping over $50 after discount
        $new_total = $subtotal - $discount_amount + $tax + $shipping;
        
        // Store coupon in session for order processing
        $_SESSION['applied_coupon'] = [
            'id' => $coupon['id'],
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value'],
            'discount_amount' => $discount_amount
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount_amount' => $discount_amount,
            'discount_type' => $coupon['discount_type'],
            'new_total' => $new_total,
            'new_tax' => $tax,
            'new_shipping' => $shipping
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to apply coupon']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>