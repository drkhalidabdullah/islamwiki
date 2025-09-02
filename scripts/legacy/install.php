<?php
/**
 * IslamWiki Framework - Web Installer
 * 
 * Author: Khalid Abdullah
 * Version: 0.0.1
 * Date: 2025-08-30
 * License: AGPL-3.0
 * 
 * This file provides a web-based installation wizard for the IslamWiki platform.
 */

// Prevent direct access if already installed
if (file_exists('.env') && !isset($_GET['force'])) {
    die('IslamWiki is already installed. If you need to reinstall, add ?force=1 to the URL.');
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            // Database configuration
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbPort = $_POST['db_port'] ?? '3306';
            $dbName = $_POST['db_name'] ?? 'islamwiki';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_password'] ?? '';
            
            try {
                $pdo = new PDO("mysql:host={$dbHost};port={$dbPort}", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");
                
                // Import schema
                $schema = file_get_contents('database/schema.sql');
                $pdo->exec($schema);
                
                // Store database config
                $_SESSION['db_config'] = [
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'name' => $dbName,
                    'user' => $dbUser,
                    'pass' => $dbPass
                ];
                
                $success[] = 'Database configured successfully!';
                $step = 3;
                
            } catch (PDOException $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage();
            }
            break;
            
        case 3:
            // Admin user creation
            $adminUser = $_POST['admin_username'] ?? '';
            $adminEmail = $_POST['admin_email'] ?? '';
            $adminPass = $_POST['admin_password'] ?? '';
            $adminPassConfirm = $_POST['admin_password_confirm'] ?? '';
            
            if (empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
                $errors[] = 'All fields are required.';
            } elseif ($adminPass !== $adminPassConfirm) {
                $errors[] = 'Passwords do not match.';
            } elseif (strlen($adminPass) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            } else {
                try {
                    $dbConfig = $_SESSION['db_config'];
                    $pdo = new PDO("mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}", $dbConfig['user'], $dbConfig['pass']);
                    
                    // Create admin user
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, display_name, email_verified_at, is_active) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)");
                    $stmt->execute([
                        $adminUser,
                        $adminEmail,
                        password_hash($adminPass, PASSWORD_DEFAULT),
                        $_POST['admin_first_name'] ?? 'Admin',
                        $_POST['admin_last_name'] ?? 'User',
                        $_POST['admin_display_name'] ?? 'Administrator'
                    ]);
                    
                    $userId = $pdo->lastInsertId();
                    
                    // Assign admin role
                    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 1)");
                    $stmt->execute([$userId, 1]); // Role ID 1 is admin
                    
                    $success[] = 'Admin user created successfully!';
                    $step = 4;
                    
                } catch (Exception $e) {
                    $errors[] = 'Failed to create admin user: ' . $e->getMessage();
                }
            }
            break;
            
        case 4:
            // Generate .env file
            try {
                $dbConfig = $_SESSION['db_config'];
                $envContent = file_get_contents('env.example');
                
                // Replace placeholders
                $envContent = str_replace('your_db_user', $dbConfig['user'], $envContent);
                $envContent = str_replace('your_db_password', $dbConfig['pass'], $envContent);
                $envContent = str_replace('localhost', $dbConfig['host'], $envContent);
                $envContent = str_replace('3306', $dbConfig['port'], $envContent);
                $envContent = str_replace('islamwiki', $dbConfig['name'], $envContent);
                
                // Generate random keys
                $envContent = str_replace('your_jwt_secret_key_here', bin2hex(random_bytes(32)), $envContent);
                $envContent = str_replace('your_app_key_here', bin2hex(random_bytes(32)), $envContent);
                
                file_put_contents('.env', $envContent);
                
                $success[] = 'Configuration file created successfully!';
                $step = 5;
                
            } catch (Exception $e) {
                $errors[] = 'Failed to create configuration file: ' . $e->getMessage();
            }
            break;
    }
}

// Check system requirements
$requirements = [
    'PHP Version (8.2+)' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'JSON Extension' => extension_loaded('json'),
    'OpenSSL Extension' => extension_loaded('openssl'),
    'Mbstring Extension' => extension_loaded('mbstring'),
    'Fileinfo Extension' => extension_loaded('fileinfo'),
    'Composer Autoloader' => file_exists('vendor/autoload.php'),
    'Writable Directory' => is_writable('.'),
];

$allRequirementsMet = !in_array(false, $requirements);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IslamWiki Framework - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .step { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .error { color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 3px; margin: 10px 0; }
        .success { color: #388e3c; background: #e8f5e8; padding: 10px; border-radius: 3px; margin: 10px 0; }
        .requirement { margin: 5px 0; }
        .requirement.pass { color: #388e3c; }
        .requirement.fail { color: #d32f2f; }
        input, select { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 3px; }
        button { background: #2196f3; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #1976d2; }
        .nav { text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🚀 IslamWiki Framework Installation</h1>
    
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <?php foreach ($success as $msg): ?>
            <div class="success">✅ <?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Step 1: System Requirements -->
    <?php if ($step == 1): ?>
        <div class="step">
            <h2>Step 1: System Requirements Check</h2>
            <?php foreach ($requirements as $requirement => $met): ?>
                <div class="requirement <?= $met ? 'pass' : 'fail' ?>">
                    <?= $met ? '✅' : '❌' ?> <?= htmlspecialchars($requirement) ?>
                </div>
            <?php endforeach; ?>
            
            <?php if ($allRequirementsMet): ?>
                <p>✅ All system requirements are met!</p>
                <div class="nav">
                    <a href="?step=2"><button type="button">Continue to Database Setup</button></a>
                </div>
            <?php else: ?>
                <p>❌ Please fix the above requirements before continuing.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Step 2: Database Configuration -->
    <?php if ($step == 2): ?>
        <div class="step">
            <h2>Step 2: Database Configuration</h2>
            <form method="POST">
                <label>Database Host:</label>
                <input type="text" name="db_host" value="localhost" required>
                
                <label>Database Port:</label>
                <input type="text" name="db_port" value="3306" required>
                
                <label>Database Name:</label>
                <input type="text" name="db_name" value="islamwiki" required>
                
                <label>Database Username:</label>
                <input type="text" name="db_user" required>
                
                <label>Database Password:</label>
                <input type="password" name="db_password" required>
                
                <button type="submit">Test Connection & Continue</button>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Step 3: Admin User Creation -->
    <?php if ($step == 3): ?>
        <div class="step">
            <h2>Step 3: Create Administrator Account</h2>
            <form method="POST">
                <label>Username:</label>
                <input type="text" name="admin_username" required>
                
                <label>Email:</label>
                <input type="email" name="admin_email" required>
                
                <label>First Name:</label>
                <input type="text" name="admin_first_name" value="Admin" required>
                
                <label>Last Name:</label>
                <input type="text" name="admin_last_name" value="User" required>
                
                <label>Display Name:</label>
                <input type="text" name="admin_display_name" value="Administrator" required>
                
                <label>Password:</label>
                <input type="password" name="admin_password" required>
                
                <label>Confirm Password:</label>
                <input type="password" name="admin_password_confirm" required>
                
                <button type="submit">Create Admin Account</button>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Step 4: Configuration -->
    <?php if ($step == 4): ?>
        <div class="step">
            <h2>Step 4: Generate Configuration</h2>
            <form method="POST">
                <p>Ready to generate the configuration file.</p>
                <button type="submit">Generate .env File</button>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Step 5: Installation Complete -->
    <?php if ($step == 5): ?>
        <div class="step">
            <h2>🎉 Installation Complete!</h2>
            <p>IslamWiki has been successfully installed on your system.</p>
            
            <h3>Next Steps:</h3>
            <ol>
                <li>Delete the <code>install.php</code> file for security</li>
                <li>Set up your web server to point to the <code>public/</code> directory</li>
                <li>Access your site at your domain</li>
                <li>Log in with your admin account</li>
            </ol>
            
            <div class="nav">
                <a href="public/"><button type="button">Go to Your Site</button></a>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="nav">
        <p>Step <?= $step ?> of 5</p>
    </div>
</body>
</html> 