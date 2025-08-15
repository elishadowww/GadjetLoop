<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Sales Analytics
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->execute([$start_date, $end_date . ' 23:59:59']);
$daily_sales = $stmt->fetchAll();

// Product Performance
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        p.main_image,
        SUM(oi.quantity) as total_sold,
        SUM(oi.total) as revenue,
        AVG(r.rating) as avg_rating,
        COUNT(r.id) as review_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date . ' 23:59:59']);
$top_products = $stmt->fetchAll();

// Customer Analytics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT o.user_id) as total_customers,
        COUNT(DISTINCT CASE WHEN order_count = 1 THEN o.user_id END) as new_customers,
        COUNT(DISTINCT CASE WHEN order_count > 1 THEN o.user_id END) as returning_customers
    FROM orders o
    JOIN (
        SELECT user_id, COUNT(*) as order_count
        FROM orders
        WHERE created_at BETWEEN ? AND ?
        GROUP BY user_id
    ) oc ON o.user_id = oc.user_id
    WHERE o.created_at BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date . ' 23:59:59', $start_date, $end_date . ' 23:59:59']);
$customer_stats = $stmt->fetch();

// Category Performance
$stmt = $pdo->prepare("
    SELECT 
        c.name,
        COUNT(oi.id) as items_sold,
        SUM(oi.total) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY revenue DESC
");
$stmt->execute([$start_date, $end_date . ' 23:59:59']);
$category_performance = $stmt->fetchAll();

// Overall Statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders
    FROM orders
    WHERE created_at BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date . ' 23:59:59']);
$overall_stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Analytics</h1>
                <div class="admin-actions">
                    <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control" style="width: auto;">
                        <span>to</span>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control" style="width: auto;">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
            
            <!-- Overall Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($overall_stats['total_orders']); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>RM<?php echo number_format($overall_stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3>RM<?php echo number_format($overall_stats['avg_order_value'], 2); ?></h3>
                        <p>Avg Order Value</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($overall_stats['completed_orders']); ?></h3>
                        <p>Completed Orders</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Sales Trend</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="sales-chart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Category Performance</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="category-chart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Top Selling Products</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                    <th>Avg Rating</th>
                                    <th>Reviews</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="../images/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($product['total_sold']); ?></td>
                                    <td>RM<?php echo number_format($product['revenue'], 2); ?></td>
                                    <td>
                                        <?php if ($product['avg_rating']): ?>
                                            <?php echo number_format($product['avg_rating'], 1); ?> ⭐
                                        <?php else: ?>
                                            No ratings
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['review_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Customer Analytics -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Customer Analytics</h3>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-info">
                                <h3><?php echo number_format($customer_stats['total_customers']); ?></h3>
                                <p>Total Customers</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">🆕</div>
                            <div class="stat-info">
                                <h3><?php echo number_format($customer_stats['new_customers']); ?></h3>
                                <p>New Customers</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">🔄</div>
                            <div class="stat-info">
                                <h3><?php echo number_format($customer_stats['returning_customers']); ?></h3>
                                <p>Returning Customers</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">📈</div>
                            <div class="stat-info">
                                <h3><?php echo $customer_stats['total_customers'] > 0 ? number_format(($customer_stats['returning_customers'] / $customer_stats['total_customers']) * 100, 1) : 0; ?>%</h3>
                                <p>Retention Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Sales Chart
            const salesCtx = document.getElementById('sales-chart').getContext('2d');
            const salesData = <?php echo json_encode($daily_sales); ?>;
            
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: salesData.map(item => item.date),
                    datasets: [{
                        label: 'Revenue (RM)',
                        data: salesData.map(item => parseFloat(item.revenue)),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'Orders',
                        data: salesData.map(item => parseInt(item.orders)),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Revenue (RM)'
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
            
            // Category Chart
            const categoryCtx = document.getElementById('category-chart').getContext('2d');
            const categoryData = <?php echo json_encode($category_performance); ?>;
            
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(item => item.name),
                    datasets: [{
                        data: categoryData.map(item => parseFloat(item.revenue)),
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', 
                            '#6f42c1', '#fd7e14', '#20c997', '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>