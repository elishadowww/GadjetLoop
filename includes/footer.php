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
        
        <script>
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        </script>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> GadgetLoop. All rights reserved.</p>
            <div class="footer-links">
                <a href="/GadjetLoop/privacy.php">Privacy Policy</a>
                <a href="/GadjetLoop/terms.php">Terms of Service</a>
            </div>
        </div>

        
    </div>
</footer>

<!-- Back to Top button (outside of footer block) -->
<button class="back-to-top-floating" onclick="scrollToTop()">
    <img src="/GadjetLoop/images/upbutton.png" alt="Back to Top" />
</button>

<style>
.back-to-top-floating {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border: none;
    background-color: transparent;
    cursor: pointer;
    z-index: 999;
    display: none;
    border-radius: 50%;
    transition: background-color 0.3s ease;
    padding: 5px;
}

.back-to-top-floating:hover {
    background-color: #007bff8c;
}


.back-to-top-floating img {
    width: 100%;
    height: auto;
}
</style>

<script>
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Show/hide on scroll
window.addEventListener('scroll', function () {
    const btn = document.querySelector('.back-to-top-floating');
    if (window.scrollY > 300) {
        btn.style.display = 'block';
    } else {
        btn.style.display = 'none';
    }
});
</script>