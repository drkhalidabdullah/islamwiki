<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Categories';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_category') {
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $color = sanitize_input($_POST['color'] ?? '#3498db');
        $parent_id = (int)($_POST['parent_id'] ?? 0);
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO content_categories (name, description, color, parent_id, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$name, $description, $color, $parent_id ?: null])) {
                    $success = 'Category created successfully.';
                    log_activity('category_created', "Created category: $name");
                } else {
                    $error = 'Failed to create category.';
                }
            } catch (Exception $e) {
                $error = 'Error creating category: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $color = sanitize_input($_POST['color'] ?? '#3498db');
        $parent_id = (int)($_POST['parent_id'] ?? 0);
        
        if ($category_id > 0 && !empty($name)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE content_categories 
                    SET name = ?, description = ?, color = ?, parent_id = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$name, $description, $color, $parent_id ?: null, $category_id])) {
                    $success = 'Category updated successfully.';
                    log_activity('category_updated', "Updated category: $name");
                } else {
                    $error = 'Failed to update category.';
                }
            } catch (Exception $e) {
                $error = 'Error updating category: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid category data.';
        }
    } elseif ($action === 'delete_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        
        if ($category_id > 0) {
            try {
                // Check if category has children
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM content_categories WHERE parent_id = ?");
                $stmt->execute([$category_id]);
                $has_children = $stmt->fetch()['count'] > 0;
                
                if ($has_children) {
                    $error = 'Cannot delete category with subcategories. Move or delete subcategories first.';
                } else {
                    // Check if category has articles
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wiki_articles WHERE category_id = ?");
                    $stmt->execute([$category_id]);
                    $has_articles = $stmt->fetch()['count'] > 0;
                    
                    if ($has_articles) {
                        $error = 'Cannot delete category with articles. Move articles to another category first.';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM content_categories WHERE id = ?");
                        if ($stmt->execute([$category_id])) {
                            $success = 'Category deleted successfully.';
                            log_activity('category_deleted', "Deleted category ID: $category_id");
                        } else {
                            $error = 'Failed to delete category.';
                        }
                    }
                }
            } catch (Exception $e) {
                $error = 'Error deleting category: ' . $e->getMessage();
            }
        }
    }
}

// Get all categories with hierarchy
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM content_categories WHERE parent_id = c.id) as child_count,
           (SELECT COUNT(*) FROM wiki_articles WHERE category_id = c.id) as article_count
    FROM content_categories c 
    ORDER BY c.parent_id IS NULL DESC, c.name
");
$categories = $stmt->fetchAll();

// Build category hierarchy
$category_tree = [];
$category_lookup = [];

foreach ($categories as $category) {
    $category_lookup[$category['id']] = $category;
    if ($category['parent_id']) {
        if (!isset($category_tree[$category['parent_id']])) {
            $category_tree[$category['parent_id']] = [];
        }
        $category_tree[$category['parent_id']][] = $category;
    } else {
        if (!isset($category_tree[0])) {
            $category_tree[0] = [];
        }
        $category_tree[0][] = $category;
    }
}

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/admin_manage_categories.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/manage_categories.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-folder"></i> Manage Categories</h1>
        <p>Create and manage content categories</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Create Category Form -->
    <div class="card">
        <h3>Create New Category</h3>
        <form method="POST" class="category-form">
            <input type="hidden" name="action" value="create_category">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="parent_id">Parent Category</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">No Parent (Top Level)</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="color">Color</label>
                <input type="color" id="color" name="color" value="#3498db">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Category
                </button>
            </div>
        </form>
    </div>

    <!-- Categories List -->
    <div class="card">
        <h3>Categories</h3>
        
        <?php if (!empty($categories)): ?>
            <div class="categories-list">
                <?php
                function render_category_tree($categories, $parent_id = 0, $level = 0) {
                    if (!isset($categories[$parent_id])) return;
                    
                    foreach ($categories[$parent_id] as $category) {
                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                        echo '<div class="category-item" style="margin-left: ' . ($level * 20) . 'px;">';
                        echo '<div class="category-header">';
                        echo '<span class="category-color" style="background-color: ' . htmlspecialchars($category['color']) . '"></span>';
                        echo '<span class="category-name">' . $indent . htmlspecialchars($category['name']) . '</span>';
                        echo '<span class="category-stats">';
                        echo '<span class="stat">' . $category['child_count'] . ' subcategories</span>';
                        echo '<span class="stat">' . $category['article_count'] . ' articles</span>';
                        echo '</span>';
                        echo '<div class="category-actions">';
                        echo '<button class="btn btn-sm btn-info" onclick="editCategory(' . $category['id'] . ')">Edit</button>';
                        echo '<form method="POST" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this category?\')">';
                        echo '<input type="hidden" name="action" value="delete_category">';
                        echo '<input type="hidden" name="category_id" value="' . $category['id'] . '">';
                        echo '<button type="submit" class="btn btn-sm btn-danger">Delete</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        if ($category['description']) {
                            echo '<div class="category-description">' . htmlspecialchars($category['description']) . '</div>';
                        }
                        echo '</div>';
                        
                        // Render children
                        render_category_tree($categories, $category['id'], $level + 1);
                    }
                }
                
                render_category_tree($category_tree);
                ?>
            </div>
        <?php else: ?>
            <p>No categories found.</p>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <a href="/admin" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Category</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update_category">
            <input type="hidden" name="category_id" id="edit_category_id">
            
            <div class="form-group">
                <label for="edit_name">Category Name *</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="edit_parent_id">Parent Category</label>
                <select id="edit_parent_id" name="parent_id">
                    <option value="">No Parent (Top Level)</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea id="edit_description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_color">Color</label>
                <input type="color" id="edit_color" name="color">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>



<?php include "../../includes/footer.php"; ?>
