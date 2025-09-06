<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Manage Categories';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('dashboard.php');
}

$success = '';
$error = '';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_category') {
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $slug = generate_slug($name);
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            // Check if category already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM content_categories WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'A category with this name already exists.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO content_categories (name, slug, description, is_active) VALUES (?, ?, ?, 1)");
                if ($stmt->execute([$name, $slug, $description])) {
                    $success = 'Category created successfully.';
                    log_activity('category_created', "Created category: $name");
                } else {
                    $error = 'Failed to create category.';
                }
            }
        }
    } elseif ($action === 'update_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $slug = generate_slug($name);
        
        if (empty($name) || !$id) {
            $error = 'Category name and ID are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE content_categories SET name = ?, slug = ?, description = ?, is_active = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $description, $is_active, $id])) {
                $success = 'Category updated successfully.';
                log_activity('category_updated', "Updated category: $name");
            } else {
                $error = 'Failed to update category.';
            }
        }
    } elseif ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id) {
            // Check if category has articles
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE category_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Cannot delete category that contains articles.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM content_categories WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $success = 'Category deleted successfully.';
                    log_activity('category_deleted', "Deleted category ID: $id");
                } else {
                    $error = 'Failed to delete category.';
                }
            }
        }
    }
}

// Get all categories
$stmt = $pdo->query("
    SELECT cc.*, COUNT(wa.id) as article_count 
    FROM content_categories cc 
    LEFT JOIN wiki_articles wa ON cc.id = wa.category_id 
    GROUP BY cc.id 
    ORDER BY cc.name
");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Categories</h1>
        <a href="admin.php" class="btn">Back to Admin Panel</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Create New Category -->
    <div class="card">
        <h2>Create New Category</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_category">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Category</button>
        </form>
    </div>
    
    <!-- Existing Categories -->
    <div class="card">
        <h2>Existing Categories</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Articles</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['slug']); ?></td>
                        <td><?php echo htmlspecialchars(truncate_text($category['description'], 100)); ?></td>
                        <td><?php echo number_format($category['article_count']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo addslashes($category['description']); ?>', <?php echo $category['is_active']; ?>)">
                                Edit
                            </button>
                            <?php if ($category['article_count'] == 0): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Edit Category</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update_category">
            <input type="hidden" name="category_id" id="edit_category_id">
            <div class="form-group">
                <label for="edit_name">Category Name *</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea id="edit_description" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active">
                    Active
                </label>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.admin-header h1 {
    color: #2c3e50;
    margin: 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.table-responsive {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.status-badge.active {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background-color: #f8d7da;
    color: #721c24;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    margin-right: 0.5rem;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1rem;
}
</style>

<script>
function editCategory(id, name, description, isActive) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_is_active').checked = isActive == 1;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
