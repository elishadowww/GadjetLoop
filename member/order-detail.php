<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    header('Location: orders.php');
    exit;
}

// Get order details
$order = getOrderById($pdo, $order_id, $user_id);

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$order_items = getOrderItems($pdo, $order_id);
$shipping_address = json_decode($order['shipping_address'], true);
$billing_address = json_decode($order['billing_address'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
    <style>
        .order-detail-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .order-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .order-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .order-items-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .order-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .sidebar-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        
        .address-info {
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
        
        @media (max-width: 768px) {
            .order-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body data-page="order-detail" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="order-detail-container">
            <!-- Order Header -->
            <div class="order-header">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div>
                        <h1>Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                        <p style="margin: 0; color: #666;">Placed on <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <div>
                        <strong>Total Amount:</strong> RM<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                    <div>
                        <strong>Payment Status:</strong> 
                        <span style="color: <?php echo $order['payment_status'] === 'paid' ? '#28a745' : '#ffc107'; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    <div>
                        <strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="order-sections">
                <!-- Order Items -->
                <div class="order-items-section">
                    <h3>Order Items (<?php echo count($order_items); ?>)</h3>
                    
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
                                <div class="item-meta">Unit Price: RM<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                            <div class="item-price">
                                RM<?php echo number_format($item['total'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Order Sidebar -->
                <div class="order-sidebar">
                    <!-- Order Summary -->
                    <div class="sidebar-card">
                        <h4>Order Summary</h4>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>RM<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>RM<?php echo number_format($order['tax_amount'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span><?php echo $order['shipping_amount'] > 0 ? 'RM' . number_format($order['shipping_amount'], 2) : 'Free'; ?></span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span>RM<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="sidebar-card">
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
                    <div class="sidebar-card">
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
                    
                    <!-- Order Actions -->
                    <div class="sidebar-card">
                        <h4>Order Actions</h4>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn btn-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                    Cancel Order
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'delivered'): ?>
                                <a href="member/reviews.php" class="btn btn-primary btn-sm">
                                    Write Review
                                </a>
                            <?php endif; ?>
                            
                            <a href="contact.php" class="btn btn-outline btn-sm">
                                Contact Support
                            </a>
                            
                            <button class="btn btn-outline btn-sm" onclick="printOrder()">
                                Print Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Back to Orders -->
            <div style="margin-top: 2rem; text-align: center;">
                <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                $.post('ajax/cancel-order.php', { order_id: orderId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel order: ' + response.message);
                    }
                });
            }
        }
        
        function printOrder() {
            window.print();
        }
    </script>
</body>
</html>