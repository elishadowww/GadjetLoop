<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Handle settings update
if ($_POST) {
    if (isset($_POST['update_general'])) {
        // In a real application, you would store these in a settings table
        $site_name = sanitizeInput($_POST['site_name']);
        $site_email = sanitizeInput($_POST['site_email']);
        $site_phone = sanitizeInput($_POST['site_phone']);
        $site_address = sanitizeInput($_POST['site_address']);
        
        // For demo purposes, we'll just show success
        $success = 'General settings updated successfully';
    }
    
    if (isset($_POST['update_email'])) {
        $smtp_host = sanitizeInput($_POST['smtp_host']);
        $smtp_port = sanitizeInput($_POST['smtp_port']);
        $smtp_user = sanitizeInput($_POST['smtp_user']);
        $smtp_pass = $_POST['smtp_pass'];
        
        $success = 'Email settings updated successfully';
    }
    
    if (isset($_POST['update_payment'])) {
        $payment_gateway = sanitizeInput($_POST['payment_gateway']);
        $stripe_public_key = sanitizeInput($_POST['stripe_public_key']);
        $stripe_secret_key = $_POST['stripe_secret_key'];
        
        $success = 'Payment settings updated successfully';
    }
    
    if (isset($_POST['update_shipping'])) {
        $free_shipping_threshold = floatval($_POST['free_shipping_threshold']);
        $standard_shipping_rate = floatval($_POST['standard_shipping_rate']);
        $express_shipping_rate = floatval($_POST['express_shipping_rate']);
        
        $success = 'Shipping settings updated successfully';
    }
}

// Get current settings (in a real app, these would come from database)
$settings = [
    'site_name' => 'GadgetLoop',
    'site_email' => 'info@gadgetloop.com',
    'site_phone' => '(555) 123-4567',
    'site_address' => '123 Tech Street, Digital City, DC 12345',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => '587',
    'smtp_user' => 'your-email@gmail.com',
    'payment_gateway' => 'stripe',
    'free_shipping_threshold' => 50.00,
    'standard_shipping_rate' => 9.99,
    'express_shipping_rate' => 19.99
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-page-header">
                <h1>Settings</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Settings Tabs -->
            <div class="dashboard-card">
                <div class="card-header">
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn btn-outline settings-tab active" data-tab="general">General</button>
                        <button class="btn btn-outline settings-tab" data-tab="email">Email</button>
                        <button class="btn btn-outline settings-tab" data-tab="payment">Payment</button>
                        <button class="btn btn-outline settings-tab" data-tab="shipping">Shipping</button>
                        <button class="btn btn-outline settings-tab" data-tab="security">Security</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- General Settings -->
                    <div id="general-settings" class="settings-panel active">
                        <h3>General Settings</h3>
                        <form method="POST">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="site_name">Site Name</label>
                                    <input type="text" id="site_name" name="site_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_email">Site Email</label>
                                    <input type="email" id="site_email" name="site_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_email']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="site_phone">Phone Number</label>
                                    <input type="text" id="site_phone" name="site_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="timezone">Timezone</label>
                                    <select id="timezone" name="timezone" class="form-control">
                                        <option value="America/New_York">Eastern Time</option>
                                        <option value="America/Chicago">Central Time</option>
                                        <option value="America/Denver">Mountain Time</option>
                                        <option value="America/Los_Angeles">Pacific Time</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_address">Address</label>
                                <textarea id="site_address" name="site_address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_general" class="btn btn-primary">Update General Settings</button>
                        </form>
                    </div>
                    
                    <!-- Email Settings -->
                    <div id="email-settings" class="settings-panel">
                        <h3>Email Settings</h3>
                        <form method="POST">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="smtp_host">SMTP Host</label>
                                    <input type="text" id="smtp_host" name="smtp_host" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_host']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="smtp_port">SMTP Port</label>
                                    <input type="number" id="smtp_port" name="smtp_port" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_port']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_user">SMTP Username</label>
                                <input type="text" id="smtp_user" name="smtp_user" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['smtp_user']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_pass">SMTP Password</label>
                                <input type="password" id="smtp_pass" name="smtp_pass" class="form-control" 
                                       placeholder="Enter new password to change">
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="smtp_encryption" value="tls" checked> Use TLS Encryption
                                </label>
                            </div>
                            
                            <button type="submit" name="update_email" class="btn btn-primary">Update Email Settings</button>
                            <button type="button" class="btn btn-outline" onclick="testEmail()">Test Email</button>
                        </form>
                    </div>
                    
                    <!-- Payment Settings -->
                    <div id="payment-settings" class="settings-panel">
                        <h3>Payment Settings</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label for="payment_gateway">Payment Gateway</label>
                                <select id="payment_gateway" name="payment_gateway" class="form-control">
                                    <option value="stripe" <?php echo $settings['payment_gateway'] === 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="square">Square</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="stripe_public_key">Stripe Publishable Key</label>
                                <input type="text" id="stripe_public_key" name="stripe_public_key" class="form-control" 
                                       placeholder="pk_test_...">
                            </div>
                            
                            <div class="form-group">
                                <label for="stripe_secret_key">Stripe Secret Key</label>
                                <input type="password" id="stripe_secret_key" name="stripe_secret_key" class="form-control" 
                                       placeholder="sk_test_...">
                            </div>
                            
                            <div class="form-group">
                                <label for="tax_rate">Tax Rate (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" class="form-control" 
                                       value="8.00" step="0.01" min="0" max="100">
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="test_mode" checked> Test Mode
                                </label>
                            </div>
                            
                            <button type="submit" name="update_payment" class="btn btn-primary">Update Payment Settings</button>
                        </form>
                    </div>
                    
                    <!-- Shipping Settings -->
                    <div id="shipping-settings" class="settings-panel">
                        <h3>Shipping Settings</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label for="free_shipping_threshold">Free Shipping Threshold ($)</label>
                                <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" 
                                       class="form-control" value="<?php echo $settings['free_shipping_threshold']; ?>" 
                                       step="0.01" min="0">
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="standard_shipping_rate">Standard Shipping Rate ($)</label>
                                    <input type="number" id="standard_shipping_rate" name="standard_shipping_rate" 
                                           class="form-control" value="<?php echo $settings['standard_shipping_rate']; ?>" 
                                           step="0.01" min="0">
                                </div>
                                
                                <div class="form-group">
                                    <label for="express_shipping_rate">Express Shipping Rate ($)</label>
                                    <input type="number" id="express_shipping_rate" name="express_shipping_rate" 
                                           class="form-control" value="<?php echo $settings['express_shipping_rate']; ?>" 
                                           step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_zones">Shipping Zones</label>
                                <textarea id="shipping_zones" name="shipping_zones" class="form-control" rows="4" 
                                          placeholder="US, CA, UK, AU (one per line)">US
CA
UK
AU</textarea>
                            </div>
                            
                            <button type="submit" name="update_shipping" class="btn btn-primary">Update Shipping Settings</button>
                        </form>
                    </div>
                    
                    <!-- Security Settings -->
                    <div id="security-settings" class="settings-panel">
                        <h3>Security Settings</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label for="max_login_attempts">Max Login Attempts</label>
                                <input type="number" id="max_login_attempts" name="max_login_attempts" 
                                       class="form-control" value="3" min="1" max="10">
                            </div>
                            
                            <div class="form-group">
                                <label for="lockout_duration">Lockout Duration (minutes)</label>
                                <input type="number" id="lockout_duration" name="lockout_duration" 
                                       class="form-control" value="15" min="1" max="1440">
                            </div>
                            
                            <div class="form-group">
                                <label for="session_timeout">Session Timeout (minutes)</label>
                                <input type="number" id="session_timeout" name="session_timeout" 
                                       class="form-control" value="30" min="5" max="480">
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="require_email_verification" checked> Require Email Verification
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="enable_captcha"> Enable CAPTCHA
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="enable_2fa"> Enable Two-Factor Authentication
                                </label>
                            </div>
                            
                            <button type="submit" name="update_security" class="btn btn-primary">Update Security Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Settings tabs
            $('.settings-tab').on('click', function() {
                const tab = $(this).data('tab');
                
                // Update active tab
                $('.settings-tab').removeClass('active');
                $(this).addClass('active');
                
                // Show corresponding panel
                $('.settings-panel').removeClass('active');
                $(`#${tab}-settings`).addClass('active');
            });
        });
        
        function testEmail() {
            // In a real application, this would send a test email
            alert('Test email sent! Check your inbox.');
        }
    </script>
    
    <style>
        .settings-panel {
            display: none;
        }
        
        .settings-panel.active {
            display: block;
        }
        
        .settings-tab.active {
            background-color: #007bff;
            color: white;
        }
    </style>
</body>
</html>