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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $agree_terms = isset($_POST['agree_terms']);

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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'An account with this email already exists';
        } else {
            $hashed_password = hashPassword($password);


            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, password, phone, 
                                     verification_token, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password, 
                               $phone, $verification_token]);

                $user_id = $pdo->lastInsertId();

                // Send verification email
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

            
                echo "<script>
                        alert('Registered! Redirecting to login...');
                        setTimeout(() => { window.location.href = 'login.php'; }, 1000);
                      </script>";
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

                <form method="POST" class="auth-form" id="registerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">👁️</button>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">👁️</button>
                            </div>

                            <div class="password-requirements" id="passwordRequirements" style="display: none;">
                                    <div class="requirements-header">Password Requirements:</div>
                                    <ul class="requirements-list">
                                        <li id="req-length" class="requirement">
                                            <span class="req-icon">✗</span>
                                            <span class="req-text">At least 6 characters</span>
                                        </li>
                                        <li id="req-lowercase" class="requirement">
                                            <span class="req-icon">✗</span>
                                            <span class="req-text">One lowercase letter</span>
                                        </li>
                                        <li id="req-uppercase" class="requirement">
                                            <span class="req-icon">✗</span>
                                            <span class="req-text">One uppercase letter</span>
                                        </li>
                                        <li id="req-number" class="requirement">
                                            <span class="req-icon">✗</span>
                                            <span class="req-text">One number</span>
                                        </li>
                                        <li id="req-special" class="requirement">
                                            <span class="req-icon">✗</span>
                                            <span class="req-text">One special character</span>
                                        </li>
                                    </ul>
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
</main>
<?php include 'includes/footer.php'; ?>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-auth-compat.js"></script>
<script src="js/main.js"></script>

<script>
    // ✅ Firebase Config
    const firebaseConfig = {
        apiKey: "AIzaSyC18Pe-WCSWPcHTWPppQ1PiRKOgFZdBtUI",
        authDomain: "gadgetloop-70fb2.firebaseapp.com",
        projectId: "gadgetloop-70fb2",
        storageBucket: "gadgetloop-70fb2.appspot.com",
        messagingSenderId: "855930704824",
        appId: "1:855930704824:web:fa018179a5f9c66ca8754d"
    };

    firebase.initializeApp(firebaseConfig);

    $(document).ready(function() {
        // ✅ Password strength indicator
        $('#password').on('input', function () {
            const password = $(this).val();
            const strength = calculatePasswordStrength(password);
            showPasswordStrength(strength);
        });

        // ✅ Confirm password validation
        $('#confirm_password').on('blur', function () {
            const password = $('#password').val();
            const confirmPassword = $(this).val();

            if (password && confirmPassword && password !== confirmPassword) {
                showFieldError($(this), 'Passwords do not match');
            } else {
                $(this).removeClass('error').siblings('.error-message').remove();
            }
        });

        // ✅ Email availability check
        $('#email').on('blur', function () {
            const email = $(this).val();
            if (email && isValidEmail(email)) {
                checkEmailAvailability(email);
            }
        });

        // ✅ Handle Firebase registration
        $('#registerForm').on('submit', async function (e) {
            e.preventDefault();

            const form = this;
            const email = $('#email').val();
            const password = $('#password').val();

            try {
                const userCredential = await firebase.auth().createUserWithEmailAndPassword(email, password);
                await userCredential.user.sendEmailVerification();
                alert("✅ Verification email sent. Please check your inbox.");

                const checkInterval = setInterval(async () => {
                    await userCredential.user.reload();
                    if (userCredential.user.emailVerified) {
                        clearInterval(checkInterval);

                        // ✅ Call PHP to update MySQL
                        fetch('verify.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ email })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert("✅ Email verified & database updated!");
                                form.submit(); // finally submit form
                            } else {
                                alert("❌ Database update failed: " + data.message);
                            }
                        });
                    }
                }, 3000); // every 3 seconds

            } catch (error) {
                alert("❌ Firebase error: " + error.message);
            }
        });
    });

    // Unhide password 
    function togglePassword(id, toggleBtn) {
        const input = document.getElementById(id);
        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        toggleBtn.textContent = isVisible ? '👁️' : '🙈';
    }

    // Helper: Password Strength
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
        const password = $('#password').val();
        const strengthTexts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
        const strengthColors = ['#ff4444', '#ff8800', '#ffaa00', '#88aa00', '#44aa44', '#00aa44'];

        const strengthHtml = `
            <div class="password-strength">
                <div class="strength-bar">
                    <div class="strength-fill" style="width: ${(strength / 6) * 100}%; background-color: ${strengthColors[strength - 1] || '#ddd'}"></div>
                </div>
                <span class="strength-text" style="color: ${strengthColors[strength - 1] || '#666'}">${strengthTexts[strength - 1] || 'Too Short'}</span>
            </div>
        `;

        $('#password').siblings('.password-strength').remove();
        $('#password').after(strengthHtml);

        // Show/hide and update requirements
        const requirements = $('#passwordRequirements');
        if (password.length > 0) {
            requirements.show();
            updatePasswordRequirements(password);
        } else {
            requirements.hide();
        }
    }

    // Add new function to update requirements
    function updatePasswordRequirements(password) {
        const requirements = [
            { id: 'req-length', test: password.length >= 6 },
            { id: 'req-lowercase', test: /[a-z]/.test(password) },
            { id: 'req-uppercase', test: /[A-Z]/.test(password) },
            { id: 'req-number', test: /[0-9]/.test(password) },
            { id: 'req-special', test: /[^A-Za-z0-9]/.test(password) }
        ];

        requirements.forEach(req => {
            const element = $('#' + req.id);
            const icon = element.find('.req-icon');
            
            if (req.test) {
                element.addClass('met').removeClass('unmet');
                icon.text('✓').css('color', '#28a745');
            } else {
                element.addClass('unmet').removeClass('met');
                icon.text('✗').css('color', '#dc3545');
            }
        });
    }

    function showFieldError($field, message) {
        $field.addClass('error').siblings('.error-message').remove();
        $field.after('<div class="error-message" style="color: red;">' + message + '</div>');
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function checkEmailAvailability(email) {
        $.post('ajax/check-email.php', { email: email }, function (response) {
            const $emailField = $('#email');
            $emailField.siblings('.error-message, .success-message').remove();

            if (response.available) {
                $emailField.removeClass('error');
                $emailField.after('<div class="success-message" style="color: green;">Email is available</div>');
            } else {
                showFieldError($emailField, 'This email is already registered');
            }
        }, 'json');
    }
</script>

</body>
</html>
