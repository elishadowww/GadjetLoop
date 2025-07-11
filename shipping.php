<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Information - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .shipping-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .shipping-content {
            background: white;
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            line-height: 1.8;
        }
        
        .shipping-content h1 {
            color: #007bff;
            margin-bottom: 2rem;
        }
        
        .shipping-content h2 {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .shipping-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        .shipping-table th,
        .shipping-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .shipping-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body data-page="shipping" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="shipping-container">
            <div class="shipping-content">
                <h1>Shipping Information</h1>
                
                <h2>Shipping Options</h2>
                <table class="shipping-table">
                    <thead>
                        <tr>
                            <th>Shipping Method</th>
                            <th>Delivery Time</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Standard Shipping</td>
                            <td>5-7 business days</td>
                            <td>$9.99 (Free on orders over $50)</td>
                        </tr>
                        <tr>
                            <td>Express Shipping</td>
                            <td>2-3 business days</td>
                            <td>$19.99</td>
                        </tr>
                        <tr>
                            <td>Overnight Shipping</td>
                            <td>1 business day</td>
                            <td>$39.99</td>
                        </tr>
                    </tbody>
                </table>
                
                <h2>Shipping Policies</h2>
                <p>We ship to all 50 states in the US and select international locations. All orders are processed within 1-2 business days.</p>
                
                <h2>Order Processing</h2>
                <p>Orders placed before 2:00 PM EST Monday through Friday will be processed the same day. Weekend orders will be processed on the next business day.</p>
                
                <h2>Tracking Your Order</h2>
                <p>Once your order ships, you'll receive a tracking number via email. You can also track your order by logging into your account.</p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>