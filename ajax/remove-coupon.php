<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove coupon']);
    exit;
}

try {
    // Remove coupon from session
    unset($_SESSION['applied_coupon']);
    
    echo json_encode(['success' => true, 'message' => 'Coupon removed successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to remove coupon']);
}
?>