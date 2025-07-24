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
    <title>About Us - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/about.css">
</head>
<body data-page="about" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>About GadgetLoop</h1>
                    <p>Your trusted partner in the world of electronic gadgets and accessories</p>
                </div>
            </div>
        </section>

        <!-- Company Story -->
        <section class="company-story">
            <div class="container">
                <div class="story-content">
                    <div class="story-text">
                        <h2>Our Story</h2>
                        <p>Founded in 2020, GadgetLoop began as a small startup with a big vision: to make the latest electronic gadgets and accessories accessible to everyone. What started as a passion project by tech enthusiasts has grown into a trusted online destination for gadget lovers worldwide.</p>
                        
                        <p>We believe that technology should enhance your life, not complicate it. That's why we carefully curate our product selection, ensuring that every item in our store meets our high standards for quality, innovation, and value.</p>
                        
                        <p>Today, we serve thousands of customers globally, offering everything from the latest smartphones and laptops to cutting-edge smart home devices and gaming accessories.</p>
                    </div>
                    <div class="story-image">
                        <img src="https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg?auto=compress&cs=tinysrgb&w=600" alt="Our Team">
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="mission-vision">
            <div class="container">
                <div class="mission-vision-grid">
                    <div class="mission-card">
                        <div class="card-icon">🎯</div>
                        <h3>Our Mission</h3>
                        <p>To provide customers with the latest and greatest electronic gadgets at competitive prices, backed by exceptional customer service and support.</p>
                    </div>
                    
                    <div class="vision-card">
                        <div class="card-icon">🚀</div>
                        <h3>Our Vision</h3>
                        <p>To become the world's most trusted online destination for electronic gadgets, where innovation meets accessibility.</p>
                    </div>
                    
                    <div class="values-card">
                        <div class="card-icon">💎</div>
                        <h3>Our Values</h3>
                        <p>Quality, Innovation, Customer-centricity, Transparency, and Continuous Improvement guide everything we do.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="why-choose-us">
            <div class="container">
                <h2>Why Choose GadgetLoop?</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">✅</div>
                        <h4>Authentic Products</h4>
                        <p>All our products are 100% authentic and come with manufacturer warranties.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">🚚</div>
                        <h4>Fast Shipping</h4>
                        <p>Free shipping on orders over $50 with express delivery options available.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">🔒</div>
                        <h4>Secure Shopping</h4>
                        <p>Your personal and payment information is protected with industry-standard encryption.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">💬</div>
                        <h4>Expert Support</h4>
                        <p>Our knowledgeable support team is here to help you make the right choice.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">↩️</div>
                        <h4>Easy Returns</h4>
                        <p>30-day hassle-free return policy on all products.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">⭐</div>
                        <h4>Best Prices</h4>
                        <p>Competitive pricing with regular deals and discounts.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <h2>Meet Our Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-photo">
                         <img src="/GadjetLoop/images/ceo.jpg" alt="Yuki Lai Yi Ting">
                        </div>
                        <h4>Yuki Lai Yi Ting</h4>
                        <p>CEO & Founder</p>
                        <div class="member-bio">
                            <p>Tech entrepreneur with 15+ years of experience in e-commerce and consumer electronics.</p>
                        </div>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-photo">
                            <img src="https://images.pexels.com/photos/3184338/pexels-photo-3184338.jpeg?auto=compress&cs=tinysrgb&w=300" alt="Sarah Johnson">
                        </div>
                        <h4>Sarah Johnson</h4>
                        <p>Head of Product</p>
                        <div class="member-bio">
                            <p>Product specialist who ensures we stock the latest and most innovative gadgets.</p>
                        </div>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-photo">
                            <img src="https://images.pexels.com/photos/3184339/pexels-photo-3184339.jpeg?auto=compress&cs=tinysrgb&w=300" alt="Mike Chen">
                        </div>
                        <h4>Mike Chen</h4>
                        <p>Customer Success Manager</p>
                        <div class="member-bio">
                            <p>Dedicated to ensuring every customer has an exceptional shopping experience.</p>
                        </div>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-photo">
                            <img src="https://images.pexels.com/photos/3184340/pexels-photo-3184340.jpeg?auto=compress&cs=tinysrgb&w=300" alt="Emily Davis">
                        </div>
                        <h4>Emily Davis</h4>
                        <p>Marketing Director</p>
                        <div class="member-bio">
                            <p>Creative marketer who helps connect customers with the perfect gadgets for their needs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <section class="statistics">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Products</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">25+</div>
                        <div class="stat-label">Countries Served</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">99.5%</div>
                        <div class="stat-label">Customer Satisfaction</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Explore Our Gadgets?</h2>
                    <p>Discover the latest electronic gadgets and accessories in our carefully curated collection.</p>
                    <div class="cta-buttons">
                        <a href="products.php" class="btn btn-primary">Shop Now</a>
                        <a href="contact.php" class="btn btn-outline">Contact Us</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script src="/js/backtotop.js"></script>
    <script>
        $(document).ready(function() {
            // Animate statistics on scroll
            function animateStats() {
                $('.stat-number').each(function() {
                    const $this = $(this);
                    const target = parseInt($this.text().replace(/[^\d]/g, ''));
                    const suffix = $this.text().replace(/[\d]/g, '');
                    
                    $({ counter: 0 }).animate({ counter: target }, {
                        duration: 2000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.ceil(this.counter) + suffix);
                        }
                    });
                });
            }
            
            // Trigger animation when statistics section comes into view
            $(window).on('scroll', function() {
                const statsSection = $('.statistics');
                const scrollTop = $(window).scrollTop();
                const windowHeight = $(window).height();
                const statsTop = statsSection.offset().top;
                
                if (scrollTop + windowHeight > statsTop && !statsSection.hasClass('animated')) {
                    statsSection.addClass('animated');
                    animateStats();
                }
            });
            
            // Team member hover effects
            $('.team-member').on('mouseenter', function() {
                $(this).find('.member-bio').slideDown(300);
            }).on('mouseleave', function() {
                $(this).find('.member-bio').slideUp(300);
            });
        });
    </script>
</body>
</html>