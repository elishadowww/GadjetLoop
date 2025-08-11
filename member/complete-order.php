<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if we have pending order data
if (!isset($_SESSION['pending_order']) || !$_POST) {
    header('Location: cart.php');
    exit;
}

$pending_order = $_SESSION['pending_order'];
$error = '';

// Validate required fields
$shipping_address = [
    'first_name' => sanitizeInput($_POST['shipping_first_name']),
    'last_name' => sanitizeInput($_POST['shipping_last_name']),
    'address' => sanitizeInput($_POST['shipping_address']),
    'city' => sanitizeInput($_POST['shipping_city']),
    'state' => sanitizeInput($_POST['shipping_state']),
    'zip_code' => sanitizeInput($_POST['shipping_zip']),
    'country' => sanitizeInput($_POST['shipping_country']),
    'phone' => sanitizeInput($_POST['shipping_phone'])
];

$billing_address = $shipping_address;
if (isset($_POST['different_billing'])) {
    $billing_address = [
        'first_name' => sanitizeInput($_POST['billing_first_name']),
        'last_name' => sanitizeInput($_POST['billing_last_name']),
        'address' => sanitizeInput($_POST['billing_address']),
        'city' => sanitizeInput($_POST['billing_city']),
        'state' => sanitizeInput($_POST['billing_state']),
        'zip_code' => sanitizeInput($_POST['billing_zip']),
        'country' => sanitizeInput($_POST['billing_country']),
        'phone' => sanitizeInput($_POST['billing_phone'])
    ];
}

$payment_method = sanitizeInput($_POST['payment_method']);

// Validate required fields
if (empty($shipping_address['first_name']) || empty($shipping_address['last_name']) || 
    empty($shipping_address['address']) || empty($shipping_address['city']) || 
    empty($payment_method)) {
    $error = 'Please fill in all required fields';
} else {
    // Create order
    $order_result = createOrder($pdo, $user_id, $pending_order['cart_items'], $shipping_address, $billing_address, $payment_method);
    
    if ($order_result['success']) {
        // Clear pending order from session
        unset($_SESSION['pending_order']);
        
        // Create notification for order confirmation
        createNotification($pdo, $user_id, 'Order Confirmed', 
            'Your order #' . $order_result['order_number'] . ' has been confirmed and is being processed.', 'order');
        
        // Create payment notification
        createNotification($pdo, $user_id, 'Payment Successful', 
            'Your payment of $' . number_format($pending_order['totals']['total'], 2) . ' has been processed successfully.', 'payment');
        
        // Redirect to order confirmation
        header('Location: order-confirmation.php?order=' . $order_result['order_number']);
        exit;
    } else {
        $error = $order_result['message'];
        // Redirect back to checkout with error
        $_SESSION['checkout_error'] = $error;
        header('Location: checkout.php');
        exit;
    }
}

// If we get here, there was an error
$_SESSION['checkout_error'] = $error ?: 'An error occurred during order processing';
header('Location: checkout.php');
exit;
?>