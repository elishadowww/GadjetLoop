<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

// Get cart items
$cart_items = getCartItems($pdo, $user_id);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Check if we have order data from checkout
if (!$_POST || !isset($_POST['place_order'])) {
    header('Location: checkout.php');
    exit;
}

// Get order data from POST
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

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['sale_price'] * $item['quantity'];
}

$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 50 ? 0 : 9.99; // Free shipping over $50
$total = $subtotal + $tax + $shipping;

// Store order data in session for processing
$_SESSION['pending_order'] = [
    'cart_items' => $cart_items,
    'shipping_address' => $shipping_address,
    'billing_address' => $billing_address,
    'payment_method' => $payment_method,
    'totals' => [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'shipping' => $shipping,
        'total' => $total
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .payment-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            color: white;
            text-align: center;
        }
        
        .payment-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            color: #333;
            backdrop-filter: blur(10px);
        }
        
        .payment-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        .payment-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .payment-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #e3f2fd;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 2rem auto;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 2rem 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #28a745);
            border-radius: 4px;
            width: 0%;
            animation: progress 4s ease-in-out forwards;
        }
        
        .payment-steps {
            text-align: left;
            margin: 2rem 0;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            opacity: 0.5;
            transition: all 0.5s ease;
        }
        
        .step.active {
            opacity: 1;
            color: #007bff;
            font-weight: 600;
        }
        
        .step.completed {
            opacity: 1;
            color: #28a745;
        }
        
        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 12px;
            transition: all 0.5s ease;
        }
        
        .step.active .step-icon {
            background: #007bff;
            color: white;
        }
        
        .step.completed .step-icon {
            background: #28a745;
            color: white;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
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
        
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .security-badge {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes progress {
            0% { width: 0%; }
            25% { width: 30%; }
            50% { width: 60%; }
            75% { width: 85%; }
            100% { width: 100%; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .payment-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @media (max-width: 768px) {
            .payment-container {
                padding: 1rem;
            }
            
            .payment-card {
                padding: 2rem;
            }
            
            .payment-title {
                font-size: 1.5rem;
            }
            
            .payment-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <div class="payment-icon">💳</div>
            <h1 class="payment-title">Processing Payment</h1>
            <p class="payment-subtitle">Please wait while we securely process your payment...</p>
            
            <div class="loading-spinner"></div>
            
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            
            <div class="payment-steps">
                <div class="step active" id="step-1">
                    <div class="step-icon">1</div>
                    <span>Validating payment information</span>
                </div>
                <div class="step" id="step-2">
                    <div class="step-icon">2</div>
                    <span>Processing payment</span>
                </div>
                <div class="step" id="step-3">
                    <div class="step-icon">3</div>
                    <span>Confirming order</span>
                </div>
                <div class="step" id="step-4">
                    <div class="step-icon">4</div>
                    <span>Preparing shipment</span>
                </div>
            </div>
            
            <div class="order-summary">
                <h4 style="margin-bottom: 1rem; color: #333;">Order Summary</h4>
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
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <div class="security-badges">
                <div class="security-badge">
                    🔒 SSL Encrypted
                </div>
                <div class="security-badge">
                    🛡️ Secure Payment
                </div>
                <div class="security-badge">
                    ✅ PCI Compliant
                </div>
            </div>
            
            <p style="font-size: 12px; color: #999; margin-top: 2rem;">
                Do not refresh or close this page during payment processing
            </p>
        </div>
    </div>
    
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        const stepDuration = 1000; // 1 second per step
        
        function processPayment() {
            const steps = [
                { id: 1, message: 'Validating payment information...', duration: 1000 },
                { id: 2, message: 'Processing payment with bank...', duration: 1500 },
                { id: 3, message: 'Confirming order details...', duration: 1000 },
                { id: 4, message: 'Preparing for shipment...', duration: 500 }
            ];
            
            let currentStepIndex = 0;
            
            function nextStep() {
                if (currentStepIndex > 0) {
                    // Mark previous step as completed
                    const prevStep = document.getElementById(`step-${currentStepIndex}`);
                    prevStep.classList.remove('active');
                    prevStep.classList.add('completed');
                    prevStep.querySelector('.step-icon').innerHTML = '✓';
                }
                
                currentStepIndex++;
                
                if (currentStepIndex <= totalSteps) {
                    // Activate current step
                    const currentStep = document.getElementById(`step-${currentStepIndex}`);
                    currentStep.classList.add('active');
                    
                    // Update title
                    document.querySelector('.payment-subtitle').textContent = steps[currentStepIndex - 1].message;
                    
                    // Schedule next step
                    setTimeout(nextStep, steps[currentStepIndex - 1].duration);
                } else {
                    // Payment complete, process the order
                    completeOrder();
                }
            }
            
            // Start the process
            setTimeout(nextStep, 500);
        }
        
        function completeOrder() {
            // Show completion message
            document.querySelector('.payment-title').textContent = 'Payment Successful!';
            document.querySelector('.payment-subtitle').textContent = 'Creating your order...';
            document.querySelector('.payment-icon').textContent = '✅';
            
            // Hide loading spinner
            document.querySelector('.loading-spinner').style.display = 'none';
            
            // Submit the actual order
            setTimeout(() => {
                // Create a form to submit the order data
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'complete-order.php';
                
                // Add all the checkout data as hidden inputs
                <?php foreach ($_POST as $key => $value): ?>
                    <?php if (is_array($value)): ?>
                        <?php foreach ($value as $subKey => $subValue): ?>
                            const input_<?php echo $key; ?>_<?php echo $subKey; ?> = document.createElement('input');
                            input_<?php echo $key; ?>_<?php echo $subKey; ?>.type = 'hidden';
                            input_<?php echo $key; ?>_<?php echo $subKey; ?>.name = '<?php echo $key; ?>[<?php echo $subKey; ?>]';
                            input_<?php echo $key; ?>_<?php echo $subKey; ?>.value = '<?php echo htmlspecialchars($subValue); ?>';
                            form.appendChild(input_<?php echo $key; ?>_<?php echo $subKey; ?>);
                        <?php endforeach; ?>
                    <?php else: ?>
                        const input_<?php echo $key; ?> = document.createElement('input');
                        input_<?php echo $key; ?>.type = 'hidden';
                        input_<?php echo $key; ?>.name = '<?php echo $key; ?>';
                        input_<?php echo $key; ?>.value = '<?php echo htmlspecialchars($value); ?>';
                        form.appendChild(input_<?php echo $key; ?>);
                    <?php endif; ?>
                <?php endforeach; ?>
                
                document.body.appendChild(form);
                form.submit();
            }, 1000);
        }
        
        // Start payment processing when page loads
        document.addEventListener('DOMContentLoaded', function() {
            processPayment();
        });
        
        // Prevent page refresh/back button during processing
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = 'Payment is being processed. Are you sure you want to leave?';
        });
        
        // Disable back button
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
        };
    </script>
</body>
</html>