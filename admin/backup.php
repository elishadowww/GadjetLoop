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

// Handle backup creation
if ($_POST && isset($_POST['create_backup'])) {
    $backup_type = sanitizeInput($_POST['backup_type']);
    
    try {
        $backup_filename = 'gadgetloop_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = '../backups/' . $backup_filename;
        
        // Create backups directory if it doesn't exist
        if (!is_dir('../backups')) {
            mkdir('../backups', 0755, true);
        }
        
        // Generate backup SQL
        $backup_sql = generateDatabaseBackup($pdo, $backup_type);
        
        if (file_put_contents($backup_path, $backup_sql)) {
            $success = "Backup created successfully: $backup_filename";
        } else {
            $error = 'Failed to create backup file';
        }
    } catch (Exception $e) {
        $error = 'Backup failed: ' . $e->getMessage();
    }
}

// Handle backup deletion
if ($_POST && isset($_POST['delete_backup'])) {
    $backup_file = sanitizeInput($_POST['backup_file']);
    $backup_path = '../backups/' . $backup_file;
    
    if (file_exists($backup_path) && unlink($backup_path)) {
        $success = 'Backup deleted successfully';
    } else {
        $error = 'Failed to delete backup';
    }
}

// Get existing backups
$backups = [];
if (is_dir('../backups')) {
    $backup_files = glob('../backups/*.sql');
    foreach ($backup_files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'size' => filesize($file),
            'created' => filemtime($file)
        ];
    }
    // Sort by creation date (newest first)
    usort($backups, function($a, $b) {
        return $b['created'] - $a['created'];
    });
}

function generateDatabaseBackup($pdo, $backup_type) {
    $sql = "-- GadgetLoop Database Backup\n";
    $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Backup Type: $backup_type\n\n";
    
    $tables = [];
    
    if ($backup_type === 'full') {
        // Get all tables
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
    } else {
        // Structure only - just get table definitions
        $tables = ['users', 'categories', 'products', 'orders', 'order_items', 'reviews', 'coupons'];
    }
    
    foreach ($tables as $table) {
        // Get table structure
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql .= "-- Table structure for `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $row['Create Table'] . ";\n\n";
        
        if ($backup_type === 'full') {
            // Get table data
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $sql .= "-- Data for table `$table`\n";
                
                foreach ($rows as $row) {
                    $values = array_map(function($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote($value);
                    }, array_values($row));
                    
                    $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
    }
    
    return $sql;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-page-header">
                <h1>Database Backup</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Create Backup -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Create New Backup</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="backup_type">Backup Type</label>
                            <select id="backup_type" name="backup_type" class="form-control" required>
                                <option value="full">Full Backup (Structure + Data)</option>
                                <option value="structure">Structure Only</option>
                            </select>
                            <small class="form-text">
                                Full backup includes all data. Structure only includes table definitions without data.
                            </small>
                        </div>
                        
                        <button type="submit" name="create_backup" class="btn btn-primary">Create Backup</button>
                    </form>
                </div>
            </div>
            
            <!-- Existing Backups -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Existing Backups (<?php echo count($backups); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($backups)): ?>
                        <p class="text-center">No backups found. Create your first backup above.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($backup['filename']); ?></strong>
                                        </td>
                                        <td><?php echo formatFileSize($backup['size']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', $backup['created']); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="../backups/<?php echo htmlspecialchars($backup['filename']); ?>" 
                                                   class="btn btn-outline btn-sm" download>Download</a>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this backup?')">
                                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                                    <button type="submit" name="delete_backup" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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

<?php
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>