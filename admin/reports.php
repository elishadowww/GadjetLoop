<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle report generation
$report_type = $_GET['type'] ?? 'sales';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$reports = [];

switch ($report_type) {
    case 'sales':
        $stmt = $pdo->prepare("
            SELECT 
                DATE(o.created_at) as date,
                COUNT(o.id) as total_orders,
                SUM(o.total_amount) as total_revenue,
                AVG(o.total_amount) as avg_order_value,
                SUM(o.tax_amount) as total_tax,
                SUM(o.shipping_amount) as total_shipping
            FROM orders o
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY DATE(o.created_at)
            ORDER BY date DESC
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $reports = $stmt->fetchAll();
        break;
        
    case 'products':
        $stmt = $pdo->prepare("
            SELECT 
                p.name,
                p.sku,
                c.name as category,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total) as total_revenue,
                p.stock_quantity,
                AVG(r.rating) as avg_rating
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.created_at BETWEEN ? AND ?
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY total_sold DESC
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $reports = $stmt->fetchAll();
        break;
        
    case 'customers':
        $stmt = $pdo->prepare("
            SELECT 
                u.first_name,
                u.last_name,
                u.email,
                COUNT(o.id) as total_orders,
                SUM(o.total_amount) as total_spent,
                MAX(o.created_at) as last_order,
                u.created_at as registration_date
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.created_at BETWEEN ? AND ?
            WHERE u.role = 'member'
            GROUP BY u.id
            ORDER BY total_spent DESC
        ");
        $stmt->execute([$start_date, $end_date . ' 23:59:59']);
        $reports = $stmt->fetchAll();
        break;
        
    case 'inventory':
        $stmt = $pdo->prepare("
            SELECT 
                p.name,
                p.sku,
                c.name as category,
                p.stock_quantity,
                p.low_stock_threshold,
                CASE 
                    WHEN p.stock_quantity = 0 THEN 'Out of Stock'
                    WHEN p.stock_quantity <= p.low_stock_threshold THEN 'Low Stock'
                    ELSE 'In Stock'
                END as stock_status,
                p.price,
                p.stock_quantity * p.price as inventory_value
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            ORDER BY p.stock_quantity ASC
        ");
        $stmt->execute();
        $reports = $stmt->fetchAll();
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Reports</h1>
                <div class="admin-actions">
                    <button class="btn btn-outline" onclick="exportReport()">Export CSV</button>
                    <button class="btn btn-primary" onclick="printReport()">Print Report</button>
                </div>
            </div>
            
            <!-- Report Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Report Configuration</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="type">Report Type</label>
                                <select id="type" name="type" class="form-control">
                                    <option value="sales" <?php echo $report_type === 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                                    <option value="products" <?php echo $report_type === 'products' ? 'selected' : ''; ?>>Product Performance</option>
                                    <option value="customers" <?php echo $report_type === 'customers' ? 'selected' : ''; ?>>Customer Report</option>
                                    <option value="inventory" <?php echo $report_type === 'inventory' ? 'selected' : ''; ?>>Inventory Report</option>
                                </select>
                            </div>
                            
                            <?php if ($report_type !== 'inventory'): ?>
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" class="form-control">
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Report Results -->
            <div class="dashboard-card" id="report-content">
                <div class="card-header">
                    <h3>
                        <?php
                        switch ($report_type) {
                            case 'sales': echo 'Sales Report'; break;
                            case 'products': echo 'Product Performance Report'; break;
                            case 'customers': echo 'Customer Report'; break;
                            case 'inventory': echo 'Inventory Report'; break;
                        }
                        ?>
                        <?php if ($report_type !== 'inventory'): ?>
                            (<?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?>)
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($reports)): ?>
                        <p class="text-center">No data found for the selected criteria.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <?php if ($report_type === 'sales'): ?>
                                            <th>Date</th>
                                            <th>Orders</th>
                                            <th>Revenue</th>
                                            <th>Avg Order Value</th>
                                            <th>Tax</th>
                                            <th>Shipping</th>
                                        <?php elseif ($report_type === 'products'): ?>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Units Sold</th>
                                            <th>Revenue</th>
                                            <th>Stock</th>
                                            <th>Rating</th>
                                        <?php elseif ($report_type === 'customers'): ?>
                                            <th>Customer</th>
                                            <th>Email</th>
                                            <th>Orders</th>
                                            <th>Total Spent</th>
                                            <th>Last Order</th>
                                            <th>Registered</th>
                                        <?php elseif ($report_type === 'inventory'): ?>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Price</th>
                                            <th>Value</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $row): ?>
                                    <tr>
                                        <?php if ($report_type === 'sales'): ?>
                                            <td><?php echo date('M j, Y', strtotime($row['date'])); ?></td>
                                            <td><?php echo number_format($row['total_orders']); ?></td>
                                            <td>RM<?php echo number_format($row['total_revenue'], 2); ?></td>
                                            <td>RM<?php echo number_format($row['avg_order_value'], 2); ?></td>
                                            <td>RM<?php echo number_format($row['total_tax'], 2); ?></td>
                                            <td>RM<?php echo number_format($row['total_shipping'], 2); ?></td>
                                        <?php elseif ($report_type === 'products'): ?>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['sku']); ?></td>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td><?php echo number_format($row['total_sold'] ?: 0); ?></td>
                                            <td>RM<?php echo number_format($row['total_revenue'] ?: 0, 2); ?></td>
                                            <td><?php echo $row['stock_quantity']; ?></td>
                                            <td><?php echo $row['avg_rating'] ? number_format($row['avg_rating'], 1) . ' ⭐' : 'No ratings'; ?></td>
                                        <?php elseif ($report_type === 'customers'): ?>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo number_format($row['total_orders'] ?: 0); ?></td>
                                            <td>RM<?php echo number_format($row['total_spent'] ?: 0, 2); ?></td>
                                            <td><?php echo $row['last_order'] ? date('M j, Y', strtotime($row['last_order'])) : 'Never'; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($row['registration_date'])); ?></td>
                                        <?php elseif ($report_type === 'inventory'): ?>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['sku']); ?></td>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td><?php echo $row['stock_quantity']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['stock_status'])); ?>">
                                                    <?php echo $row['stock_status']; ?>
                                                </span>
                                            </td>
                                            <td>RM<?php echo number_format($row['price'], 2); ?></td>
                                            <td>RM<?php echo number_format($row['inventory_value'], 2); ?></td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                
                                <!-- Summary Row -->
                                <tfoot>
                                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                                        <?php if ($report_type === 'sales'): ?>
                                            <td>Total</td>
                                            <td><?php echo number_format(array_sum(array_column($reports, 'total_orders'))); ?></td>
                                            <td>RM<?php echo number_format(array_sum(array_column($reports, 'total_revenue')), 2); ?></td>
                                            <td>RM<?php echo number_format(array_sum(array_column($reports, 'avg_order_value')) / count($reports), 2); ?></td>
                                            <td>RM<?php echo number_format(array_sum(array_column($reports, 'total_tax')), 2); ?></td>
                                            <td>RM<?php echo number_format(array_sum(array_column($reports, 'total_shipping')), 2); ?></td>
                                        <?php elseif ($report_type === 'products'): ?>
                                            <td colspan="3">Total</td>
                                            <td><?php echo number_format(array_sum(array_column($reports, 'total_sold'))); ?></td>
                                            <td>RM<?php echo number_format(array_sum(array_column($reports, 'total_revenue')), 2); ?></td>
                                            <td><?php echo number_format(array_sum(array_column($reports, 'stock_quantity'))); ?></td>
                                            <td>-</td>
                                        <?php elseif ($report_type === 'inventory'): ?>
                                            <td colspan="5">Total Inventory Value</td>
                                            <td>-</td>
                                            <td>RM<?php echo number_format(array_sum(array_column($reports, 'inventory_value')), 2); ?></td>
                                        <?php endif; ?>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        function exportReport() {
            const reportType = '<?php echo $report_type; ?>';
            const startDate = '<?php echo $start_date; ?>';
            const endDate = '<?php echo $end_date; ?>';
            
            window.location.href = `ajax/export-report.php?type=${reportType}&start_date=${startDate}&end_date=${endDate}`;
        }
        
        function printReport() {
            const printContent = document.getElementById('report-content').innerHTML;
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Report - GadgetLoop</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .status-badge { padding: 2px 6px; border-radius: 3px; font-size: 12px; }
                        .status-out-of-stock { background: #f8d7da; color: #721c24; }
                        .status-low-stock { background: #fff3cd; color: #856404; }
                        .status-in-stock { background: #d4edda; color: #155724; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <h1>GadgetLoop - Report</h1>
                    <p>Generated on: ${new Date().toLocaleDateString()}</p>
                    ${printContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>