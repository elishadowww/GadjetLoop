<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Clear all session data
session_unset();
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php?message=logged_out');
exit;
?>