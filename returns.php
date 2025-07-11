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
    <title>Returns & Refunds - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .returns-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .returns-content {
            background: white;
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            line-height: 1.8;
        }
        
        .returns-content h1 {
            color: #007bff;
            margin-bottom: 2rem;
        }
        
        .returns-content h2 {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .highlight-box {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body data-page="returns" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="returns-container">
            <div class="returns-content">
                <h1>Returns & Refunds</h1>
                
                <div class="highlight-box">
                    <strong>30-Day Return Policy:</strong> We offer a hassle-free 30-day return policy on all products.
                </div>
                
                <h2>Return Eligibility</h2>
                <p>Items must be returned within 30 days of delivery in their original condition with all packaging, accessories, and documentation.</p>
                
                <h2>How to Return an Item</h2>
                <ol>
                    <li>Log into your account and go to "My Orders"</li>
                    <li>Find your order and click "Return Item"</li>
                    <li>Select the items you want to return and reason</li>
                    <li>Print the prepaid return label</li>
                    <li>Package the item securely and attach the label</li>
                    <li>Drop off at any authorized shipping location</li>
                </ol>
                
                <h2>Refund Processing</h2>
                <p>Refunds are processed within 3-5 business days after we receive your returned item. The refund will be credited to your original payment method.</p>
                
                <h2>Exchanges</h2>
                <p>We offer exchanges for defective items or wrong items shipped. Contact our customer service team for assistance with exchanges.</p>
                
                <h2>Non-Returnable Items</h2>
                <ul>
                    <li>Items damaged by misuse</li>
                    <li>Items returned after 30 days</li>
                    <li>Items without original packaging</li>
                    <li>Personalized or customized items</li>
                </ul>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>