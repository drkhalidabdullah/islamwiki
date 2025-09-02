<?php

/**
 * Test Admin Database Dashboard Integration
 * 
 * This script tests the integration of the database dashboard into the admin panel
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

require_once 'vendor/autoload.php';

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Database\MigrationManager;
use IslamWiki\Admin\DatabaseController;
use IslamWiki\Admin\AdminDatabaseManager;

echo "🧪 **Testing Admin Database Dashboard Integration**\n";
echo "================================================\n\n";

try {
    // Test 1: Check if required classes exist
    echo "✅ **Test 1: Class Existence Check**\n";
    
    if (class_exists('IslamWiki\Core\Database\DatabaseManager')) {
        echo "   - DatabaseManager class exists\n";
    } else {
        echo "   ❌ DatabaseManager class missing\n";
        exit(1);
    }
    
    if (class_exists('IslamWiki\Core\Database\MigrationManager')) {
        echo "   - MigrationManager class exists\n";
    } else {
        echo "   ❌ MigrationManager class missing\n";
        exit(1);
    }
    
    if (class_exists('IslamWiki\Admin\DatabaseController')) {
        echo "   - DatabaseController class exists\n";
    } else {
        echo "   ❌ DatabaseController class missing\n";
        exit(1);
    }
    
    if (class_exists('IslamWiki\Admin\AdminDatabaseManager')) {
        echo "   - AdminDatabaseManager class exists\n";
    } else {
        echo "   ❌ AdminDatabaseManager class missing\n";
        exit(1);
    }
    
    echo "   ✅ All required classes exist\n\n";
    
    // Test 2: Check if database connection is available
    echo "✅ **Test 2: Database Connection Check**\n";
    
    $database = new DatabaseManager([
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'timezone' => 'UTC'
    ]);
    if ($database->isConnected()) {
        echo "   - Database connection successful\n";
        echo "   - Connection time: " . $database->testConnection()['response_time'] . "ms\n";
    } else {
        echo "   ❌ Database connection failed\n";
        exit(1);
    }
    
    echo "   ✅ Database connection working\n\n";
    
    // Test 3: Test AdminDatabaseManager functionality
    echo "✅ **Test 3: AdminDatabaseManager Functionality**\n";
    
    $migrationManager = new MigrationManager($database, 'database/migrations/');
    $adminDbManager = new AdminDatabaseManager($database, $migrationManager);
    
    // Test overview
    $overview = $adminDbManager->getOverview();
    if ($overview['success']) {
        echo "   - Database overview generated successfully\n";
        echo "   - Tables found: " . count($overview['data']['tables']) . "\n";
        echo "   - Migrations: " . $overview['data']['migrations']['executed_migrations'] . "/" . $overview['data']['migrations']['total_migrations'] . "\n";
    } else {
        echo "   ❌ Database overview failed: " . $overview['error'] . "\n";
        exit(1);
    }
    
    // Test health check
    $health = $adminDbManager->getDatabaseHealth();
    if ($health['success']) {
        echo "   - Database health check successful\n";
        echo "   - Overall health: " . $health['data']['overall_health'] . "\n";
    } else {
        echo "   ❌ Database health check failed: " . $health['error'] . "\n";
        exit(1);
    }
    
    echo "   ✅ AdminDatabaseManager working correctly\n\n";
    
    // Test 4: Test DatabaseController API endpoints
    echo "✅ **Test 4: DatabaseController API Endpoints**\n";
    
    $dbController = new DatabaseController($database, $migrationManager);
    
    // Test overview endpoint
    $overviewResponse = $dbController->overview();
    if ($overviewResponse['success']) {
        echo "   - Overview endpoint working\n";
    } else {
        echo "   ❌ Overview endpoint failed: " . $overviewResponse['error'] . "\n";
        exit(1);
    }
    
    // Test health endpoint
    $healthResponse = $dbController->health();
    if ($healthResponse['success']) {
        echo "   - Health endpoint working\n";
    } else {
        echo "   ❌ Health endpoint failed: " . $healthResponse['error'] . "\n";
        exit(1);
    }
    
    // Test migration status endpoint
    $migrationResponse = $dbController->migrationStatus();
    if (isset($migrationResponse['total_migrations'])) {
        echo "   - Migration status endpoint working\n";
        echo "   - Total migrations: " . $migrationResponse['total_migrations'] . "\n";
        echo "   - Executed: " . $migrationResponse['executed_migrations'] . "\n";
        echo "   - Pending: " . $migrationResponse['pending_migrations'] . "\n";
    } else {
        echo "   ❌ Migration status endpoint failed: " . ($migrationResponse['error'] ?? 'Unknown error') . "\n";
        exit(1);
    }
    
    echo "   ✅ All API endpoints working correctly\n\n";
    
    // Test 5: Test query execution (safe queries only)
    echo "✅ **Test 5: Safe Query Execution**\n";
    
    $queryResponse = $dbController->executeQuery(['sql' => 'SHOW TABLES']);
    if ($queryResponse['success']) {
        echo "   - Safe query execution working\n";
        echo "   - Tables returned: " . count($queryResponse['data']) . "\n";
    } else {
        echo "   ❌ Safe query execution failed: " . $queryResponse['error'] . "\n";
        exit(1);
    }
    
    echo "   ✅ Query execution working correctly\n\n";
    
    // Test 6: Check React component integration
    echo "✅ **Test 6: React Component Integration**\n";
    
    if (file_exists('resources/js/components/admin/DatabaseDashboard.tsx')) {
        echo "   - DatabaseDashboard.tsx component exists\n";
    } else {
        echo "   ❌ DatabaseDashboard.tsx component missing\n";
        exit(1);
    }
    
    if (file_exists('resources/js/pages/AdminPage.tsx')) {
        echo "   - AdminPage.tsx exists and should include database view\n";
    } else {
        echo "   ❌ AdminPage.tsx missing\n";
        exit(1);
    }
    
    // Check if AdminPage includes database view
    $adminPageContent = file_get_contents('resources/js/pages/AdminPage.tsx');
    if (strpos($adminPageContent, "'database'") !== false) {
        echo "   - Database view type added to AdminPage\n";
    } else {
        echo "   ❌ Database view type not found in AdminPage\n";
        exit(1);
    }
    
    if (strpos($adminPageContent, 'DatabaseDashboard') !== false) {
        echo "   - DatabaseDashboard imported in AdminPage\n";
    } else {
        echo "   ❌ DatabaseDashboard import not found in AdminPage\n";
        exit(1);
    }
    
    if (strpos($adminPageContent, 'Database Management') !== false) {
        echo "   - Database Management navigation button added\n";
    } else {
        echo "   ❌ Database Management navigation button not found\n";
        exit(1);
    }
    
    echo "   ✅ React component integration complete\n\n";
    
    // Test 7: Check admin routes configuration
    echo "✅ **Test 7: Admin Routes Configuration**\n";
    
    if (file_exists('config/admin_database_routes.php')) {
        echo "   - Admin database routes file exists\n";
        
        $routes = require 'config/admin_database_routes.php';
        $expectedEndpoints = [
            '/admin/api/database/overview',
            '/admin/api/database/health',
            '/admin/api/database/migrations/run',
            '/admin/api/database/migrations/rollback',
            '/admin/api/database/query'
        ];
        
        foreach ($expectedEndpoints as $endpoint) {
            $found = false;
            foreach ($routes as $route => $config) {
                if (strpos($route, $endpoint) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                echo "   - Endpoint configured: " . $endpoint . "\n";
            } else {
                echo "   ❌ Endpoint missing: " . $endpoint . "\n";
                exit(1);
            }
        }
    } else {
        echo "   ❌ Admin database routes file missing\n";
        exit(1);
    }
    
    echo "   ✅ Admin routes configuration complete\n\n";
    
    // Summary
    echo "🎉 **Admin Database Dashboard Integration Test Results**\n";
    echo "=====================================================\n";
    echo "✅ All 7 tests passed successfully!\n";
    echo "✅ Database dashboard is fully integrated into admin panel\n";
    echo "✅ All API endpoints are working correctly\n";
    echo "✅ React components are properly configured\n";
    echo "✅ Admin routes are properly configured\n\n";
    
    echo "🚀 **Ready to Use!**\n";
    echo "The database dashboard is now accessible at:\n";
    echo "- Frontend: /admin#database\n";
    echo "- API Endpoints: /admin/api/database/*\n";
    echo "- Features: Overview, Health, Migrations, Query Execution\n\n";
    
    echo "📋 **Next Steps:**\n";
    echo "1. Access the admin dashboard at /admin\n";
    echo "2. Click on 'Database Management' in the sidebar\n";
    echo "3. Monitor database health and performance\n";
    echo "4. Run migrations and execute safe queries\n";
    echo "5. View database statistics and table information\n\n";
    
} catch (Exception $e) {
    echo "❌ **Test Failed with Exception:**\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ **Test completed successfully!**\n"; 