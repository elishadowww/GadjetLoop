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
                    <div class="map-box">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.7534651983833!2d116.12683327581546!3d6.028537428715512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x323b665bce325bf1%3A0xfc7bcf77c145bc5f!2sTunku%20Abdul%20Rahman%20University%20Of%20Management%20And%20Technology%2C%20Sabah%20Branch%20(TAR%20UMT)!5e0!3m2!1sen!2smy!4v1752317591804!5m2!1sen!2smy" width="1165" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">

    <div class="container">
        <div class="faq-header">
            <h2>Frequently Asked Questions</h2>
            <p>Everything you need to know about our products and services</p>
        </div>

        <div class="faq-container">
            <div class="faq-grid">
                <div class="faq-card" data-faq="1">
                    <div class="faq-header-content">
                        <div class="faq-icon">
                            <span>🚚</span>
                        </div>
                        <div class="faq-question-wrapper">
                            <div class="faq-category">Shipping</div>
                            <h4 class="faq-question">What are your shipping options?</h4>
                        </div>
                        <div class="faq-toggle">
                            <span class="toggle-icon">+</span>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>We offer multiple shipping options to suit your needs. Standard shipping (5-7 business days) is free on orders over RM 50. Express shipping (2-3 business days) is available for RM 9.99, and overnight shipping for RM 19.99. We also provide tracking information for all orders.</p>
                    </div>
                </div>

                <div class="faq-card" data-faq="2">
                    <div class="faq-header-content">
                        <div class="faq-icon">
                            <span>↩️</span>
                        </div>
                        <div class="faq-question-wrapper">
                            <div class="faq-category">Returns</div>
                            <h4 class="faq-question">What is your return policy?</h4>
                        </div>
                        <div class="faq-toggle">
                            <span class="toggle-icon">+</span>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>We offer a hassle-free 30-day return policy on all products. Items must be in original condition with all packaging and accessories. Returns are free for defective items, while customer returns have a RM 5.99 processing fee. Refunds are processed within 3-5 business days.</p>
                    </div>
                </div>

                <div class="faq-card" data-faq="3">
                    <div class="faq-header-content">
                        <div class="faq-icon">
                            <span>🛡️</span>
                        </div>
                        <div class="faq-question-wrapper">
                            <div class="faq-category">Warranty</div>
                            <h4 class="faq-question">Do you offer warranty on products?</h4>
                        </div>
                        <div class="faq-toggle">
                            <span class="toggle-icon">+</span>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! All our products come with manufacturer warranties ranging from 1-3 years depending on the item. We also offer extended warranty plans for added protection. Our warranty covers manufacturing defects and hardware failures under normal use.</p>
                    </div>
                </div>

                <div class="faq-card" data-faq="4">
                    <div class="faq-header-content">
                        <div class="faq-icon">
                            <span>⏰</span>
                        </div>
                        <div class="faq-question-wrapper">
                            <div class="faq-category">Orders</div>
                            <h4 class="faq-question">How can I track my order?</h4>
                        </div>
                        <div class="faq-toggle">
                            <span class="toggle-icon">+</span>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Once your order ships, you'll receive a confirmation email with tracking information. You can also log into your account dashboard to view real-time order status. We provide tracking numbers for all carriers including FedEx, UPS, and USPS.</p>
                    </div>
                </div>

                <div class="faq-card" data-faq="5">
                    <div class="faq-header-content">
                        <div class="faq-icon">
                            <span>📞</span>
                        </div>
                        <div class="faq-question-wrapper">
                            <div class="faq-category">Support</div>
                            <h4 class="faq-question">Do you offer technical support?</h4>
                        </div>
                        <div class="faq-toggle">
                            <span class="toggle-icon">+</span>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely! Our certified technical support team is available Monday-Friday 9AM-8PM EST. We provide setup assistance, troubleshooting, and product guidance. You can reach us via live chat, email, or phone for immediate help.</p>
                    </div>
                </div>

                <div class="faq-card" data-faq="6">
                    <div class="faq-header-content">
                        <div class="faq-icon">
                            <span>⚙️</span>
                        </div>
                        <div class="faq-question-wrapper">
                            <div class="faq-category">Orders</div>
                            <h4 class="faq-question">Can I cancel or modify my order?</h4>
                        </div>
                        <div class="faq-toggle">
                            <span class="toggle-icon">+</span>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Orders can be cancelled or modified within 1 hour of placement, as long as they haven't entered the fulfillment process. After this window, changes may not be possible. Contact our customer service team immediately for assistance with any modifications.</p>

                    </div>
                </div>
            </div>
        </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

            });
            
            // FAQ accordion
            $(document).ready(function () {
    // Hide all on load
    $('.faq-content').hide();

    $('.faq-item h4').on('click', function () {
        const $faqItem = $(this).closest('.faq-item');

        if ($faqItem.hasClass('active')) {
            // If already open, close it
            $faqItem.removeClass('active');
            $faqItem.find('.faq-content').slideUp(300);
        } else {
            // Close others
            $('.faq-item').removeClass('active').find('.faq-content').slideUp(300);

            // Open clicked
            $faqItem.addClass('active');
            $faqItem.find('.faq-content').slideDown(300);
        }
    });
}); 




            
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
        
        if (!isValid) {
            e.preventDefault();
            showAlert('Please fill in all required fields', 'error');
        }
    });
    
    // Remove error class on input
    $('.form-control').on('input change', function() {
        $(this).removeClass('error');
    });
    
    // FAQ accordion - Only one item open at a time
    $('.faq-card').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $clickedCard = $(this);
        const $clickedAnswer = $clickedCard.find('.faq-answer');
        const $clickedToggle = $clickedCard.find('.toggle-icon');
        
        // Check if the clicked card is currently active
        if ($clickedCard.hasClass('active')) {
            // Close the clicked card
            $clickedCard.removeClass('active');
            $clickedAnswer.slideUp(300);
            $clickedToggle.text('+');
        } else {
            // Close all other cards first
            $('.faq-card.active').removeClass('active');
            $('.faq-card .faq-answer').slideUp(300);
            $('.toggle-icon').text('+');
            
            // Open the clicked card
            $clickedCard.addClass('active');
            $clickedAnswer.slideDown(300);
            $clickedToggle.text('−');
        }
    });
    
    // Initialize FAQ - hide all answers
    $('.faq-answer').hide();
    
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
</body>
</html>