<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

$success = '';
$error = '';

// Get cart items
$cart_items = getCartItems($pdo, $user_id);

if (empty($cart_items)) {
    header('Location: ../cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['sale_price'] * $item['quantity'];
}

$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 50 ? 0 : 9.99; // Free shipping over $50
$total = $subtotal + $tax + $shipping;

// Handle order submission
if ($_POST && isset($_POST['place_order'])) {
    $shipping_address = [
        'first_name' => sanitizeInput($_POST['shipping_first_name']),
        'last_name' => sanitizeInput($_POST['shipping_last_name']),
        'address' => sanitizeInput($_POST['shipping_address']),
        'city' => sanitizeInput($_POST['shipping_city']),
        'state' => sanitizeInput($_POST['shipping_state']),
        'zip_code' => sanitizeInput($_POST['shipping_zip']),
        'country' => sanitizeInput($_POST['shipping_country']),
        'phone' => sanitizeInput($_POST['shipping_phone'])
    ];
    
    $billing_address = $shipping_address;
    if (isset($_POST['different_billing'])) {
        $billing_address = [
            'first_name' => sanitizeInput($_POST['billing_first_name']),
            'last_name' => sanitizeInput($_POST['billing_last_name']),
            'address' => sanitizeInput($_POST['billing_address']),
            'city' => sanitizeInput($_POST['billing_city']),
            'state' => sanitizeInput($_POST['billing_state']),
            'zip_code' => sanitizeInput($_POST['billing_zip']),
            'country' => sanitizeInput($_POST['billing_country']),
            'phone' => sanitizeInput($_POST['billing_phone'])
        ];
    }
    
    $payment_method = sanitizeInput($_POST['payment_method']);
    
    // Validate required fields
    if (empty($shipping_address['first_name']) || empty($shipping_address['last_name']) || 
        empty($shipping_address['address']) || empty($shipping_address['city']) || 
        empty($payment_method)) {
        $error = 'Please fill in all required fields';
    } else {
        // Create order
        $order_result = createOrder($pdo, $user_id, $cart_items, $shipping_address, $billing_address, $payment_method);
        
        if ($order_result['success']) {
            // Create notification for order confirmation
            createNotification($pdo, $user_id, 'Order Confirmed', 
                'Your order #' . $order_result['order_number'] . ' has been confirmed and is being processed.', 'order');
            
            // Redirect to order confirmation
            header('Location: order-confirmation.php?order=' . $order_result['order_number']);
            exit;
        } else {
            $error = $order_result['message'];
        }
    }
}

// Get user's default addresses
$default_shipping = getDefaultAddress($pdo, $user_id, 'shipping');
$default_billing = getDefaultAddress($pdo, $user_id, 'billing');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .checkout-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            align-items: start;
        }
        
        .checkout-form {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .billing-address {
            display: none;
        }
        
        .billing-address.show {
            display: block;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #007bff;
        }
        
        .payment-method.selected {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        
        .payment-details {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .payment-details.show {
            display: block;
        }
        
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
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
            border-top: 2px solid #007bff;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: bold;
        }
        
        .place-order-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        
        .saved-address {
            background: #e3f2fd;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .saved-address:hover {
            background: #bbdefb;
        }
        
        .saved-address.selected {
            background: #1976d2;
            color: white;
        }
        
        .address-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .use-address-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .checkout-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body data-page="checkout" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="checkout-container">
            <h1>Checkout</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="checkout-layout">
                <div class="checkout-form">
                    <form method="POST" id="checkout-form">
                        <!-- Shipping Address -->
                        <div class="form-section">
                            <h3 class="section-title">🚚 Shipping Address</h3>
                            
                            <?php if ($default_shipping): ?>
                                <div class="saved-address" onclick="useAddress('shipping', <?php echo htmlspecialchars(json_encode($default_shipping)); ?>)">
                                    <div class="address-option">
                                        <div>
                                            <strong>Default Shipping Address</strong><br>
                                            <?php echo htmlspecialchars($default_shipping['first_name'] . ' ' . $default_shipping['last_name']); ?><br>
                                            <?php echo htmlspecialchars($default_shipping['address_line_1']); ?><br>
                                            <?php echo htmlspecialchars($default_shipping['city'] . ', ' . $default_shipping['state'] . ' ' . $default_shipping['zip_code']); ?>
                                        </div>
                                        <button type="button" class="use-address-btn">Use This Address</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_first_name">First Name *</label>
                                    <input type="text" id="shipping_first_name" name="shipping_first_name" 
                                           class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_last_name">Last Name *</label>
                                    <input type="text" id="shipping_last_name" name="shipping_last_name" 
                                           class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_address">Address *</label>
                                <input type="text" id="shipping_address" name="shipping_address" 
                                       class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_city">City *</label>
                                    <input type="text" id="shipping_city" name="shipping_city" 
                                           class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_state">State *</label>
                                    <input type="text" id="shipping_state" name="shipping_state" 
                                           class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="shipping_zip">ZIP Code *</label>
                                    <input type="text" id="shipping_zip" name="shipping_zip" 
                                           class="form-control" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="shipping_country">Country *</label>
                                    <select id="shipping_country" name="shipping_country" class="form-control" required>
                                        <option value="">Select Country</option>
                                        <option value="US" <?php echo ($user['country'] ?? '') === 'US' ? 'selected' : ''; ?>>United States</option>
                                        <option value="CA" <?php echo ($user['country'] ?? '') === 'CA' ? 'selected' : ''; ?>>Canada</option>
                                        <option value="UK" <?php echo ($user['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="AU" <?php echo ($user['country'] ?? '') === 'AU' ? 'selected' : ''; ?>>Australia</option>
                                        <option value="DE" <?php echo ($user['country'] ?? '') === 'DE' ? 'selected' : ''; ?>>Germany</option>
                                        <option value="FR" <?php echo ($user['country'] ?? '') === 'FR' ? 'selected' : ''; ?>>France</option>
                                        <option value="IT" <?php echo ($user['country'] ?? '') === 'IT' ? 'selected' : ''; ?>>Italy</option>
                                        <option value="ES" <?php echo ($user['country'] ?? '') === 'ES' ? 'selected' : ''; ?>>Spain</option>
                                        <option value="NL" <?php echo ($user['country'] ?? '') === 'NL' ? 'selected' : ''; ?>>Netherlands</option>
                                        <option value="BE" <?php echo ($user['country'] ?? '') === 'BE' ? 'selected' : ''; ?>>Belgium</option>
                                        <option value="CH" <?php echo ($user['country'] ?? '') === 'CH' ? 'selected' : ''; ?>>Switzerland</option>
                                        <option value="AT" <?php echo ($user['country'] ?? '') === 'AT' ? 'selected' : ''; ?>>Austria</option>
                                        <option value="SE" <?php echo ($user['country'] ?? '') === 'SE' ? 'selected' : ''; ?>>Sweden</option>
                                        <option value="NO" <?php echo ($user['country'] ?? '') === 'NO' ? 'selected' : ''; ?>>Norway</option>
                                        <option value="DK" <?php echo ($user['country'] ?? '') === 'DK' ? 'selected' : ''; ?>>Denmark</option>
                                        <option value="FI" <?php echo ($user['country'] ?? '') === 'FI' ? 'selected' : ''; ?>>Finland</option>
                                        <option value="JP" <?php echo ($user['country'] ?? '') === 'JP' ? 'selected' : ''; ?>>Japan</option>
                                        <option value="KR" <?php echo ($user['country'] ?? '') === 'KR' ? 'selected' : ''; ?>>South Korea</option>
                                        <option value="SG" <?php echo ($user['country'] ?? '') === 'SG' ? 'selected' : ''; ?>>Singapore</option>
                                        <option value="HK" <?php echo ($user['country'] ?? '') === 'HK' ? 'selected' : ''; ?>>Hong Kong</option>
                                        <option value="NZ" <?php echo ($user['country'] ?? '') === 'NZ' ? 'selected' : ''; ?>>New Zealand</option>
                                        <option value="BR" <?php echo ($user['country'] ?? '') === 'BR' ? 'selected' : ''; ?>>Brazil</option>
                                        <option value="MX" <?php echo ($user['country'] ?? '') === 'MX' ? 'selected' : ''; ?>>Mexico</option>
                                        <option value="IN" <?php echo ($user['country'] ?? '') === 'IN' ? 'selected' : ''; ?>>India</option>
                                        <option value="CN" <?php echo ($user['country'] ?? '') === 'CN' ? 'selected' : ''; ?>>China</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_phone">Phone Number</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" 
                                       class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Billing Address -->
                        <div class="form-section">
                            <div class="form-check">
                                <input type="checkbox" id="different_billing" name="different_billing">
                                <label for="different_billing">Use different billing address</label>
                            </div>
                            
                            <div class="billing-address" id="billing-address">
                                <h3 class="section-title">💳 Billing Address</h3>
                                
                                <?php if ($default_billing): ?>
                                    <div class="saved-address" onclick="useAddress('billing', <?php echo htmlspecialchars(json_encode($default_billing)); ?>)">
                                        <div class="address-option">
                                            <div>
                                                <strong>Default Billing Address</strong><br>
                                                <?php echo htmlspecialchars($default_billing['first_name'] . ' ' . $default_billing['last_name']); ?><br>
                                                <?php echo htmlspecialchars($default_billing['address_line_1']); ?><br>
                                                <?php echo htmlspecialchars($default_billing['city'] . ', ' . $default_billing['state'] . ' ' . $default_billing['zip_code']); ?>
                                            </div>
                                            <button type="button" class="use-address-btn">Use This Address</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="billing_first_name">First Name *</label>
                                        <input type="text" id="billing_first_name" name="billing_first_name" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="billing_last_name">Last Name *</label>
                                        <input type="text" id="billing_last_name" name="billing_last_name" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="billing_address">Address *</label>
                                    <input type="text" id="billing_address" name="billing_address" class="form-control">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="billing_city">City *</label>
                                        <input type="text" id="billing_city" name="billing_city" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="billing_state">State *</label>
                                        <input type="text" id="billing_state" name="billing_state" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="billing_zip">ZIP Code *</label>
                                        <input type="text" id="billing_zip" name="billing_zip" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="billing_country">Country *</label>
                                        <select id="billing_country" name="billing_country" class="form-control">
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <option value="DE">Germany</option>
                                            <option value="FR">France</option>
                                            <option value="IT">Italy</option>
                                            <option value="ES">Spain</option>
                                            <option value="NL">Netherlands</option>
                                            <option value="BE">Belgium</option>
                                            <option value="CH">Switzerland</option>
                                            <option value="AT">Austria</option>
                                            <option value="SE">Sweden</option>
                                            <option value="NO">Norway</option>
                                            <option value="DK">Denmark</option>
                                            <option value="FI">Finland</option>
                                            <option value="JP">Japan</option>
                                            <option value="KR">South Korea</option>
                                            <option value="SG">Singapore</option>
                                            <option value="HK">Hong Kong</option>
                                            <option value="NZ">New Zealand</option>
                                            <option value="BR">Brazil</option>
                                            <option value="MX">Mexico</option>
                                            <option value="IN">India</option>
                                            <option value="CN">China</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3 class="section-title">💳 Payment Method</h3>
                            
                            <div class="payment-methods">
                                <div class="payment-method" data-method="credit_card">
                                    <input type="radio" name="payment_method" value="credit_card" id="credit_card" required>
                                    <label for="credit_card">💳 Credit/Debit Card</label>
                                </div>
                                
                                <div class="payment-method" data-method="paypal">
                                    <input type="radio" name="payment_method" value="paypal" id="paypal">
                                    <label for="paypal">🅿️ PayPal</label>
                                </div>
                                
                                <div class="payment-method" data-method="apple_pay">
                                    <input type="radio" name="payment_method" value="apple_pay" id="apple_pay">
                                    <label for="apple_pay">🍎 Apple Pay</label>
                                </div>
                            </div>
                            
                            <div class="payment-details" id="credit_card_details">
                                <div class="form-group">
                                    <label for="card_number">Card Number *</label>
                                    <input type="text" id="card_number" name="card_number" class="form-control" 
                                           placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date *</label>
                                        <input type="text" id="expiry_date" name="expiry_date" class="form-control" 
                                               placeholder="MM/YY" maxlength="5">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV *</label>
                                        <input type="text" id="cvv" name="cvv" class="form-control" 
                                               placeholder="123" maxlength="4">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="card_name">Name on Card *</label>
                                    <input type="text" id="card_name" name="card_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary place-order-btn">
                            Place Order - RM<?php echo number_format($total, 2); ?>
                        </button>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="../images/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">
                                RM<?php echo number_format($item['sale_price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>RM<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span>RM<?php echo number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span><?php echo $shipping > 0 ? 'RM' . number_format($shipping, 2) : 'Free'; ?></span>
                        </div>
                        
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span>RM<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle billing address
            $('#different_billing').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#billing-address').addClass('show');
                } else {
                    $('#billing-address').removeClass('show');
                }
            });
            
            // Payment method selection
            $('.payment-method').on('click', function() {
                const method = $(this).data('method');
                const radio = $(this).find('input[type="radio"]');
                
                // Update visual selection
                $('.payment-method').removeClass('selected');
                $(this).addClass('selected');
                
                // Check radio button
                radio.prop('checked', true);
                
                // Show/hide payment details
                $('.payment-details').removeClass('show');
                if (method === 'credit_card') {
                    $('#credit_card_details').addClass('show');
                }
            });
            
            // Card number formatting
            $('#card_number').on('input', function() {
                let value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                $(this).val(formattedValue);
            });
            
            // Expiry date formatting
            $('#expiry_date').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                $(this).val(value);
            });
            
            // CVV validation
            $('#cvv').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(value);
            });
            
            // Form validation
            $('#checkout-form').on('submit', function(e) {
                const paymentMethod = $('input[name="payment_method"]:checked').val();
                
                if (paymentMethod === 'credit_card') {
                    const cardNumber = $('#card_number').val().replace(/\s/g, '');
                    const expiryDate = $('#expiry_date').val();
                    const cvv = $('#cvv').val();
                    const cardName = $('#card_name').val();
                    
                    if (!cardNumber || cardNumber.length < 13 || !expiryDate || !cvv || !cardName) {
                        e.preventDefault();
                        alert('Please fill in all credit card details');
                        return false;
                    }
                }
                
                // Show loading state
                $('.place-order-btn').prop('disabled', true).html('<span class="loading"></span> Processing Order...');
            });
        });
        
        function useAddress(type, address) {
            const prefix = type === 'shipping' ? 'shipping_' : 'billing_';
            
            $(`#${prefix}first_name`).val(address.first_name);
            $(`#${prefix}last_name`).val(address.last_name);
            $(`#${prefix}address`).val(address.address_line_1);
            $(`#${prefix}city`).val(address.city);
            $(`#${prefix}state`).val(address.state);
            $(`#${prefix}zip`).val(address.zip_code);
            $(`#${prefix}country`).val(address.country);
            
            if (address.phone) {
                $(`#${prefix}phone`).val(address.phone);
            }
            
            // Visual feedback
            $(`.saved-address`).removeClass('selected');
            $(event.target).closest('.saved-address').addClass('selected');
        }
    </script>
</body>
</html>