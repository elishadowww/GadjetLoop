<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {

$success = '';
$error = '';

if (isset($_POST['update_category'])) {
    $id = intval($_POST['category_id']);
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $sort_order = intval($_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $description, $sort_order, $is_active, $id]);
        $success = 'Category updated successfully';
    } catch (PDOException $e) {
        $error = 'Failed to update category';
    }
}

if (isset($_POST['delete_category'])) {
    $id = intval($_POST['category_id']);
    try {
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $product_count = $stmt->fetchColumn();
        if ($product_count > 0) {
            $error = 'Cannot delete category with existing products';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Category deleted successfully';
        }
    } catch (PDOException $e) {
        $error = 'Failed to delete category';
    }
}
}

// Get categories with product counts
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
    GROUP BY c.id 
    ORDER BY c.sort_order, c.name
");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin - GadgetLoop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body data-page="admin">
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-page-header"> 
                <h1>Categories</h1>
                
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Categories Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Categories (<?php echo count($categories); ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td><?php echo $category['product_count']; ?> products</td>
                                    <td><?php echo $category['sort_order']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button class="btn btn-outline btn-sm" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">Edit</button>
                                            <?php if ($category['product_count'] == 0): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            <?php endif; ?>
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
    
    
    <!-- Edit Category Modal -->
    <div id="edit-category-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px;">
            <h3>Edit Category</h3>
            <form method="POST" id="edit-category-form">
                <input type="hidden" name="category_id" id="edit-category-id">
                <div class="form-group">
                    <label for="edit-name">Name *</label>
                    <input type="text" id="edit-name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit-sort-order">Sort Order</label>
                    <input type="number" id="edit-sort-order" name="sort_order" class="form-control">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="edit-is-active" name="is_active"> Active
                    </label>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="hideEditCategoryModal()">Cancel</button>
                    <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>


        
        function editCategory(category) {
            document.getElementById('edit-category-id').value = category.id;
            document.getElementById('edit-name').value = category.name;
            document.getElementById('edit-description').value = category.description || '';
            document.getElementById('edit-sort-order').value = category.sort_order;
            document.getElementById('edit-is-active').checked = category.is_active == 1;
            document.getElementById('edit-category-modal').style.display = 'block';
        }
        
        function hideEditCategoryModal() {
            document.getElementById('edit-category-modal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {

            const editModal = document.getElementById('edit-category-modal');

            if (event.target === editModal) {
                hideEditCategoryModal();
            }
        }
    </script>
</body>
</html>