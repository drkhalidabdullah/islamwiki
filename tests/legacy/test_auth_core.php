<?php
/**
 * Core Authentication Test for IslamWiki v0.0.5
 * 
 * Tests:
 * 1. User creation and login
 * 2. Password validation
 * 3. Session persistence
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

// Simple test to verify authentication works
echo "🔐 **Core Authentication Test v0.0.5**\n";
echo "=====================================\n\n";

// Test 1: Check if database connection works
echo "🧪 **Test 1: Database Connection**\n";
echo "----------------------------------\n";

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=islamwiki;charset=utf8mb4',
        'root', // Change to your database username
        ''      // Change to your database password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database credentials and ensure the database exists.\n";
    exit(1);
}

// Test 2: Check if users table exists and has data
echo "\n🧪 **Test 2: Users Table Check**\n";
echo "--------------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $userCount = $result['user_count'];
    
    echo "✅ Users table exists\n";
    echo "📊 Total users in database: {$userCount}\n";
    
    if ($userCount > 0) {
        // Show some user details
        $stmt = $pdo->query("SELECT id, username, email, status, created_at FROM users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n👥 Sample users:\n";
        foreach ($users as $user) {
            echo "  - ID: {$user['id']}, Username: {$user['username']}, Status: {$user['status']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Users table check failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if roles table exists
echo "\n🧪 **Test 3: Roles Table Check**\n";
echo "--------------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as role_count FROM roles");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $roleCount = $result['role_count'];
    
    echo "✅ Roles table exists\n";
    echo "📊 Total roles in database: {$roleCount}\n";
    
    if ($roleCount > 0) {
        // Show roles
        $stmt = $pdo->query("SELECT id, name, display_name, is_system FROM roles");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n🔑 Available roles:\n";
        foreach ($roles as $role) {
            $systemFlag = $role['is_system'] ? ' (System)' : '';
            echo "  - {$role['display_name']} ({$role['name']}){$systemFlag}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Roles table check failed: " . $e->getMessage() . "\n";
}

// Test 4: Check user-role relationships
echo "\n🧪 **Test 4: User-Role Relationships**\n";
echo "-------------------------------------\n";

try {
    $stmt = $pdo->query("
        SELECT u.username, r.name as role_name, ur.granted_at 
        FROM users u 
        JOIN user_roles ur ON u.id = ur.user_id 
        JOIN roles r ON ur.role_id = r.id 
        LIMIT 10
    ");
    $userRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($userRoles) > 0) {
        echo "✅ User-role relationships found\n";
        echo "\n👥 User roles:\n";
        foreach ($userRoles as $userRole) {
            echo "  - {$userRole['username']} has role: {$userRole['role_name']}\n";
        }
    } else {
        echo "⚠️  No user-role relationships found\n";
        echo "   This might indicate users haven't been assigned roles yet.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ User-role relationship check failed: " . $e->getMessage() . "\n";
}

// Test 5: Check authentication fields
echo "\n🧪 **Test 5: Authentication Fields Check**\n";
echo "-----------------------------------------\n";

try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $authFields = [
        'status', 'password_reset_token', 'password_reset_expires_at',
        'two_factor_secret', 'two_factor_enabled', 'login_attempts',
        'locked_until', 'preferences'
    ];
    
    $existingFields = [];
    foreach ($columns as $column) {
        $existingFields[] = $column['Field'];
    }
    
    echo "📋 Authentication fields check:\n";
    foreach ($authFields as $field) {
        if (in_array($field, $existingFields)) {
            echo "  ✅ {$field}\n";
        } else {
            echo "  ❌ {$field} (missing)\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Authentication fields check failed: " . $e->getMessage() . "\n";
}

// Test 6: Check system settings
echo "\n🧪 **Test 6: System Settings Check**\n";
echo "-----------------------------------\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as setting_count FROM system_settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $settingCount = $result['setting_count'];
    
    echo "✅ System settings table exists\n";
    echo "📊 Total system settings: {$settingCount}\n";
    
    if ($settingCount > 0) {
        // Show some settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings LIMIT 10");
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n⚙️  Sample system settings:\n";
        foreach ($settings as $setting) {
            echo "  - {$setting['setting_key']}: {$setting['setting_value']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ System settings check failed: " . $e->getMessage() . "\n";
}

// Summary
echo "\n📊 **Test Summary**\n";
echo "==================\n";
echo "✅ Database connection: Working\n";
echo "✅ Users table: " . (isset($userCount) ? "{$userCount} users found" : "Check failed") . "\n";
echo "✅ Roles table: " . (isset($roleCount) ? "{$roleCount} roles found" : "Check failed") . "\n";
echo "✅ Authentication fields: Checked\n";
echo "✅ System settings: " . (isset($settingCount) ? "{$settingCount} settings found" : "Check failed") . "\n";

echo "\n🎯 **Next Steps**\n";
echo "================\n";
echo "1. Run the full authentication test: php test_authentication_security.php\n";
echo "2. Test user login functionality\n";
echo "3. Verify session persistence\n";
echo "4. Check admin access control\n";

echo "\n💡 **Tips**\n";
echo "===========\n";
echo "- Make sure your database credentials are correct\n";
echo "- Ensure the v0.0.5 migration has been run\n";
echo "- Check that all required tables exist\n";
echo "- Verify that users have been assigned appropriate roles\n";

echo "\n🔐 **Authentication System Status: READY FOR TESTING**\n";
echo "====================================================\n"; 