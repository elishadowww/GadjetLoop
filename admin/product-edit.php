<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$product_id = intval($_GET['id'] ?? 0);
$success = '';
$error = '';

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if ($_POST && isset($_POST['update_product'])) {
    // Handle product update
}

// Handle product update
if ($_POST && isset($_POST['update_product'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $short_description = sanitizeInput($_POST['short_description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $discount_percentage = intval($_POST['discount_percentage']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);
    $sku = sanitizeInput($_POST['sku']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $meta_title = sanitizeInput($_POST['meta_title']);
    $meta_description = sanitizeInput($_POST['meta_description']);
    
    // Validation
    if (empty($name) || empty($description) || $category_id <= 0 || $price <= 0) {
        $error = 'Please fill in all required fields';
    } elseif ($discount_percentage < 0 || $discount_percentage > 100) {
        $error = 'Discount percentage must be between 0 and 100';
    } else {
        try {
            // Handle main image upload
            $main_image = $product['main_image']; // Keep existing image by default
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = uploadFile($_FILES['main_image'], '../images/products/', ['jpg', 'jpeg', 'png', 'gif']);
                if ($upload_result['success']) {
                    // Delete old image if it exists
                    if ($product['main_image'] && file_exists('../images/products/' . $product['main_image'])) {
                        unlink('../images/products/' . $product['main_image']);
                    }
                    $main_image = $upload_result['filename'];
                } else {
                    $error = $upload_result['message'];
                }
            }
            if (!$error) {
                // Update product
                $stmt = $pdo->prepare("
                    UPDATE products SET 
                    name = ?, description = ?, short_description = ?, category_id = ?, price = ?, 
                    discount_percentage = ?, stock_quantity = ?, low_stock_threshold = ?, sku = ?, 
                    main_image = ?, is_featured = ?, is_active = ?, meta_title = ?, meta_description = ?,
                    updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $description, $short_description, $category_id, $price,
                    $discount_percentage, $stock_quantity, $low_stock_threshold, $sku,
                    $main_image, $is_featured, $is_active, $meta_title, $meta_description,
                    $product_id
                ]);
                // Handle additional images
                if (isset($_FILES['additional_images'])) {
                    foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['additional_images']['name'][$key],
                                'tmp_name' => $tmp_name,
                                'size' => $_FILES['additional_images']['size'][$key],
                                'type' => $_FILES['additional_images']['type'][$key]
                            ];
                            $upload_result = uploadFile($file, '../uploads/products/', ['jpg', 'jpeg', 'png', 'gif']);
                            if ($upload_result['success']) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO product_images (product_id, image_path, sort_order, created_at) 
                                    VALUES (?, ?, ?, NOW())
                                ");
                                $stmt->execute([$product_id, $upload_result['filename'], $key]);
                            }
                        }
                    }
                }
                $success = 'Product updated successfully';
                // Refresh product data
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'SKU already exists';
            } else {
                $error = 'Failed to update product';
            }
        }
    }
}

// Handle image deletion
if ($_POST && isset($_POST['delete_image'])) {
    $image_id = intval($_POST['image_id']);
    try {
        // Get image path
        $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->execute([$image_id, $product_id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete file
            if (file_exists('../uploads/products/' . $image['image_path'])) {
                unlink('../uploads/products/' . $image['image_path']);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
            $stmt->execute([$image_id]);
            
            $success = 'Image deleted successfully';
        }
    } catch (PDOException $e) {
        $error = 'Failed to delete image';
    }
}

// Get categories
$categories = getCategories($pdo);

// Get product images
$product_images = [];
// Scan /GadjetLoop/images/products for images matching the product SKU or ID
$images_dir = realpath(__DIR__ . '/../images/products');
if ($images_dir) {
    $image_files = glob($images_dir . '/*');
    foreach ($image_files as $img) {
        $filename = basename($img);
        // Match images by SKU or product ID in filename
        if (strpos($filename, $product['sku']) !== false || strpos($filename, (string)$product_id) !== false) {
            $product_images[] = [
                'image_path' => $filename,
                'id' => $filename // Use filename as ID for deletion (if needed)
            ];
        }
    }
}

// Get product reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$product_id]);
$recent_reviews = $stmt->fetchAll();

// Get product statistics
$stmt = $pdo->prepare("
    SELECT 
        AVG(rating) as avg_rating,
        COUNT(*) as review_count,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM reviews 
    WHERE product_id = ? AND is_approved = 1
");
$stmt->execute([$product_id]);
$review_stats = $stmt->fetch();

// Get sales statistics
$stmt = $pdo->prepare("
    SELECT 
        SUM(oi.quantity) as total_sold,
        SUM(oi.total) as total_revenue,
        COUNT(DISTINCT o.user_id) as unique_customers
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE oi.product_id = ? AND o.payment_status = 'paid'
");
$stmt->execute([$product_id]);
$sales_stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .product-edit-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .product-edit-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .product-form-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .product-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .image-upload-area {
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .image-upload-area:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .current-image {
            max-width: 200px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .additional-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .image-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        .delete-image {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        
        .rating-breakdown {
            margin-bottom: 1.5rem;
        }
        
        .rating-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .rating-bar {
            flex: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .rating-fill {
            height: 100%;
            background: #ffc107;
        }
        
        .rating-count {
            font-size: 12px;
            color: #666;
            min-width: 30px;
        }
        
        .review-item {
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .reviewer-name {
            font-weight: 500;
            color: #333;
        }
        
        .review-rating {
            color: #ffc107;
        }
        
        .review-comment {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        @media (max-width: 768px) {
            .product-edit-layout {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="product-edit-container">
                <div class="admin-header">
                    <h1>Edit Product</h1>
                    <div class="admin-actions">
                        <a href="products.php" class="btn btn-outline">← Back to Products</a>
                        <a href="../product-detail.php?id=<?php echo $product['id']; ?>" target="_blank" class="btn btn-outline">View Product</a>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <div class="product-edit-layout">
                    <div class="product-form-section">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3 class="section-title">Basic Information</h3>
                            <form method="POST" enctype="multipart/form-data" id="product-form">
                                <input type="hidden" name="update_product" value="1">
                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="category_id">Category *</label>
                                        <select id="category_id" name="category_id" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="sku">SKU *</label>
                                        <input type="text" id="sku" name="sku" class="form-control" 
                                               value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="short_description">Short Description</label>
                                    <textarea id="short_description" name="short_description" class="form-control" rows="3" 
                                              placeholder="Brief product description for listings"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                                    <small class="form-text">Maximum 500 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Full Description *</label>
                                    <textarea id="description" name="description" class="form-control" rows="8" 
                                              placeholder="Detailed product description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                <button type="submit" name="update_product" class="btn btn-primary" style="margin-top:1rem;">Save Changes</button>
                            </form>
                        </div>
                        
                        <!-- Pricing & Inventory -->
                        <div class="form-section">
                            <h3 class="section-title">Pricing & Inventory</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Price (RM) *</label>
                                    <input type="number" id="price" name="price" class="form-control" 
                                           value="<?php echo $product['price']; ?>" step="0.01" min="0" required form="product-form">
                                </div>
                                
                                <div class="form-group">
                                    <label for="discount_percentage">Discount (%) </label>
                                    <input type="number" id="discount_percentage" name="discount_percentage" class="form-control" 
                                           value="<?php echo $product['discount_percentage']; ?>" min="0" max="100" form="product-form">
                                    <small class="form-text">0-100% discount</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="stock_quantity">Stock Quantity *</label>
                                    <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                           value="<?php echo $product['stock_quantity']; ?>" min="0" required form="product-form">
                                </div>
                                
                                <div class="form-group">
                                    <label for="low_stock_threshold">Low Stock Alert</label>
                                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control" 
                                           value="<?php echo $product['low_stock_threshold']; ?>" min="0" form="product-form">
                                    <small class="form-text">Alert when stock falls below this number</small>
                                </div>
                            </div>
                            
                            <!-- Price Preview -->
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
                                <h4>Price Preview:</h4>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span id="original-price" style="text-decoration: line-through; color: #999;">RM<?php echo number_format($product['price'], 2); ?></span>
                                    <span id="sale-price" style="font-size: 1.25rem; font-weight: 600; color: #dc3545;">RM<?php echo number_format($product['price'] * (1 - $product['discount_percentage'] / 100), 2); ?></span>
                                    <span id="savings" style="background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 12px;">Save <?php echo $product['discount_percentage']; ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Images -->
                        <div class="form-section">
                            <h3 class="section-title">Product Images</h3>
                            
                            <!-- Main Image -->
                            <div class="form-group">
                                <label for="main_image">Main Product Image</label>
                                <?php if ($product['main_image']): ?>
                                    <div style="margin-bottom: 1rem;">
                                        <img src="../images/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                             alt="Current Image" class="current-image">
                                    </div>
                                <?php endif; ?>
                                <div class="image-upload-area" onclick="document.getElementById('main_image').click()">
                                    <div class="upload-icon">📷</div>
                                    <p>Click to upload new main image</p>
                                    <small>JPG, PNG, GIF up to 5MB</small>
                                </div>
                                <input type="file" id="main_image" name="main_image" accept="image/*" style="display: none;" form="product-form">
                            </div>
                            
                        
                        <!-- SEO Settings -->
                        <div class="form-section">
                            <h3 class="section-title">SEO Settings</h3>
                            <div class="form-group">
                                <label for="meta_title">Meta Title</label>
                                <input type="text" id="meta_title" name="meta_title" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['meta_title']); ?>" form="product-form">
                                <small class="form-text">Recommended: 50-60 characters</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="meta_description">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" class="form-control" rows="3" 
                                          form="product-form"><?php echo htmlspecialchars($product['meta_description']); ?></textarea>
                                <small class="form-text">Recommended: 150-160 characters</small>
                            </div>
                        </div>
                        
                        <!-- Product Settings -->
                        <div class="form-section">
                            <h3 class="section-title">Product Settings</h3>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_featured" name="is_featured" 
                                           <?php echo $product['is_featured'] ? 'checked' : ''; ?> form="product-form">
                                    <label for="is_featured">Featured Product</label>
                                </div>
                                <small class="form-text">Featured products appear on homepage</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" 
                                           <?php echo $product['is_active'] ? 'checked' : ''; ?> form="product-form">
                                    <label for="is_active">Active Product</label>
                                </div>
                                <small class="form-text">Inactive products are hidden from customers</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-sidebar">
                        <!-- Product Statistics -->
                        <div class="form-section">
                            <h4>Product Statistics</h4>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo number_format($sales_stats['total_sold'] ?: 0); ?></div>
                                    <div class="stat-label">Units Sold</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">RM<?php echo number_format($sales_stats['total_revenue'] ?: 0, 0); ?></div>
                                    <div class="stat-label">Revenue</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo number_format($review_stats['review_count'] ?: 0); ?></div>
                                    <div class="stat-label">Reviews</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $review_stats['avg_rating'] ? number_format($review_stats['avg_rating'], 1) : '0.0'; ?></div>
                                    <div class="stat-label">Avg Rating</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rating Breakdown -->
                        <?php if ($review_stats['review_count'] > 0): ?>
                        <div class="form-section">
                            <h4>Rating Breakdown</h4>
                            <div class="rating-breakdown">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="rating-row">
                                    <span><?php echo $i; ?>★</span>
                                    <div class="rating-bar">
                                        <?php 
                                        $count_field = ['', 'one_star', 'two_star', 'three_star', 'four_star', 'five_star'][$i];
                                        $count = $review_stats[$count_field] ?: 0;
                                        $percentage = $review_stats['review_count'] > 0 ? ($count / $review_stats['review_count']) * 100 : 0;
                                        ?>
                                        <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="rating-count"><?php echo $count; ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Recent Reviews -->
                        <?php if (!empty($recent_reviews)): ?>
                        <div class="form-section">
                            <h4>Recent Reviews</h4>
                            <?php foreach ($recent_reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></span>
                                    <span class="review-rating"><?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></span>
                                </div>
                                <?php if ($review['title']): ?>
                                    <div style="font-weight: 500; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($review['title']); ?></div>
                                <?php endif; ?>
                                <div class="review-comment"><?php echo htmlspecialchars(substr($review['comment'], 0, 150)); ?><?php echo strlen($review['comment']) > 150 ? '...' : ''; ?></div>
                                <small style="color: #999;"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                            <a href="reviews.php?product=<?php echo $product['id']; ?>" class="btn btn-outline btn-sm btn-block">View All Reviews</a>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Quick Actions -->
                        <div class="form-section">
                            <h4>Quick Actions</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="duplicateProduct()">Duplicate Product</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="generateSKU()">Generate SKU</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="bulkUpdateStock()">Bulk Update Stock</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct()">Delete Product</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Price calculation
            function updatePricePreview() {
                const price = parseFloat($('#price').val()) || 0;
                const discount = parseInt($('#discount_percentage').val()) || 0;
                const salePrice = price * (1 - discount / 100);
                
                        $('#original-price').text('RM' + price.toFixed(2));
                        $('#sale-price').text('RM' + salePrice.toFixed(2));
                        $('#savings').text('Save ' + discount + '%');
                
                    if (discount > 0) {
                        $('#original-price').show();
                        $('#savings').show();
                    } else {
                        $('#original-price').hide();
                        $('#savings').hide();
                    }
            }
            
            $('#price, #discount_percentage').on('input', updatePricePreview);
            updatePricePreview(); // Initial calculation
            
            // Character counters
            $('#short_description').on('input', function() {
                const maxLength = 500;
                const currentLength = $(this).val().length;
                const remaining = maxLength - currentLength;
                
                if (!$(this).siblings('.char-counter').length) {
                    $(this).after('<div class="char-counter"></div>');
                }
                
                $(this).siblings('.char-counter').text(currentLength + '/' + maxLength + ' characters');
                
                if (remaining < 50) {
                    $(this).siblings('.char-counter').css('color', '#dc3545');
                } else {
                    $(this).siblings('.char-counter').css('color', '#666');
                }
            });
            
            $('#meta_title').on('input', function() {
                const maxLength = 60;
                const currentLength = $(this).val().length;
                
                if (!$(this).siblings('.char-counter').length) {
                    $(this).after('<div class="char-counter"></div>');
                }
                
                $(this).siblings('.char-counter').text(currentLength + '/' + maxLength + ' characters');
                
                if (currentLength > maxLength) {
                    $(this).siblings('.char-counter').css('color', '#dc3545');
                } else if (currentLength > 50) {
                    $(this).siblings('.char-counter').css('color', '#ffc107');
                } else {
                    $(this).siblings('.char-counter').css('color', '#666');
                }
            });
            
            $('#meta_description').on('input', function() {
                const maxLength = 160;
                const currentLength = $(this).val().length;
                
                if (!$(this).siblings('.char-counter').length) {
                    $(this).after('<div class="char-counter"></div>');
                }
                
                $(this).siblings('.char-counter').text(currentLength + '/' + maxLength + ' characters');
                
                if (currentLength > maxLength) {
                    $(this).siblings('.char-counter').css('color', '#dc3545');
                } else if (currentLength > 150) {
                    $(this).siblings('.char-counter').css('color', '#ffc107');
                } else {
                    $(this).siblings('.char-counter').css('color', '#666');
                }
            });
            
            // Form validation
            $('#product-form').on('submit', function(e) {
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
        });
        
        function generateSKU() {
            const name = $('#name').val();
            const category = $('#category_id option:selected').text();
            
            if (name && category !== 'Select Category') {
                const nameCode = name.substring(0, 3).toUpperCase();
                const categoryCode = category.substring(0, 3).toUpperCase();
                const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                const sku = nameCode + categoryCode + randomNum;
                
                $('#sku').val(sku);
            } else {
                alert('Please enter product name and select category first');
            }
        }
        
        function duplicateProduct() {
            if (confirm('Create a duplicate of this product?')) {
                window.location.href = 'product-add.php?duplicate=<?php echo $product['id']; ?>';
            }
        }
        
        function bulkUpdateStock() {
            const newStock = prompt('Enter new stock quantity:', '<?php echo $product['stock_quantity']; ?>');
            if (newStock !== null && !isNaN(newStock) && newStock >= 0) {
                $('#stock_quantity').val(newStock);
                showAlert('Stock quantity updated. Click Save Changes to apply.', 'info');
            }
        }
        
        function deleteProduct() {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                $.post('ajax/delete-product.php', { product_id: <?php echo $product['id']; ?> }, function(response) {
                    if (response.success) {
                        window.location.href = 'products.php';
                    } else {
                        alert('Failed to delete product: ' + response.message);
                    }
                });
            }
        }
    </script>
</body>
</html>