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
        // Check rate limiting for login attempts
        if (!check_rate_limit('login_attempts')) {
            $error = 'Too many login attempts. Please try again later.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check if account is locked
                if ($user['is_locked'] && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
                    $lockTime = strtotime($user['locked_until']) - time();
                    $hours = floor($lockTime / 3600);
                    $minutes = floor(($lockTime % 3600) / 60);
                    $error = "Account is locked. Try again in {$hours}h {$minutes}m.";
                } else {
                    // Check password
                    if (verify_password($password, $user['password_hash'])) {
                        // Reset failed login attempts on successful login
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET last_login_at = NOW(), last_seen_at = NOW(), 
                                failed_login_attempts = 0, last_failed_login = NULL,
                                is_locked = 0, locked_until = NULL
                            WHERE id = ?
                        ");
                        $stmt->execute([$user['id']]);
                        
                        // Log successful login
                        $stmt = $pdo->prepare("
                            INSERT INTO user_security (user_id, ip_address, action_type, user_agent) 
                            VALUES (?, ?, 'login_success', ?)
                        ");
                        $stmt->execute([$user['id'], get_client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
                        
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['display_name'] = $user['display_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['login_time'] = time();
                        
                        // Log activity
                        log_activity('login', 'User logged in successfully');
                        
                        show_message('Login successful!', 'success');
                        
                        // Clear return URL from session after we've captured it
                        unset($_SESSION['return_url']);
                        
                        redirect($return_url);
                    } else {
                        // Failed login - increment attempts
                        $failed_attempts = $user['failed_login_attempts'] + 1;
                        $max_attempts = 5; // Get from settings
                        $lock_duration = 1800; // 30 minutes
                        
                        $update_data = [
                            'failed_login_attempts' => $failed_attempts,
                            'last_failed_login' => date('Y-m-d H:i:s')
                        ];
                        
                        // Lock account if max attempts reached
                        if ($failed_attempts >= $max_attempts) {
                            $update_data['is_locked'] = 1;
                            $update_data['locked_until'] = date('Y-m-d H:i:s', time() + $lock_duration);
                        }
                        
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET failed_login_attempts = ?, last_failed_login = ?, 
                                is_locked = ?, locked_until = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $update_data['failed_login_attempts'],
                            $update_data['last_failed_login'],
                            $update_data['is_locked'] ?? 0,
                            $update_data['locked_until'] ?? null,
                            $user['id']
                        ]);
                        
                        // Log failed login
                        $stmt = $pdo->prepare("
                            INSERT INTO user_security (user_id, ip_address, action_type, user_agent) 
                            VALUES (?, ?, 'login_failed', ?)
                        ");
                        $stmt->execute([$user['id'], get_client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
                        
                        if ($failed_attempts >= $max_attempts) {
                            $error = "Account locked due to too many failed attempts. Try again in 30 minutes.";
                        } else {
                            $remaining = $max_attempts - $failed_attempts;
                            $error = "Invalid username or password. {$remaining} attempts remaining.";
                        }
                        
                        log_activity('login_failed', 'Failed login attempt', $user['id'], [
                            'username' => $username,
                            'attempts' => $failed_attempts
                        ]);
                    }
                }
            } else {
                // User not found - still log the attempt
                $stmt = $pdo->prepare("
                    INSERT INTO user_security (user_id, ip_address, action_type, user_agent) 
                    VALUES (NULL, ?, 'login_failed', ?)
                ");
                $stmt->execute([get_client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
                
                $error = 'Invalid username or password.';
                log_activity('login_failed', 'Failed login attempt - user not found', null, ['username' => $username]);
            }
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
