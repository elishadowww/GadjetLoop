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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
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
                        <img src="uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" 
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
                            <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
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
                                <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="sale-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                <?php else: ?>
                                <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                    ♡
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
                        <div id="google-map"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>
    <script>
        // Initialize Google Maps
        function initMap() {
            const storeLocation = { lat: 40.7128, lng: -74.0060 };
            const map = new google.maps.Map(document.getElementById('google-map'), {
                zoom: 15,
                center: storeLocation
            });
            const marker = new google.maps.Marker({
                position: storeLocation,
                map: map,
                title: 'GadgetLoop Store'
            });
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
</body>
</html>