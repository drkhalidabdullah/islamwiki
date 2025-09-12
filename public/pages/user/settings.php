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

<style>
/* Settings Container */
.settings-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.settings-header {
    margin-bottom: 2rem;
}

.settings-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.settings-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

/* Settings Layout */
.settings-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    min-height: 600px;
}

/* Sidebar Navigation */
.settings-sidebar {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.settings-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 0.5rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #495057;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
    font-weight: 500;
}

.nav-link:hover {
    background: #e9ecef;
    color: #2c3e50;
}

.nav-item.active .nav-link {
    background: #3498db;
    color: white;
}

.nav-link i {
    width: 20px;
    text-align: center;
}

/* Settings Content */
.settings-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
}

/* Page Headers */
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #ecf0f1;
}

.page-header h2 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #7f8c8d;
    margin: 0;
}

/* Form Styling */
.settings-form, .profile-form, .security-form, .privacy-form, .notification-form, .preferences-form {
    max-width: 600px;
}

.form-section, .preferences-section, .notification-section, .privacy-section, .security-section, .account-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #ecf0f1;
}

.form-section:last-child, .preferences-section:last-child, .notification-section:last-child, 
.privacy-section:last-child, .security-section:last-child, .account-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h3, .preferences-section h3, .notification-section h3, 
.privacy-section h3, .security-section h3, .account-section h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #7f8c8d;
    font-size: 0.875rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-weight: normal;
    cursor: pointer;
    margin-bottom: 0;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.checkbox-content {
    flex: 1;
}

.checkbox-content strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.checkbox-content small {
    color: #7f8c8d;
    font-size: 0.875rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #ecf0f1;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* Overview Page */
.settings-overview {
    max-width: 800px;
}

.overview-header {
    margin-bottom: 2rem;
}

.overview-header h2 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.overview-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.account-summary {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.summary-item {
    color: #2c3e50;
    font-size: 0.9rem;
}

.summary-item strong {
    color: #34495e;
}

.status-active {
    color: #27ae60;
    font-weight: 600;
}

.status-inactive {
    color: #e74c3c;
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #7f8c8d;
    font-weight: 500;
}

.overview-sections {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.2s;
}

.quick-action:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.quick-action i {
    font-size: 1.5rem;
    color: #3498db;
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
}

.activity-content p {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
}

.activity-content small {
    color: #7f8c8d;
    font-size: 0.875rem;
}

.no-activity, .no-sessions, .no-logs, .no-content {
    color: #7f8c8d;
    font-style: italic;
    text-align: center;
    padding: 2rem;
}

.recent-content {
    max-height: 300px;
    overflow-y: auto;
}

.content-item {
    padding: 1rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.content-item:last-child {
    border-bottom: none;
}

.content-preview h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1rem;
}

.content-preview p {
    margin: 0 0 0.5rem 0;
    color: #34495e;
    line-height: 1.4;
}

.content-preview small {
    color: #7f8c8d;
    font-size: 0.875rem;
}

/* Security Page */
.sessions-list, .security-logs {
    max-height: 400px;
    overflow-y: auto;
}

.session-item, .log-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.session-info, .log-content {
    flex: 1;
}

.session-details strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.session-details small {
    color: #7f8c8d;
    font-size: 0.875rem;
}

.session-meta {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #7f8c8d;
}

.current-session {
    background: #27ae60;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.terminate-form {
    margin: 0;
}

.log-icon {
    width: 40px;
    height: 40px;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #3498db;
}

/* Account Page */
.account-info, .storage-info {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.info-item, .storage-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child, .storage-item:last-child {
    border-bottom: none;
}

.info-item label, .storage-item label {
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.verified-badge {
    background: #27ae60;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.verify-link {
    color: #3498db;
    text-decoration: none;
    font-size: 0.875rem;
    margin-left: 0.5rem;
}

.verify-link:hover {
    text-decoration: underline;
}

.status-active {
    color: #27ae60;
    font-weight: 600;
}

.status-inactive {
    color: #e74c3c;
    font-weight: 600;
}

.data-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.inline-form {
    display: inline-block;
}

.data-info {
    margin: 0;
    color: #7f8c8d;
}

.danger-zone {
    border: 2px solid #e74c3c;
    border-radius: 8px;
    padding: 1.5rem;
    background: #fdf2f2;
}

.danger-zone h3 {
    color: #e74c3c;
    margin-bottom: 1rem;
}

.danger-actions {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.danger-action {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #f5c6cb;
}

.action-info h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.action-info p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.875rem;
}

/* Modals */
.modal {
    display: none;
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
    margin: 10% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}

.modal-content h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.modal-content p {
    color: #7f8c8d;
    margin-bottom: 1rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

/* Icon Styles */
.icon-dashboard::before { content: "📊"; }
.icon-user::before { content: "👤"; }
.icon-shield::before { content: "🛡️"; }
.icon-lock::before { content: "🔒"; }
.icon-bell::before { content: "🔔"; }
.icon-settings::before { content: "⚙️"; }
.icon-cog::before { content: "🔧"; }
.icon-posts::before { content: "📝"; }
.icon-articles::before { content: "📚"; }
.icon-followers::before { content: "👥"; }
.icon-following::before { content: "👤"; }
.icon-comments::before { content: "💬"; }
.icon-plus::before { content: "➕"; }
.icon-download::before { content: "⬇️"; }
.icon-trash::before { content: "🗑️"; }
.icon-login_success::before { content: "✅"; }
.icon-login_failed::before { content: "❌"; }
.icon-password_changed::before { content: "🔑"; }
.icon-session_terminated::before { content: "🚪"; }
.icon-profile_updated::before { content: "✏️"; }
.icon-preferences_updated::before { content: "⚙️"; }
.icon-notifications_updated::before { content: "🔔"; }
.icon-privacy_updated::before { content: "🔒"; }
.icon-account_deactivated::before { content: "⏸️"; }
.icon-account_deleted::before { content: "🗑️"; }

/* Responsive Design */
@media (max-width: 768px) {
    .settings-container {
        padding: 1rem;
    }
    
    .settings-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .settings-sidebar {
        position: static;
        order: 2;
    }
    
    .settings-nav {
        display: flex;
        overflow-x: auto;
        gap: 0.5rem;
        padding-bottom: 0.5rem;
    }
    
    .nav-item {
        margin-bottom: 0;
        flex-shrink: 0;
    }
    
    .nav-link {
        white-space: nowrap;
        padding: 0.5rem 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .overview-sections {
        grid-template-columns: 1fr;
    }
    
    .account-summary {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .danger-action {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .modal-content {
        margin: 5% auto;
        width: 95%;
        padding: 1.5rem;
    }
    
    .modal-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include "../../includes/footer.php";; ?>
