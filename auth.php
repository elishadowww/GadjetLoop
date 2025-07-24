<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['token'])) {
    http_response_code(400);
    echo "Token missing";
    exit;
}

$token = $input['token'];

try {
    // Initialize Firebase
    $factory = (new Factory)->withServiceAccount(__DIR__ . '/includes/firebase-service-account.json');
    $auth = $factory->createAuth();

    // Verify ID token
    $verifiedIdToken = $auth->verifyIdToken($token);
    $uid = $verifiedIdToken->claims()->get('sub');
    $firebaseUser = $auth->getUser($uid);
    $email = $firebaseUser->email;

    // Connect to your DB
    require_once 'includes/config.php';
    session_start();

    // Check if user exists in your database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        echo "Login successful";
    } else {
        // You can auto-register user if needed
        echo "User not found in database. Please register.";
    }

} catch (Exception $e) {
    http_response_code(401);
    echo "Invalid token: " . $e->getMessage();
}
