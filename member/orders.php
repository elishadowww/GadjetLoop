<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a member
if (!isLoggedIn() || isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

// Get user orders
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$orders = getOrdersByUser($pdo, $user_id, $page, $per_page);

// Get total orders count for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
</head>
<body data-page="orders" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="member-layout">
                <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>My Orders</h1>
                        <p>View and track all your orders</p>
                    </div>
                    
                    <div class="orders-content">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">📦</div>
                                <h3>No orders yet</h3>
                                <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                                <a href="../products.php" class="btn btn-primary">Browse Products</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <?php $order_items = getOrderItems($pdo, $order['id']); ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-number">
                                                <strong>Order #<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                            </div>
                                            <div class="order-date">
                                                <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="order-items">
                                            <?php foreach ($order_items as $item): ?>
                                            <div class="order-item">
                                                <div class="item-image">
                                                    <img src="../uploads/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                </div>
                                                <div class="item-details">
                                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                                    <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                                </div>
                                                <div class="item-price">$<?php echo number_format($item['total'], 2); ?></div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="order-footer">
                                            <div class="order-status">
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                            <div class="order-total">
                                                Total: $<?php echo number_format($order['total_amount'], 2); ?>
                                            </div>
                                            <div class="order-actions">
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">View Details</a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <button class="btn btn-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">Cancel</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <?php if ($i == $page): ?>
                                            <span class="current"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                // AJAX call to cancel order
                $.post('../ajax/cancel-order.php', { order_id: orderId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel order: ' + response.message);
                    }
                });
            }
        }
    </script>
</body>
</html>