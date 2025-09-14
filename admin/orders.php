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

// Handle order status updates
if ($_POST && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $success = 'Order status updated successfully';
    } catch (PDOException $e) {
        $error = 'Failed to update order status';
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = ['o.id IS NOT NULL'];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'o.status = ?';
    $params[] = $status_filter;
}

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = 'DATE(o.created_at) = CURDATE()';
            break;
        case 'week':
            $where_conditions[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            break;
        case 'month':
            $where_conditions[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            break;
    }
}

if ($search) {
    $where_conditions[] = '(o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Get orders
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email,
    COUNT(oi.id) as item_count
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(total_amount) as total_revenue
    FROM orders
");
$stmt->execute();
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-page-header">
                <h1>Orders</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Order Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🚚</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['shipped_orders']); ?></h3>
                        <p>Shipped Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>RM<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Filters</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Order number or customer">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="date">Date Range</label>
                                <select id="date" name="date" class="form-control">
                                    <option value="">All Time</option>
                                    <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                    <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                                    <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Orders (<?php echo count($orders); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                            <br>
                                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td><strong>RM<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>