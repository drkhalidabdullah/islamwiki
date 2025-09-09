<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Login';

// Get return URL from query parameter or session
$return_url = $_GET['return'] ?? $_SESSION['return_url'] ?? '/dashboard';

// Redirect if already logged in
if (is_logged_in()) {
    redirect($return_url);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && verify_password($password, $user['password_hash'])) {
            // Update last login
            $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_seen_at = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['display_name'] = $user['display_name'];
            $_SESSION['email'] = $user['email'];
            
            // Log activity
            log_activity('login', 'User logged in successfully');
            
            show_message('Login successful!', 'success');
            
            // Clear return URL from session after we've captured it
            unset($_SESSION['return_url']);
            
            redirect($return_url);
        } else {
            $error = 'Invalid username or password.';
            log_activity('login_failed', 'Failed login attempt', null, ['username' => $username]);
        }
    }
}

include "../../includes/header.php";;
?>

<div class="form-container">
    <h2>Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username or Email:</label>
            <input type="text" id="username" name="username" required 
                   value="<?php echo htmlspecialchars($username ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 1rem;">
        Don't have an account? <a href="/register<?php echo $return_url !== '/dashboard' ? '?return=' . urlencode($return_url) : ''; ?>">Register here</a>
    </p>
</div>

<?php include "../../includes/footer.php";; ?>
