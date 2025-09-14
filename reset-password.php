<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    // Find the reset request
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if (!$reset) {
        $error = 'This password reset link is invalid or has expired.';
    } elseif ($_POST && isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please enter and confirm your new password.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Update user password
            $hashed = hashPassword($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashed, $reset['user_id']]);
            // Delete all reset tokens for this user
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$reset['user_id']]);
            $success = 'Your password has been reset. You can now <a href=\"login.php\">login</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body data-page="reset-password">
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="auth-container">
            <h1>Reset Password</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!$success && empty($error)): ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
