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
    <title>Warranty Information - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .warranty-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .warranty-content {
            background: white;
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            line-height: 1.8;
        }
        
        .warranty-content h1 {
            color: #007bff;
            margin-bottom: 2rem;
        }
        
        .warranty-content h2 {
            color: #333;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .warranty-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        .warranty-table th,
        .warranty-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .warranty-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body data-page="warranty" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="warranty-container">
            <div class="warranty-content">
                <h1>Warranty Information</h1>
                
                <h2>Manufacturer Warranties</h2>
                <p>All products sold by GadgetLoop come with manufacturer warranties. Warranty periods vary by product and manufacturer.</p>
                
                <h2>Warranty Coverage by Category</h2>
                <table class="warranty-table">
                    <thead>
                        <tr>
                            <th>Product Category</th>
                            <th>Warranty Period</th>
                            <th>Coverage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Smartphones</td>
                            <td>1 Year</td>
                            <td>Manufacturing defects, hardware failures</td>
                        </tr>
                        <tr>
                            <td>Laptops</td>
                            <td>1-3 Years</td>
                            <td>Hardware defects, component failures</td>
                        </tr>
                        <tr>
                            <td>Audio Equipment</td>
                            <td>1-2 Years</td>
                            <td>Manufacturing defects, speaker/driver issues</td>
                        </tr>
                        <tr>
                            <td>Accessories</td>
                            <td>90 Days - 1 Year</td>
                            <td>Manufacturing defects</td>
                        </tr>
                    </tbody>
                </table>
                
                <h2>What's Covered</h2>
                <ul>
                    <li>Manufacturing defects</li>
                    <li>Hardware component failures</li>
                    <li>Software issues (when applicable)</li>
                    <li>Normal wear and tear (limited coverage)</li>
                </ul>
                
                <h2>What's Not Covered</h2>
                <ul>
                    <li>Physical damage from drops or impacts</li>
                    <li>Water damage</li>
                    <li>Damage from misuse or abuse</li>
                    <li>Cosmetic damage that doesn't affect functionality</li>
                    <li>Damage from unauthorized repairs</li>
                </ul>
                
                <h2>How to Claim Warranty</h2>
                <ol>
                    <li>Contact our customer service team</li>
                    <li>Provide proof of purchase and serial number</li>
                    <li>Describe the issue in detail</li>
                    <li>Follow the provided troubleshooting steps</li>
                    <li>If needed, we'll arrange for repair or replacement</li>
                </ol>
                
                <h2>Extended Warranty Options</h2>
                <p>We offer extended warranty plans for most products. These plans provide additional coverage beyond the manufacturer warranty and include accidental damage protection.</p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>