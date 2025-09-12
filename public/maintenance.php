<?php
require_once 'config/config.php';
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
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            padding: 2rem 1rem;
        }

        .maintenance-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: 600px;
        }

        .maintenance-content {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .maintenance-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .maintenance-subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .maintenance-message {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
        }

        .maintenance-message p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #2c3e50;
            margin: 0;
        }

        .maintenance-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            text-align: center;
        }

        .detail-icon {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .maintenance-footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Login Form Styles */
        .login-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid #e9ecef;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .login-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-error {
            background: #f8d7da;
            color: #721c24;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
            font-size: 0.9rem;
        }

        .login-success {
            background: #d4edda;
            color: #155724;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
            font-size: 0.9rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .admin-notice {
            background: #e8f4f8;
            border: 1px solid #b8daff;
            color: #004085;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .admin-notice i {
            color: #667eea;
            margin-right: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .maintenance-wrapper {
                grid-template-columns: 1fr;
                max-width: 600px;
            }
            
            .login-section {
                border-left: none;
                border-top: 1px solid #e9ecef;
            }
            
            .maintenance-content {
                padding: 2rem;
            }
            
            .login-section {
                padding: 2rem;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .maintenance-content {
                padding: 1.5rem;
            }
            
            .login-section {
                padding: 1.5rem;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-wrapper">
        <!-- Left Side: Maintenance Information -->
        <div class="maintenance-content">
            <div class="maintenance-icon">
                <i class="fas fa-tools"></i>
            </div>
            
            <h1 class="maintenance-title">Site Maintenance</h1>
            <p class="maintenance-subtitle">We're working hard to improve your experience</p>
            
            <div class="maintenance-message">
                <p><?php echo htmlspecialchars($maintenance_message); ?></p>
            </div>
            
            <div class="maintenance-details">
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="detail-label">Estimated Time</div>
                    <div class="detail-value"><?php echo htmlspecialchars($estimated_downtime); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="detail-label">Started</div>
                    <div class="detail-value"><?php echo date('M j, Y \a\t g:i A'); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="detail-label">Status</div>
                    <div class="detail-value">In Progress</div>
                </div>
            </div>
            
            <div class="maintenance-footer">
                <p>Thank you for your patience. We'll be back online soon!</p>
                <p><strong><?php echo SITE_NAME; ?></strong> - Islamic Knowledge Platform</p>
            </div>
        </div>
        
        <!-- Right Side: Admin Login Form -->
        <div class="login-section">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 class="login-title">Admin Login</h2>
                <p class="login-subtitle">Administrators can access the site during maintenance</p>
            </div>
            
            <form class="login-form" method="POST">
                <?php if ($login_error): ?>
                    <div class="login-error">
                        <i class="fas fa-exclamation-triangle"></i>
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
                    <i class="fas fa-sign-in-alt"></i>
                    Login as Admin
                </button>
            </form>
            
            <div class="admin-notice">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Only administrators can log in during maintenance mode.
            </div>
        </div>
    </div>
</body>
</html>