<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Edit User';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$user_id = (int)($_GET['id'] ?? 0);
if (!$user_id) {
    show_message('User ID is required.', 'error');
    redirect('/admin');
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    show_message('User not found.', 'error');
    redirect('/admin');
}

// Get user roles
$stmt = $pdo->prepare("
    SELECT r.name, r.display_name 
    FROM user_roles ur 
    JOIN roles r ON ur.role_id = r.id 
    WHERE ur.user_id = ?
");
$stmt->execute([$user_id]);
$user_roles = $stmt->fetchAll();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $display_name = sanitize_input($_POST['display_name'] ?? '');
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $bio = sanitize_input($_POST['bio'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($username) || empty($email)) {
            $error = 'Username and email are required.';
        } else {
            // Check if username/email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, display_name = ?, full_name = ?, bio = ?, is_active = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    
                    if ($stmt->execute([$username, $email, $display_name, $full_name, $bio, $is_active, $user_id])) {
                        $success = 'User profile updated successfully.';
                        log_activity('user_updated', "Updated user: $username (ID: $user_id)");
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                    } else {
                        $error = 'Failed to update user profile.';
                    }
                } catch (Exception $e) {
                    $error = 'Error updating user: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'update_role') {
        $new_role = $_POST['role'] ?? '';
        if (in_array($new_role, ['admin', 'moderator', 'editor', 'user', 'scholar', 'reviewer'])) {
            try {
                // Remove existing roles
                $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Add new role
                $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
                $stmt->execute([$new_role]);
                $role = $stmt->fetch();
                
                if ($role) {
                    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    if ($stmt->execute([$user_id, $role['id']])) {
                        $success = 'User role updated successfully.';
                        log_activity('user_role_updated', "Updated role for user ID: $user_id to $new_role");
                        
                        // Refresh user roles
                        $stmt = $pdo->prepare("
                            SELECT r.name, r.display_name 
                            FROM user_roles ur 
                            JOIN roles r ON ur.role_id = r.id 
                            WHERE ur.user_id = ?
                        ");
                        $stmt->execute([$user_id]);
                        $user_roles = $stmt->fetchAll();
                    } else {
                        $error = 'Failed to update user role.';
                    }
                } else {
                    $error = 'Role not found.';
                }
            } catch (Exception $e) {
                $error = 'Error updating role: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid role selected.';
        }
    } elseif ($action === 'reset_password') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Password fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$hashed_password, $user_id])) {
                    $success = 'Password reset successfully.';
                    log_activity('user_password_reset', "Reset password for user ID: $user_id");
                } else {
                    $error = 'Failed to reset password.';
                }
            } catch (Exception $e) {
                $error = 'Error resetting password: ' . $e->getMessage();
            }
        }
    }
}

// Get available roles
$stmt = $pdo->query("SELECT name, display_name FROM roles ORDER BY name");
$available_roles = $stmt->fetchAll();

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/admin_edit_user.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/edit_user_admin.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-user-edit"></i> Edit User</h1>
        <p>Edit user: <?php echo htmlspecialchars($user['username']); ?></p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="user-edit-tabs">
        <button class="tab-button active" onclick="showTab('profile')">Profile</button>
        <button class="tab-button" onclick="showTab('role')">Role & Permissions</button>
        <button class="tab-button" onclick="showTab('password')">Reset Password</button>
    </div>

    <!-- Profile Tab -->
    <div id="profile-tab" class="tab-content active">
        <div class="card">
            <form method="POST" class="user-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($user['display_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                        Active Account
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Role Tab -->
    <div id="role-tab" class="tab-content">
        <div class="card">
            <h3>Current Roles</h3>
            <?php if (!empty($user_roles)): ?>
                <div class="current-roles">
                    <?php foreach ($user_roles as $role): ?>
                        <span class="role-badge"><?php echo htmlspecialchars($role['display_name']); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No roles assigned.</p>
            <?php endif; ?>

            <form method="POST" class="role-form">
                <input type="hidden" name="action" value="update_role">
                
                <div class="form-group">
                    <label for="role">Assign Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <?php foreach ($available_roles as $role): ?>
                            <option value="<?php echo $role['name']; ?>">
                                <?php echo htmlspecialchars($role['display_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-tag"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Tab -->
    <div id="password-tab" class="tab-content">
        <div class="card">
            <form method="POST" class="password-form">
                <input type="hidden" name="action" value="reset_password">
                
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="user-meta">
        <div class="meta-item">
            <strong>User ID:</strong> <?php echo $user['id']; ?>
        </div>
        <div class="meta-item">
            <strong>Joined:</strong> <?php echo format_date($user['created_at']); ?>
        </div>
        <div class="meta-item">
            <strong>Last Updated:</strong> <?php echo format_date($user['updated_at']); ?>
        </div>
        <div class="meta-item">
            <strong>Last Login:</strong> <?php echo $user['last_login'] ? format_date($user['last_login']) : 'Never'; ?>
        </div>
    </div>

    <div class="form-actions">
        <a href="/admin/manage_users" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>



<?php include "../../includes/footer.php"; ?>
