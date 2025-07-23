<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get featured products and categories
$featured_products = getFeaturedProducts($pdo, 8);
$categories = getCategories($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GadgetLoop - Electronic Gadgets & Accessories</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome to GadgetLoop</h1>
                <p>Discover the latest electronic gadgets and accessories</p>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
            <div class="hero-image">
                <img src="images/gadjet.png" alt="Latest Gadgets">
            </div>
        </section>


        <!-- Categories Section -->
        <section class="categories">
            <div class="container">
                <h2>Shop by Category</h2>
                <div class="category-grid">
                    <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <img src="images/categories/<?php echo htmlspecialchars($category['image']); ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">Browse</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="featured-products">
            <div class="container">
                <h2>Featured Products</h2>
                <div class="product-grid">
                    <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if ($product['discount_percentage'] > 0): ?>
                            <span class="discount-badge"><?php echo $product['discount_percentage']; ?>% OFF</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-rating">
                                <?php echo generateStarRating($product['average_rating']); ?>
                                <span>(<?php echo $product['review_count']; ?>)</span>
                            </div>
                            <div class="product-price">
                                <?php if ($product['discount_percentage'] > 0): ?>
                                <span class="original-price">RM<?php echo number_format($product['price'], 2); ?></span>
                                <span class="sale-price">RM<?php echo number_format($product['sale_price'], 2); ?></span>
                                <?php else: ?>
                                <span class="price">RM<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                           <div class="product-actions">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                                Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                            +Wishlist
                                        </button>
                                    </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Store Location -->
        <section class="store-location">
            <div class="container">
                <h2>Visit Our Store</h2>
                <div class="location-content">
                    <div class="location-info">
                        <h3>GadgetLoop Store</h3>
                        <p>123 Tech Street, Digital City, DC 12345</p>
                        <p>Phone: (555) 123-4567</p>
                        <p>Email: info@gadgetloop.com</p>
                        <div class="store-hours">
                            <h4>Store Hours:</h4>
                            <p>Monday - Friday: 9:00 AM - 8:00 PM</p>
                            <p>Saturday: 10:00 AM - 6:00 PM</p>
                            <p>Sunday: 12:00 PM - 5:00 PM</p>
                        </div>
                    </div>
                    <div class="map-container">
                        <div class="map-box">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.7534651983833!2d116.12683327581546!3d6.028537428715512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x323b665bce325bf1%3A0xfc7bcf77c145bc5f!2sTunku%20Abdul%20Rahman%20University%20Of%20Management%20And%20Technology%2C%20Sabah%20Branch%20(TAR%20UMT)!5e0!3m2!1sen!2smy!4v1752317591804!5m2!1sen!2smy" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <body class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>
    <script src="/js/backtotop.js"></script>
</html>