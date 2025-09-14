<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check maintenance mode (but allow registration if enabled)
check_maintenance_mode();

$page_title = 'Register';

// Check if registration is enabled
$allow_registration = get_system_setting('allow_registration', true);

// Get return URL from query parameter or session
$return_url = $_GET['return'] ?? $_SESSION['return_url'] ?? '/dashboard';

// Redirect if already logged in
if (is_logged_in()) {
    redirect($return_url);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Create user
            $password_hash = hash_password($password);
            $display_name = $first_name . ' ' . $last_name;
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, display_name) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $display_name])) {
                $user_id = $pdo->lastInsertId();
                
                // Assign user role
                $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'user'");
                $stmt->execute();
                $role = $stmt->fetch();
                
                if ($role) {
                    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $role['id']]);
                }
                
                log_activity('user_registered', 'New user registered', $user_id);
                
                show_message('Registration successful! Please login.', 'success');
                redirect('/login' . ($return_url !== '/dashboard' ? '?return=' . urlencode($return_url) : ''));
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include "../../includes/header.php";;

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/auth_register.css">
<?php
?>

<div class="form-container">
    <h2>Create Account</h2>
    
    <?php if (!$allow_registration): ?>
        <div class="alert alert-error">
            <h3><i class="fas fa-lock"></i> Registration Currently Closed</h3>
            <p>New user registration is currently disabled. If you need access to this platform, please contact an administrator.</p>
            <p><a href="/login" class="btn btn-primary">Go to Login Page</a></p>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required 
                       value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required 
                       value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required 
                   value="<?php echo htmlspecialchars($username ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn">Create Account</button>
        </form>
        
        <p >
            Already have an account? <a href="/login<?php echo $return_url !== '/dashboard' ? '?return=' . urlencode($return_url) : ''; ?>">Login here</a>
        </p>
    <?php endif; ?>
</div>


<?php include "../../includes/footer.php";; ?>
