<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>GadgetLoop</h3>
                <p>Your trusted source for the latest electronic gadgets and accessories.</p>
                <div class="social-links">
                    <a href="#" class="social-link">📘</a>
                    <a href="#" class="social-link">🐦</a>
                    <a href="#" class="social-link">📷</a>
                    <a href="#" class="social-link">💼</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="shipping.php">Shipping Info</a></li>
                    <li><a href="returns.php">Returns</a></li>
                    <li><a href="warranty.php">Warranty</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Info</h4>
                <p>📍 123 Tech Street, Digital City, DC 12345</p>
                <p>📞 (555) 123-4567</p>
                <p>✉️ info@gadgetloop.com</p>
                <p>🕒 Mon-Fri: 9AM-8PM, Sat: 10AM-6PM</p>
            </div>
        </div>

        <style>

           
.footer-links a:visited {
    color: inherit;
    text-decoration: none;
}
.footer-links a {
    text-decoration: none;
}
.back-to-top {
            margin-top: 20px;
            margin-right: -150px;
            display: inline-block;
            padding: 5px 5px;
            background-color: #343a40;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            float: right;
            transition: background-color 0.3s ease;
        }

        .back-to-top:hover {
            background-color: #007bff;
        }


</style>
        <button class="back-to-top" onclick="scrollToTop()">
        <img src="images/buttonup.png" alt="Back to Top">
</button>

        <script>
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        </script>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> GadgetLoop. All rights reserved.</p>
            <div class="footer-links">
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
            </div>
        </div>

        
    </div>
</footer>