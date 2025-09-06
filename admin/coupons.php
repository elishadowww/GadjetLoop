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

// Handle coupon actions
if ($_POST) {
    if (isset($_POST['add_coupon'])) {
        $code = strtoupper(sanitizeInput($_POST['code']));
        $description = sanitizeInput($_POST['description']);
        $discount_type = sanitizeInput($_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $minimum_amount = floatval($_POST['minimum_amount']);
        $maximum_discount = floatval($_POST['maximum_discount']) ?: null;
        $usage_limit = intval($_POST['usage_limit']) ?: null;
        $expires_at = $_POST['expires_at'] ?: null;
        
        if (empty($code) || empty($discount_type) || $discount_value <= 0) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO coupons (code, description, discount_type, discount_value, minimum_amount, 
                                       maximum_discount, usage_limit, expires_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$code, $description, $discount_type, $discount_value, $minimum_amount, 
                               $maximum_discount, $usage_limit, $expires_at]);
                $success = 'Coupon created successfully';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = 'Coupon code already exists';
                } else {
                    $error = 'Failed to create coupon';
                }
            }
        }
    }
    
    if (isset($_POST['toggle_status'])) {
        $coupon_id = intval($_POST['coupon_id']);
        try {
            $stmt = $pdo->prepare("UPDATE coupons SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$coupon_id]);
            $success = 'Coupon status updated successfully';
        } catch (PDOException $e) {
            $error = 'Failed to update coupon status';
        }
    }
    
    if (isset($_POST['delete_coupon'])) {
        $coupon_id = intval($_POST['coupon_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->execute([$coupon_id]);
            $success = 'Coupon deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete coupon';
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Build query
$where_conditions = ['c.id IS NOT NULL'];
$params = [];

if ($status_filter === 'active') {
    $where_conditions[] = 'c.is_active = 1 AND (c.expires_at IS NULL OR c.expires_at > NOW())';
} elseif ($status_filter === 'inactive') {
    $where_conditions[] = 'c.is_active = 0';
} elseif ($status_filter === 'expired') {
    $where_conditions[] = 'c.expires_at IS NOT NULL AND c.expires_at <= NOW()';
}

if ($type_filter) {
    $where_conditions[] = 'c.discount_type = ?';
    $params[] = $type_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get coupons with usage statistics
$stmt = $pdo->prepare("
    SELECT c.*, 
    COUNT(cu.id) as total_usage,
    SUM(cu.discount_amount) as total_discount_given
    FROM coupons c 
    LEFT JOIN coupon_usage cu ON c.id = cu.coupon_id
    WHERE $where_clause
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$coupons = $stmt->fetchAll();

// Get coupon statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_coupons,
        SUM(CASE WHEN is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) THEN 1 ELSE 0 END) as active_coupons,
        SUM(CASE WHEN expires_at IS NOT NULL AND expires_at <= NOW() THEN 1 ELSE 0 END) as expired_coupons,
        SUM(used_count) as total_usage
    FROM coupons
");
$stmt->execute();
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-page-header">
                <h1>Coupons</h1>
                <div class="admin-actions">
                    <button class="btn btn-primary" onclick="showAddCouponModal()">Add Coupon</button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Coupon Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🎫</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_coupons']); ?></h3>
                        <p>Total Coupons</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['active_coupons']); ?></h3>
                        <p>Active Coupons</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['expired_coupons']); ?></h3>
                        <p>Expired Coupons</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_usage']); ?></h3>
                        <p>Total Usage</p>
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
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="type">Discount Type</label>
                                <select id="type" name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="percentage" <?php echo $type_filter === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                                    <option value="fixed" <?php echo $type_filter === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
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
            
            <!-- Coupons Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Coupons (<?php echo count($coupons); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Discount</th>
                                    <th>Usage</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coupons as $coupon): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($coupon['description']); ?></td>
                                    <td>
                                        <?php if ($coupon['discount_type'] === 'percentage'): ?>
                                            <?php echo $coupon['discount_value']; ?>%
                                        <?php else: ?>
                                            RM<?php echo number_format($coupon['discount_value'], 2); ?>
                                        <?php endif; ?>
                                        <?php if ($coupon['minimum_amount'] > 0): ?>
                                            <br><small>Min: RM<?php echo number_format($coupon['minimum_amount'], 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $coupon['total_usage'] ?: 0; ?>
                                        <?php if ($coupon['usage_limit']): ?>
                                            / <?php echo $coupon['usage_limit']; ?>
                                        <?php endif; ?>
                                        <?php if ($coupon['total_discount_given']): ?>
                                            <br><small>Saved: RM<?php echo number_format($coupon['total_discount_given'], 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($coupon['expires_at']): ?>
                                            <?php 
                                            $expires = strtotime($coupon['expires_at']);
                                            $is_expired = $expires <= time();
                                            ?>
                                            <span style="color: <?php echo $is_expired ? '#dc3545' : '#333'; ?>">
                                                <?php echo date('M j, Y', $expires); ?>
                                            </span>
                                        <?php else: ?>
                                            Never
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $is_active = $coupon['is_active'] && (!$coupon['expires_at'] || strtotime($coupon['expires_at']) > time());
                                        $is_expired = $coupon['expires_at'] && strtotime($coupon['expires_at']) <= time();
                                        ?>
                                        <span class="status-badge status-<?php echo $is_expired ? 'expired' : ($is_active ? 'active' : 'inactive'); ?>">
                                            <?php echo $is_expired ? 'Expired' : ($is_active ? 'Active' : 'Inactive'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <?php if (!$is_expired): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                                    <button type="submit" name="toggle_status" class="btn btn-outline btn-sm">
                                                        <?php echo $coupon['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                                <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                                <button type="submit" name="delete_coupon" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
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
    
    <!-- Add Coupon Modal -->
    <div id="add-coupon-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <h3>Add Coupon</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code">Coupon Code *</label>
                        <input type="text" id="code" name="code" class="form-control" required style="text-transform: uppercase;">
                    </div>
                    
                    <div class="form-group">
                        <label for="discount_type">Discount Type *</label>
                        <select id="discount_type" name="discount_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" class="form-control">
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="discount_value">Discount Value *</label>
                        <input type="number" id="discount_value" name="discount_value" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="minimum_amount">Minimum Order Amount</label>
                        <input type="number" id="minimum_amount" name="minimum_amount" class="form-control" step="0.01" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="maximum_discount">Maximum Discount (for %)</label>
                        <input type="number" id="maximum_discount" name="maximum_discount" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="usage_limit">Usage Limit</label>
                        <input type="number" id="usage_limit" name="usage_limit" class="form-control" min="1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="expires_at">Expiry Date</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" class="form-control">
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="hideAddCouponModal()">Cancel</button>
                    <button type="submit" name="add_coupon" class="btn btn-primary">Add Coupon</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        function showAddCouponModal() {
            document.getElementById('add-coupon-modal').style.display = 'block';
        }
        
        function hideAddCouponModal() {
            document.getElementById('add-coupon-modal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('add-coupon-modal');
            if (event.target === modal) {
                hideAddCouponModal();
            }
        }
        
        // Auto-generate coupon code
        $('#code').on('focus', function() {
            if (!$(this).val()) {
                const randomCode = 'SAVE' + Math.random().toString(36).substr(2, 6).toUpperCase();
                $(this).val(randomCode);
            }
        });
    </script>
</body>
</html>