<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

$success = '';
$error = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $zip_code = sanitizeInput($_POST['zip_code'] ?? '');
    $country = sanitizeInput($_POST['country'] ?? '');
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'First name and last name are required';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE users SET 
                first_name = ?, last_name = ?, phone = ?, 
                address = ?, city = ?, state = ?, zip_code = ?, country = ?,
                updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$first_name, $last_name, $phone, $address, $city, $state, $zip_code, $country, $user_id]);
            
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $success = 'Profile updated successfully';
            
            // Refresh user data
            $user = getUserById($pdo, $user_id);
        } catch (PDOException $e) {
            $error = 'Failed to update profile';
        }
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif (!verifyPassword($current_password, $user['password'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        try {
            $hashed_password = hashPassword($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            $success = 'Password changed successfully';
        } catch (PDOException $e) {
            $error = 'Failed to change password';
        }
    }
}

// Handle profile photo upload
if ($_POST && isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['profile_photo'], 'uploads/profiles/', ['jpg', 'jpeg', 'png']);
        
        if ($upload_result['success']) {
            try {
                // Delete old photo if exists
                if ($user['profile_photo'] && file_exists('uploads/profiles/' . $user['profile_photo'])) {
                    unlink('uploads/profiles/' . $user['profile_photo']);
                }
                
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$upload_result['filename'], $user_id]);
                
                $success = 'Profile photo updated successfully';
                $user['profile_photo'] = $upload_result['filename'];
            } catch (PDOException $e) {
                $error = 'Failed to update profile photo';
            }
        } else {
            $error = $upload_result['message'];
        }
    } else {
        $error = 'Please select a valid image file';
    }
}

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE user_id = ? AND payment_status = 'paid'");
$stmt->execute([$user_id]);
$total_spent = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$stmt->execute([$user_id]);
$wishlist_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
$stmt->execute([$user_id]);
$reviews_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
</head>
<body data-page="profile" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="member-layout">
                <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>My Profile</h1>
                        <p>Manage your account information and preferences</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <!-- Profile Statistics -->
                    <div class="profile-stats">
                        <div class="stat-card">
                            <div class="stat-icon">🛒</div>
                            <div class="stat-info">
                                <h3><?php echo $total_orders; ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                                <p>Total Spent</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">♡</div>
                            <div class="stat-info">
                                <h3><?php echo $wishlist_count; ?></h3>
                                <p>Wishlist Items</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">⭐</div>
                            <div class="stat-info">
                                <h3><?php echo $reviews_count; ?></h3>
                                <p>Reviews Written</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-sections">
                        <!-- Profile Photo Section -->
                        <div class="profile-section">
                            <div class="section-header">
                                <h3>Profile Photo</h3>
                            </div>
                            <div class="section-content">
                                <div class="photo-upload-area">
                                    <div class="current-photo">
                                        <?php if ($user['profile_photo']): ?>
                                            <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                                                 alt="Profile Photo" class="profile-photo">
                                        <?php else: ?>
                                            <div class="default-avatar">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form method="POST" enctype="multipart/form-data" class="photo-upload-form">
                                        <input type="file" name="profile_photo" accept="image/*" class="file-input" id="photo-input">
                                        <label for="photo-input" class="btn btn-outline">Choose Photo</label>
                                        <button type="submit" name="upload_photo" class="btn btn-primary">Upload</button>
                                        <p class="upload-info">JPG, PNG or GIF. Max size 2MB.</p>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Information Section -->
                        <div class="profile-section">
                            <div class="section-header">
                                <h3>Personal Information</h3>
                            </div>
                            <div class="section-content">
                                <form method="POST" class="profile-form">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="first_name">First Name *</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="last_name">Last Name *</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <small class="form-text">Email cannot be changed. Contact support if needed.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="state">State</label>
                                            <input type="text" id="state" name="state" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="zip_code">ZIP Code</label>
                                            <input type="text" id="zip_code" name="zip_code" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <select id="country" name="country" class="form-control">
                                            <option value="">Select Country</option>
                                            <option value="US" <?php echo ($user['country'] ?? '') === 'US' ? 'selected' : ''; ?>>United States</option>
                                            <option value="CA" <?php echo ($user['country'] ?? '') === 'CA' ? 'selected' : ''; ?>>Canada</option>
                                            <option value="UK" <?php echo ($user['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                            <option value="AU" <?php echo ($user['country'] ?? '') === 'AU' ? 'selected' : ''; ?>>Australia</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Change Password Section -->
                        <div class="profile-section">
                            <div class="section-header">
                                <h3>Change Password</h3>
                            </div>
                            <div class="section-content">
                                <form method="POST" class="password-form">
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                                        <small class="form-text">Minimum 6 characters</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Account Information -->
                        <div class="profile-section">
                            <div class="section-header">
                                <h3>Account Information</h3>
                            </div>
                            <div class="section-content">
                                <div class="account-info">
                                    <div class="info-item">
                                        <label>Member Since</label>
                                        <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <label>Account Status</label>
                                        <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <label>Email Verified</label>
                                        <span class="status-badge <?php echo $user['is_verified'] ? 'status-verified' : 'status-unverified'; ?>">
                                            <?php echo $user['is_verified'] ? 'Verified' : 'Unverified'; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <label>Last Updated</label>
                                        <span><?php echo date('F j, Y g:i A', strtotime($user['updated_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Password strength indicator
            $('#new_password').on('input', function() {
                const password = $(this).val();
                const strength = calculatePasswordStrength(password);
                showPasswordStrength(strength);
            });
            
            // Confirm password validation
            $('#confirm_password').on('blur', function() {
                const password = $('#new_password').val();
                const confirmPassword = $(this).val();
                
                if (password && confirmPassword && password !== confirmPassword) {
                    showFieldError($(this), 'Passwords do not match');
                } else {
                    $(this).removeClass('error').siblings('.error-message').remove();
                }
            });
            
            // Photo preview
            $('#photo-input').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('.profile-photo, .default-avatar').replaceWith(
                            `<img src="${e.target.result}" alt="Profile Photo" class="profile-photo">`
                        );
                    };
                    reader.readAsDataURL(file);
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
            
            $('#new_password').siblings('.password-strength').remove();
            $('#new_password').after(strengthHtml);
        }
    </script>
</body>
</html>