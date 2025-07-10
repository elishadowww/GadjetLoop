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
    <title>Privacy Policy - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .policy-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .policy-content {
            background: white;
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            line-height: 1.8;
        }
        
        .policy-content h1 {
            color: #007bff;
            margin-bottom: 1rem;
        }
        
        .policy-content h2 {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .policy-content p {
            margin-bottom: 1rem;
            color: #666;
        }
        
        .policy-content ul {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .policy-content li {
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
<body data-page="privacy" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="policy-container">
            <div class="policy-content">
                <h1>Privacy Policy</h1>
                
                <div class="last-updated">
                    <strong>Last Updated:</strong> <?php echo date('F j, Y'); ?>
                </div>
                
                <h2>1. Information We Collect</h2>
                <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support.</p>
                
                <h3>Personal Information</h3>
                <ul>
                    <li>Name and contact information (email, phone, address)</li>
                    <li>Payment information (processed securely through our payment partners)</li>
                    <li>Account credentials and preferences</li>
                    <li>Purchase history and product reviews</li>
                </ul>
                
                <h3>Automatically Collected Information</h3>
                <ul>
                    <li>Device and browser information</li>
                    <li>IP address and location data</li>
                    <li>Website usage and navigation patterns</li>
                    <li>Cookies and similar tracking technologies</li>
                </ul>
                
                <h2>2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Process and fulfill your orders</li>
                    <li>Provide customer support and respond to inquiries</li>
                    <li>Send important updates about your account and orders</li>
                    <li>Improve our website and services</li>
                    <li>Personalize your shopping experience</li>
                    <li>Prevent fraud and ensure security</li>
                    <li>Comply with legal obligations</li>
                </ul>
                
                <h2>3. Information Sharing</h2>
                <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following circumstances:</p>
                <ul>
                    <li><strong>Service Providers:</strong> With trusted partners who help us operate our business</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                    <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                    <li><strong>Consent:</strong> When you explicitly consent to sharing</li>
                </ul>
                
                <h2>4. Data Security</h2>
                <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. This includes:</p>
                <ul>
                    <li>SSL encryption for data transmission</li>
                    <li>Secure payment processing</li>
                    <li>Regular security audits and updates</li>
                    <li>Access controls and employee training</li>
                </ul>
                
                <h2>5. Cookies and Tracking</h2>
                <p>We use cookies and similar technologies to enhance your browsing experience, analyze website traffic, and personalize content. You can control cookie settings through your browser preferences.</p>
                
                <h2>6. Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Access and update your personal information</li>
                    <li>Delete your account and associated data</li>
                    <li>Opt out of marketing communications</li>
                    <li>Request a copy of your data</li>
                    <li>Object to certain data processing activities</li>
                </ul>
                
                <h2>7. Data Retention</h2>
                <p>We retain your personal information for as long as necessary to provide our services, comply with legal obligations, resolve disputes, and enforce our agreements.</p>
                
                <h2>8. Children's Privacy</h2>
                <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.</p>
                
                <h2>9. International Transfers</h2>
                <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data.</p>
                
                <h2>10. Changes to This Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on our website and updating the "Last Updated" date.</p>
                
                <h2>11. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> privacy@gadgetloop.com</li>
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