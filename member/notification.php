<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: /GadgetLoop/login.php');
    exit;
}

if (isAdmin()) {
    header('Location: /GadgetLoop/admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

$success = '';
$error = '';

// Handle notification actions
if ($_POST) {
    if (isset($_POST['mark_read'])) {
        $notification_id = intval($_POST['notification_id']);
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);
            $success = 'Notification marked as read';
        } catch (PDOException $e) {
            $error = 'Failed to update notification: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['mark_all_read'])) {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            $success = 'All notifications marked as read';
        } catch (PDOException $e) {
            $error = 'Failed to update notifications: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_notification'])) {
        $notification_id = intval($_POST['notification_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);
            $success = 'Notification deleted';
        } catch (PDOException $e) {
            $error = 'Failed to delete notification: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['clear_all'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $success = 'All notifications cleared';
        } catch (PDOException $e) {
            $error = 'Failed to clear notifications: ' . $e->getMessage();
        }
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query
$where_conditions = ['user_id = ?'];
$params = [$user_id];

if ($filter === 'unread') {
    $where_conditions[] = 'is_read = 0';
} elseif ($filter === 'read') {
    $where_conditions[] = 'is_read = 1';
}

$where_clause = implode(' AND ', $where_conditions);

// Get notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE $where_clause
    ORDER BY created_at DESC
");
$stmt->execute($params);
$notifications = $stmt->fetchAll();

// Get notification counts
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_notifications,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_notifications
    FROM notifications 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/member.css">
    <style>
        .notifications-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .notifications-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex: 1;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        .notifications-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #e9ecef;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .notifications-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .notification-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .notification-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .notification-card.unread {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .notification-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .notification-message {
            color: #666;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .notification-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #999;
        }
        
        .notification-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .notification-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .notification-type {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .type-success {
            background: #d4edda;
            color: #155724;
        }
        
        .type-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .type-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .unread-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 8px;
            height: 8px;
            background: #007bff;
            border-radius: 50%;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .notifications-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .notifications-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .notifications-filters {
                flex-wrap: wrap;
            }
            
            .notifications-actions {
                flex-direction: column;
            }
            
            .notification-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .notification-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body data-page="notifications" class="logged-in">
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="member-layout">
                <?php include 'includes/member-sidebar.php'; ?>
                
                <div class="member-content">
                    <div class="page-header">
                        <h1>My Notifications</h1>
                        <p>Stay updated with your account activity and order updates</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <!-- Notification Statistics -->
                    <div class="notifications-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['total_notifications']; ?></div>
                            <div class="stat-label">Total</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['unread_notifications']; ?></div>
                            <div class="stat-label">Unread</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['read_notifications']; ?></div>
                            <div class="stat-label">Read</div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="notifications-filters">
                        <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            All (<?php echo $stats['total_notifications']; ?>)
                        </a>
                        <a href="?filter=unread" class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                            Unread (<?php echo $stats['unread_notifications']; ?>)
                        </a>
                        <a href="?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
                            Read (<?php echo $stats['read_notifications']; ?>)
                        </a>
                    </div>
                    
                    <!-- Bulk Actions -->
                    <?php if (!empty($notifications)): ?>
                        <div class="notifications-actions">
                            <?php if ($stats['unread_notifications'] > 0): ?>
                                <form method="POST" style="display: inline;">
                                    <button type="submit" name="mark_all_read" class="btn btn-primary btn-sm">
                                        Mark All as Read
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear all notifications?')">
                                <button type="submit" name="clear_all" class="btn btn-danger btn-sm">
                                    Clear All
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Notifications List -->
                    <div class="notifications-content">
                        <?php if (empty($notifications)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">🔔</div>
                                <h3>No notifications</h3>
                                <p>You're all caught up! New notifications will appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="notifications-list">
                                <?php foreach ($notifications as $notification): ?>
                                    <?php
                                        $type = $notification['type'] ?? 'other';
                                        $title = $notification['title'] ?? '';
                                    ?>
                                    <div class="notification-card <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                        <?php if (!$notification['is_read']): ?>
                                            <div class="unread-indicator"></div>
                                        <?php endif; ?>
                                        <div class="notification-header">
                                            <div style="display: flex; align-items: flex-start;">
                                                <div class="notification-icon">
                                                    <?php
                                                    switch ($type) {
                                                        case 'order': echo '📦'; break;
                                                        case 'payment': echo '💳'; break;
                                                        case 'shipping': echo '🚚'; break;
                                                        case 'review': echo '⭐'; break;
                                                        case 'promotion': echo '🎉'; break;
                                                        case 'account': echo '👤'; break;
                                                        default: echo '🔔'; break;
                                                    }
                                                    ?>
                                                </div>
                                                <div class="notification-content">
                                                    <div class="notification-title"><?php echo htmlspecialchars($title); ?></div>
                                                    <div class="notification-message"><?php echo nl2br(htmlspecialchars($notification['message'] ?? '')); ?></div>
                                                </div>
                                            </div>
                                            <div class="notification-actions">
                                                <?php if (!$notification['is_read']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                        <button type="submit" name="mark_read" class="btn btn-outline btn-sm">Mark Read</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this notification?')">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" name="delete_notification" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="notification-meta">
                                            <div class="notification-date">
                                                <span>📅 <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></span>
                                                <?php if ($notification['is_read']): ?>
                                                    <span>• Read <?php echo date('M j, Y g:i A', strtotime($notification['read_at'])); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="notification-type type-<?php echo $type; ?>">
                                                <?php echo ucfirst($type); ?>
                                            </span>
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
    
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="../js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-refresh notifications every 30 seconds
            setInterval(function() {
                // Check for new notifications
                $.get('ajax/check-notifications.php', function(data) {
                    if (data.new_count > 0) {
                        // Show notification badge in header
                        updateNotificationBadge(data.total_unread);
                    }
                });
            }, 30000);
            
            // Mark notification as read when clicked
            $('.notification-card.unread').on('click', function() {
                const $card = $(this);
                const notificationId = $card.find('input[name="notification_id"]').val();
                
                if (notificationId) {
                    $.post('ajax/mark-notification-read.php', {
                        notification_id: notificationId
                    }, function(response) {
                        if (response.success) {
                            $card.removeClass('unread');
                            $card.find('.unread-indicator').remove();
                            updateNotificationCounts();
                        }
                    });
                }
            });
        });
        
        function updateNotificationBadge(count) {
            const $badge = $('#notification-badge');
            if (count > 0) {
                $badge.text(count).show();
            } else {
                $badge.hide();
            }
        }
        
        function updateNotificationCounts() {
            // Update the statistics
            location.reload();
        }
    </script>
</body>
</html>