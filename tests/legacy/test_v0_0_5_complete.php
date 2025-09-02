<?php

/**
 * Comprehensive Test Suite for IslamWiki v0.0.5
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

require_once 'src/Core/Database/DatabaseManager.php';
require_once 'src/Services/User/UserService.php';
require_once 'src/Controllers/AuthController.php';

class TestSuiteV005
{
    private $database;
    private $userService;
    private $authController;
    private $testResults = [];
    private $testCount = 0;
    private $passedCount = 0;

    public function __construct()
    {
        $this->loadEnvironment();
        $this->initializeServices();
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

    private function initializeServices()
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
            $this->userService = new IslamWiki\Services\User\UserService($this->database);
            $this->authController = new IslamWiki\Controllers\AuthController($this->database);
            
            echo "✅ Services initialized successfully\n";
        } catch (Exception $e) {
            die("❌ Service initialization failed: " . $e->getMessage() . "\n");
        }
    }

    private function runTest($testName, callable $testFunction)
    {
        $this->testCount++;
        echo "\n🧪 Running test {$this->testCount}: {$testName}\n";
        echo str_repeat('-', 50) . "\n";
        
        try {
            $result = $testFunction();
            if ($result) {
                echo "✅ PASS: {$testName}\n";
                $this->passedCount++;
                $this->testResults[] = ['test' => $testName, 'status' => 'PASS', 'message' => 'Test passed successfully'];
            } else {
                echo "❌ FAIL: {$testName}\n";
                $this->testResults[] = ['test' => $testName, 'status' => 'FAIL', 'message' => 'Test failed'];
            }
        } catch (Exception $e) {
            echo "❌ ERROR: {$testName} - " . $e->getMessage() . "\n";
            $this->testResults[] = ['test' => $testName, 'status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    public function testDatabaseConnection()
    {
        try {
            $result = $this->database->execute("SELECT 1 as test")->fetch();
            return $result['test'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }

    public function testUserServiceMethods()
    {
        try {
            // Test createUser
            $userData = [
                'username' => 'testuser_' . time(),
                'email' => 'testuser_' . time() . '@islamwiki.org',
                'password' => 'TestPassword123!',
                'first_name' => 'Test',
                'last_name' => 'User',
                'display_name' => 'Test User',
                'bio' => 'Test user for v0.0.5 testing'
            ];
            
            $result = $this->userService->createUser($userData);
            if (!$result['success']) {
                throw new Exception("User creation failed: " . ($result['error'] ?? 'Unknown error'));
            }
            
            $userId = $result['user_id'];
            
            // Test getUser
            $user = $this->userService->getUser($userId);
            if (!$user || $user['username'] !== $userData['username']) {
                throw new Exception("getUser failed to retrieve correct user");
            }
            
            // Test getUserByUsername
            $userByUsername = $this->userService->getUserByUsername($userData['username']);
            if (!$userByUsername || $userByUsername['id'] != $userId) {
                throw new Exception("getUserByUsername failed");
            }
            
            // Test getUserByEmail
            $userByEmail = $this->userService->getUserByEmail($userData['email']);
            if (!$userByEmail || $userByEmail['id'] != $userId) {
                throw new Exception("getUserByEmail failed");
            }
            
            // Test updateEmailVerificationToken
            $token = 'test_token_' . time();
            if (!$this->userService->updateEmailVerificationToken($userId, $token)) {
                throw new Exception("updateEmailVerificationToken failed");
            }
            
            // Test getUserByVerificationToken
            $userByToken = $this->userService->getUserByVerificationToken($token);
            if (!$userByToken || $userByToken['id'] != $userId) {
                throw new Exception("getUserByVerificationToken failed");
            }
            
            // Test verifyEmail
            if (!$this->userService->verifyEmail($userId)) {
                throw new Exception("verifyEmail failed");
            }
            
            // Test updatePasswordResetToken
            $resetToken = 'reset_token_' . time();
            if (!$this->userService->updatePasswordResetToken($userId, $resetToken)) {
                throw new Exception("updatePasswordResetToken failed");
            }
            
            // Test getUserByResetToken
            $userByResetToken = $this->userService->getUserByResetToken($resetToken);
            if (!$userByResetToken || $userByResetToken['id'] != $userId) {
                throw new Exception("getUserByResetToken failed");
            }
            
            // Test updatePassword
            $newPasswordHash = password_hash('NewPassword123!', PASSWORD_DEFAULT);
            if (!$this->userService->updatePassword($userId, $newPasswordHash)) {
                throw new Exception("updatePassword failed");
            }
            
            // Test getUsers
            $users = $this->userService->getUsers(['search' => 'testuser'], 1, 10);
            if (!isset($users['users']) || !isset($users['pagination'])) {
                throw new Exception("getUsers failed");
            }
            
            // Test getUserStatistics
            $stats = $this->userService->getUserStatistics();
            if (!isset($stats['total_users'])) {
                throw new Exception("getUserStatistics failed");
            }
            
            // Test getUsersByStatus
            $pendingUsers = $this->userService->getUsersByStatus('pending_verification');
            if (!isset($pendingUsers['users'])) {
                throw new Exception("getUsersByStatus failed");
            }
            
            // Test getPendingVerificationCount
            $pendingCount = $this->userService->getPendingVerificationCount();
            if (!is_numeric($pendingCount)) {
                throw new Exception("getPendingVerificationCount failed");
            }
            
            // Test getUsersRequiringPasswordReset
            $resetUsers = $this->userService->getUsersRequiringPasswordReset();
            if (!is_array($resetUsers)) {
                throw new Exception("getUsersRequiringPasswordReset failed");
            }
            
            // Clean up test user
            $this->userService->deleteUser($userId);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testAuthControllerEndpoints()
    {
        try {
            // Test registration endpoint
            $registerData = [
                'username' => 'auth_test_' . time(),
                'email' => 'auth_test_' . time() . '@islamwiki.org',
                'password' => 'TestPassword123!',
                'password_confirmation' => 'TestPassword123!',
                'first_name' => 'Auth',
                'last_name' => 'Test',
                'display_name' => 'Auth Test User'
            ];
            
            $result = $this->authController->handleRequest('POST', 'auth/register', $registerData);
            if (!$result['success']) {
                throw new Exception("Registration failed: " . ($result['error'] ?? 'Unknown error'));
            }
            
            $userId = $result['data']['user_id'];
            
            // Test login endpoint (should fail without email verification)
            $loginData = [
                'username' => $registerData['username'],
                'password' => $registerData['password']
            ];
            
            $loginResult = $this->authController->handleRequest('POST', 'auth/login', $loginData);
            if ($loginResult['success']) {
                throw new Exception("Login should fail without email verification");
            }
            
            // Test email verification endpoint
            $user = $this->userService->getUserByUsername($registerData['username']);
            if (!$user || !$user['email_verification_token']) {
                throw new Exception("User or verification token not found");
            }
            
            $verifyData = ['token' => $user['email_verification_token']];
            $verifyResult = $this->authController->handleRequest('POST', 'auth/verify-email', $verifyData);
            if (!$verifyResult['success']) {
                throw new Exception("Email verification failed: " . ($verifyResult['error'] ?? 'Unknown error'));
            }
            
            // Test login endpoint (should succeed after verification)
            $loginResult = $this->authController->handleRequest('POST', 'auth/login', $loginData);
            if (!$loginResult['success']) {
                throw new Exception("Login failed after verification: " . ($loginResult['error'] ?? 'Unknown error'));
            }
            
            // Test forgot password endpoint
            $forgotData = ['email' => $registerData['email']];
            $forgotResult = $this->authController->handleRequest('POST', 'auth/forgot-password', $forgotData);
            if (!$forgotResult['success']) {
                throw new Exception("Forgot password failed: " . ($forgotResult['error'] ?? 'Unknown error'));
            }
            
            // Test password reset endpoint
            $user = $this->userService->getUserByUsername($registerData['username']);
            if (!$user || !$user['password_reset_token']) {
                throw new Exception("User or reset token not found");
            }
            
            $resetData = [
                'token' => $user['password_reset_token'],
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!'
            ];
            
            $resetResult = $this->authController->handleRequest('POST', 'auth/reset-password', $resetData);
            if (!$resetResult['success']) {
                throw new Exception("Password reset failed: " . ($resetResult['error'] ?? 'Unknown error'));
            }
            
            // Test login with new password
            $newLoginData = [
                'username' => $registerData['username'],
                'password' => 'NewPassword123!'
            ];
            
            $newLoginResult = $this->authController->handleRequest('POST', 'auth/login', $newLoginData);
            if (!$newLoginResult['success']) {
                throw new Exception("Login with new password failed: " . ($newLoginResult['error'] ?? 'Unknown error'));
            }
            
            // Clean up test user
            $this->userService->deleteUser($userId);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testSecurityFeatures()
    {
        try {
            // Test password strength validation
            $weakPasswordData = [
                'username' => 'security_test_' . time(),
                'email' => 'security_test_' . time() . '@islamwiki.org',
                'password' => 'weak',
                'password_confirmation' => 'weak',
                'first_name' => 'Security',
                'last_name' => 'Test',
                'display_name' => 'Security Test User'
            ];
            
            $result = $this->authController->handleRequest('POST', 'auth/register', $weakPasswordData);
            if ($result['success']) {
                throw new Exception("Registration should fail with weak password");
            }
            
            // Test password confirmation mismatch
            $mismatchData = [
                'username' => 'security_test_' . time(),
                'email' => 'security_test_' . time() . '@islamwiki.org',
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
                'first_name' => 'Security',
                'last_name' => 'Test',
                'display_name' => 'Security Test User'
            ];
            
            $result = $this->authController->handleRequest('POST', 'auth/register', $mismatchData);
            if ($result['success']) {
                throw new Exception("Registration should fail with password mismatch");
            }
            
            // Test duplicate username
            $duplicateData = [
                'username' => 'duplicate_user',
                'email' => 'duplicate_' . time() . '@islamwiki.org',
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'StrongPassword123!',
                'first_name' => 'Duplicate',
                'last_name' => 'User',
                'display_name' => 'Duplicate User'
            ];
            
            // Create first user
            $result1 = $this->authController->handleRequest('POST', 'auth/register', $duplicateData);
            if (!$result1['success']) {
                throw new Exception("First user creation failed");
            }
            
            $userId1 = $result1['data']['user_id'];
            
            // Try to create second user with same username
            $duplicateData['email'] = 'duplicate2_' . time() . '@islamwiki.org';
            $result2 = $this->authController->handleRequest('POST', 'auth/register', $duplicateData);
            if ($result2['success']) {
                throw new Exception("Registration should fail with duplicate username");
            }
            
            // Clean up test user
            $this->userService->deleteUser($userId1);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testUserManagementFeatures()
    {
        try {
            // Create test user
            $userData = [
                'username' => 'management_test_' . time(),
                'email' => 'management_test_' . time() . '@islamwiki.org',
                'password' => 'TestPassword123!',
                'first_name' => 'Management',
                'last_name' => 'Test',
                'display_name' => 'Management Test User',
                'bio' => 'Test user for management testing'
            ];
            
            $result = $this->userService->createUser($userData);
            if (!$result['success']) {
                throw new Exception("User creation failed");
            }
            
            $userId = $result['user_id'];
            
            // Test role assignment
            $roleResult = $this->userService->assignRole($userId, 'verified_user');
            if (!$roleResult['success']) {
                throw new Exception("Role assignment failed");
            }
            
            // Test role checking
            if (!$this->userService->userHasRole($userId, 'verified_user')) {
                throw new Exception("Role checking failed");
            }
            
            // Test user update
            $updateData = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'bio' => 'Updated bio'
            ];
            
            $updateResult = $this->userService->updateUser($userId, $updateData);
            if (!$updateResult['success']) {
                throw new Exception("User update failed");
            }
            
            // Verify update
            $updatedUser = $this->userService->getUser($userId);
            if ($updatedUser['first_name'] !== 'Updated' || $updatedUser['last_name'] !== 'Name') {
                throw new Exception("User update verification failed");
            }
            
            // Test user statistics
            $stats = $this->userService->getUserStatistics();
            if (!isset($stats['total_users']) || $stats['total_users'] < 1) {
                throw new Exception("User statistics failed");
            }
            
            // Test role distribution
            $roleDistribution = $this->userService->getRoleDistribution();
            if (!is_array($roleDistribution)) {
                throw new Exception("Role distribution failed");
            }
            
            // Clean up test user
            $this->userService->deleteUser($userId);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function runAllTests()
    {
        echo "🚀 Starting IslamWiki v0.0.5 Comprehensive Test Suite\n";
        echo "====================================================\n";
        echo "This test suite will verify all v0.0.5 functionality:\n";
        echo "• Database connectivity and migrations\n";
        echo "• User service methods and functionality\n";
        echo "• Authentication controller endpoints\n";
        echo "• Security features and validation\n";
        echo "• User management and role system\n\n";
        
        // Run all tests
        $this->runTest("Database Connection", [$this, 'testDatabaseConnection']);
        $this->runTest("User Service Methods", [$this, 'testUserServiceMethods']);
        $this->runTest("Auth Controller Endpoints", [$this, 'testAuthControllerEndpoints']);
        $this->runTest("Security Features", [$this, 'testSecurityFeatures']);
        $this->runTest("User Management Features", [$this, 'testUserManagementFeatures']);
        
        // Display results
        $this->displayResults();
    }

    private function displayResults()
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "🏁 TEST SUITE RESULTS\n";
        echo str_repeat('=', 60) . "\n";
        echo "Total Tests: {$this->testCount}\n";
        echo "Passed: {$this->passedCount}\n";
        echo "Failed: " . ($this->testCount - $this->passedCount) . "\n";
        echo "Success Rate: " . round(($this->passedCount / $this->testCount) * 100, 2) . "%\n\n";
        
        if ($this->passedCount === $this->testCount) {
            echo "🎉 ALL TESTS PASSED! v0.0.5 is ready for production use.\n";
        } else {
            echo "⚠️  Some tests failed. Please review the results above.\n";
        }
        
        echo "\nDetailed Results:\n";
        foreach ($this->testResults as $result) {
            $status = $result['status'] === 'PASS' ? '✅' : '❌';
            echo "{$status} {$result['test']}: {$result['status']}\n";
            if ($result['status'] !== 'PASS') {
                echo "   Message: {$result['message']}\n";
            }
        }
        
        echo "\n" . str_repeat('=', 60) . "\n";
    }
}

// Run the test suite if called directly
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    $testSuite = new TestSuiteV005();
    $testSuite->runAllTests();
} else {
    echo "This test suite can be run from command line or with ?run=1 parameter\n";
} 