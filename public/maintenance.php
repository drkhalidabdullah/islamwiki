<?php
require_once 'config/config.php';
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/maintenance.css">
<?php
require_once 'includes/functions.php';

$page_title = 'Site Maintenance';

// If maintenance mode is disabled, redirect to home
if (!is_maintenance_mode()) {
    header('Location: /');
    exit;
}

// Handle login form submission
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $login_error = 'Please fill in all fields.';
    } else {
        // Check rate limiting for login attempts
        if (!check_rate_limit('login_attempts')) {
            $login_error = 'Too many login attempts. Please try again later.';
        } else {
            // Get user from database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if user is admin
                if (is_admin($user['id'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = true;
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Log successful login
                    log_activity('login_success', 'Successful admin login during maintenance', $user['id']);
                    
                    // Redirect to admin panel
                    header('Location: /admin');
                    exit;
                } else {
                    $login_error = 'Only administrators can log in during maintenance.';
                }
            } else {
                $login_error = 'Invalid username or password.';
            }
        }
    }
}

// Get maintenance message from settings
$maintenance_message = get_system_setting('maintenance_message', 'We are currently performing scheduled maintenance. Please check back later.');
$estimated_downtime = get_system_setting('estimated_downtime', '2-4 hours');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo get_site_name(); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="maintenance-wrapper">
        <!-- Left Side: Maintenance Information -->
        <div class="maintenance-content">
            <div class="maintenance-icon">
                <i class="iw iw-tools"></i>
            </div>
            
            <h1 class="maintenance-title">Site Maintenance</h1>
            <p class="maintenance-subtitle">We're working hard to improve your experience</p>
            
            <div class="maintenance-message">
                <p><?php echo htmlspecialchars($maintenance_message); ?></p>
            </div>
            
            <div class="maintenance-details">
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="iw iw-clock"></i>
                    </div>
                    <div class="detail-label">Estimated Time</div>
                    <div class="detail-value"><?php echo htmlspecialchars($estimated_downtime); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="iw iw-calendar"></i>
                    </div>
                    <div class="detail-label">Started</div>
                    <div class="detail-value"><?php echo date('M j, Y \a\t g:i A'); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="iw iw-info-circle"></i>
                    </div>
                    <div class="detail-label">Status</div>
                    <div class="detail-value">In Progress</div>
                </div>
            </div>
            
            <div class="maintenance-footer">
                <p>Thank you for your patience. We'll be back online soon!</p>
                <p><strong><?php echo get_site_name(); ?></strong> - Islamic Knowledge Platform</p>
            </div>
        </div>
        
        <!-- Right Side: Admin Login Form -->
        <div class="login-section">
            <div class="login-header">
                <div class="login-icon">
                    <i class="iw iw-shield-alt"></i>
                </div>
                <h2 class="login-title">Admin Login</h2>
                <p class="login-subtitle">Administrators can access the site during maintenance</p>
            </div>
            
            <form class="login-form" method="POST">
                <?php if ($login_error): ?>
                    <div class="login-error">
                        <i class="iw iw-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           placeholder="Enter your username or email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" name="login" class="btn-login">
                    <i class="iw iw-sign-in-alt"></i>
                    Login as Admin
                </button>
            </form>
            
            <div class="admin-notice">
                <i class="iw iw-info-circle"></i>
                <strong>Note:</strong> Only administrators can log in during maintenance mode.
            </div>
        </div>
    </div>
</body>
</html>