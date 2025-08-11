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
$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    header('Location: dashboard.php');
    exit;
}

// Get order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_number, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: dashboard.php');
    exit;
}

// Get order items
$order_items = getOrderItems($pdo, $order['id']);
$shipping_address = json_decode($order['shipping_address'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .confirmation-header {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .confirmation-details {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .detail-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .order-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 6px;
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
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .item-quantity {
            font-size: 14px;
            color: #666;
        }
        
        .item-price {
            font-weight: 600;
            color: #333;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-total {
            border-top: 2px solid #28a745;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .address-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
        }
        
        .next-steps {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .step:last-child {
            margin-bottom: 0;
        }
        
        .step-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .step-content h4 {
            margin: 0 0 0.25rem 0;
            color: #333;
        }
        
        .step-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body data-page="order-confirmation" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="confirmation-container">
            <div class="confirmation-header">
                <div class="success-icon">✅</div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your purchase. Your order has been successfully placed.</p>
                <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
            </div>
            
            <div class="confirmation-details">
                <!-- Order Items -->
                <div class="detail-section">
                    <h3 class="section-title">Order Items</h3>
                    <div class="order-items">
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../images/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">
                                RM<?php echo number_format($item['total'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="detail-section">
                    <h3 class="section-title">Order Summary</h3>
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
                
                <!-- Shipping Information -->
                <div class="detail-section">
                    <h3 class="section-title">Shipping Information</h3>
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
                
                <!-- Payment Information -->
                <div class="detail-section">
                    <h3 class="section-title">Payment Information</h3>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                    <p><strong>Payment Status:</strong> 
                        <span style="color: <?php echo $order['payment_status'] === 'paid' ? '#28a745' : '#ffc107'; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What happens next?</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Order Processing</h4>
                        <p>We'll process your order within 1-2 business days and prepare it for shipment.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Shipping Notification</h4>
                        <p>You'll receive an email with tracking information once your order ships.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Delivery</h4>
                        <p>Your order will be delivered to the address provided within 5-7 business days.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Leave a Review</h4>
                        <p>Share your experience by leaving a review for the products you purchased.</p>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-primary">View Order History</a>
                <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                <a href="contact.php" class="btn btn-outline">Contact Support</a>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="../js/main.js"></script>
</body>
</html>