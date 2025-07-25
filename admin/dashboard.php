<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get dashboard statistics
$stats = [];

// Total users
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'member'");
$stmt->execute();
$stats['total_users'] = $stmt->fetchColumn();

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE is_active = 1");
$stmt->execute();
$stats['total_products'] = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders");
$stmt->execute();
$stats['total_orders'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'");
$stmt->execute();
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE stock_quantity <= low_stock_threshold AND is_active = 1 
    ORDER BY stock_quantity ASC 
    LIMIT 10
");
$stmt->execute();
$low_stock_products = $stmt->fetchAll();

// Top selling products (last 30 days)
$stmt = $pdo->prepare("
    SELECT p.name, p.main_image, SUM(oi.quantity) as total_sold, SUM(oi.total) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmt->execute();
$top_products = $stmt->fetchAll();

// Monthly sales data for chart
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as order_count,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");
$stmt->execute();
$monthly_sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="dashboard-heading" >
                <div class="dashboard-header-left">
                <h1>Dashboard</h1>
                </div>
                <div class="admin-actions">
                    <button class="btn btn-primary-refresh" onclick="refreshDashboard()">Refresh</button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Sales Overview</h3>
                        <select id="chart-period">
                            <option value="12">Last 12 Months</option>
                            <option value="6">Last 6 Months</option>
                            <option value="3">Last 3 Months</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Top Selling Products</h3>
                        <a href="products.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="top-products-list">
                            <?php foreach ($top_products as $product): ?>
                            <div class="top-product-item">
                                <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p><?php echo $product['total_sold']; ?> sold</p>
                                </div>
                                <div class="product-revenue">
                                    $<?php echo number_format($product['revenue'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders and Low Stock -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline btn-sm">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Low Stock Alert</h3>
                        <a href="products.php?filter=low_stock" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_products)): ?>
                            <p class="text-center text-muted">No low stock products</p>
                        <?php else: ?>
                            <div class="low-stock-list">
                                <?php foreach ($low_stock_products as $product): ?>
                                <div class="low-stock-item">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-info">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="stock-level <?php echo $product['stock_quantity'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                                            <?php echo $product['stock_quantity']; ?> in stock
                                        </p>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-primary btn-sm">Update Stock</a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/chart.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize sales chart
            initializeSalesChart();
            
            // Handle chart period change
            $('#chart-period').on('change', function() {
                updateSalesChart($(this).val());
            });
        });
        
        function initializeSalesChart() {
            const ctx = document.getElementById('sales-chart').getContext('2d');
            const salesData = <?php echo json_encode($monthly_sales); ?>;
            
            const labels = salesData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });
            
            const revenueData = salesData.map(item => parseFloat(item.revenue));
            const orderData = salesData.map(item => parseInt(item.order_count));
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: revenueData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'Orders',
                        data: orderData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Revenue ($)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Orders'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }
        
        function refreshDashboard() {
            location.reload();
        }
        
        function updateSalesChart(period) {
            // AJAX call to get updated chart data
            $.get('ajax/get-sales-data.php', { period: period }, function(data) {
                // Update chart with new data
                // Implementation would depend on Chart.js update methods
            });
        }
    </script>
</body>
</html>