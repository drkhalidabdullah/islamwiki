<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Settings';
require_login();

$current_user = get_user($_SESSION['user_id']);
$user_profile = get_user_profile($_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $display_name = sanitize_input($_POST['display_name'] ?? '');
        $bio = sanitize_input($_POST['bio'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($display_name) || empty($email)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = 'Email already exists.';
            } else {
                // Update user
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, display_name = ?, bio = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$first_name, $last_name, $display_name, $bio, $email, $_SESSION['user_id']])) {
                    $success = 'Profile updated successfully.';
                    log_activity('profile_updated', 'Updated profile information');
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all password fields.';
        } elseif (!verify_password($current_password, $current_user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $password_hash = hash_password($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$password_hash, $_SESSION['user_id']])) {
                $success = 'Password updated successfully.';
                log_activity('password_changed', 'Changed password');
            } else {
                $error = 'Failed to update password.';
            }
        }
    } elseif ($action === 'update_preferences') {
        $language = sanitize_input($_POST['language'] ?? 'en');
        $timezone = sanitize_input($_POST['timezone'] ?? 'UTC');
        $notifications = isset($_POST['notifications']) ? 1 : 0;
        
        // Update or create user profile
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (user_id, preferences) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE preferences = VALUES(preferences)
        ");
        
        $preferences = json_encode([
            'language' => $language,
            'timezone' => $timezone,
            'notifications' => $notifications
        ]);
        
        if ($stmt->execute([$_SESSION['user_id'], $preferences])) {
            $success = 'Preferences updated successfully.';
            log_activity('preferences_updated', 'Updated user preferences');
        } else {
            $error = 'Failed to update preferences.';
        }
    }
    
    // Refresh user data
    $current_user = get_user($_SESSION['user_id']);
    $user_profile = get_user_profile($_SESSION['user_id']);
}

// Get available languages
$stmt = $pdo->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order");
$languages = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="settings-container">
    <h1>Settings</h1>
    <p>Manage your account settings and preferences.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="settings-tabs">
        <button class="tab-button active" onclick="showTab('profile')">Profile</button>
        <button class="tab-button" onclick="showTab('password')">Password</button>
        <button class="tab-button" onclick="showTab('preferences')">Preferences</button>
    </div>
    
    <!-- Profile Tab -->
    <div id="profile-tab" class="tab-content active">
        <div class="card">
            <h2>Profile Information</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo htmlspecialchars($current_user['first_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required 
                               value="<?php echo htmlspecialchars($current_user['last_name']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="display_name">Display Name *</label>
                    <input type="text" id="display_name" name="display_name" required 
                           value="<?php echo htmlspecialchars($current_user['display_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($current_user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($current_user['bio']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
    
    <!-- Password Tab -->
    <div id="password-tab" class="tab-content">
        <div class="card">
            <h2>Change Password</h2>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
    
    <!-- Preferences Tab -->
    <div id="preferences-tab" class="tab-content">
        <div class="card">
            <h2>Preferences</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_preferences">
                
                <div class="form-group">
                    <label for="language">Language</label>
                    <select id="language" name="language">
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?php echo $lang['code']; ?>" 
                                    <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['language'] ?? 'en' : 'en') === $lang['code'] ? 'selected' : ''; ?>>
                                <?php echo $lang['native_name']; ?> (<?php echo $lang['name']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone">
                        <option value="UTC" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        <option value="America/New_York" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                        <option value="America/Chicago" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                        <option value="America/Denver" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                        <option value="America/Los_Angeles" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                        <option value="Europe/London" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'Europe/London' ? 'selected' : ''; ?>>London</option>
                        <option value="Europe/Paris" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'Europe/Paris' ? 'selected' : ''; ?>>Paris</option>
                        <option value="Asia/Dubai" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'Asia/Dubai' ? 'selected' : ''; ?>>Dubai</option>
                        <option value="Asia/Karachi" <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['timezone'] ?? 'UTC' : 'UTC') === 'Asia/Karachi' ? 'selected' : ''; ?>>Karachi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="notifications" 
                               <?php echo ($user_profile['preferences'] ? json_decode($user_profile['preferences'], true)['notifications'] ?? 0 : 0) ? 'checked' : ''; ?>>
                        Enable email notifications
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Preferences</button>
            </form>
        </div>
    </div>
</div>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

.settings-tabs {
    display: flex;
    border-bottom: 2px solid #ecf0f1;
    margin: 2rem 0;
}

.tab-button {
    background: none;
    border: none;
    padding: 1rem 2rem;
    cursor: pointer;
    font-size: 1rem;
    color: #666;
    border-bottom: 2px solid transparent;
    transition: all 0.3s;
}

.tab-button.active {
    color: #3498db;
    border-bottom-color: #3498db;
}

.tab-button:hover {
    color: #3498db;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: normal;
    cursor: pointer;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .settings-tabs {
        flex-direction: column;
    }
    
    .tab-button {
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Remove active class from all buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>
