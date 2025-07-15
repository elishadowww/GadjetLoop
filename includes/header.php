<?php
if (!isset($pdo)) {
    $configPath1 = __DIR__ . '/config.php';
    $configPath2 = __DIR__ . '/../includes/config.php';
    if (file_exists($configPath1)) {
        require_once $configPath1;
    } elseif (file_exists($configPath2)) {
        require_once $configPath2;
    }
}
require_once __DIR__ . '/functions.php';
?>

<header class="main-header">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <a href="index.php">
                    <h1>GadgetLoop</h1>
                </a>
            </div>
            
            <div class="search-bar">
                <form action="products.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search for gadgets..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-btn">🔍</button>
                </form>
            </div>
            
            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                    <div class="user-menu">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="dropdown">
                            <button class="dropdown-btn">Account ▼</button>
                            <div class="dropdown-content">
                                <a href="/GadjetLoop/member/profile.php">Profile</a>
                                <a href="/GadjetLoop/member/orders.php">Orders</a>
                                <a href="/GadjetLoop/member/wishlist.php">Wishlist</a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/dashboard.php">Admin Panel</a>
                                <?php endif; ?>
                                <a href="/GadjetLooplogout.php">Logout</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cart-icon">
                        <a href="cart.php">
                            🛒 <span class="cart-count" id="cart-count"><?php
                                if (isLoggedIn()) {
                                    try {
                                        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $cartCount = (int)$stmt->fetchColumn();
                                        echo $cartCount;
                                    } catch (Exception $e) {
                                        echo '0';
                                    }
                                } else {
                                    echo '0';
                                }
                            ?></span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <nav class="main-nav">
            <ul class="nav-menu">
                <li><a href="/GadjetLoop/index.php">Home</a></li>
                <li class="dropdown">
                    <a href="/GadjetLoop/products.php">Products ▼</a>
                    <div class="dropdown-content">
                        <?php
                        $categories = getCategories($pdo);
                        foreach ($categories as $category):
                        ?>
                        <a href="products.php?category=<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li><a href="/GadjetLoop/about.php">About</a></li>
                <li><a href="/GadjetLoop/contact.php">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>