<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Permissions';
require_login();
require_admin();

$current_user = get_user($_SESSION['user_id']);

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_role') {
        $name = sanitize_input($_POST['name'] ?? '');
        $display_name = sanitize_input($_POST['display_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name) || empty($display_name)) {
            $errors[] = 'Role name and display name are required.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO roles (name, display_name, description, permissions) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $display_name, $description, json_encode($permissions)]);
                $success = 'Role created successfully.';
            } catch (Exception $e) {
                $errors[] = 'Error creating role: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_role') {
        $role_id = (int)($_POST['role_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];
        
        if ($role_id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE roles SET permissions = ? WHERE id = ?");
                $stmt->execute([json_encode($permissions), $role_id]);
                $success = 'Role permissions updated successfully.';
            } catch (Exception $e) {
                $errors[] = 'Error updating role: ' . $e->getMessage();
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
                $success = 'Role assigned successfully.';
            } catch (Exception $e) {
                $errors[] = 'Error assigning role: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'remove_role') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $role_id = (int)($_POST['role_id'] ?? 0);
        
        if ($user_id > 0 && $role_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
                $stmt->execute([$user_id, $role_id]);
                $success = 'Role removed successfully.';
            } catch (Exception $e) {
                $errors[] = 'Error removing role: ' . $e->getMessage();
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

<div class="admin-page">
    <div class="admin-header">
        <h1>Manage Permissions</h1>
        <div class="admin-actions">
            <a href="/admin" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

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
            
            <div class="form-group">
                <label>Permissions:</label>
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
                    <div class="role-card">
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

<script>
function editRole(roleId) {
    // Get role data via AJAX or from a data attribute
    // For now, we'll show the modal and let the user select permissions
    document.getElementById('edit_role_id').value = roleId;
    document.getElementById('editRoleModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editRoleModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editRoleModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

<style>
.admin-page {
    max-width: 1400px;
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
    margin: 0;
    color: #2c3e50;
}

.admin-actions {
    display: flex;
    gap: 1rem;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.form {
    display: grid;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.permission-category {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
}

.permission-category h4 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    font-size: 1rem;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
}

.permission-item input[type="checkbox"] {
    margin: 0;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.role-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    transition: box-shadow 0.3s;
}

.role-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.role-header {
    margin-bottom: 1rem;
}

.role-header h3 {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
}

.role-name {
    color: #6c757d;
    font-size: 0.875rem;
    font-family: monospace;
}

.role-description {
    color: #666;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.role-permissions h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 0.9rem;
}

.role-permissions ul {
    margin: 0;
    padding-left: 1.5rem;
    font-size: 0.875rem;
    color: #666;
}

.no-permissions {
    color: #6c757d;
    font-style: italic;
    font-size: 0.875rem;
    margin: 0;
}

.role-actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.user-roles-table {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
    vertical-align: top;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.user-info strong {
    color: #2c3e50;
}

.user-roles {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.role-badge {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.remove-role {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    font-weight: bold;
    padding: 0;
    margin-left: 0.25rem;
}

.assign-role-form {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.assign-role-form select {
    padding: 0.25rem 0.5rem;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 0.875rem;
}

.no-roles {
    color: #6c757d;
    font-style: italic;
    font-size: 0.875rem;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .permissions-grid {
        grid-template-columns: 1fr;
    }
    
    .roles-grid {
        grid-template-columns: 1fr;
    }
    
    .assign-role-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
