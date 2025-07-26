<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle product actions
$success = '';
$error = '';

if ($_POST) {
    if (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        try {
            $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
            $stmt->execute([$product_id]);
            $success = 'Product deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete product';
        }
    }
    
    if (isset($_POST['toggle_featured'])) {
        $product_id = intval($_POST['product_id']);
        try {
            $stmt = $pdo->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->execute([$product_id]);
            $success = 'Product featured status updated';
        } catch (PDOException $e) {
            $error = 'Failed to update product';
        }
    }
}

// Get filters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = ['p.id IS NOT NULL'];
$params = [];

if ($category_filter) {
    $where_conditions[] = 'p.category_id = ?';
    $params[] = $category_filter;
}

if ($status_filter === 'active') {
    $where_conditions[] = 'p.is_active = 1';
} elseif ($status_filter === 'inactive') {
    $where_conditions[] = 'p.is_active = 0';
} elseif ($status_filter === 'featured') {
    $where_conditions[] = 'p.is_featured = 1';
} elseif ($status_filter === 'low_stock') {
    $where_conditions[] = 'p.stock_quantity <= p.low_stock_threshold';
}

if ($search) {
    $where_conditions[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Get products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name,
    AVG(r.rating) as average_rating,
    COUNT(r.id) as review_count
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN reviews r ON p.id = r.product_id 
    WHERE $where_clause
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = getCategories($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Products</h1>
                <div class="admin-actions">
                    <a href="product-add.php" class="btn btn-primary">Add Product</a>
                    <button class="btn btn-outline" onclick="exportData('products', 'csv')">Export CSV</button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Filters</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Product name or SKU">
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="featured" <?php echo $status_filter === 'featured' ? 'selected' : ''; ?>>Featured</option>
                                    <option value="low_stock" <?php echo $status_filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Products (<?php echo count($products); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="../images/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <br>
                                            <small>SKU: <?php echo htmlspecialchars($product['sku']); ?></small>
                                            <?php if ($product['is_featured']): ?>
                                                <span class="status-badge" style="background: #007bff; color: white; font-size: 10px;">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>
                                        <?php if ($product['discount_percentage'] > 0): ?>
                                            <span style="text-decoration: line-through; color: #999;">RM<?php echo number_format($product['price'], 2); ?></span><br>
                                            <strong style="color: #dc3545;">RM<?php echo number_format($product['price'] * (1 - $product['discount_percentage'] / 100), 2); ?></strong>
                                        <?php else: ?>
                                            <strong>RM<?php echo number_format($product['price'], 2); ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo $product['stock_quantity'] <= $product['low_stock_threshold'] ? 'stock-level low-stock' : ''; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['review_count'] > 0): ?>
                                            <?php echo number_format($product['average_rating'], 1); ?> ⭐ (<?php echo $product['review_count']; ?>)
                                        <?php else: ?>
                                            No reviews
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="toggle_featured" class="btn btn-outline btn-sm">
                                                    <?php echo $product['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>