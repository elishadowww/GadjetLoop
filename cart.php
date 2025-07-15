<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle cart updates
if ($_POST) {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = intval($quantity);
            
            if ($quantity <= 0) {
                // Remove item
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            } else {
                // Update quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $user_id, $product_id]);
            }
        }
        $success = 'Cart updated successfully';
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $success = 'Item removed from cart';
    }
    
    if (isset($_POST['clear_cart'])) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $success = 'Cart cleared';
    }
}

// Get cart items
$cart_items = getCartItems($pdo, $user_id);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['sale_price'] * $item['quantity'];
}

$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 50 ? 0 : 9.99; // Free shipping over $50
$total = $subtotal + $tax + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .cart-items {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .item-details h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .item-price {
            color: #666;
            font-size: 14px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .qty-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.25rem;
        }
        
        .cart-summary {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .summary-total {
            border-top: 2px solid #007bff;
            padding-top: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
            }
        }
    </style>
</head>
<body data-page="cart" class="logged-in">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="cart-container">
            <h1>Shopping Cart</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <h3>Cart Items (<?php echo count($cart_items); ?>)</h3>
                        
                        <form method="POST">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="images/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <div class="item-price">
                                        <?php if ($item['discount_percentage'] > 0): ?>
                                            <span style="text-decoration: line-through; color: #999;">$<?php echo number_format($item['price'], 2); ?></span>
                                            $<?php echo number_format($item['sale_price'], 2); ?>
                                        <?php else: ?>
                                            $<?php echo number_format($item['price'], 2); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                    <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                           class="qty-input">
                                    <button type="button" class="qty-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                </div>
                                
                                <div class="item-total">
                                    <strong>$<?php echo number_format($item['sale_price'] * $item['quantity'], 2); ?></strong>
                                </div>
                                
                                <div class="item-actions">
                                    <button type="submit" name="remove_item" value="1" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Remove this item?')">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                                <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                                <button type="submit" name="clear_cart" class="btn btn-outline" 
                                        onclick="return confirm('Clear entire cart?')">Clear Cart</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                        </div>
                        
                        <?php if ($subtotal < 50 && $subtotal > 0): ?>
                            <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin: 1rem 0; font-size: 14px;">
                                Add $<?php echo number_format(50 - $subtotal, 2); ?> more for free shipping!
                            </div>
                        <?php endif; ?>
                        
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top: 2rem;">
                            Proceed to Checkout
                        </a>
                        
                        <a href="products.php" class="btn btn-outline btn-block" style="margin-top: 1rem;">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        function updateQuantity(productId, change) {
            const input = $(`input[name="quantities[${productId}]"]`);
            const currentVal = parseInt(input.val()) || 1;
            const max = parseInt(input.attr('max')) || 999;
            const newVal = Math.max(1, Math.min(max, currentVal + change));
            input.val(newVal);
        }
    </script>
</body>
</html>