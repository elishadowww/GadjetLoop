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

// Handle review actions
if ($_POST) {
    if (isset($_POST['approve_review'])) {
        $review_id = intval($_POST['review_id']);
        try {
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
            $stmt->execute([$review_id]);
            $success = 'Review approved successfully';
        } catch (PDOException $e) {
            $error = 'Failed to approve review';
        }
    }
    
    if (isset($_POST['reject_review'])) {
        $review_id = intval($_POST['review_id']);
        try {
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?");
            $stmt->execute([$review_id]);
            $success = 'Review rejected successfully';
        } catch (PDOException $e) {
            $error = 'Failed to reject review';
        }
    }
    
    if (isset($_POST['delete_review'])) {
        $review_id = intval($_POST['review_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$review_id]);
            $success = 'Review deleted successfully';
        } catch (PDOException $e) {
            $error = 'Failed to delete review';
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$rating_filter = $_GET['rating'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = ['r.id IS NOT NULL'];
$params = [];

if ($status_filter === 'approved') {
    $where_conditions[] = 'r.is_approved = 1';
} elseif ($status_filter === 'pending') {
    $where_conditions[] = 'r.is_approved = 0';
}

if ($rating_filter) {
    $where_conditions[] = 'r.rating = ?';
    $params[] = $rating_filter;
}

if ($search) {
    $where_conditions[] = '(p.name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR r.title LIKE ? OR r.comment LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Get reviews
$stmt = $pdo->prepare("
    SELECT r.*, p.name as product_name, p.main_image as product_image,
    u.first_name, u.last_name, u.email
    FROM reviews r 
    JOIN products p ON r.product_id = p.id 
    JOIN users u ON r.user_id = u.id
    WHERE $where_clause
    ORDER BY r.created_at DESC
");
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Get review statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_reviews,
        SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending_reviews,
        AVG(rating) as average_rating
    FROM reviews
");
$stmt->execute();
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Reviews</h1>
                <div class="admin-actions">
                    <button class="btn btn-outline" onclick="exportData('reviews', 'csv')">Export CSV</button>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Review Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_reviews']); ?></h3>
                        <p>Total Reviews</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['approved_reviews']); ?></h3>
                        <p>Approved Reviews</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['pending_reviews']); ?></h3>
                        <p>Pending Reviews</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['average_rating'], 1); ?></h3>
                        <p>Average Rating</p>
                    </div>
                </div>
            </div>
            
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
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Product, user, or review content">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select id="rating" name="rating" class="form-control">
                                    <option value="">All Ratings</option>
                                    <option value="5" <?php echo $rating_filter === '5' ? 'selected' : ''; ?>>5 Stars</option>
                                    <option value="4" <?php echo $rating_filter === '4' ? 'selected' : ''; ?>>4 Stars</option>
                                    <option value="3" <?php echo $rating_filter === '3' ? 'selected' : ''; ?>>3 Stars</option>
                                    <option value="2" <?php echo $rating_filter === '2' ? 'selected' : ''; ?>>2 Stars</option>
                                    <option value="1" <?php echo $rating_filter === '1' ? 'selected' : ''; ?>>1 Star</option>
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
            
            <!-- Reviews List -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Reviews (<?php echo count($reviews); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <p class="text-center">No reviews found.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <?php foreach ($reviews as $review): ?>
                            <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 1.5rem;">
                                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                    <img src="../images/products/<?php echo htmlspecialchars($review['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($review['product_name']); ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between; align-items: start;">
                                            <div>
                                                <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($review['product_name']); ?></h4>
                                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                                    <div><?php echo generateStarRating($review['rating']); ?></div>
                                                    <span style="font-weight: 600;"><?php echo $review['rating']; ?>/5</span>
                                                </div>
                                                <p style="margin: 0; color: #666; font-size: 14px;">
                                                    By <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?> 
                                                    on <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <span class="status-badge status-<?php echo $review['is_approved'] ? 'approved' : 'pending'; ?>">
                                                    <?php echo $review['is_approved'] ? 'Approved' : 'Pending'; ?>
                                                </span>
                                                <?php if ($review['is_verified_purchase']): ?>
                                                    <span class="status-badge" style="background: #28a745; color: white; margin-left: 0.5rem;">Verified Purchase</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($review['title']): ?>
                                    <h5 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($review['title']); ?></h5>
                                <?php endif; ?>
                                
                                <p style="margin: 0 0 1rem 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if (!$review['is_approved']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" name="approve_review" class="btn btn-primary btn-sm">Approve</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" name="reject_review" class="btn btn-outline btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this review?')">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" name="delete_review" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>