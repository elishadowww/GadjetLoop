<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a member
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

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

// Get recent orders
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? 
    GROUP BY o.id
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

// Get wishlist items
$stmt = $pdo->prepare("
    SELECT w.*, p.name, p.price, p.main_image, p.discount_percentage,
    CASE WHEN p.discount_percentage > 0 
         THEN p.price * (1 - p.discount_percentage / 100) 
         ELSE p.price END as sale_price
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ? AND p.is_active = 1
    ORDER BY w.created_at DESC 
    LIMIT 4
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
</head>
<body data-page="dashboard" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
            <div class="member-layout">
                              <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                        <p>Here's an overview of your account activity</p>
                    </div>
                    
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
                                <h3>RM<?php echo number_format($total_spent, 2); ?></h3>
                                <p>Total Spent</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">🩶</div>
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
                    
                    <div class="dashboard-sections">
                        <!-- Recent Orders -->
                        <div class="dashboard-section">
                            <div class="section-header">
                                <h3>Recent Orders</h3>
                                <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
                            </div>
                            <div class="section-content">
                                <?php if (empty($recent_orders)): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">📦</div>
                                        <h4>No orders yet</h4>
                                        <p>Start shopping to see your orders here</p>
                                        <a href="products.php" class="btn btn-primary">Browse Products</a>
                                    </div>
                                <?php else: ?>
                                    <div class="orders-list">
                                        <?php foreach ($recent_orders as $order): ?>
                                        <div class="order-item">
                                            <div class="order-info">
                                                <h4>Order #<?php echo htmlspecialchars($order['order_number']); ?></h4>
                                                <p><?php echo $order['item_count']; ?> items • RM<?php echo number_format($order['total_amount'], 2); ?></p>
                                                <small><?php echo date('M j, Y', strtotime($order['created_at'])); ?></small>
                                            </div>
                                            <div class="order-status">
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Wishlist Preview -->
                        <div class="dashboard-section">
                            <div class="section-header">
                                <h3>Wishlist</h3>
                                <a href="wishlist.php" class="btn btn-outline btn-sm">View All</a>
                            </div>
                            <div class="section-content">
                                <?php if (empty($wishlist_items)): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">🩶</div>
                                        <h4>No wishlist items</h4>
                                        <p>Save products you love for later</p>
                                        <a href="products.php" class="btn btn-primary">Browse Products</a>
                                    </div>
                                <?php else: ?>
                                    <div class="wishlist-preview">
                                        <?php foreach ($wishlist_items as $item): ?>
                                        <div class="wishlist-item">
                                            <img src="../images/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <p class="price">RM<?php echo number_format($item['sale_price'], 2); ?></p>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="dashboard-section">
                            <div class="section-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="section-content">
                                <div class="quick-actions">
                                    <a href="profile.php" class="action-card">
                                        <div class="action-icon">👤</div>
                                        <h4>Update Profile</h4>
                                        <p>Manage your personal information</p>
                                    </a>
                                    
                                    <a href="orders.php" class="action-card">
                                        <div class="action-icon">📋</div>
                                        <h4>Order History</h4>
                                        <p>View all your past orders</p>
                                    </a>
                                    
                                    <a href="member/addresses.php" class="action-card">
                                        <div class="action-icon">📍</div>
                                        <h4>Addresses</h4>
                                        <p>Manage shipping addresses</p>
                                    </a>
                                    
                                    <a href="../contact.php" class="action-card">
                                        <div class="action-icon">💬</div>
                                        <h4>Contact Support</h4>
                                        <p>Get help with your account</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>