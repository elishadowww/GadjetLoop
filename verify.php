<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle GET request with token
$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if (!$token) {
    $message = 'Invalid verification link.';
} else {
    // Find user by token
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['is_verified']) {
            $message = 'Your account is already verified.';
            $success = true;
        } else {
            // Update is_verified
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                $message = 'Your email has been verified! You can now log in.';
                $success = true;
            } else {
                $message = 'Verification failed. Please try again.';
            }
        }
    } else {
        $message = 'Invalid or expired verification link.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body data-page="verify">
    <?php include 'includes/header.php'; ?>
    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Email Verification</h1>
                    </div>
                    <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <?php if ($success): ?>
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
