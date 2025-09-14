<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_POST && isset($_POST['submit_forgot'])) {
    $email = sanitizeInput($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);
            // Send email
            $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/GadjetLoop/reset-password.php?token=' . $token;
            $subject = 'Password Reset Request';
            $message = "Hello,\n\nTo reset your password, click the link below:\n$reset_link\n\nThis link will expire in 1 hour.";
            sendEmail($user['email'], $subject, $message);
            $success = 'A password reset link has been sent to your email.';
        } else {
            $error = 'No account found with that email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body data-page="forgot-password">
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="auth-container">
            <h1>Forgot Password</h1>
            <p>Enter your email address to receive a password reset link.</p>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <button type="submit" name="submit_forgot" class="btn btn-primary">Send Reset Link</button>
            </form>
            <div style="margin-top:2rem;">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
