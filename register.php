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
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" required>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-auth-compat.js"></script>
<script>
    // ✅ Your Firebase config
    const firebaseConfig = {
        apiKey: "AIzaSyC18Pe-WCSWPcHTWPppQ1PiRKOgFZdBtUI",
        authDomain: "gadgetloop-70fb2.firebaseapp.com",
        projectId: "gadgetloop-70fb2",
        storageBucket: "gadgetloop-70fb2.appspot.com",
        messagingSenderId: "855930704824",
        appId: "1:855930704824:web:fa018179a5f9c66ca8754d"
    };

    // ✅ Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    // ✅ Handle registration
    document.getElementById('registerForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = this;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

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
            }, 3000); // check every 3 seconds

        } catch (error) {
            alert("❌ Firebase error: " + error.message);
        }
    });
</script>
<script src="js/main.js"></script>
</body>
</html>
