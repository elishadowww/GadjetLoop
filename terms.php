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
    <title>Terms of Service - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .terms-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .terms-content {
            background: white;
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            line-height: 1.8;
        }
        
        .terms-content h1 {
            color: #007bff;
            margin-bottom: 1rem;
        }
        
        .terms-content h2 {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .terms-content p {
            margin-bottom: 1rem;
            color: #666;
        }
        
        .terms-content ul {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .terms-content li {
            margin-bottom: 0.5rem;
            color: #666;
        }
        
        .last-updated {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body data-page="terms" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="terms-container">
            <div class="terms-content">
                <h1>Terms of Service</h1>
                
                <div class="last-updated">
                    <strong>Last Updated:</strong> <?php echo date('F j, Y'); ?>
                </div>
                
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using GadgetLoop's website and services, you accept and agree to be bound by the terms and provision of this agreement.</p>
                
                <h2>2. Use License</h2>
                <p>Permission is granted to temporarily download one copy of the materials on GadgetLoop's website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                <ul>
                    <li>Modify or copy the materials</li>
                    <li>Use the materials for any commercial purpose or for any public display</li>
                    <li>Attempt to reverse engineer any software contained on the website</li>
                    <li>Remove any copyright or other proprietary notations from the materials</li>
                </ul>
                
                <h2>3. Account Registration</h2>
                <p>To access certain features of our service, you must register for an account. You agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain and update your information to keep it accurate</li>
                    <li>Maintain the security of your password</li>
                    <li>Accept responsibility for all activities under your account</li>
                </ul>
                
                <h2>4. Product Information and Pricing</h2>
                <p>We strive to provide accurate product descriptions and pricing. However:</p>
                <ul>
                    <li>Product images are for illustration purposes and may not reflect exact appearance</li>
                    <li>Prices are subject to change without notice</li>
                    <li>We reserve the right to correct pricing errors</li>
                    <li>Product availability is not guaranteed</li>
                </ul>
                
                <h2>5. Orders and Payment</h2>
                <p>By placing an order, you agree that:</p>
                <ul>
                    <li>All information provided is accurate and complete</li>
                    <li>You are authorized to use the payment method</li>
                    <li>We may cancel orders for any reason</li>
                    <li>Payment is due at the time of order</li>
                </ul>
                
                <h2>6. Shipping and Delivery</h2>
                <p>Shipping times are estimates and not guaranteed. We are not responsible for delays caused by:</p>
                <ul>
                    <li>Weather conditions</li>
                    <li>Carrier delays</li>
                    <li>Customs processing</li>
                    <li>Incorrect shipping information</li>
                </ul>
                
                <h2>7. Returns and Refunds</h2>
                <p>We offer a 30-day return policy for most items. Returns must be:</p>
                <ul>
                    <li>In original condition and packaging</li>
                    <li>Accompanied by proof of purchase</li>
                    <li>Initiated within 30 days of delivery</li>
                    <li>Subject to our return policy terms</li>
                </ul>
                
                <h2>8. Prohibited Uses</h2>
                <p>You may not use our service:</p>
                <ul>
                    <li>For any unlawful purpose or to solicit others to unlawful acts</li>
                    <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                    <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                    <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                    <li>To submit false or misleading information</li>
                </ul>
                
                <h2>9. Intellectual Property</h2>
                <p>The service and its original content, features, and functionality are and will remain the exclusive property of GadgetLoop and its licensors.</p>
                
                <h2>10. Disclaimer</h2>
                <p>The materials on GadgetLoop's website are provided on an 'as is' basis. GadgetLoop makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                
                <h2>11. Limitations</h2>
                <p>In no event shall GadgetLoop or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on GadgetLoop's website.</p>
                
                <h2>12. Accuracy of Materials</h2>
                <p>The materials appearing on GadgetLoop's website could include technical, typographical, or photographic errors. GadgetLoop does not warrant that any of the materials on its website are accurate, complete, or current.</p>
                
                <h2>13. Links</h2>
                <p>GadgetLoop has not reviewed all of the sites linked to our website and is not responsible for the contents of any such linked site.</p>
                
                <h2>14. Modifications</h2>
                <p>GadgetLoop may revise these terms of service for its website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.</p>
                
                <h2>15. Governing Law</h2>
                <p>These terms and conditions are governed by and construed in accordance with the laws of the United States and you irrevocably submit to the exclusive jurisdiction of the courts in that state or location.</p>
                
                <h2>16. Contact Information</h2>
                <p>If you have any questions about these Terms of Service, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> legal@gadgetloop.com</li>
                    <li><strong>Phone:</strong> (555) 123-4567</li>
                    <li><strong>Address:</strong> 123 Tech Street, Digital City, DC 12345</li>
                </ul>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>