<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Permissions';
require_login();
require_admin();

$current_user = get_user($_SESSION['user_id']);

// Remove old error/success variables - using show_message() now

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_role') {
        $name = sanitize_input($_POST['name'] ?? '');
        $display_name = sanitize_input($_POST['display_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name) || empty($display_name)) {
            show_message('Role name and display name are required.', 'error');
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO roles (name, display_name, description, permissions) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $display_name, $description, json_encode($permissions)]);
                show_message('Role created successfully.', 'success');
            } catch (Exception $e) {
                show_message('Error creating role: ' . $e->getMessage(), 'error');
            }
        }
    } elseif ($action === 'update_role') {
        $role_id = (int)($_POST['role_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];
        
        if ($role_id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE roles SET permissions = ? WHERE id = ?");
                $stmt->execute([json_encode($permissions), $role_id]);
                show_message('Role permissions updated successfully.', 'success');
            } catch (Exception $e) {
                show_message('Error updating role: ' . $e->getMessage(), 'error');
            }
        }
    } elseif ($action === 'assign_role') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $role_id = (int)($_POST['role_id'] ?? 0);
        
        if ($user_id > 0 && $role_id > 0) {
            try {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO user_roles (user_id, role_id, granted_by) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user_id, $role_id, $_SESSION['user_id']]);
                show_message('Role assigned successfully.', 'success');
            } catch (Exception $e) {
                show_message('Error assigning role: ' . $e->getMessage(), 'error');
            }
        }
    } elseif ($action === 'remove_role') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $role_id = (int)($_POST['role_id'] ?? 0);
        
        if ($user_id > 0 && $role_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
                $stmt->execute([$user_id, $role_id]);
                show_message('Role removed successfully.', 'success');
            } catch (Exception $e) {
                show_message('Error removing role: ' . $e->getMessage(), 'error');
            }
        }
    }
}

// Get all roles
$stmt = $pdo->query("SELECT * FROM roles ORDER BY name");
$roles = $stmt->fetchAll();

// Get all users with their roles
$stmt = $pdo->query("
    SELECT u.*, 
           GROUP_CONCAT(r.name) as role_names,
           GROUP_CONCAT(r.display_name) as role_display_names
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    GROUP BY u.id
    ORDER BY u.username
");
$users = $stmt->fetchAll();

// Define available permissions
$available_permissions = [
    'wiki' => [
        'wiki.create' => 'Create Articles',
        'wiki.edit' => 'Edit Articles',
        'wiki.delete' => 'Delete Articles',
        'wiki.protect' => 'Protect Articles',
        'wiki.upload' => 'Upload Files',
        'wiki.manage_files' => 'Manage Files',
        'wiki.manage_redirects' => 'Manage Redirects',
        'wiki.manage_categories' => 'Manage Categories',
    ],
    'admin' => [
        'admin.access' => 'Access Admin Panel',
        'admin.manage_users' => 'Manage Users',
        'admin.manage_roles' => 'Manage Roles',
        'admin.system_settings' => 'System Settings',
        'admin.view_logs' => 'View System Logs',
    ],
    'content' => [
        'content.create_post' => 'Create Posts',
        'content.edit_post' => 'Edit Posts',
        'content.delete_post' => 'Delete Posts',
        'content.moderate' => 'Moderate Content',
    ],
    'social' => [
        'social.send_messages' => 'Send Messages',
        'social.create_groups' => 'Create Groups',
        'social.moderate_groups' => 'Moderate Groups',
    ]
];

include '../../includes/header.php';

?>
<script src="/skins/bismillah/assets/js/admin_manage_permissions.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/manage_permissions.css">
<?php
?>

<div class="admin-page">
    <div class="admin-header">
        <h1>Manage Permissions</h1>
        <div class="admin-actions">
            <a href="/admin" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>

    <!-- Error and success messages are now handled by toast notifications -->

    <!-- Create New Role -->
    <div class="card">
        <h2>Create New Role</h2>
        <form method="POST" class="form">
            <input type="hidden" name="action" value="create_role">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Role Name:</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="e.g., moderator" pattern="[a-z_]+"
                           title="Only lowercase letters and underscores allowed">
                </div>
                
                <div class="form-group">
                    <label for="display_name">Display Name:</label>
                    <input type="text" id="display_name" name="display_name" required 
                           placeholder="e.g., Moderator">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3" 
                          placeholder="Describe the role's purpose..."></textarea>
            </div>
            
            <div class="form-group permissions-section">
                <label class="permissions-label">Permissions:</label>
                <div class="permissions-grid">
                    <?php foreach ($available_permissions as $category => $perms): ?>
                        <div class="permission-category">
                            <h4><?php echo ucfirst($category); ?></h4>
                            <?php foreach ($perms as $perm => $label): ?>
                                <label class="permission-item">
                                    <input type="checkbox" name="permissions[]" value="<?php echo $perm; ?>">
                                    <span><?php echo htmlspecialchars($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Role</button>
        </form>
    </div>

    <!-- Manage Existing Roles -->
    <div class="card">
        <h2>Manage Roles</h2>
        
        <?php if (empty($roles)): ?>
            <p>No roles have been created yet.</p>
        <?php else: ?>
            <div class="roles-grid">
                <?php foreach ($roles as $role): ?>
                    <div class="role-card" data-role-id="<?php echo $role['id']; ?>" data-permissions='<?php echo htmlspecialchars($role['permissions']); ?>'>
                        <div class="role-header">
                            <h3><?php echo htmlspecialchars($role['display_name']); ?></h3>
                            <span class="role-name"><?php echo htmlspecialchars($role['name']); ?></span>
                        </div>
                        
                        <?php if ($role['description']): ?>
                            <p class="role-description"><?php echo htmlspecialchars($role['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="role-permissions">
                            <h4>Permissions:</h4>
                            <?php 
                            $permissions = json_decode($role['permissions'], true) ?: [];
                            if (empty($permissions)): 
                            ?>
                                <p class="no-permissions">No permissions assigned</p>
                            <?php else: ?>
                                <ul>
                                    <?php foreach ($permissions as $perm): ?>
                                        <li><?php echo htmlspecialchars($perm); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <div class="role-actions">
                            <button onclick="editRole(<?php echo $role['id']; ?>)" class="btn btn-sm btn-primary">Edit</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- User Role Management -->
    <div class="card">
        <h2>User Role Management</h2>
        
        <div class="user-roles-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Current Roles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <strong><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($user['username']); ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($user['role_names']): ?>
                                    <div class="user-roles">
                                        <?php 
                                        $role_names = explode(',', $user['role_names']);
                                        $role_display_names = explode(',', $user['role_display_names']);
                                        for ($i = 0; $i < count($role_names); $i++): 
                                        ?>
                                            <span class="role-badge">
                                                <?php echo htmlspecialchars($role_display_names[$i]); ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Remove this role?')">
                                                    <input type="hidden" name="action" value="remove_role">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="role_id" value="<?php echo array_search($role_names[$i], array_column($roles, 'name')); ?>">
                                                    <button type="submit" class="remove-role">×</button>
                                                </form>
                                            </span>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="no-roles">No roles assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="assign-role-form">
                                    <input type="hidden" name="action" value="assign_role">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role_id" required>
                                        <option value="">Select Role...</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['id']; ?>">
                                                <?php echo htmlspecialchars($role['display_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div id="editRoleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Role Permissions</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" id="editRoleForm">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="role_id" id="edit_role_id">
            
            <div class="modal-body">
                <div class="permissions-grid">
                    <?php foreach ($available_permissions as $category => $perms): ?>
                        <div class="permission-category">
                            <h4><?php echo ucfirst($category); ?></h4>
                            <?php foreach ($perms as $perm => $label): ?>
                                <label class="permission-item">
                                    <input type="checkbox" name="permissions[]" value="<?php echo $perm; ?>" 
                                           class="edit-permission" data-perm="<?php echo $perm; ?>">
                                    <span><?php echo htmlspecialchars($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Permissions</button>
            </div>
        </form>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>
