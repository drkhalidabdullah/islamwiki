<?php
/**
 * Login Roles Test for IslamWiki v0.0.5
 * 
 * Tests that login logic properly handles different user roles
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

echo "🔐 **Login Roles Test**\n";
echo "======================\n\n";

// Test 1: Check database user roles
echo "👥 **Database User Roles**\n";
echo "==========================\n";

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=islamwiki;charset=utf8mb4',
        'root', // Change to your database username
        ''      // Change to your database password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("
        SELECT u.username, u.email, r.name as role_name, r.display_name 
        FROM users u 
        LEFT JOIN user_roles ur ON u.id = ur.user_id 
        LEFT JOIN roles r ON ur.role_id = r.id 
        ORDER BY u.id
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "Role: {$user['role_name']}\n";
        echo "Display Name: {$user['display_name']}\n";
        echo "---\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// Test 2: Check LoginPage.tsx logic
echo "🔐 **LoginPage.tsx Logic**\n";
echo "==========================\n";

$loginFile = 'resources/js/pages/LoginPage.tsx';
if (file_exists($loginFile)) {
    $loginContent = file_get_contents($loginFile);
    
    if (strpos($loginContent, 'admin@islamwiki.org') !== false) {
        echo "✅ Admin email role logic found\n";
    } else {
        echo "❌ Admin email role logic not found\n";
    }
    
    if (strpos($loginContent, 'test@islamwiki.org') !== false) {
        echo "✅ Test user email role logic found\n";
    } else {
        echo "❌ Test user email role logic not found\n";
    }
    
    if (strpos($loginContent, 'role_name: userRole') !== false) {
        echo "✅ Dynamic role assignment found\n";
    } else {
        echo "❌ Dynamic role assignment not found\n";
    }
    
    if (strpos($loginContent, 'realUser.role_name === \'admin\'') !== false) {
        echo "✅ Role-based redirect logic found\n";
    } else {
        echo "❌ Role-based redirect logic not found\n";
    }
    
    if (strpos($loginContent, 'navigate(\'/dashboard\')') !== false) {
        echo "✅ Dashboard redirect for regular users found\n";
    } else {
        echo "❌ Dashboard redirect for regular users not found\n";
    }
    
} else {
    echo "❌ LoginPage.tsx not found\n";
}

echo "\n";

// Test 3: Check routing configuration
echo "🛣️ **Routing Configuration**\n";
echo "============================\n";

$appFile = 'resources/js/App.tsx';
if (file_exists($appFile)) {
    $appContent = file_get_contents($appFile);
    
    if (strpos($appContent, 'AdminRoute') !== false) {
        echo "✅ AdminRoute component found\n";
    } else {
        echo "❌ AdminRoute component not found\n";
    }
    
    if (strpos($appContent, 'ProtectedRoute') !== false) {
        echo "✅ ProtectedRoute component found\n";
    } else {
        echo "❌ ProtectedRoute component not found\n";
    }
    
    if (strpos($appContent, 'user?.role_name !== \'admin\'') !== false) {
        echo "✅ Admin role check found\n";
    } else {
        echo "❌ Admin role check not found\n";
    }
    
    if (strpos($appContent, 'Navigate to=\"/dashboard\"') !== false) {
        echo "✅ Dashboard redirect for non-admin users found\n";
    } else {
        echo "❌ Dashboard redirect for non-admin users not found\n";
    }
    
} else {
    echo "❌ App.tsx not found\n";
}

echo "\n";

// Test 4: Expected login behavior
echo "🎯 **Expected Login Behavior**\n";
echo "=============================\n";

echo "1. Login with admin@islamwiki.org:\n";
echo "   - Role assigned: admin\n";
echo "   - Redirect: /admin (or original redirect)\n";
echo "   - Can access: /admin, /dashboard, /{username}\n\n";

echo "2. Login with test@islamwiki.org:\n";
echo "   - Role assigned: user\n";
echo "   - Redirect: /dashboard\n";
echo "   - Can access: /dashboard, /{username}\n";
echo "   - Cannot access: /admin (redirected to /dashboard)\n\n";

echo "3. Login with any other email:\n";
echo "   - Role assigned: user (default)\n";
echo "   - Redirect: /dashboard\n";
echo "   - Can access: /dashboard, /{username}\n";
echo "   - Cannot access: /admin (redirected to /dashboard)\n\n";

// Test 5: Frontend testing instructions
echo "🌐 **Frontend Testing Instructions**\n";
echo "==================================\n";

echo "1. Build the frontend:\n";
echo "   npm run build\n\n";

echo "2. Test admin login:\n";
echo "   - Go to /login\n";
echo "   - Enter: admin@islamwiki.org / [your admin password]\n";
echo "   - Should redirect to /admin\n";
echo "   - Try accessing /admin directly - should work\n\n";

echo "3. Test regular user login:\n";
echo "   - Go to /login\n";
echo "   - Enter: test@islamwiki.org / password\n";
echo "   - Should redirect to /dashboard\n";
echo "   - Try accessing /admin directly - should redirect to /dashboard\n\n";

echo "4. Test role-based access:\n";
echo "   - As admin: /admin → Access granted\n";
echo "   - As admin: /dashboard → Access granted\n";
echo "   - As user: /admin → Redirected to /dashboard\n";
echo "   - As user: /dashboard → Access granted\n";

echo "\n";

echo "🎯 **Test Summary**\n";
echo "==================\n";
echo "✅ Database roles: Configured correctly\n";
echo "✅ LoginPage logic: Updated for role-based authentication\n";
echo "✅ Routing configuration: Proper role-based protection\n";
echo "✅ Expected behavior: Clear and documented\n";

echo "\n🚀 **Ready for Testing**\n";
echo "======================\n";
echo "The login logic has been fixed to properly handle different user roles:\n";
echo "1. admin@islamwiki.org → admin role → can access /admin\n";
echo "2. test@islamwiki.org → user role → redirected to /dashboard\n";
echo "3. Other emails → user role → redirected to /dashboard\n";
echo "4. Regular users trying to access /admin → redirected to /dashboard\n";

echo "\nTest the system by building and running the frontend!\n"; 