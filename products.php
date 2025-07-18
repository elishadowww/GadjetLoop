<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get filter parameters
$filters = [
    'category' => $_GET['category'] ?? '',
    'search' => $_GET['search'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest'
];

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;

// Get products and categories
$products = getProducts($pdo, $filters, $page, $per_page);
$categories = getCategories($pdo);

// Get price range for filter
$stmt = $pdo->prepare("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE is_active = 1");
$stmt->execute();
$price_range = $stmt->fetch();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM products p WHERE p.is_active = 1";
$count_params = [];

if (!empty($filters['category'])) {
    $count_sql .= " AND p.category_id = ?";
    $count_params[] = $filters['category'];
}

if (!empty($filters['search'])) {
    $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $count_params[] = '%' . $filters['search'] . '%';
    $count_params[] = '%' . $filters['search'] . '%';
}

if (!empty($filters['min_price'])) {
    $count_sql .= " AND p.price >= ?";
    $count_params[] = $filters['min_price'];
}

if (!empty($filters['max_price'])) {
    $count_sql .= " AND p.price <= ?";
    $count_params[] = $filters['max_price'];
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Get current category name
$current_category = '';
if (!empty($filters['category'])) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$filters['category']]);
    $current_category = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_category ? $current_category . ' - ' : ''; ?>Products - GadgetLoop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/products.css">
</head>
<body data-page="products" class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="products.php">Products</a>
                <?php if ($current_category): ?>
                    <span>/</span>
                    <span><?php echo htmlspecialchars($current_category); ?></span>
                <?php endif; ?>
            </nav>
            
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <?php if ($current_category): ?>
                        <?php echo htmlspecialchars($current_category); ?>
                    <?php elseif (!empty($filters['search'])): ?>
                        Search Results for "<?php echo htmlspecialchars($filters['search']); ?>"
                    <?php else: ?>
                        All Products
                    <?php endif; ?>
                </h1>
                <p class="results-count"><?php echo $total_products; ?> products found</p>
            </div>
            
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <div class="filters-header">
                        <h3>Filters</h3>
                        <button type="button" class="clear-filters">Clear All</button>
                    </div>
                    
                    <form class="filter-form" method="GET">
                        <!-- Keep search term -->
                        <?php if (!empty($filters['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>">
                        <?php endif; ?>
                        
                        <!-- Categories Filter -->
                        <div class="filter-group">
                            <h4>Categories</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="category" value="" 
                                           <?php echo empty($filters['category']) ? 'checked' : ''; ?>>
                                    <span>All Categories</span>
                                </label>
                                <?php foreach ($categories as $category): ?>
                                <label class="filter-option">
                                    <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                           <?php echo $filters['category'] == $category['id'] ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="filter-group">
                            <h4>Price Range</h4>
                            <div class="price-range-container">
                                <div id="price-range" 
                                     data-min="<?php echo $price_range['min_price']; ?>"
                                     data-max="<?php echo $price_range['max_price']; ?>"
                                     data-current-min="<?php echo $filters['min_price'] ?: $price_range['min_price']; ?>"
                                     data-current-max="<?php echo $filters['max_price'] ?: $price_range['max_price']; ?>">
                                </div>
                                <div class="price-inputs">
                                    <input type="number" name="min_price" id="price-min" 
                                           value="<?php echo $filters['min_price']; ?>" 
                                           placeholder="Min" min="0">
                                    <span>to</span>
                                    <input type="number" name="max_price" id="price-max" 
                                           value="<?php echo $filters['max_price']; ?>" 
                                           placeholder="Max" min="0">
                                </div>
                                <div id="price-display"></div>
                            </div>
                        </div>
                        
                        <!-- Rating Filter -->
                        <div class="filter-group">
                            <h4>Customer Rating</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" name="rating" value="">
                                    <span>All Ratings</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="rating" value="4">
                                    <span>★★★★☆ & up</span>
                                </label>
                                <label class="filter-option">
                                    <input type="radio" name="rating" value="3">
                                    <span>★★★☆☆ & up</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Availability Filter -->
                        <div class="filter-group">
                            <h4>Availability</h4>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" name="in_stock" value="1">
                                    <span>In Stock Only</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="on_sale" value="1">
                                    <span>On Sale</span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    </form>
                </aside>
                
                <!-- Products Content -->
                <div class="products-content">
                    <!-- Toolbar -->
                    <div class="products-toolbar">
                        <div class="view-toggle">
                            <button type="button" data-view="grid" class="view-btn active">⊞ Grid</button>
                            <button type="button" data-view="list" class="view-btn">☰ List</button>
                        </div>
                        
                        <div class="sort-options">
                            <label for="sort-select">Sort by:</label>
                            <select id="sort-select" name="sort">
                                <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_asc" <?php echo $filters['sort'] === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_desc" <?php echo $filters['sort'] === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name_asc" <?php echo $filters['sort'] === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                                <option value="name_desc" <?php echo $filters['sort'] === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                                <option value="rating" <?php echo $filters['sort'] === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Products Grid -->
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <div class="no-products-icon">📦</div>
                            <h3>No products found</h3>
                            <p>Try adjusting your filters or search terms.</p>
                            <a href="products.php" class="btn btn-primary">View All Products</a>
                        </div>
                    <?php else: ?>
                        <div class="product-grid grid-view">
                            <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                        <img src="images/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </a>
                                    <?php if ($product['discount_percentage'] > 0): ?>
                                        <span class="discount-badge"><?php echo $product['discount_percentage']; ?>% OFF</span>
                                    <?php endif; ?>
                                    <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                        <span class="low-stock-badge">Only <?php echo $product['stock_quantity']; ?> left</span>
                                    <?php elseif ($product['stock_quantity'] == 0): ?>
                                        <span class="out-of-stock-badge">Out of Stock</span>
                                    <?php endif; ?>
                                    
                                    <div class="product-overlay">
                                        <button class="btn btn-outline quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                            Quick View
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <div class="product-category">
                                        <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </a>
                                    </div>
                                    
                                    <h3>
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="product-rating">
                                        <?php echo generateStarRating($product['average_rating'] ?: 0); ?>
                                        <span class="rating-count">(<?php echo $product['review_count']; ?>)</span>
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
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize filters from localStorage
            const savedView = localStorage.getItem('product-view') || 'grid';
            toggleProductView(savedView);

            // View toggle functionality
            $('.view-btn').on('click', function() {
                const view = $(this).data('view');
                toggleProductView(view);
            });

            // Clear filters
            $('.clear-filters').on('click', function() {
                window.location.href = 'products.php';
            });

            // Handle sort changes
            $('#sort-select').on('change', function() {
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('sort', $(this).val());
                currentUrl.searchParams.set('page', '1'); // Reset to first page
                window.location.href = currentUrl.toString();
            });

            // Quick View modal
            $(document).on('click', '.quick-view-btn', function(e) {
                e.preventDefault();
                const productId = $(this).data('product-id');
                $('#quickViewContent').html('Loading...');
                $('#quickViewModal').show();

                $.ajax({
                    url: 'quick_view.php',
                    type: 'GET',
                    data: { id: productId },
                    success: function(response) {
                        $('#quickViewContent').html(response);
                    },
                    error: function() {
                        $('#quickViewContent').html('Error loading product details.');
                    }
                });
            });

            $('#closeQuickView').on('click', function() {
                $('#quickViewModal').hide();
            });

            // Handle filter changes
            $('.filter-form input, .filter-form select').on('change', function() {
                $(this).closest('form').submit();
            });
        });
    </script>

    <div id="quickViewModal" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); background:#fff; z-index:9999; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.2); min-width:300px;">
    <span id="closeQuickView" style="cursor:pointer; float:right; font-size:20px;">&times;</span>
    <div id="quickViewContent"></div>
</div>
</body>
</html>