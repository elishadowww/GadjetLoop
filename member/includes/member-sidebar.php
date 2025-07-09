<aside class="member-sidebar">
    <div class="member-profile-card">
        <div class="profile-avatar">
            <?php if ($user['profile_photo']): ?>
                <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                     alt="Profile Photo">
            <?php else: ?>
                <div class="default-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <span class="member-since">Member since <?php echo date('Y', strtotime($user['created_at'])); ?></span>
        </div>
    </div>
    
    <nav class="member-nav">
        <ul class="nav-menu">
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
                <a href="orders.php">
                    <span class="nav-icon">🛒</span>
                    <span class="nav-text">My Orders</span>
                    <?php
                    // Get pending orders count
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending', 'processing')");
                    $stmt->execute([$user_id]);
                    $pending_orders = $stmt->fetchColumn();
                    if ($pending_orders > 0):
                    ?>
                        <span class="nav-badge"><?php echo $pending_orders; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'wishlist.php' ? 'active' : ''; ?>">
                <a href="wishlist.php">
                    <span class="nav-icon">♡</span>
                    <span class="nav-text">Wishlist</span>
                    <?php
                    // Get wishlist count
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $wishlist_count = $stmt->fetchColumn();
                    if ($wishlist_count > 0):
                    ?>
                        <span class="nav-badge"><?php echo $wishlist_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'reviews.php' ? 'active' : ''; ?>">
                <a href="reviews.php">
                    <span class="nav-icon">⭐</span>
                    <span class="nav-text">My Reviews</span>
                
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'addresses.php' ? 'active' : ''; ?>">
                <a href="addresses.php">
                    <span class="nav-icon">📍</span>
                    <span class="nav-text">Addresses</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : ''; ?>">
                <a href="notifications.php">
                    <span class="nav-icon">🔔</span>
                    <span class="nav-text">Notifications</span>
                    <?php
                    // Get unread notifications count
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                    $stmt->execute([$user_id]);
                    $unread_notifications = $stmt->fetchColumn();
                    if ($unread_notifications > 0):
                    ?>
                        <span class="nav-badge"><?php echo $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <li class="nav-item">
                <a href="../index.php">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-text">Back to Store</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="../logout.php" class="logout-link">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="help-section">
            <h4>Need Help?</h4>
            <p>Contact our customer support team</p>
            <a href="../contact.php" class="btn btn-outline btn-sm btn-block">Contact Support</a>
        </div>
    </div>
</aside>

<script>
$(document).ready(function() {
    // Mobile sidebar toggle
    $('.member-sidebar-toggle').on('click', function() {
        $('.member-sidebar').toggleClass('active');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.member-sidebar, .member-sidebar-toggle').length) {
                $('.member-sidebar').removeClass('active');
            }
        }
    });
    
    // Logout confirmation
    $('.logout-link').on('click', function(e) {
        if (!confirm('Are you sure you want to logout?')) {
            e.preventDefault();
        }
    });
});
</script>