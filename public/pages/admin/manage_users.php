<?php
require_once '../../includes/functions.php';
require_once '../../config/config.php';
require_once '../../config/database.php';

$page_title = 'Manage Users';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect_with_return_url();
}


// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'delete_user' && $user_id) {
        if ($user_id == $_SESSION['user_id']) {
            show_message('You cannot delete your own account.', 'error');
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                show_message('User deleted successfully.', 'success');
                log_activity('user_deleted', "Deleted user ID: $user_id");
            } else {
                show_message('Failed to delete user.', 'error');
            }
        }
    } elseif ($action === 'update_role' && $user_id) {
        $new_role = $_POST['role'] ?? '';
        if (in_array($new_role, ['admin', 'moderator', 'editor', 'user', 'scholar', 'reviewer'])) {
            // First remove existing roles
            $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Add new role
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$new_role]);
            $role = $stmt->fetch();
            
            if ($role) {
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                if ($stmt->execute([$user_id, $role['id']])) {
                    show_message('User role updated successfully.', 'success');
                    log_activity('user_role_updated', "Updated user ID: $user_id to role: $new_role");
                } else {
                    show_message('Failed to update user role.', 'error');
                }
            }
        }
    } elseif ($action === 'reset_password' && $user_id) {
        $new_password = $_POST['new_password'] ?? '';
        if (strlen($new_password) >= 6) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$password_hash, $user_id])) {
                show_message('Password reset successfully.', 'success');
                log_activity('user_password_reset', "Reset password for user ID: $user_id");
            } else {
                show_message('Failed to reset password.', 'error');
            }
        } else {
            show_message('Password must be at least 6 characters long.', 'error');
        }
    } elseif ($action === 'edit_user' && $user_id) {
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $display_name = sanitize_input($_POST['display_name'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($username) || empty($email) || empty($first_name) || empty($last_name)) {
            $error = 'All required fields must be filled.';
        } else {
            // Check if username/email already exists for other users
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username or email already exists.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, first_name = ?, last_name = ?, display_name = ?, is_active = ?
                    WHERE id = ?
                ");
                if ($stmt->execute([$username, $email, $first_name, $last_name, $display_name, $is_active, $user_id])) {
                    $success = 'User updated successfully.';
                    log_activity('user_updated', "Updated user ID: $user_id");
                } else {
                    $error = 'Failed to update user.';
                }
            }
        }
    }
}

// Get all users with their roles
$stmt = $pdo->query("
    SELECT u.*, r.name as role_name, r.display_name as role_display 
    FROM users u 
    LEFT JOIN user_roles ur ON u.id = ur.user_id 
    LEFT JOIN roles r ON ur.role_id = r.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get available roles
$stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
$roles = $stmt->fetchAll();

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/manage_users.js"></script>
<?php
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/manage_users.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Users</h1>
        <a href="/admin" class="btn">Back to Admin Panel</a>
    </div>
    
    
    <div class="card">
        <h2>All Users</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Display Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['display_name'] ?: $user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" onchange="this.form.submit()">
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['name']; ?>" 
                                            <?php echo ($user['role_name'] === $role['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['display_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo format_date($user['created_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <!-- Edit User Button -->
                                <button type="button" class="btn btn-sm btn-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode(['id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'display_name' => $user['display_name'], 'is_active' => $user['is_active']])); ?>)">
                                    Edit
                                </button>
                                
                                <!-- Reset Password Button -->
                                <button type="button" class="btn btn-sm btn-warning" onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    Reset Password
                                </button>
                                
                                <!-- Delete Button -->
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted">Current User</span>
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

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST" id="editUserForm">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div class="form-group">
                <label for="edit_username">Username *</label>
                <input type="text" id="edit_username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="edit_email">Email *</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_first_name">First Name *</label>
                    <input type="text" id="edit_first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_last_name">Last Name *</label>
                    <input type="text" id="edit_last_name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_display_name">Display Name</label>
                <input type="text" id="edit_display_name" name="display_name">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                    Active User
                </label>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reset Password</h3>
            <span class="close" onclick="closeResetModal()">&times;</span>
        </div>
        <form method="POST" id="resetPasswordForm">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="reset_user_id">
            
            <div class="form-group">
                <label>User: <span id="reset_username"></span></label>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <small>Password must be at least 6 characters long.</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeResetModal()">Cancel</button>
                <button type="submit" class="btn btn-warning">Reset Password</button>
            </div>
        </form>
    </div>
</div>



<?php include "../../includes/footer.php";; ?>
