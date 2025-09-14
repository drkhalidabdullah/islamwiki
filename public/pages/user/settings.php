<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Account Settings';
require_login();

$current_user = get_user($_SESSION['user_id']);
$user_profile = get_user_profile($_SESSION['user_id']);

// Get current settings page
$page = $_GET['page'] ?? 'overview';

// Handle form submissions
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

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/settings.css">
<?php
?>

<div class="settings-container">
    <div class="settings-header">
        <h1>Account Settings</h1>
        <p>Manage your personal account settings, security, and preferences.</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="settings-layout">
        <nav class="settings-sidebar">
            <ul class="settings-nav">
                <li class="nav-item <?php echo $page === 'overview' ? 'active' : ''; ?>">
                    <a href="?page=overview" class="nav-link">
                        <i class="icon-dashboard"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $page === 'profile' ? 'active' : ''; ?>">
                    <a href="?page=profile" class="nav-link">
                        <i class="icon-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $page === 'security' ? 'active' : ''; ?>">
                    <a href="?page=security" class="nav-link">
                        <i class="icon-shield"></i>
                        <span>Security</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $page === 'privacy' ? 'active' : ''; ?>">
                    <a href="?page=privacy" class="nav-link">
                        <i class="icon-lock"></i>
                        <span>Privacy</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $page === 'notifications' ? 'active' : ''; ?>">
                    <a href="?page=notifications" class="nav-link">
                        <i class="icon-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item <?php echo $page === 'preferences' ? 'active' : ''; ?>">
                    <a href="?page=preferences" class="nav-link">
                        <i class="icon-settings"></i>
                        <span>Preferences</span>
                    </a>
                </li>
                <?php if (get_system_setting('allow_skin_selection', true)): ?>
                <li class="nav-item">
                    <a href="/skin_selection" class="nav-link">
                        <i class="fas fa-palette"></i>
                        <span>Skin Selection</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item <?php echo $page === 'account' ? 'active' : ''; ?>">
                    <a href="?page=account" class="nav-link">
                        <i class="icon-cog"></i>
                        <span>Account</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <main class="settings-content">
            <?php
            switch ($page) {
                case 'overview':
                    include 'settings/overview.php';
                    break;
                case 'profile':
                    include 'settings/profile.php';
                    break;
                case 'security':
                    include 'settings/security.php';
                    break;
                case 'privacy':
                    include 'settings/privacy.php';
                    break;
                case 'notifications':
                    include 'settings/notifications.php';
                    break;
                case 'preferences':
                    include 'settings/preferences.php';
                    break;
                case 'account':
                    include 'settings/account.php';
                    break;
                default:
                    include 'settings/overview.php';
                    break;
            }
            ?>
        </main>
    </div>
</div>


<?php include "../../includes/footer.php";; ?>
