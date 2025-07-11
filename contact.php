<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_POST) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // In a real application, you would send this to your email or save to database
        // For demo purposes, we'll just show a success message
        
        try {
            // Save contact message to database (optional)
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            // Note: You'll need to create this table if you want to store messages
            
            // Send email notification (in production)
            $email_subject = "New Contact Form Submission: " . $subject;
            $email_message = "
                <h3>New Contact Form Submission</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong></p>
                <p>{$message}</p>
            ";
            
            sendEmail('admin@gadgetloop.com', $email_subject, $email_message);
            
            $success = 'Thank you for your message! We will get back to you within 24 hours.';
            
            // Clear form data
            $name = $email = $phone = $subject = $message = '';
            
        } catch (Exception $e) {
            $error = 'There was an error sending your message. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/contact.css">
</head>
<body data-page="contact" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="contact-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Contact Us</h1>
                    <p>We're here to help! Get in touch with our team</p>
                </div>
            </div>
        </section>

        <!-- Contact Content -->
        <section class="contact-content">
            <div class="container">
                <div class="contact-layout">
                    <!-- Contact Information -->
                    <div class="contact-info">
                        <h2>Get in Touch</h2>
                        <p>Have a question about our products or need support? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                        
                        <div class="contact-methods">
                            <div class="contact-method">
                                <div class="method-icon">📍</div>
                                <div class="method-details">
                                    <h4>Visit Our Store</h4>
                                    <p>123 Tech Street<br>Digital City, DC 12345<br>United States</p>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon">📞</div>
                                <div class="method-details">
                                    <h4>Call Us</h4>
                                    <p>Phone: (555) 123-4567<br>Toll Free: 1-800-GADGET<br>Mon-Fri: 9AM-8PM EST</p>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon">✉️</div>
                                <div class="method-details">
                                    <h4>Email Us</h4>
                                    <p>General: info@gadgetloop.com<br>Support: support@gadgetloop.com<br>Sales: sales@gadgetloop.com</p>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon">💬</div>
                                <div class="method-details">
                                    <h4>Live Chat</h4>
                                    <p>Available 24/7 for instant support<br>Click the chat icon in the bottom right</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Business Hours -->
                        <div class="business-hours">
                            <h3>Business Hours</h3>
                            <div class="hours-list">
                                <div class="hours-item">
                                    <span class="day">Monday - Friday</span>
                                    <span class="time">9:00 AM - 8:00 PM</span>
                                </div>
                                <div class="hours-item">
                                    <span class="day">Saturday</span>
                                    <span class="time">10:00 AM - 6:00 PM</span>
                                </div>
                                <div class="hours-item">
                                    <span class="day">Sunday</span>
                                    <span class="time">12:00 PM - 5:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="contact-form-section">
                        <div class="form-container">
                            <h2>Send us a Message</h2>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" class="contact-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Full Name *</label>
                                        <input type="text" id="name" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email Address *</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="subject">Subject *</label>
                                        <select id="subject" name="subject" class="form-control" required>
                                            <option value="">Select a subject</option>
                                            <option value="General Inquiry" <?php echo ($subject ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                            <option value="Product Support" <?php echo ($subject ?? '') === 'Product Support' ? 'selected' : ''; ?>>Product Support</option>
                                            <option value="Order Issue" <?php echo ($subject ?? '') === 'Order Issue' ? 'selected' : ''; ?>>Order Issue</option>
                                            <option value="Return/Refund" <?php echo ($subject ?? '') === 'Return/Refund' ? 'selected' : ''; ?>>Return/Refund</option>
                                            <option value="Technical Support" <?php echo ($subject ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                            <option value="Partnership" <?php echo ($subject ?? '') === 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                                            <option value="Other" <?php echo ($subject ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message">Message *</label>
                                    <textarea id="message" name="message" class="form-control" rows="6" 
                                              placeholder="Please describe your inquiry in detail..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="map-section">
            <div class="container">
                <h2>Find Our Store</h2>
                <div class="map-container">
                    <div id="contact-map"></div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="container">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-grid">
                    <div class="faq-item">
                        <h4>What are your shipping options?</h4>
                        <p>We offer free standard shipping on orders over $50. Express shipping options are available for faster delivery.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>What is your return policy?</h4>
                        <p>We offer a 30-day hassle-free return policy on all products. Items must be in original condition with packaging.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Do you offer warranty on products?</h4>
                        <p>Yes, all our products come with manufacturer warranties. Extended warranty options are available for select items.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>How can I track my order?</h4>
                        <p>Once your order ships, you'll receive a tracking number via email. You can also track orders in your account dashboard.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Do you offer technical support?</h4>
                        <p>Yes, our technical support team is available to help with product setup, troubleshooting, and general questions.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Can I cancel or modify my order?</h4>
                        <p>Orders can be cancelled or modified within 1 hour of placement. Contact us immediately for assistance.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Form validation
            $('.contact-form').on('submit', function(e) {
                let isValid = true;
                
                // Check required fields
                $(this).find('[required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).addClass('error');
                        isValid = false;
                    } else {
                        $(this).removeClass('error');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    showAlert('Please fill in all required fields', 'error');
                }
            });
            
            // Remove error class on input
            $('.form-control').on('input change', function() {
                $(this).removeClass('error');
            });
            
            // FAQ accordion
            $('.faq-item h4').on('click', function() {
                const $item = $(this).parent();
                const $content = $(this).next('p');
                
                if ($item.hasClass('active')) {
                    $item.removeClass('active');
                    $content.slideUp(300);
                } else {
                    $('.faq-item').removeClass('active');
                    $('.faq-item p').slideUp(300);
                    $item.addClass('active');
                    $content.slideDown(300);
                }
            });
            
            // Initialize FAQ - hide all answers
            $('.faq-item p').hide();
            
            // Character counter for message
            $('#message').on('input', function() {
                const maxLength = 1000;
                const currentLength = $(this).val().length;
                const remaining = maxLength - currentLength;
                
                if (!$('.char-counter').length) {
                    $(this).after('<div class="char-counter"></div>');
                }
                
                $('.char-counter').text(`${currentLength}/${maxLength} characters`);
                
                if (remaining < 50) {
                    $('.char-counter').addClass('warning');
                } else {
                    $('.char-counter').removeClass('warning');
                }
            });
        });
        
        // Initialize Google Maps
        function initContactMap() {
            const storeLocation = { lat: 40.7128, lng: -74.0060 };
            const map = new google.maps.Map(document.getElementById('contact-map'), {
                zoom: 15,
                center: storeLocation,
                styles: [
                    {
                        featureType: 'all',
                        elementType: 'geometry.fill',
                        stylers: [{ weight: '2.00' }]
                    },
                    {
                        featureType: 'all',
                        elementType: 'geometry.stroke',
                        stylers: [{ color: '#9c9c9c' }]
                    }
                ]
            });
            
            const marker = new google.maps.Marker({
                position: storeLocation,
                map: map,
                title: 'GadgetLoop Store',
                animation: google.maps.Animation.DROP
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <h4>GadgetLoop Store</h4>
                        <p>123 Tech Street<br>Digital City, DC 12345</p>
                        <p><strong>Phone:</strong> (555) 123-4567</p>
                        <p><strong>Hours:</strong> Mon-Fri 9AM-8PM</p>
                    </div>
                `
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initContactMap"></script>
</body>
</html>