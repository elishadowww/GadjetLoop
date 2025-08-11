<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get unread notification count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetchColumn();
    
    // Get total notification count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_count = $stmt->fetchColumn();
    
    // Check for new notifications (created in last 30 seconds)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)");
    $stmt->execute([$user_id]);
    $new_count = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'total_unread' => $unread_count,
        'total_count' => $total_count,
        'new_count' => $new_count
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>