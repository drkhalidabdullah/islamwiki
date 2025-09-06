<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Profile';
require_login();

$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Update basic info
    if (!empty($full_name) && !empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = 'Email already exists.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$full_name, $email, $_SESSION['user_id']])) {
                    $_SESSION['full_name'] = $full_name;
                    $success = 'Profile updated successfully.';
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    }
    
    // Update password if provided
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $error = 'Please enter your current password to change it.';
        } elseif (!verify_password($current_password, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $error = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $password_hash = hash_password($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$password_hash, $_SESSION['user_id']])) {
                $success = 'Password updated successfully.';
            } else {
                $error = 'Failed to update password.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Edit Profile</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            <small>Username cannot be changed</small>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required 
                   value="<?php echo htmlspecialchars($user['full_name']); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        
        <div class="form-group">
            <label for="role">Role:</label>
            <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled>
        </div>
        
        <h3>Change Password (Optional)</h3>
        
        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password">
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" 
                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
        
        <button type="submit" class="btn">Update Profile</button>
    </form>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</div>

<style>
.form-container {
    max-width: 600px;
}

.form-group small {
    color: #666;
    font-size: 0.8rem;
}

h3 {
    margin: 2rem 0 1rem 0;
    color: #2c3e50;
    border-bottom: 1px solid #ecf0f1;
    padding-bottom: 0.5rem;
}
</style>

<?php include 'includes/footer.php'; ?>
