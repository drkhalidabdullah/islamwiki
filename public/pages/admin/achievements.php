<?php
/**
 * Admin Achievements Management Page
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

// Check admin permissions
require_permission('admin.access');

// Include achievement extension
require_once __DIR__ . '/../../extensions/achievements/extension.php';

$achievements_extension = new AchievementsExtension();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_achievement':
                $result = $achievements_extension->createAchievement($_POST);
                $message = $result ? 'Achievement created successfully' : 'Failed to create achievement';
                break;
                
            case 'update_achievement':
                $result = $achievements_extension->updateAchievement($_POST['id'], $_POST);
                $message = $result ? 'Achievement updated successfully' : 'Failed to update achievement';
                break;
                
            case 'delete_achievement':
                $result = $achievements_extension->deleteAchievement($_POST['id']);
                $message = $result ? 'Achievement deleted successfully' : 'Failed to delete achievement';
                break;
                
            case 'create_category':
                $result = $achievements_extension->createCategory($_POST);
                $message = $result ? 'Category created successfully' : 'Failed to create category';
                break;
                
            case 'update_category':
                $result = $achievements_extension->updateCategory($_POST['id'], $_POST);
                $message = $result ? 'Category updated successfully' : 'Failed to update category';
                break;
                
            case 'delete_category':
                $result = $achievements_extension->deleteCategory($_POST['id']);
                $message = $result ? 'Category deleted successfully' : 'Failed to delete category';
                break;
        }
    }
}

// Get data for display
$achievements = $achievements_extension->getAllAchievements();
$categories = $achievements_extension->getCategories();
$types = $achievements_extension->getTypes();
$status = $achievements_extension->getStatus();

?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Achievement System Management</h1>
        <div class="admin-actions">
            <button class="btn btn-primary" onclick="openModal('achievementModal')">
                <i class="fas fa-plus"></i> Create Achievement
            </button>
            <button class="btn btn-secondary" onclick="openModal('categoryModal')">
                <i class="fas fa-folder-plus"></i> Create Category
            </button>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $result ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- System Status -->
    <div class="admin-section">
        <h2>System Status</h2>
        <div class="status-grid">
            <div class="status-item">
                <div class="status-label">Extension Status</div>
                <div class="status-value <?php echo $status['enabled'] ? 'enabled' : 'disabled'; ?>">
                    <?php echo $status['enabled'] ? 'Enabled' : 'Disabled'; ?>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label">Version</div>
                <div class="status-value"><?php echo $status['version']; ?></div>
            </div>
            <div class="status-item">
                <div class="status-label">Total Achievements</div>
                <div class="status-value"><?php echo $status['total_achievements']; ?></div>
            </div>
            <div class="status-item">
                <div class="status-label">Total Users</div>
                <div class="status-value"><?php echo $status['total_users']; ?></div>
            </div>
        </div>
    </div>

    <!-- Categories Management -->
    <div class="admin-section">
        <h2>Achievement Categories</h2>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Color</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <div class="category-info">
                                    <div class="color-preview" style="background-color: <?php echo $category['color']; ?>"></div>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo htmlspecialchars($category['color']); ?></td>
                            <td><?php echo $category['sort_order']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="editCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Achievements Management -->
    <div class="admin-section">
        <h2>Achievements</h2>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Rarity</th>
                        <th>Points</th>
                        <th>XP</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($achievements as $achievement): ?>
                        <tr>
                            <td>
                                <div class="achievement-info">
                                    <div class="color-preview" style="background-color: <?php echo $achievement['color']; ?>"></div>
                                    <?php echo htmlspecialchars($achievement['name']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($achievement['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($achievement['type_name']); ?></td>
                            <td>
                                <span class="rarity-badge rarity-<?php echo $achievement['rarity']; ?>">
                                    <?php echo ucfirst($achievement['rarity']); ?>
                                </span>
                            </td>
                            <td><?php echo $achievement['points']; ?></td>
                            <td><?php echo $achievement['xp_reward']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $achievement['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $achievement['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="editAchievement(<?php echo $achievement['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteAchievement(<?php echo $achievement['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Achievement Modal -->
<div id="achievementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create Achievement</h3>
            <button class="modal-close" onclick="closeModal('achievementModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="achievementForm" method="POST">
                <input type="hidden" name="action" value="create_achievement">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="slug">Slug *</label>
                        <input type="text" name="slug" id="slug" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea name="description" id="description" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="long_description">Long Description</label>
                    <textarea name="long_description" id="long_description" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="type_id">Type *</label>
                        <select name="type_id" id="type_id" required>
                            <option value="">Select Type</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="icon">Icon</label>
                        <input type="text" name="icon" id="icon" placeholder="fas fa-trophy">
                    </div>
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="color" name="color" id="color" value="#f39c12">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="rarity">Rarity</label>
                        <select name="rarity" id="rarity">
                            <option value="common">Common</option>
                            <option value="uncommon">Uncommon</option>
                            <option value="rare">Rare</option>
                            <option value="epic">Epic</option>
                            <option value="legendary">Legendary</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="level_requirement">Level Requirement</label>
                        <input type="number" name="level_requirement" id="level_requirement" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="points">Points</label>
                        <input type="number" name="points" id="points" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="xp_reward">XP Reward</label>
                        <input type="number" name="xp_reward" id="xp_reward" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" checked> Active
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('achievementModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Achievement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create Category</h3>
            <button class="modal-close" onclick="closeModal('categoryModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="categoryForm" method="POST">
                <input type="hidden" name="action" value="create_category">
                
                <div class="form-group">
                    <label for="cat_name">Name *</label>
                    <input type="text" name="name" id="cat_name" required>
                </div>
                
                <div class="form-group">
                    <label for="cat_slug">Slug *</label>
                    <input type="text" name="slug" id="cat_slug" required>
                </div>
                
                <div class="form-group">
                    <label for="cat_description">Description</label>
                    <textarea name="description" id="cat_description" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cat_icon">Icon</label>
                        <input type="text" name="icon" id="cat_icon" placeholder="fas fa-folder">
                    </div>
                    
                    <div class="form-group">
                        <label for="cat_color">Color</label>
                        <input type="color" name="color" id="cat_color" value="#3498db">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cat_sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="cat_sort_order" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" checked> Active
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('categoryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Achievement System CSS and JS -->
<link rel="stylesheet" href="/extensions/achievements/assets/css/achievements.css">
<script src="/extensions/achievements/assets/js/achievements.js"></script>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editAchievement(id) {
    // TODO: Implement edit functionality
    alert('Edit functionality coming soon!');
}

function deleteAchievement(id) {
    if (confirm('Are you sure you want to delete this achievement?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_achievement">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function editCategory(id) {
    // TODO: Implement edit functionality
    alert('Edit functionality coming soon!');
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});

document.getElementById('cat_name').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('cat_slug').value = slug;
});
</script>
