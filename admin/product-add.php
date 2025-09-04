<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';
$duplicate_id = intval($_GET['duplicate'] ?? 0);

// Get product data for duplication
$duplicate_product = null;
if ($duplicate_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$duplicate_id]);
    $duplicate_product = $stmt->fetch();
}

// Handle product creation
if ($_POST && isset($_POST['add_product'])) {
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
            $main_image = '';
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = uploadFile($_FILES['main_image'], '../uploads/products/', ['jpg', 'jpeg', 'png', 'gif']);
                if ($upload_result['success']) {
                    $main_image = $upload_result['filename'];
                } else {
                    $error = $upload_result['message'];
                }
            }
            
            if (!$error) {
                // Generate SKU if empty
                if (empty($sku)) {
                    $sku = strtoupper(substr($name, 0, 3)) . rand(1000, 9999);
                }
                
                // Insert product
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, description, short_description, category_id, price, 
                                        discount_percentage, stock_quantity, low_stock_threshold, sku, 
                                        main_image, is_featured, is_active, meta_title, meta_description, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $name, $description, $short_description, $category_id, $price,
                    $discount_percentage, $stock_quantity, $low_stock_threshold, $sku,
                    $main_image, $is_featured, $is_active, $meta_title, $meta_description
                ]);
                
                $product_id = $pdo->lastInsertId();
                
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
                
                $success = 'Product created successfully';
                
                // Redirect to edit page
                header('Location: product-edit.php?id=' . $product_id);
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'SKU already exists';
            } else {
                $error = 'Failed to create product';
            }
        }
    }
}

// Get categories
$categories = getCategories($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .product-add-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
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
        
        .form-control.error {
            border-color: #dc3545;
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
        
        .char-counter {
            font-size: 12px;
            color: #666;
            margin-top: 0.25rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
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
            <div class="product-add-container">
                <div class="admin-header">
                    <h1><?php echo $duplicate_product ? 'Duplicate Product' : 'Add New Product'; ?></h1>
                    <div class="admin-actions">
                        <a href="products.php" class="btn btn-outline">← Back to Products</a>
                        <button type="submit" form="product-form" class="btn btn-primary">Save Product</button>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>
                    <form method="POST" enctype="multipart/form-data" id="product-form">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo $duplicate_product ? htmlspecialchars($duplicate_product['name'] . ' (Copy)') : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($duplicate_product && $duplicate_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="sku">SKU *</label>
                                <input type="text" id="sku" name="sku" class="form-control" 
                                       value="<?php echo $duplicate_product ? '' : ''; ?>" required>
                                <button type="button" class="btn btn-outline btn-sm" onclick="generateSKU()" style="margin-top: 0.5rem;">Generate SKU</button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_description">Short Description</label>
                            <textarea id="short_description" name="short_description" class="form-control" rows="3" 
                                      placeholder="Brief product description for listings"><?php echo $duplicate_product ? htmlspecialchars($duplicate_product['short_description']) : ''; ?></textarea>
                            <small class="form-text">Maximum 500 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Full Description *</label>
                            <textarea id="description" name="description" class="form-control" rows="8" 
                                      placeholder="Detailed product description" required><?php echo $duplicate_product ? htmlspecialchars($duplicate_product['description']) : ''; ?></textarea>
                        </div>
                    </form>
                </div>
                
                <!-- Pricing & Inventory -->
                <div class="form-section">
                    <h3 class="section-title">Pricing & Inventory</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price ($) *</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   value="<?php echo $duplicate_product ? $duplicate_product['price'] : ''; ?>" 
                                   step="0.01" min="0" required form="product-form">
                        </div>
                        
                        <div class="form-group">
                            <label for="discount_percentage">Discount (%)</label>
                            <input type="number" id="discount_percentage" name="discount_percentage" class="form-control" 
                                   value="<?php echo $duplicate_product ? $duplicate_product['discount_percentage'] : '0'; ?>" 
                                   min="0" max="100" form="product-form">
                            <small class="form-text">0-100% discount</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" 
                                   value="<?php echo $duplicate_product ? $duplicate_product['stock_quantity'] : '0'; ?>" 
                                   min="0" required form="product-form">
                        </div>
                        
                        <div class="form-group">
                            <label for="low_stock_threshold">Low Stock Alert</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control" 
                                   value="<?php echo $duplicate_product ? $duplicate_product['low_stock_threshold'] : '10'; ?>" 
                                   min="0" form="product-form">
                            <small class="form-text">Alert when stock falls below this number</small>
                        </div>
                    </div>
                    
                    <!-- Price Preview -->
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
                        <h4>Price Preview:</h4>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span id="original-price" style="text-decoration: line-through; color: #999;">$0.00</span>
                            <span id="sale-price" style="font-size: 1.25rem; font-weight: 600; color: #dc3545;">$0.00</span>
                            <span id="savings" style="background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 12px;">Save 0%</span>
                        </div>
                    </div>
                </div>
                
                <!-- Images -->
                <div class="form-section">
                    <h3 class="section-title">Product Images</h3>
                    
                    <!-- Main Image -->
                    <div class="form-group">
                        <label for="main_image">Main Product Image *</label>
                        <div class="image-upload-area" onclick="document.getElementById('main_image').click()">
                            <div class="upload-icon">📷</div>
                            <p>Click to upload main product image</p>
                            <small>JPG, PNG, GIF up to 5MB</small>
                        </div>
                        <input type="file" id="main_image" name="main_image" accept="image/*" style="display: none;" form="product-form" required>
                    </div>
                    
                    <!-- Additional Images -->
                    <div class="form-group">
                        <label>Additional Images (Optional)</label>
                        <div class="image-upload-area" onclick="document.getElementById('additional_images').click()">
                            <div class="upload-icon">🖼️</div>
                            <p>Add more product images</p>
                            <small>Select multiple files</small>
                        </div>
                        <input type="file" id="additional_images" name="additional_images[]" accept="image/*" multiple style="display: none;" form="product-form">
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class="form-section">
                    <h3 class="section-title">SEO Settings</h3>
                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" class="form-control" 
                               value="<?php echo $duplicate_product ? htmlspecialchars($duplicate_product['meta_title']) : ''; ?>" form="product-form">
                        <small class="form-text">Recommended: 50-60 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" rows="3" 
                                  form="product-form"><?php echo $duplicate_product ? htmlspecialchars($duplicate_product['meta_description']) : ''; ?></textarea>
                        <small class="form-text">Recommended: 150-160 characters</small>
                    </div>
                </div>
                
                <!-- Product Settings -->
                <div class="form-section">
                    <h3 class="section-title">Product Settings</h3>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="is_featured" name="is_featured" 
                                   <?php echo ($duplicate_product && $duplicate_product['is_featured']) ? 'checked' : ''; ?> form="product-form">
                            <label for="is_featured">Featured Product</label>
                        </div>
                        <small class="form-text">Featured products appear on homepage</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="is_active" name="is_active" checked form="product-form">
                            <label for="is_active">Active Product</label>
                        </div>
                        <small class="form-text">Inactive products are hidden from customers</small>
                    </div>
                    
                    <button type="submit" name="add_product" class="btn btn-primary btn-block" form="product-form">
                        Create Product
                    </button>
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
                
                $('#original-price').text('$' + price.toFixed(2));
                $('#sale-price').text('$' + salePrice.toFixed(2));
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
                
                if (!$(this).siblings('.char-counter').length) {
                    $(this).after('<div class="char-counter"></div>');
                }
                
                $(this).siblings('.char-counter').text(currentLength + '/' + maxLength + ' characters');
                
                if (currentLength > maxLength) {
                    $(this).siblings('.char-counter').css('color', '#dc3545');
                } else if (currentLength > 450) {
                    $(this).siblings('.char-counter').css('color', '#ffc107');
                } else {
                    $(this).siblings('.char-counter').css('color', '#666');
                }
            });
            
            // Auto-generate meta title from product name
            $('#name').on('blur', function() {
                const name = $(this).val();
                if (name && !$('#meta_title').val()) {
                    $('#meta_title').val(name + ' - GadgetLoop');
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
    </script>
</body>
</html>