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
    <title>Help Center - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .help-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .help-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .help-search {
            max-width: 500px;
            margin: 2rem auto 0;
            position: relative;
        }
        
        .help-search input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-size: 16px;
        }
        
        .help-search button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
        }
        
        .help-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .help-category {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .help-category:hover {
            transform: translateY(-5px);
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .category-links {
            list-style: none;
            padding: 0;
        }
        
        .category-links li {
            margin-bottom: 0.5rem;
        }
        
        .category-links a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        
        .category-links a:hover {
            text-decoration: underline;
        }
        
        .contact-support {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body data-page="help" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="help-container">
            <div class="help-hero">
                <h1>How can we help you?</h1>
                <p>Find answers to your questions and get the support you need</p>
                
                <div class="help-search">
                    <input type="text" placeholder="Search for help topics..." id="help-search">
                    <button type="button">🔍</button>
                </div>
            </div>
            
            <div class="help-categories">
                <div class="help-category">
                    <div class="category-icon">📦</div>
                    <h3>Orders & Shipping</h3>
                    <p>Track your orders, shipping information, and delivery details</p>
                    <ul class="category-links">
                        <li><a href="#order-status">How to check order status</a></li>
                        <li><a href="#shipping-times">Shipping times and costs</a></li>
                        <li><a href="#tracking">Package tracking</a></li>
                        <li><a href="#delivery-issues">Delivery problems</a></li>
                    </ul>
                </div>
                
                <div class="help-category">
                    <div class="category-icon">↩️</div>
                    <h3>Returns & Refunds</h3>
                    <p>Information about our return policy and refund process</p>
                    <ul class="category-links">
                        <li><a href="#return-policy">Return policy</a></li>
                        <li><a href="#start-return">How to start a return</a></li>
                        <li><a href="#refund-process">Refund processing times</a></li>
                        <li><a href="#exchange">Product exchanges</a></li>
                    </ul>
                </div>
                
                <div class="help-category">
                    <div class="category-icon">💳</div>
                    <h3>Payment & Billing</h3>
                    <p>Payment methods, billing questions, and account management</p>
                    <ul class="category-links">
                        <li><a href="#payment-methods">Accepted payment methods</a></li>
                        <li><a href="#billing-issues">Billing problems</a></li>
                        <li><a href="#payment-security">Payment security</a></li>
                        <li><a href="#invoices">Invoices and receipts</a></li>
                    </ul>
                </div>
                
                <div class="help-category">
                    <div class="category-icon">👤</div>
                    <h3>Account & Profile</h3>
                    <p>Manage your account settings and personal information</p>
                    <ul class="category-links">
                        <li><a href="#create-account">Creating an account</a></li>
                        <li><a href="#password-reset">Password reset</a></li>
                        <li><a href="#update-profile">Update profile information</a></li>
                        <li><a href="#delete-account">Delete account</a></li>
                    </ul>
                </div>
                
                <div class="help-category">
                    <div class="category-icon">📱</div>
                    <h3>Product Support</h3>
                    <p>Product information, warranties, and technical support</p>
                    <ul class="category-links">
                        <li><a href="#product-info">Product specifications</a></li>
                        <li><a href="#warranty">Warranty information</a></li>
                        <li><a href="#tech-support">Technical support</a></li>
                        <li><a href="#manuals">User manuals</a></li>
                    </ul>
                </div>
                
                <div class="help-category">
                    <div class="category-icon">🔒</div>
                    <h3>Security & Privacy</h3>
                    <p>Information about data protection and account security</p>
                    <ul class="category-links">
                        <li><a href="#privacy-policy">Privacy policy</a></li>
                        <li><a href="#data-security">Data security</a></li>
                        <li><a href="#account-security">Account security tips</a></li>
                        <li><a href="#cookies">Cookie policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="contact-support">
                <h2>Still need help?</h2>
                <p>Can't find what you're looking for? Our customer support team is here to help.</p>
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
                    <a href="contact.php" class="btn btn-primary">Contact Support</a>
                    <a href="tel:+15551234567" class="btn btn-outline">Call (555) 123-4567</a>
                    <a href="mailto:support@gadgetloop.com" class="btn btn-outline">Email Support</a>
                </div>
                
                <div style="margin-top: 2rem; font-size: 14px; color: #666;">
                    <p><strong>Support Hours:</strong></p>
                    <p>Monday - Friday: 9:00 AM - 8:00 PM EST<br>
                    Saturday: 10:00 AM - 6:00 PM EST<br>
                    Sunday: 12:00 PM - 5:00 PM EST</p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script src="/js/backtotop.js"></script>
    <script>
        $(document).ready(function() {
            // Help search functionality
            $('#help-search').on('keypress', function(e) {
                if (e.which === 13) {
                    const query = $(this).val().trim();
                    if (query) {
                        // In a real application, this would search the help database
                        alert('Searching for: ' + query);
                    }
                }
            });
            
            // Category link clicks
            $('.category-links a').on('click', function(e) {
                e.preventDefault();
                const topic = $(this).attr('href').substring(1);
                alert('This would show help content for: ' + topic);
            });
        });
    </script>
</body>
</html>