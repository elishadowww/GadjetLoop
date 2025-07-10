<?php
// Run this file once to create demo users with proper password hashing
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "<h2>Creating Demo Users...</h2>";

// Create admin user
$admin_password = hashPassword('admin123');
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password, role, is_active, is_verified) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE password = VALUES(password)
");
$result1 = $stmt->execute(['Admin', 'User', 'admin@gadgetloop.com', $admin_password, 'admin', 1, 1]);

// Create member user
$member_password = hashPassword('member123');
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password, role, is_active, is_verified) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE password = VALUES(password)
");
$result2 = $stmt->execute(['Demo', 'Member', 'member@gadgetloop.com', $member_password, 'member', 1, 1]);

if ($result1 && $result2) {
    echo "<p style='color: green;'>✅ Demo users created successfully!</p>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Login Credentials:</h3>";
    echo "<strong>Admin:</strong> admin@gadgetloop.com / admin123<br>";
    echo "<strong>Member:</strong> member@gadgetloop.com / member123<br>";
    echo "</div>";
    
    // Verify the passwords were hashed correctly
    $stmt = $pdo->prepare("SELECT email, password FROM users WHERE email IN (?, ?)");
    $stmt->execute(['admin@gadgetloop.com', 'member@gadgetloop.com']);
    $users = $stmt->fetchAll();
    
    echo "<h3>Password Verification:</h3>";
    foreach ($users as $user) {
        $test_password = ($user['email'] === 'admin@gadgetloop.com') ? 'admin123' : 'member123';
        $verify_result = password_verify($test_password, $user['password']);
        echo "<p>" . $user['email'] . ": " . ($verify_result ? "✅ Password hash verified" : "❌ Password hash failed") . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error creating demo users!</p>";
}
?>