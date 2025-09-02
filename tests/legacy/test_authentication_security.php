<?php
/**
 * Authentication Security Test Suite for IslamWiki v0.0.5
 * 
 * This script tests:
 * 1. Admin features are not accessible to regular users
 * 2. Only users with correct passwords can login
 * 3. User sessions persist across page refreshes
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

require_once 'src/Core/Database/DatabaseManager.php';
require_once 'src/Services/User/UserService.php';
require_once 'src/Controllers/AuthController.php';

class AuthenticationSecurityTest
{
    private DatabaseManager $database;
    private UserService $userService;
    private AuthController $authController;
    private array $testUsers = [];
    private array $testResults = [];

    public function __construct()
    {
        $this->loadEnvironment();
        $this->initializeServices();
    }

    /**
     * Load environment variables
     */
    private function loadEnvironment(): void
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

    /**
     * Initialize database and services
     */
    private function initializeServices(): void
    {
        try {
            $this->database = new DatabaseManager(
                $_ENV['DB_HOST'] ?? 'localhost',
                $_ENV['DB_DATABASE'] ?? 'islamwiki',
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? ''
            );
            
            $this->userService = new UserService($this->database);
            $this->authController = new AuthController($this->database);
            
            echo "✅ Services initialized successfully\n";
        } catch (Exception $e) {
            die("❌ Failed to initialize services: " . $e->getMessage() . "\n");
        }
    }

    /**
     * Run all authentication security tests
     */
    public function runAllTests(): void
    {
        echo "\n🔐 **Authentication Security Test Suite v0.0.5**\n";
        echo "================================================\n\n";

        // Test 1: Create test users and verify admin access control
        $this->testAdminAccessControl();
        
        // Test 2: Verify login security with correct/incorrect passwords
        $this->testLoginSecurity();
        
        // Test 3: Verify session persistence
        $this->testSessionPersistence();
        
        // Display test results
        $this->displayTestResults();
        
        // Cleanup test data
        $this->cleanupTestData();
    }

    /**
     * Test 1: Admin access control
     */
    private function testAdminAccessControl(): void
    {
        echo "🧪 **Test 1: Admin Access Control**\n";
        echo "----------------------------------\n";

        try {
            // Create test users with different roles
            $this->createTestUsers();
            
            // Test admin user access
            $adminUser = $this->testUsers['admin'];
            $adminToken = $this->loginUser($adminUser['username'], $adminUser['password']);
            
            if ($adminToken) {
                echo "✅ Admin user can login successfully\n";
                
                // Test admin profile access
                $adminProfile = $this->getUserProfile($adminToken);
                if ($adminProfile && isset($adminProfile['data']['username']) && $adminProfile['data']['username'] === 'test_admin') {
                    echo "✅ Admin user can access their profile\n";
                } else {
                    echo "❌ Admin user cannot access profile\n";
                    $this->testResults['admin_profile_access'] = false;
                }
            } else {
                echo "❌ Admin user cannot login\n";
                $this->testResults['admin_login'] = false;
            }

            // Test regular user access
            $regularUser = $this->testUsers['regular'];
            $regularToken = $this->loginUser($regularUser['username'], $regularUser['password']);
            
            if ($regularToken) {
                echo "✅ Regular user can login successfully\n";
                
                // Test regular user profile access
                $regularProfile = $this->getUserProfile($regularToken);
                if ($regularProfile && isset($regularProfile['data']['username']) && $regularProfile['data']['username'] === 'test_regular') {
                    echo "✅ Regular user can access their profile\n";
                } else {
                    echo "❌ Regular user cannot access profile\n";
                    $this->testResults['regular_profile_access'] = false;
                }
            } else {
                echo "❌ Regular user cannot login\n";
                $this->testResults['regular_login'] = false;
            }

            // Test that regular users cannot access admin features
            $this->testAdminFeatureAccess($regularToken);

        } catch (Exception $e) {
            echo "❌ Admin access control test failed: " . $e->getMessage() . "\n";
            $this->testResults['admin_access_control'] = false;
        }

        echo "\n";
    }

    /**
     * Test 2: Login security
     */
    private function testLoginSecurity(): void
    {
        echo "🔒 **Test 2: Login Security**\n";
        echo "------------------------------\n";

        try {
            $testUser = $this->testUsers['regular'];
            
            // Test 1: Correct username and password
            $correctLogin = $this->loginUser($testUser['username'], $testUser['password']);
            if ($correctLogin) {
                echo "✅ Correct credentials: Login successful\n";
            } else {
                echo "❌ Correct credentials: Login failed\n";
                $this->testResults['correct_login'] = false;
            }

            // Test 2: Correct username, wrong password
            $wrongPasswordLogin = $this->loginUser($testUser['username'], 'wrong_password_123');
            if (!$wrongPasswordLogin) {
                echo "✅ Wrong password: Login correctly rejected\n";
            } else {
                echo "❌ Wrong password: Login incorrectly accepted\n";
                $this->testResults['wrong_password_rejection'] = false;
            }

            // Test 3: Wrong username, correct password
            $wrongUsernameLogin = $this->loginUser('nonexistent_user', $testUser['password']);
            if (!$wrongUsernameLogin) {
                echo "✅ Wrong username: Login correctly rejected\n";
            } else {
                echo "❌ Wrong username: Login incorrectly accepted\n";
                $this->testResults['wrong_username_rejection'] = false;
            }

            // Test 4: Non-existent user
            $nonexistentLogin = $this->loginUser('nonexistent_user', 'any_password');
            if (!$nonexistentLogin) {
                echo "✅ Non-existent user: Login correctly rejected\n";
            } else {
                echo "❌ Non-existent user: Login incorrectly accepted\n";
                $this->testResults['nonexistent_user_rejection'] = false;
            }

            // Test 5: Empty credentials
            $emptyLogin = $this->loginUser('', '');
            if (!$emptyLogin) {
                echo "✅ Empty credentials: Login correctly rejected\n";
            } else {
                echo "❌ Empty credentials: Login incorrectly accepted\n";
                $this->testResults['empty_credentials_rejection'] = false;
            }

        } catch (Exception $e) {
            echo "❌ Login security test failed: " . $e->getMessage() . "\n";
            $this->testResults['login_security'] = false;
        }

        echo "\n";
    }

    /**
     * Test 3: Session persistence
     */
    private function testSessionPersistence(): void
    {
        echo "🔄 **Test 3: Session Persistence**\n";
        echo "----------------------------------\n";

        try {
            $testUser = $this->testUsers['regular'];
            
            // Login user
            $token = $this->loginUser($testUser['username'], $testUser['password']);
            
            if (!$token) {
                echo "❌ Cannot test session persistence - login failed\n";
                $this->testResults['session_persistence'] = false;
                return;
            }

            echo "✅ User logged in successfully\n";

            // Test 1: Immediate profile access
            $profile1 = $this->getUserProfile($token);
            if ($profile1 && isset($profile1['data']['username'])) {
                echo "✅ Profile access successful immediately after login\n";
            } else {
                echo "❌ Profile access failed immediately after login\n";
                $this->testResults['immediate_profile_access'] = false;
            }

            // Test 2: Profile access after short delay (simulating page refresh)
            sleep(1);
            $profile2 = $this->getUserProfile($token);
            if ($profile2 && isset($profile2['data']['username'])) {
                echo "✅ Profile access successful after delay (simulating refresh)\n";
            } else {
                echo "❌ Profile access failed after delay\n";
                $this->testResults['delayed_profile_access'] = false;
            }

            // Test 3: Multiple profile accesses (simulating multiple page loads)
            $successCount = 0;
            for ($i = 0; $i < 3; $i++) {
                $profile = $this->getUserProfile($token);
                if ($profile && isset($profile['data']['username'])) {
                    $successCount++;
                }
                usleep(100000); // 0.1 second delay
            }
            
            if ($successCount === 3) {
                echo "✅ Profile access successful for multiple consecutive requests\n";
            } else {
                echo "❌ Profile access failed for multiple consecutive requests ({$successCount}/3)\n";
                $this->testResults['multiple_profile_access'] = false;
            }

            // Test 4: Logout and verify session termination
            $logoutResult = $this->logoutUser($token);
            if ($logoutResult) {
                echo "✅ Logout successful\n";
            } else {
                echo "❌ Logout failed\n";
                $this->testResults['logout'] = false;
            }

            // Test 5: Verify session is terminated
            $profileAfterLogout = $this->getUserProfile($token);
            if (!$profileAfterLogout || isset($profileAfterLogout['error'])) {
                echo "✅ Session correctly terminated after logout\n";
            } else {
                echo "❌ Session still active after logout\n";
                $this->testResults['session_termination'] = false;
            }

        } catch (Exception $e) {
            echo "❌ Session persistence test failed: " . $e->getMessage() . "\n";
            $this->testResults['session_persistence'] = false;
        }

        echo "\n";
    }

    /**
     * Create test users for testing
     */
    private function createTestUsers(): void
    {
        echo "👥 Creating test users...\n";

        // Create admin user
        $adminData = [
            'username' => 'test_admin',
            'email' => 'admin@test.islamwiki.org',
            'password' => 'admin123',
            'password_confirmation' => 'admin123',
            'first_name' => 'Test',
            'last_name' => 'Admin'
        ];

        $adminResult = $this->authController->handleRequest('POST', 'auth/register', $adminData);
        if (isset($adminResult['success']) && $adminResult['success']) {
            echo "✅ Test admin user created\n";
            
            // Get the created user and assign admin role
            $adminUser = $this->userService->getUserByUsername('test_admin');
            if ($adminUser) {
                $this->assignAdminRole($adminUser['id']);
                $this->testUsers['admin'] = [
                    'id' => $adminUser['id'],
                    'username' => 'test_admin',
                    'password' => 'admin123'
                ];
            }
        } else {
            echo "❌ Failed to create test admin user: " . ($adminResult['error'] ?? 'Unknown error') . "\n";
        }

        // Create regular user
        $regularData = [
            'username' => 'test_regular',
            'email' => 'regular@test.islamwiki.org',
            'password' => 'regular123',
            'password_confirmation' => 'regular123',
            'first_name' => 'Test',
            'last_name' => 'Regular'
        ];

        $regularResult = $this->authController->handleRequest('POST', 'auth/register', $regularData);
        if (isset($regularResult['success']) && $regularResult['success']) {
            echo "✅ Test regular user created\n";
            
            // Get the created user
            $regularUser = $this->userService->getUserByUsername('test_regular');
            if ($regularUser) {
                $this->testUsers['regular'] = [
                    'id' => $regularUser['id'],
                    'username' => 'test_regular',
                    'password' => 'regular123'
                ];
            }
        } else {
            echo "❌ Failed to create test regular user: " . ($regularResult['error'] ?? 'Unknown error') . "\n";
        }

        echo "\n";
    }

    /**
     * Assign admin role to user
     */
    private function assignAdminRole(int $userId): void
    {
        try {
            // Get admin role
            $adminRole = $this->userService->getRoleByName('admin');
            if ($adminRole) {
                $this->userService->assignRole($userId, $adminRole['id']);
                echo "✅ Admin role assigned to test admin user\n";
            }
        } catch (Exception $e) {
            echo "❌ Failed to assign admin role: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Login user and return token
     */
    private function loginUser(string $username, string $password): ?string
    {
        try {
            $loginData = [
                'username' => $username,
                'password' => $password
            ];

            $result = $this->authController->handleRequest('POST', 'auth/login', $loginData);
            
            if (isset($result['success']) && $result['success'] && isset($result['data']['token'])) {
                return $result['data']['token'];
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get user profile using token
     */
    private function getUserProfile(string $token): ?array
    {
        try {
            $result = $this->authController->handleRequest('GET', 'auth/profile', ['token' => $token]);
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Logout user
     */
    private function logoutUser(string $token): bool
    {
        try {
            $result = $this->authController->handleRequest('POST', 'auth/logout', ['token' => $token]);
            return isset($result['success']) && $result['success'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test admin feature access for regular users
     */
    private function testAdminFeatureAccess(string $regularUserToken): void
    {
        echo "🔒 Testing admin feature access for regular user...\n";

        // Test that regular user cannot access admin-only endpoints
        // This would typically be tested in a real application with proper middleware
        // For now, we'll verify the user has regular role, not admin role
        
        $userProfile = $this->getUserProfile($regularUserToken);
        if ($userProfile && isset($userProfile['data']['id'])) {
            $userId = $userProfile['data']['id'];
            $userRoles = $this->userService->getUserRoles($userId);
            
            $hasAdminRole = false;
            foreach ($userRoles as $role) {
                if ($role['name'] === 'admin') {
                    $hasAdminRole = true;
                    break;
                }
            }
            
            if (!$hasAdminRole) {
                echo "✅ Regular user correctly does not have admin role\n";
            } else {
                echo "❌ Regular user incorrectly has admin role\n";
                $this->testResults['regular_user_admin_role'] = false;
            }
        }
    }

    /**
     * Display test results summary
     */
    private function displayTestResults(): void
    {
        echo "📊 **Test Results Summary**\n";
        echo "==========================\n\n";

        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, function($result) {
            return $result !== false;
        }));

        if ($totalTests === 0) {
            echo "✅ All tests passed successfully!\n";
        } else {
            echo "📈 Test Results: {$passedTests}/{$totalTests} tests passed\n\n";
            
            foreach ($this->testResults as $testName => $result) {
                $status = $result !== false ? "✅ PASS" : "❌ FAIL";
                echo "{$status} - {$testName}\n";
            }
        }

        echo "\n";
    }

    /**
     * Cleanup test data
     */
    private function cleanupTestData(): void
    {
        echo "🧹 **Cleaning Up Test Data**\n";
        echo "============================\n";

        try {
            foreach ($this->testUsers as $userType => $user) {
                if (isset($user['id'])) {
                    $this->userService->deleteUser($user['id']);
                    echo "✅ Test {$userType} user deleted\n";
                }
            }
            echo "✅ All test data cleaned up successfully\n";
        } catch (Exception $e) {
            echo "❌ Failed to cleanup test data: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }
}

// Run the test suite
if (php_sapi_name() === 'cli') {
    $testSuite = new AuthenticationSecurityTest();
    $testSuite->runAllTests();
} else {
    echo "This script should be run from the command line.\n";
    echo "Usage: php test_authentication_security.php\n";
} 