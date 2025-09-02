<?php

/**
 * Database Setup Script for IslamWiki v0.0.5
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

require_once 'src/Core/Database/DatabaseManager.php';

class DatabaseSetupV005
{
    private $database;
    private $migrationFile = 'database/migrations/2025_01_27_000003_add_user_authentication_fields.php';

    public function __construct()
    {
        // Load environment variables
        $this->loadEnvironment();
        
        // Initialize database connection
        $this->initializeDatabase();
    }

    private function loadEnvironment()
    {
        if (file_exists('.env')) {
            $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    private function initializeDatabase()
    {
        try {
            $config = [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_NAME'] ?? 'islamwiki',
                'username' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASS'] ?? '',
                'charset' => 'utf8mb4'
            ];

            $this->database = new IslamWiki\Core\Database\DatabaseManager($config);
            echo "✅ Database connection established successfully\n";
        } catch (Exception $e) {
            die("❌ Database connection failed: " . $e->getMessage() . "\n");
        }
    }

    public function runMigration()
    {
        echo "\n🚀 Starting v0.0.5 Database Migration...\n";
        echo "==========================================\n\n";

        try {
            // Check if migration file exists
            if (!file_exists($this->migrationFile)) {
                throw new Exception("Migration file not found: {$this->migrationFile}");
            }

            // Include migration class
            require_once $this->migrationFile;
            
            // Create migration instance
            $migration = new AddUserAuthenticationFields();
            
            // Run migration
            echo "📋 Running migration: Add User Authentication Fields\n";
            $sql = $migration->up();
            
            // Split SQL into individual statements
            $statements = $this->splitSqlStatements($sql);
            
            $this->database->beginTransaction();
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    echo "  → " . substr($statement, 0, 60) . "...\n";
                    $this->database->execute($statement);
                }
            }
            
            $this->database->commit();
            echo "✅ Migration completed successfully!\n\n";
            
            // Verify migration
            $this->verifyMigration();
            
        } catch (Exception $e) {
            $this->database->rollback();
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    private function splitSqlStatements($sql)
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Split by semicolon, but preserve semicolons in strings
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (($char === "'" || $char === '"') && $sql[$i-1] !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }
            
            if ($char === ';' && !$inString) {
                $current .= $char;
                $statements[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        if (!empty(trim($current))) {
            $statements[] = $current;
        }
        
        return array_filter(array_map('trim', $statements));
    }

    private function verifyMigration()
    {
        echo "🔍 Verifying migration...\n";
        
        try {
            // Check if new columns exist
            $columns = $this->database->execute("SHOW COLUMNS FROM users LIKE 'status'")->fetchAll();
            if (empty($columns)) {
                throw new Exception("Status column not found");
            }
            
            $columns = $this->database->execute("SHOW COLUMNS FROM users LIKE 'password_reset_token'")->fetchAll();
            if (empty($columns)) {
                throw new Exception("Password reset token column not found");
            }
            
            // Check if new tables exist
            $tables = $this->database->execute("SHOW TABLES LIKE 'user_verification_logs'")->fetchAll();
            if (empty($tables)) {
                throw new Exception("User verification logs table not found");
            }
            
            $tables = $this->database->execute("SHOW TABLES LIKE 'user_login_logs'")->fetchAll();
            if (empty($tables)) {
                throw new Exception("User login logs table not found");
            }
            
            $tables = $this->database->execute("SHOW TABLES LIKE 'user_security_settings'")->fetchAll();
            if (empty($tables)) {
                throw new Exception("User security settings table not found");
            }
            
            // Check if new roles exist
            $roles = $this->database->execute("SELECT name FROM roles WHERE name IN ('verified_user', 'trusted_user')")->fetchAll();
            if (count($roles) !== 2) {
                throw new Exception("New roles not found");
            }
            
            // Check if new system settings exist
            $settings = $this->database->execute("SELECT COUNT(*) as count FROM system_settings WHERE `key` LIKE 'jwt_%'")->fetch();
            if ($settings['count'] < 2) {
                throw new Exception("JWT settings not found");
            }
            
            echo "✅ Migration verification completed successfully!\n";
            
        } catch (Exception $e) {
            echo "❌ Migration verification failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function createTestData()
    {
        echo "\n🧪 Creating test data for v0.0.5...\n";
        echo "====================================\n\n";
        
        try {
            // Create test user for authentication testing
            $testUser = [
                'username' => 'testuser_v005',
                'email' => 'testuser_v005@islamwiki.org',
                'password' => 'TestPassword123!',
                'first_name' => 'Test',
                'last_name' => 'User',
                'display_name' => 'Test User v0.0.5',
                'bio' => 'Test user for v0.0.5 authentication testing'
            ];
            
            echo "👤 Creating test user...\n";
            $result = $this->database->execute(
                "INSERT INTO users (username, email, password_hash, first_name, last_name, display_name, bio, status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_verification', NOW(), NOW())",
                [
                    $testUser['username'],
                    $testUser['email'],
                    password_hash($testUser['password'], PASSWORD_DEFAULT),
                    $testUser['first_name'],
                    $testUser['last_name'],
                    $testUser['display_name'],
                    $testUser['bio']
                ]
            );
            
            $userId = $this->database->lastInsertId();
            
            // Assign verified_user role
            $roleId = $this->database->execute("SELECT id FROM roles WHERE name = 'verified_user'")->fetch()['id'];
            $this->database->execute(
                "INSERT INTO user_roles (user_id, role_id, granted_at) VALUES (?, ?, NOW())",
                [$userId, $roleId]
            );
            
            // Create user profile
            $this->database->execute(
                "INSERT INTO user_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())",
                [$userId]
            );
            
            // Create user security settings
            $this->database->execute(
                "INSERT INTO user_security_settings (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())",
                [$userId]
            );
            
            echo "✅ Test user created successfully (ID: {$userId})\n";
            echo "   Username: {$testUser['username']}\n";
            echo "   Password: {$testUser['password']}\n";
            echo "   Status: pending_verification\n";
            
        } catch (Exception $e) {
            echo "❌ Test data creation failed: " . $e->getMessage() . "\n";
        }
    }

    public function run()
    {
        echo "🏗️  IslamWiki Framework v0.0.5 Database Setup\n";
        echo "==============================================\n";
        echo "This script will set up the database for v0.0.5 features:\n";
        echo "• User authentication and verification\n";
        echo "• Password reset functionality\n";
        echo "• Two-factor authentication support\n";
        echo "• Enhanced security logging\n";
        echo "• User status management\n\n";
        
        // Run migration
        if ($this->runMigration()) {
            // Create test data
            $this->createTestData();
            
            echo "\n🎉 v0.0.5 Database setup completed successfully!\n";
            echo "==============================================\n";
            echo "Next steps:\n";
            echo "1. Test the authentication system\n";
            echo "2. Verify email verification flow\n";
            echo "3. Test password reset functionality\n";
            echo "4. Check user management features\n";
            echo "5. Run the test suite\n\n";
            
            return true;
        }
        
        return false;
    }
}

// Run the setup if called directly
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    $setup = new DatabaseSetupV005();
    $setup->run();
} else {
    echo "This script can be run from command line or with ?run=1 parameter\n";
} 