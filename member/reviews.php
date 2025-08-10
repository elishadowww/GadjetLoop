<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

$success = '';
$error = '';

// Handle review submission
if ($_POST && isset($_POST['submit_review'])) {
    $product_id = intval($_POST['product_id']);
    $order_id = intval($_POST['order_id']) ?: null;
    $rating = intval($_POST['rating']);
    $title = sanitizeInput($_POST['title']);
    $comment = sanitizeInput($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars';
    } elseif (empty($comment)) {
        $error = 'Please write a review comment';
    } else {
        try {
            // Check if user already reviewed this product
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            
            if ($stmt->fetch()) {
                $error = 'You have already reviewed this product';
            } else {
                // Check if this is a verified purchase
                $is_verified = false;
                if ($order_id) {
                    $stmt = $pdo->prepare("
                        SELECT oi.id FROM order_items oi 
                        JOIN orders o ON oi.order_id = o.id 
                        WHERE o.user_id = ? AND oi.product_id = ? AND o.id = ?
                    ");
                    $stmt->execute([$user_id, $product_id, $order_id]);
                    $is_verified = $stmt->fetch() !== false;
                }
                
                // Insert review
                $stmt = $pdo->prepare("
                    INSERT INTO reviews (product_id, user_id, order_id, rating, title, comment, is_verified_purchase, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$product_id, $user_id, $order_id, $rating, $title, $comment, $is_verified]);
                
                $success = 'Review submitted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to submit review. Please try again.';
        }
    }
}

// Get user's reviews
$stmt = $pdo->prepare("
    SELECT r.*, p.name as product_name, p.main_image 
    FROM reviews r 
    JOIN products p ON r.product_id = p.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$user_reviews = $stmt->fetchAll();

// Get products user can review (from completed orders)
$stmt = $pdo->prepare("
    SELECT DISTINCT p.id, p.name, p.main_image, o.id as order_id, o.order_number
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    JOIN orders o ON oi.order_id = o.id
    LEFT JOIN reviews r ON p.id = r.product_id AND r.user_id = ?
    WHERE o.user_id = ? AND o.status = 'delivered' AND r.id IS NULL
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$reviewable_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - GadgetLoop</title>
    <link rel="stylesheet" href="/GadjetLoop/css/style.css">
    <link rel="stylesheet" href="/GadjetLoop/css/member.css">
    <style>
        .reviews-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .reviews-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tab-btn {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .reviewable-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .reviewable-product {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .reviewable-product:hover {
            transform: translateY(-2px);
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-details h4 {
            margin: 0 0 0.25rem 0;
            color: #333;
        }
        
        .order-info {
            font-size: 12px;
            color: #666;
        }
        
        .review-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 6px;
            margin-top: 1rem;
        }
        
        .rating-input {
            display: flex;
            gap: 0.25rem;
            margin-bottom: 1rem;
        }
        
        .star-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s ease;
        }
        
        .star-btn.active,
        .star-btn:hover {
            color: #ffc107;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .user-reviews {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .review-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .review-product-image {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .review-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .review-meta h4 {
            margin: 0 0 0.25rem 0;
            color: #333;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .stars {
            color: #ffc107;
        }
        
        .review-date {
            font-size: 12px;
            color: #666;
        }
        
        .verified-badge {
            background: #28a745;
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .review-content {
            margin-top: 1rem;
        }
        
        .review-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .review-comment {
            color: #666;
            line-height: 1.6;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .reviewable-products {
                grid-template-columns: 1fr;
            }
            
            .reviews-tabs {
                flex-direction: column;
                gap: 0;
            }
            
            .tab-btn {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #e9ecef;
            }
        }
    </style>
</head>
<body data-page="reviews" class="logged-in">
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="member-layout">
                <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>My Reviews</h1>
                        <p>View, write, and manage your product reviews</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <div class="reviews-tabs">
                        <button class="tab-btn active" data-tab="write-review">Write Review</button>
                        <button class="tab-btn" data-tab="my-reviews">My Reviews (<?php echo count($user_reviews); ?>)</button>
                    </div>
                    
                    <!-- Write Review Tab -->
                    <div class="tab-content active" id="write-review">
                        <?php if (empty($reviewable_products)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">📝</div>
                                <h3>No products to review</h3>
                                <p>You can write reviews for products from your completed orders.</p>
                                <a href="products.php" class="btn btn-primary">Shop Now</a>
                            </div>
                        <?php else: ?>
                            <div class="reviewable-products">
                                <?php foreach ($reviewable_products as $product): ?>
                                <div class="reviewable-product">
                                    <div class="product-info">
                                        <div class="product-image">
                                            <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="product-details">
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <div class="order-info">Order #<?php echo htmlspecialchars($product['order_number']); ?></div>
                                        </div>
                                    </div>
                                    
                                    <button class="btn btn-primary btn-sm" onclick="showReviewForm(<?php echo $product['id']; ?>, <?php echo $product['order_id']; ?>)">
                                        Write Review
                                    </button>
                                    
                                    <div class="review-form" id="review-form-<?php echo $product['id']; ?>" style="display: none;">
                                        <form method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="order_id" value="<?php echo $product['order_id']; ?>">
                                            
                                            <div class="form-group">
                                                <label>Rating *</label>
                                                <div class="rating-input" data-product="<?php echo $product['id']; ?>">
                                                    <button type="button" class="star-btn" data-rating="1">★</button>
                                                    <button type="button" class="star-btn" data-rating="2">★</button>
                                                    <button type="button" class="star-btn" data-rating="3">★</button>
                                                    <button type="button" class="star-btn" data-rating="4">★</button>
                                                    <button type="button" class="star-btn" data-rating="5">★</button>
                                                </div>
                                                <input type="hidden" name="rating" id="rating-<?php echo $product['id']; ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="title-<?php echo $product['id']; ?>">Review Title</label>
                                                <input type="text" id="title-<?php echo $product['id']; ?>" name="title" 
                                                       class="form-control" placeholder="Summarize your review">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="comment-<?php echo $product['id']; ?>">Review *</label>
                                                <textarea id="comment-<?php echo $product['id']; ?>" name="comment" 
                                                          class="form-control" rows="4" 
                                                          placeholder="Share your experience with this product..." required></textarea>
                                            </div>
                                            
                                            <div style="display: flex; gap: 1rem;">
                                                <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                                                <button type="button" class="btn btn-outline" onclick="hideReviewForm(<?php echo $product['id']; ?>)">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- My Reviews Tab -->
                    <div class="tab-content" id="my-reviews">
                        <?php if (empty($user_reviews)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">⭐</div>
                                <h3>No reviews yet</h3>
                                <p>You haven't written any reviews yet. Share your experience with products you've purchased!</p>
                            </div>
                        <?php else: ?>
                            <div class="user-reviews">
                                <?php foreach ($user_reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="review-product-image">
                                            <img src="/GadjetLoop/images/products/<?php echo htmlspecialchars($review['main_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($review['product_name']); ?>">
                                        </div>
                                        <div class="review-meta">
                                            <h4><?php echo htmlspecialchars($review['product_name']); ?></h4>
                                            <div class="review-rating">
                                                <div class="stars">
                                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                                </div>
                                                <span><?php echo $review['rating']; ?>/5</span>
                                                <?php if ($review['is_verified_purchase']): ?>
                                                    <span class="verified-badge">Verified Purchase</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="review-date"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="review-content">
                                        <?php if ($review['title']): ?>
                                            <div class="review-title"><?php echo htmlspecialchars($review['title']); ?></div>
                                        <?php endif; ?>
                                        <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.tab-btn').on('click', function() {
                const tab = $(this).data('tab');
                
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                $('.tab-content').removeClass('active');
                $('#' + tab).addClass('active');
            });
            
            // Star rating
            $('.star-btn').on('click', function() {
                const rating = $(this).data('rating');
                const productId = $(this).closest('.rating-input').data('product');
                const $stars = $(this).closest('.rating-input').find('.star-btn');
                
                // Update visual stars
                $stars.removeClass('active');
                $stars.slice(0, rating).addClass('active');
                
                // Update hidden input
                $('#rating-' + productId).val(rating);
            });
            
            // Star hover effect
            $('.star-btn').on('mouseenter', function() {
                const rating = $(this).data('rating');
                const $stars = $(this).closest('.rating-input').find('.star-btn');
                
                $stars.removeClass('hover');
                $stars.slice(0, rating).addClass('hover');
            });
            
            $('.rating-input').on('mouseleave', function() {
                $(this).find('.star-btn').removeClass('hover');
            });
        });
        
        function showReviewForm(productId, orderId) {
            $('#review-form-' + productId).slideDown(300);
        }
        
        function hideReviewForm(productId) {
            $('#review-form-' + productId).slideUp(300);
        }
    </script>
</body>
</html>