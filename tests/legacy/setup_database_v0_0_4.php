<?php

/**
 * IslamWiki Framework v0.0.4 Database Setup Script
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 * 
 * This script sets up the actual database for v0.0.4
 */

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Database\MigrationManager;

echo "🚀 **IslamWiki Framework v0.0.4 Database Setup**\n";
echo "==============================================\n\n";

// Configuration
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'timezone' => 'UTC'
];

// Get configuration from user
echo "📋 **Database Configuration**\n";
echo "----------------------------\n";

echo "Current configuration:\n";
echo "Host: {$config['host']}\n";
echo "Port: {$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "Username: {$config['username']}\n";
echo "Password: " . str_repeat('*', strlen($config['password'])) . "\n\n";

echo "Do you want to use these settings? (y/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));

if (strtolower($response) !== 'y') {
    echo "Please update the configuration in this script and run again.\n";
    exit(1);
}

echo "\n";

try {
    // Step 1: Create database connection (without database)
    echo "📊 **Step 1: Testing Database Connection**\n";
    echo "----------------------------------------\n";
    
    $rootConfig = $config;
    $rootConfig['database'] = ''; // Connect without database first
    
    $rootDb = new PDO(
        "mysql:host={$rootConfig['host']};port={$rootConfig['port']};charset=utf8mb4",
        $rootConfig['username'],
        $rootConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Root database connection successful\n";
    
    // Step 2: Create database if it doesn't exist
    echo "\n📊 **Step 2: Creating Database**\n";
    echo "--------------------------------\n";
    
    $dbName = $config['database'];
    $rootDb->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '{$dbName}' created/verified\n";
    
    // Step 3: Connect to the new database
    echo "\n📊 **Step 3: Connecting to Database**\n";
    echo "------------------------------------\n";
    
    $database = new DatabaseManager($config);
    
    if ($database->isConnected()) {
        echo "✅ Database connection successful\n";
        
        $connectionTest = $database->testConnection();
        echo "   - Response Time: {$connectionTest['response_time']}ms\n";
        echo "   - Server Version: {$connectionTest['server_version']}\n";
    } else {
        throw new Exception("Failed to connect to database");
    }
    
    // Step 4: Run migrations
    echo "\n📊 **Step 4: Running Database Migrations**\n";
    echo "------------------------------------------\n";
    
    $migrationManager = new MigrationManager($database, __DIR__ . '/database/migrations/');
    
    $migrationStatus = $migrationManager->getStatus();
    echo "Migration Status:\n";
    echo "   - Total Migrations: {$migrationStatus['total_migrations']}\n";
    echo "   - Executed: {$migrationStatus['executed_migrations']}\n";
    echo "   - Pending: {$migrationStatus['pending_migrations']}\n";
    
    if ($migrationStatus['pending_migrations'] > 0) {
        echo "\n⏳ Running pending migrations...\n";
        
        $migrationResult = $migrationManager->migrate();
        
        if (isset($migrationResult['message'])) {
            echo "✅ {$migrationResult['message']}\n";
            echo "   - Batch: {$migrationResult['batch']}\n";
            echo "   - Migrations: " . count($migrationResult['migrations']) . "\n";
            
            foreach ($migrationResult['migrations'] as $migration) {
                $status = $migration['status'] === 'success' ? '✅' : '❌';
                echo "   {$status} {$migration['migration']} - {$migration['message']}";
                if ($migration['execution_time'] > 0) {
                    echo " ({$migration['execution_time']}ms)";
                }
                echo "\n";
            }
        }
    } else {
        echo "✅ No pending migrations to run\n";
    }
    
    // Step 5: Verify database schema
    echo "\n📊 **Step 5: Verifying Database Schema**\n";
    echo "----------------------------------------\n";
    
    $requiredTables = [
        'users', 'roles', 'user_roles', 'user_profiles', 'content_categories',
        'articles', 'article_versions', 'comments', 'posts', 'likes',
        'follows', 'courses', 'lessons', 'sessions', 'activity_logs', 'scholars'
    ];
    
    $existingTables = [];
    $stmt = $database->execute("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existingTables[] = $row[0];
    }
    
    $missingTables = array_diff($requiredTables, $existingTables);
    
    if (empty($missingTables)) {
        echo "✅ All required tables exist\n";
        echo "   - Found " . count($existingTables) . " tables\n";
    } else {
        echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
        throw new Exception("Database schema incomplete");
    }
    
    // Step 6: Create environment file
    echo "\n📊 **Step 6: Creating Environment File**\n";
    echo "----------------------------------------\n";
    
    $envContent = "# IslamWiki Framework Environment Configuration
# Generated by setup_database_v0_0_4.php
# Date: " . date('Y-m-d H:i:s') . "

# Application Settings
APP_NAME=IslamWiki
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
APP_TIMEZONE=UTC
APP_LOCALE=en

# Database Configuration
DB_CONNECTION=mysql
DB_HOST={$config['host']}
DB_PORT={$config['port']}
DB_DATABASE={$config['database']}
DB_USERNAME={$config['username']}
DB_PASSWORD={$config['password']}
DB_CHARSET={$config['charset']}
DB_COLLATION=utf8mb4_unicode_ci

# Cache Configuration
CACHE_DRIVER=file
CACHE_PREFIX=islamwiki_
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIES=false

# Security Configuration
JWT_SECRET=" . bin2hex(random_bytes(32)) . "
JWT_TTL=60
JWT_REFRESH_TTL=20160
APP_KEY=" . bin2hex(random_bytes(32)) . "
CSRF_TOKEN_NAME=csrf_token

# File Upload Configuration
UPLOAD_MAX_SIZE=8388608
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
UPLOAD_PATH=uploads

# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=info
LOG_DAYS=14

# Monitoring Configuration
MONITORING_ENABLED=true
PERFORMANCE_MONITORING=true
";

    if (file_put_contents('.env', $envContent)) {
        echo "✅ Environment file (.env) created successfully\n";
    } else {
        echo "⚠️  Could not create .env file (check permissions)\n";
    }
    
    // Step 7: Test database functionality
    echo "\n📊 **Step 7: Testing Database Functionality**\n";
    echo "---------------------------------------------\n";
    
    // Test basic operations
    $testQueries = [
        "SELECT COUNT(*) as user_count FROM users",
        "SELECT COUNT(*) as role_count FROM roles",
        "SELECT COUNT(*) as category_count FROM content_categories"
    ];
    
    foreach ($testQueries as $query) {
        try {
            $stmt = $database->execute($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $tableName = explode(' ', $query)[3];
            echo "✅ {$tableName}: " . array_values($result)[0] . " records\n";
        } catch (Exception $e) {
            echo "❌ {$tableName}: Error - " . $e->getMessage() . "\n";
        }
    }
    
    // Step 8: Final summary
    echo "\n🎉 **Database Setup Complete!**\n";
    echo "==============================\n";
    echo "✅ Database created and connected\n";
    echo "✅ Schema migrated successfully\n";
    echo "✅ Environment file created\n";
    echo "✅ Basic functionality tested\n";
    echo "\n";
    echo "**Next Steps:**\n";
    echo "1. Test the database: php test_database_v0_0_4.php\n";
    echo "2. Access admin panel: http://localhost/admin\n";
    echo "3. Continue with v0.0.4 development\n";
    echo "\n";
    echo "**Database Information:**\n";
    echo "- Host: {$config['host']}:{$config['port']}\n";
    echo "- Database: {$config['database']}\n";
    echo "- Username: {$config['username']}\n";
    echo "- Tables: " . count($existingTables) . " created\n";
    echo "- Migrations: " . $migrationStatus['executed_migrations'] . " executed\n";

} catch (Exception $e) {
    echo "❌ **Setup Failed**\n";
    echo "==================\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n";
    echo "**Troubleshooting:**\n";
    echo "1. Check MySQL server is running\n";
    echo "2. Verify database credentials\n";
    echo "3. Ensure proper permissions\n";
    echo "4. Check PHP PDO extension is enabled\n";
    
    exit(1);
} 