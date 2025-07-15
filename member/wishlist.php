<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a member
if (!isLoggedIn() || isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

// Get wishlist items
$stmt = $pdo->prepare("
    SELECT w.*, p.name, p.price, p.main_image, p.discount_percentage, p.stock_quantity,
    CASE WHEN p.discount_percentage > 0 
         THEN p.price * (1 - p.discount_percentage / 100) 
         ELSE p.price END as sale_price
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ? AND p.is_active = 1
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
</head>
<body data-page="wishlist" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="member-layout">
                <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>My Wishlist</h1>
                        <p>Save your favorite products for later</p>
                    </div>
                    
                    <div class="wishlist-content">
                        <?php if (empty($wishlist_items)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">♡</div>
                                <h3>Your wishlist is empty</h3>
                                <p>Save products you love to your wishlist and shop them later.</p>
                                <a href="../products.php" class="btn btn-primary">Browse Products</a>
                            </div>
                        <?php else: ?>
                            <div class="wishlist-grid">
                                <?php foreach ($wishlist_items as $item): ?>
                                <div class="wishlist-item">
                                    <div class="wishlist-item-image">
                                        <img src="../images/products/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php if ($item['discount_percentage'] > 0): ?>
                                            <span class="discount-badge"><?php echo $item['discount_percentage']; ?>% OFF</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="wishlist-item-info">
                                        <h3 class="wishlist-item-name">
                                            <a href="../product-detail.php?id=<?php echo $item['product_id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <div class="wishlist-item-price">
                                            <?php if ($item['discount_percentage'] > 0): ?>
                                                <span class="original-price">$<?php echo number_format($item['price'], 2); ?></span>
                                                <span class="sale-price">$<?php echo number_format($item['sale_price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="wishlist-item-actions">
                                            <?php if ($item['stock_quantity'] > 0): ?>
                                                <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $item['product_id']; ?>">
                                                    Add to Cart
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary" disabled>Out of Stock</button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-danger remove-from-wishlist" data-product-id="<?php echo $item['product_id']; ?>">
                                                Remove
                                            </button>
                                        </div>
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
    <script src="../js/cart.js"></script>
    <script>
        $(document).ready(function() {
            // Remove from wishlist
            $('.remove-from-wishlist').on('click', function() {
                const productId = $(this).data('product-id');
                const $item = $(this).closest('.wishlist-item');
                
                if (confirm('Remove this item from your wishlist?')) {
                    $.post('../ajax/toggle-wishlist.php', { product_id: productId }, function(response) {
                        if (response.success) {
                            $item.fadeOut(300, function() {
                                $(this).remove();
                                if ($('.wishlist-item').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            alert('Failed to remove item from wishlist');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>