<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $agree_terms = isset($_POST['agree_terms']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!$agree_terms) {
        $error = 'Please agree to the terms and conditions';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists';
        } else {
            // Create user account
            $hashed_password = hashPassword($password);
            $verification_token = generateToken();
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, password, phone, 
                                     verification_token, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password, 
                               $phone, $verification_token]);
                
                $user_id = $pdo->lastInsertId();
                
                // Send verification email (in production)
                $verification_link = SITE_URL . "/verify-email.php?token=" . $verification_token;
                $email_subject = "Verify your GadgetLoop account";
                $email_message = "
                    <h2>Welcome to GadgetLoop!</h2>
                    <p>Thank you for registering. Please click the link below to verify your email address:</p>
                    <p><a href='{$verification_link}'>Verify Email Address</a></p>
                    <p>If you didn't create this account, please ignore this email.</p>
                ";
                
                sendEmail($email, $email_subject, $email_message);
                
                $success = 'Account created successfully! Please check your email to verify your account.';
                
                // For demo purposes, auto-verify the account
                $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, is_active = 1 WHERE id = ?");
                $stmt->execute([$user_id]);
                
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body data-page="register">
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Create Account</h1>
                        <p>Join GadgetLoop and start shopping</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="auth-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <small class="form-text">Minimum 6 characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="agree_terms" name="agree_terms" class="form-check-input" required>
                                <label for="agree_terms" class="form-check-label">
                                    I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> 
                                    and <a href="privacy.php" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </form>
                    
                    <div class="auth-links">
                        <p>Already have an account? <a href="login.php">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Password strength indicator
            $('#password').on('input', function() {
                const password = $(this).val();
                const strength = calculatePasswordStrength(password);
                showPasswordStrength(strength);
            });
            
            // Confirm password validation
            $('#confirm_password').on('blur', function() {
                const password = $('#password').val();
                const confirmPassword = $(this).val();
                
                if (password && confirmPassword && password !== confirmPassword) {
                    showFieldError($(this), 'Passwords do not match');
                } else {
                    $(this).removeClass('error').siblings('.error-message').remove();
                }
            });
            
            // Email availability check
            $('#email').on('blur', function() {
                const email = $(this).val();
                if (email && isValidEmail(email)) {
                    checkEmailAvailability(email);
                }
            });
        });
        
        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }
        
        function showPasswordStrength(strength) {
            const strengthTexts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
            const strengthColors = ['#ff4444', '#ff8800', '#ffaa00', '#88aa00', '#44aa44', '#00aa44'];
            
            let strengthHtml = `
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" style="width: ${(strength / 6) * 100}%; background-color: ${strengthColors[strength - 1] || '#ddd'}"></div>
                    </div>
                    <span class="strength-text" style="color: ${strengthColors[strength - 1] || '#666'}">${strengthTexts[strength - 1] || 'Too Short'}</span>
                </div>
            `;
            
            $('#password').siblings('.password-strength').remove();
            $('#password').after(strengthHtml);
        }
        
        function checkEmailAvailability(email) {
            $.post('ajax/check-email.php', { email: email }, function(response) {
                const $emailField = $('#email');
                
                if (response.available) {
                    $emailField.removeClass('error').siblings('.error-message').remove();
                    $emailField.after('<div class="success-message">Email is available</div>');
                } else {
                    showFieldError($emailField, 'This email is already registered');
                }
            });
        }
    </script>
</body>
</html>