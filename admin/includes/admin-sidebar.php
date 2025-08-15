<?php
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$order_count = $stmt->fetchColumn();
?>
<aside class="admin-sidebar">
    <nav class="admin-nav">
        <ul class="nav-menu">
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Products</span>
            </li>
            
            <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : ''; ?>">
                <a href="products.php">
                    <span class="nav-icon">📦</span>
                    <span class="nav-text">Products</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'category-add.php', 'category-edit.php']) ? 'active' : ''; ?>">
                <a href="categories.php">
                    <span class="nav-icon">📂</span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Orders</span>
            </li>
            
            <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['orders.php', 'order-detail.php']) ? 'active' : ''; ?>">
                <a href="orders.php">
                    <span class="nav-icon">🛒</span>
                    <span class="nav-text">Orders</span>
                    <span class="nav-badge"><?php echo (int)$order_count; ?></span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Users</span>
            </li>
            
            <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'user-detail.php']) ? 'active' : ''; ?>">
                <a href="users.php">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'reviews.php' ? 'active' : ''; ?>">
                <a href="reviews.php">
                    <span class="nav-icon">⭐</span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Marketing</span>
            </li>
            
            <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['coupons.php', 'coupon-add.php', 'coupon-edit.php']) ? 'active' : ''; ?>">
                <a href="coupons.php">
                    <span class="nav-icon">🎫</span>
                    <span class="nav-text">Coupons</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Reports</span>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : ''; ?>">
                <a href="analytics.php">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Analytics</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">System</span>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'backup.php' ? 'active' : ''; ?>">
                <a href="backup.php">
                    <span class="nav-icon">💾</span>
                    <span class="nav-text">Backup</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">        
        <div class="quick-actions">
            <a href="products.php" class="btn btn-primary btn-sm btn-block">Add Product</a>
            <a href="../index.php" target="_blank" class="btn btn-outline btn-sm btn-block">View Site</a>
        </div>
    </div>
</aside>

<script>
$(document).ready(function() {
    // Sidebar toggle for mobile
    $('.sidebar-toggle').on('click', function() {
        $('.admin-sidebar').toggleClass('active');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.admin-sidebar, .sidebar-toggle').length) {
                $('.admin-sidebar').removeClass('active');
            }
        }
    });
    
    // Highlight current page
    const currentPage = window.location.pathname.split('/').pop();
    $('.nav-item a').each(function() {
        const href = $(this).attr('href');
        if (href === currentPage) {
            $(this).parent().addClass('active');
        }
    });
});
</script>
