<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
$success = '';
$error = '';

if ($order_id <= 0) {
    header('Location: orders.php');
    exit;
}

// Handle order updates
if ($_POST) {
    if (isset($_POST['update_status'])) {
        $status = sanitizeInput($_POST['status']);
        $notes = sanitizeInput($_POST['notes'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $notes, $order_id]);
            
            // Create notification for customer
            $stmt = $pdo->prepare("SELECT user_id, order_number FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order_info = $stmt->fetch();
            
            if ($order_info) {
                $status_messages = [
                    'processing' => 'Your order is now being processed',
                    'shipped' => 'Your order has been shipped and is on its way',
                    'delivered' => 'Your order has been delivered successfully',
                    'cancelled' => 'Your order has been cancelled'
                ];
                
                if (isset($status_messages[$status])) {
                    createNotification($pdo, $order_info['user_id'], 'Order Update', 
                        $status_messages[$status] . ' (Order #' . $order_info['order_number'] . ')', 'order');
                }
            }
            
            $success = 'Order updated successfully';
        } catch (PDOException $e) {
            $error = 'Failed to update order';
        }
    }
    
    if (isset($_POST['add_tracking'])) {
        $tracking_number = sanitizeInput($_POST['tracking_number']);
        $carrier = sanitizeInput($_POST['carrier']);
        
        try {
            $stmt = $pdo->prepare("UPDATE orders SET tracking_number = ?, carrier = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$tracking_number, $carrier, $order_id]);
            $success = 'Tracking information added successfully';
        } catch (PDOException $e) {
            $error = 'Failed to add tracking information';
        }
    }
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$order_items = getOrderItems($pdo, $order_id);
$shipping_address = json_decode($order['shipping_address'], true);
$billing_address = json_decode($order['billing_address'], true);

// Get coupon usage if any
$stmt = $pdo->prepare("
    SELECT cu.*, c.code, c.discount_type 
    FROM coupon_usage cu 
    JOIN coupons c ON cu.coupon_id = c.id 
    WHERE cu.order_id = ?
");
$stmt->execute([$order_id]);
$coupon_usage = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlspecialchars($order['order_number']); ?> - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .order-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .order-header-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .order-status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .order-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .order-main {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .order-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .section-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .item-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            font-weight: 600;
            color: #007bff;
            font-size: 1.1rem;
        }
        
        .customer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 14px;
            color: #333;
        }
        
        .address-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            line-height: 1.6;
            color: #666;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-total {
            border-top: 2px solid #007bff;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .status-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .tracking-form {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .order-sections {
                grid-template-columns: 1fr;
            }
            
            .customer-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="order-detail-container">
                <div class="admin-header">
                    <h1>Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                    <div class="admin-actions">
                        <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
                        <button class="btn btn-primary" onclick="printOrder()">Print Order</button>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Order Header -->
                <div class="order-header-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                        <div>
                            <h2 style="margin: 0 0 0.5rem 0;">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                            <p style="margin: 0; color: #666;">Placed on <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div>
                            <span class="order-status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="customer-info">
                        <div class="info-item">
                            <span class="info-label">Customer</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['phone'] ?: 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Method</span>
                            <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Status</span>
                            <span class="info-value">
                                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Amount</span>
                            <span class="info-value"><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="order-sections">
                    <div class="order-main">
                        <!-- Order Items -->
                        <div class="section-card">
                            <h3 class="section-title">Order Items (<?php echo count($order_items); ?>)</h3>
                            <div class="order-items">
                                <?php foreach ($order_items as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="../images/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="item-meta">Quantity: <?php echo $item['quantity']; ?></div>
                                        <div class="item-meta">Unit Price: $<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                    <div class="item-price">
                                        $<?php echo number_format($item['total'], 2); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Order Status Management -->
                        <div class="section-card">
                            <h3 class="section-title">Order Management</h3>
                            
                            <div class="status-form">
                                <h4>Update Order Status</h4>
                                <form method="POST">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                        <div class="form-group">
                                            <label for="status">Order Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="notes">Admin Notes</label>
                                        <textarea id="notes" name="notes" class="form-control" rows="3" 
                                                  placeholder="Add notes about this order..."><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <button type="submit" name="update_status" class="btn btn-primary">Update Order</button>
                                </form>
                            </div>
                            
                            <?php if ($order['status'] === 'shipped' || $order['status'] === 'processing'): ?>
                            <div class="tracking-form">
                                <h4>Tracking Information</h4>
                                <form method="POST">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                        <div class="form-group">
                                            <label for="tracking_number">Tracking Number</label>
                                            <input type="text" id="tracking_number" name="tracking_number" class="form-control" 
                                                   value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="carrier">Carrier</label>
                                            <select id="carrier" name="carrier" class="form-control">
                                                <option value="">Select Carrier</option>
                                                <option value="ups" <?php echo ($order['carrier'] ?? '') === 'ups' ? 'selected' : ''; ?>>UPS</option>
                                                <option value="fedex" <?php echo ($order['carrier'] ?? '') === 'fedex' ? 'selected' : ''; ?>>FedEx</option>
                                                <option value="usps" <?php echo ($order['carrier'] ?? '') === 'usps' ? 'selected' : ''; ?>>USPS</option>
                                                <option value="dhl" <?php echo ($order['carrier'] ?? '') === 'dhl' ? 'selected' : ''; ?>>DHL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" name="add_tracking" class="btn btn-primary">Update Tracking</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-sidebar">
                        <!-- Order Summary -->
                        <div class="section-card">
                            <h4>Order Summary</h4>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax:</span>
                                <span>$<?php echo number_format($order['tax_amount'], 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span><?php echo $order['shipping_amount'] > 0 ? '$' . number_format($order['shipping_amount'], 2) : 'Free'; ?></span>
                            </div>
                            <?php if ($coupon_usage): ?>
                            <div class="summary-row" style="color: #28a745;">
                                <span>Coupon (<?php echo htmlspecialchars($coupon_usage['code']); ?>):</span>
                                <span>-$<?php echo number_format($coupon_usage['discount_amount'], 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                        
                        <!-- Customer Information -->
                        <div class="section-card">
                            <h4>Customer Information</h4>
                            <div style="margin-bottom: 1rem;">
                                <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong><br>
                                <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>"><?php echo htmlspecialchars($order['email']); ?></a>
                                <?php if ($order['phone']): ?>
                                    <br><a href="tel:<?php echo htmlspecialchars($order['phone']); ?>"><?php echo htmlspecialchars($order['phone']); ?></a>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="users.php?search=<?php echo urlencode($order['email']); ?>" class="btn btn-outline btn-sm">View User</a>
                                <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>" class="btn btn-outline btn-sm">Send Email</a>
                            </div>
                        </div>
                        
                        <!-- Shipping Address -->
                        <div class="section-card">
                            <h4>Shipping Address</h4>
                            <div class="address-info">
                                <strong><?php echo htmlspecialchars($shipping_address['first_name'] . ' ' . $shipping_address['last_name']); ?></strong><br>
                                <?php echo htmlspecialchars($shipping_address['address']); ?><br>
                                <?php echo htmlspecialchars($shipping_address['city'] . ', ' . $shipping_address['state'] . ' ' . $shipping_address['zip_code']); ?><br>
                                <?php echo htmlspecialchars($shipping_address['country']); ?>
                                <?php if (!empty($shipping_address['phone'])): ?>
                                    <br>Phone: <?php echo htmlspecialchars($shipping_address['phone']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Billing Address -->
                        <?php if ($billing_address && $billing_address !== $shipping_address): ?>
                        <div class="section-card">
                            <h4>Billing Address</h4>
                            <div class="address-info">
                                <strong><?php echo htmlspecialchars($billing_address['first_name'] . ' ' . $billing_address['last_name']); ?></strong><br>
                                <?php echo htmlspecialchars($billing_address['address']); ?><br>
                                <?php echo htmlspecialchars($billing_address['city'] . ', ' . $billing_address['state'] . ' ' . $billing_address['zip_code']); ?><br>
                                <?php echo htmlspecialchars($billing_address['country']); ?>
                                <?php if (!empty($billing_address['phone'])): ?>
                                    <br>Phone: <?php echo htmlspecialchars($billing_address['phone']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Order Timeline -->
                        <div class="section-card">
                            <h4>Order Timeline</h4>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="width: 8px; height: 8px; background: #28a745; border-radius: 50%;"></span>
                                    <div>
                                        <strong>Order Placed</strong><br>
                                        <small><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></small>
                                    </div>
                                </div>
                                
                                <?php if ($order['status'] !== 'pending'): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="width: 8px; height: 8px; background: #007bff; border-radius: 50%;"></span>
                                    <div>
                                        <strong>Status: <?php echo ucfirst($order['status']); ?></strong><br>
                                        <small><?php echo date('M j, Y g:i A', strtotime($order['updated_at'])); ?></small>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($order['tracking_number']): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="width: 8px; height: 8px; background: #ffc107; border-radius: 50%;"></span>
                                    <div>
                                        <strong>Tracking Added</strong><br>
                                        <small><?php echo htmlspecialchars($order['carrier']); ?>: <?php echo htmlspecialchars($order['tracking_number']); ?></small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        function printOrder() {
            window.print();
        }
        
        // Auto-refresh order status every 30 seconds
        setInterval(function() {
            // In a real application, you might want to check for status updates
        }, 30000);
    </script>
</body>
</html>