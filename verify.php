<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit;
}

// Get email from POST
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

// Update is_verified in database
$stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
if ($stmt->execute([$email])) {
    echo json_encode(['status' => 'success', 'message' => 'User verified']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
