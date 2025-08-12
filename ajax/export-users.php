<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Only allow admins
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_export_' . date('Ymd_His') . '.csv"');

// Get filters from GET (optional)
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = ['u.id IS NOT NULL'];
$params = [];

if ($role_filter) {
    $where_conditions[] = 'u.role = ?';
    $params[] = $role_filter;
}
if ($status_filter === 'active') {
    $where_conditions[] = 'u.is_active = 1';
} elseif ($status_filter === 'inactive') {
    $where_conditions[] = 'u.is_active = 0';
} elseif ($status_filter === 'verified') {
    $where_conditions[] = 'u.is_verified = 1';
} elseif ($status_filter === 'unverified') {
    $where_conditions[] = 'u.is_verified = 0';
}
if ($search) {
    $where_conditions[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$where_clause = implode(' AND ', $where_conditions);

$stmt = $pdo->prepare("
    SELECT u.*, 
    COUNT(o.id) as total_orders,
    SUM(o.total_amount) as total_spent,
    MAX(o.created_at) as last_order_date
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE $where_clause
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output CSV header
$header = [
    'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Role', 'Active', 'Verified', 'Total Orders', 'Total Spent', 'Last Order Date', 'Joined'
];
echo implode(',', $header) . "\r\n";

foreach ($users as $user) {
    $row = [
        $user['id'],
        '"' . str_replace('"', '""', $user['first_name']) . '"',
        '"' . str_replace('"', '""', $user['last_name']) . '"',
        '"' . str_replace('"', '""', $user['email']) . '"',
        '"' . str_replace('"', '""', $user['phone']) . '"',
        $user['role'],
        $user['is_active'] ? 'Active' : 'Inactive',
        $user['is_verified'] ? 'Verified' : 'Unverified',
        $user['total_orders'] ?: 0,
        number_format($user['total_spent'] ?: 0, 2),
        $user['last_order_date'] ? date('Y-m-d', strtotime($user['last_order_date'])) : '',
        $user['created_at'] ? date('Y-m-d', strtotime($user['created_at'])) : ''
    ];
    echo implode(',', $row) . "\r\n";
}
