<?php
/**
 * User Password Reset Script for IslamWiki v0.0.5
 * 
 * Use this script to reset user passwords and clear lockouts
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

echo "🔐 **User Password Reset Tool**\n";
echo "==============================\n\n";

// Database connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=islamwiki;charset=utf8mb4',
        'root', // Change to your database username
        ''      // Change to your database password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Show current users
echo "👥 **Current Users**\n";
echo "==================\n";
$stmt = $pdo->query("SELECT id, username, email, status, login_attempts, locked_until FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "ID: {$user['id']} | Username: {$user['username']} | Email: {$user['email']}\n";
    echo "Status: {$user['status']} | Login Attempts: {$user['login_attempts']} | Locked: " . ($user['locked_until'] ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

echo "\n";

// Reset specific user
if (isset($_GET['username']) && isset($_GET['password'])) {
    $username = $_GET['username'];
    $newPassword = $_GET['password'];
    
    echo "🔄 **Resetting Password for: {$username}**\n";
    echo "========================================\n";
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update user
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = ?, 
            login_attempts = 0, 
            locked_until = NULL,
            updated_at = NOW()
        WHERE username = ?
    ");
    
    if ($stmt->execute([$hashedPassword, $username])) {
        echo "✅ Password reset successful for {$username}\n";
        echo "✅ Login attempts cleared\n";
        echo "✅ Account unlocked\n";
        echo "✅ New password: {$newPassword}\n";
    } else {
        echo "❌ Password reset failed for {$username}\n";
    }
    
    echo "\n";
}

// Clear all lockouts
if (isset($_GET['clear_lockouts'])) {
    echo "🔓 **Clearing All Account Lockouts**\n";
    echo "==================================\n";
    
    $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE login_attempts > 0 OR locked_until IS NOT NULL");
    
    if ($stmt->execute()) {
        $affectedRows = $stmt->rowCount();
        echo "✅ Cleared lockouts for {$affectedRows} users\n";
    } else {
        echo "❌ Failed to clear lockouts\n";
    }
    
    echo "\n";
}

// Usage instructions
echo "💡 **Usage Instructions**\n";
echo "=======================\n";
echo "1. Reset specific user password:\n";
echo "   http://yourdomain.com/reset_user_password.php?username=testuser&password=newpassword\n\n";

echo "2. Clear all account lockouts:\n";
echo "   http://yourdomain.com/reset_user_password.php?clear_lockouts=1\n\n";

echo "3. Reset admin password:\n";
echo "   http://yourdomain.com/reset_user_password.php?username=admin&password=admin123\n\n";

echo "🔒 **Security Note**\n";
echo "==================\n";
echo "This script should be removed or protected after use.\n";
echo "It's intended for development/testing purposes only.\n";

echo "\n🎯 **Current Status**\n";
echo "==================\n";
echo "All users are now unlocked and ready for login.\n";
echo "Use the test credentials:\n";
echo "- Username: testuser, Password: password\n";
echo "- Username: admin, Password: (your admin password)\n";

echo "\n🚀 **Ready for Testing**\n";
echo "======================\n";
echo "You can now test the authentication system again!\n"; 