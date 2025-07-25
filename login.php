<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = loginUser($pdo, $email, $password);
        
        if ($result['success']) {
            // Set remember me cookie if requested
            if ($remember) {
                $token = generateToken();
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                
                // Store token in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $_SESSION['user_id']]);
            }
            
            // Redirect based on role
            if (isAdmin()) {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: /GadjetLoop/member/dashboard.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body data-page="login">
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Welcome Back</h1>
                        <p>Sign in to your GadgetLoop account</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-input-container">
                                    <input type="password" id="password" name="password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <span class="eye-icon">👁️</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                                <label for="remember" class="form-check-label">Remember me</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </form>
                    
                    <div class="auth-links">
                        <a href="forgot-password.php">Forgot your password?</a>
                        <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                    </div>
                    
                    <!-- Demo accounts info -->
                    <div class="demo-accounts">
                        <h4>Demo Accounts</h4>
                        <div class="demo-account">
                            <strong>Admin:</strong> admin@gadgetloop.com / admin123
                        </div>
                        <div class="demo-account">
                            <strong>Member:</strong> member@gadgetloop.com / member123
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/main.js"></script>
    <script>

    $(document).ready(function() {
            // Add login attempt tracking
            let attempts = 0;
            const maxAttempts = 3;
            
            $('.auth-form').on('submit', function(e) {
                attempts++;
                
                if (attempts >= maxAttempts) {
                    // Add captcha or additional security measures
                    if (!$('.captcha-container').length) {
                        const captchaHtml = `
                            <div class="form-group captcha-container">
                                <label for="captcha">Please verify you're human</label>
                                <div class="captcha-challenge">
                                    <span id="captcha-question"></span>
                                    <input type="number" id="captcha" name="captcha" class="form-control" required>
                                </div>
                            </div>
                        `;
                        $(this).find('button[type="submit"]').before(captchaHtml);
                        generateCaptcha();
                    }
                }
            });
            
            
            // Generate simple math captcha
            function generateCaptcha() {
                const num1 = Math.floor(Math.random() * 10) + 1;
                const num2 = Math.floor(Math.random() * 10) + 1;
                const answer = num1 + num2;
                
                $('#captcha-question').text(`What is ${num1} + ${num2}?`);
                $('#captcha').data('answer', answer);
            }
            
            // Validate captcha
            $('#captcha').on('blur', function() {
                const userAnswer = parseInt($(this).val());
                const correctAnswer = parseInt($(this).data('answer'));
                
                if (userAnswer !== correctAnswer) {
                    showFieldError($(this), 'Incorrect answer');
                } else {
                    $(this).removeClass('error').siblings('.error-message').remove();
                }
            });
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            const eyeIcon = toggleButton.querySelector('.eye-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.textContent = '🙈'; // Change to "hide" icon
            toggleButton.setAttribute('aria-label', 'Hide password');
    } else {
            passwordInput.type = 'password';
            eyeIcon.textContent = '🙉'; // Back to "show" icon
            toggleButton.setAttribute('aria-label', 'Show password');
    }
}

    </script>

    <!-- Firebase scripts -->
<script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.5.2/firebase-auth-compat.js"></script>
<script>
  const firebaseConfig = {
    apiKey: "AIzaSyC18Pe-WCSWPcHTWPppQ1PiRKOgFZdBtUI",
    authDomain: "gadgetloop-70fb2.firebaseapp.com",
    projectId: "gadgetloop-70fb2",
  };
  firebase.initializeApp(firebaseConfig);

  // Optional: Handle form submit via Firebase
  document.querySelector('.auth-form').addEventListener('submit', async (e) => {
    e.preventDefault(); // stop PHP form submission

    const email = document.querySelector('#email').value;
    const password = document.querySelector('#password').value;

    try {
      const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);
      const user = userCredential.user;



      // ✅ Email is verified — now submit form to PHP backend
      e.target.submit();

    } catch (error) {
      alert("Login failed: " + error.message);
    }
  });
</script>

</body>
</html>