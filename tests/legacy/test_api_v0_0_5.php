<?php

/**
 * API Test Suite for IslamWiki v0.0.5
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

require_once 'src/Core/Database/DatabaseManager.php';
require_once 'src/Services/User/UserService.php';
require_once 'src/Controllers/AuthController.php';

class APITestSuiteV005
{
    private $database;
    private $userService;
    private $authController;
    private $testResults = [];
    private $testCount = 0;
    private $passedCount = 0;
    private $testUsers = [];

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
            
            echo "✅ API Test Services initialized successfully\n";
        } catch (Exception $e) {
            die("❌ API Test Service initialization failed: " . $e->getMessage() . "\n");
        }
    }

    private function runTest($testName, callable $testFunction)
    {
        $this->testCount++;
        echo "\n🧪 Running API test {$this->testCount}: {$testName}\n";
        echo str_repeat('-', 60) . "\n";
        
        try {
            $result = $testFunction();
            if ($result) {
                echo "✅ PASS: {$testName}\n";
                $this->passedCount++;
                $this->testResults[] = ['test' => $testName, 'status' => 'PASS', 'message' => 'API test passed successfully'];
            } else {
                echo "❌ FAIL: {$testName}\n";
                $this->testResults[] = ['test' => $testName, 'status' => 'FAIL', 'message' => 'API test failed'];
            }
        } catch (Exception $e) {
            echo "❌ ERROR: {$testName} - " . $e->getMessage() . "\n";
            $this->testResults[] = ['test' => $testName, 'status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    private function createTestUser($suffix = '')
    {
        $timestamp = time() . $suffix;
        $userData = [
            'username' => "api_test_{$timestamp}",
            'email' => "api_test_{$timestamp}@islamwiki.org",
            'password' => 'TestPassword123!',
            'first_name' => 'API',
            'last_name' => 'Test',
            'display_name' => "API Test User {$timestamp}",
            'bio' => 'Test user for API testing'
        ];
        
        $result = $this->userService->createUser($userData);
        if (!$result['success']) {
            throw new Exception("Failed to create test user: " . ($result['error'] ?? 'Unknown error'));
        }
        
        $userId = $result['user_id'];
        $this->testUsers[] = $userId;
        
        return [
            'id' => $userId,
            'data' => $userData,
            'result' => $result
        ];
    }

    private function cleanupTestUsers()
    {
        foreach ($this->testUsers as $userId) {
            try {
                $this->userService->deleteUser($userId);
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
        $this->testUsers = [];
    }

    public function testUserRegistrationAPI()
    {
        try {
            echo "📝 Testing User Registration API...\n";
            
            // Test successful registration
            $userData = [
                'username' => 'reg_test_' . time(),
                'email' => 'reg_test_' . time() . '@islamwiki.org',
                'password' => 'TestPassword123!',
                'password_confirmation' => 'TestPassword123!',
                'first_name' => 'Registration',
                'last_name' => 'Test',
                'display_name' => 'Registration Test User'
            ];
            
            $result = $this->authController->handleRequest('POST', 'auth/register', $userData);
            
            if (!$result['success']) {
                throw new Exception("Registration failed: " . ($result['error'] ?? 'Unknown error'));
            }
            
            if (!isset($result['data']['user_id'])) {
                throw new Exception("User ID not returned in registration response");
            }
            
            $userId = $result['data']['user_id'];
            $this->testUsers[] = $userId;
            
            echo "  ✅ User registration successful (ID: {$userId})\n";
            
            // Test duplicate username
            $duplicateData = $userData;
            $duplicateData['email'] = 'duplicate_' . time() . '@islamwiki.org';
            
            $duplicateResult = $this->authController->handleRequest('POST', 'auth/register', $duplicateData);
            if ($duplicateResult['success']) {
                throw new Exception("Duplicate username registration should fail");
            }
            
            echo "  ✅ Duplicate username validation working\n";
            
            // Test duplicate email
            $duplicateEmailData = $userData;
            $duplicateEmailData['username'] = 'duplicate_' . time();
            
            $duplicateEmailResult = $this->authController->handleRequest('POST', 'auth/register', $duplicateEmailData);
            if ($duplicateEmailResult['success']) {
                throw new Exception("Duplicate email registration should fail");
            }
            
            echo "  ✅ Duplicate email validation working\n";
            
            // Test weak password
            $weakPasswordData = $userData;
            $weakPasswordData['username'] = 'weak_pass_' . time();
            $weakPasswordData['email'] = 'weak_pass_' . time() . '@islamwiki.org';
            $weakPasswordData['password'] = 'weak';
            $weakPasswordData['password_confirmation'] = 'weak';
            
            $weakPasswordResult = $this->authController->handleRequest('POST', 'auth/register', $weakPasswordData);
            if ($weakPasswordResult['success']) {
                throw new Exception("Weak password registration should fail");
            }
            
            echo "  ✅ Password strength validation working\n";
            
            // Test password confirmation mismatch
            $mismatchData = $userData;
            $mismatchData['username'] = 'mismatch_' . time();
            $mismatchData['email'] = 'mismatch_' . time() . '@islamwiki.org';
            $mismatchData['password_confirmation'] = 'DifferentPassword123!';
            
            $mismatchResult = $this->authController->handleRequest('POST', 'auth/register', $mismatchData);
            if ($mismatchResult['success']) {
                throw new Exception("Password mismatch registration should fail");
            }
            
            echo "  ✅ Password confirmation validation working\n";
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testEmailVerificationAPI()
    {
        try {
            echo "📧 Testing Email Verification API...\n";
            
            // Create test user
            $testUser = $this->createTestUser('_verify');
            $userId = $testUser['id'];
            
            // Get verification token
            $user = $this->userService->getUser($userId);
            if (!$user || !$user['email_verification_token']) {
                throw new Exception("Verification token not found");
            }
            
            $token = $user['email_verification_token'];
            
            // Test email verification
            $verifyData = ['token' => $token];
            $verifyResult = $this->authController->handleRequest('POST', 'auth/verify-email', $verifyData);
            
            if (!$verifyResult['success']) {
                throw new Exception("Email verification failed: " . ($verifyResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Email verification successful\n";
            
            // Verify user status changed
            $verifiedUser = $this->userService->getUser($userId);
            if ($verifiedUser['status'] !== 'active') {
                throw new Exception("User status not updated to active after verification");
            }
            
            echo "  ✅ User status updated to active\n";
            
            // Test invalid token
            $invalidTokenData = ['token' => 'invalid_token_' . time()];
            $invalidTokenResult = $this->authController->handleRequest('POST', 'auth/verify-email', $invalidTokenData);
            
            if ($invalidTokenResult['success']) {
                throw new Exception("Invalid token verification should fail");
            }
            
            echo "  ✅ Invalid token validation working\n";
            
            // Test resend verification
            $resendData = ['email' => $testUser['data']['email']];
            $resendResult = $this->authController->handleRequest('POST', 'auth/resend-verification', $resendData);
            
            if (!$resendResult['success']) {
                throw new Exception("Resend verification failed: " . ($resendResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Resend verification working\n";
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testUserLoginAPI()
    {
        try {
            echo "🔐 Testing User Login API...\n";
            
            // Create and verify test user
            $testUser = $this->createTestUser('_login');
            $userId = $testUser['id'];
            
            // Verify email first
            $user = $this->userService->getUser($userId);
            $this->userService->verifyEmail($userId);
            
            // Test successful login
            $loginData = [
                'username' => $testUser['data']['username'],
                'password' => $testUser['data']['password']
            ];
            
            $loginResult = $this->authController->handleRequest('POST', 'auth/login', $loginData);
            
            if (!$loginResult['success']) {
                throw new Exception("Login failed: " . ($loginResult['error'] ?? 'Unknown error'));
            }
            
            if (!isset($loginResult['data']['token'])) {
                throw new Exception("Login token not returned");
            }
            
            echo "  ✅ User login successful\n";
            
            // Test login with email instead of username
            $emailLoginData = [
                'username' => $testUser['data']['email'],
                'password' => $testUser['data']['password']
            ];
            
            $emailLoginResult = $this->authController->handleRequest('POST', 'auth/login', $emailLoginData);
            
            if (!$emailLoginResult['success']) {
                throw new Exception("Email login failed: " . ($emailLoginResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Email login working\n";
            
            // Test invalid password
            $invalidPasswordData = [
                'username' => $testUser['data']['username'],
                'password' => 'WrongPassword123!'
            ];
            
            $invalidPasswordResult = $this->authController->handleRequest('POST', 'auth/login', $invalidPasswordData);
            
            if ($invalidPasswordResult['success']) {
                throw new Exception("Invalid password login should fail");
            }
            
            echo "  ✅ Invalid password validation working\n";
            
            // Test non-existent user
            $nonExistentData = [
                'username' => 'nonexistent_' . time(),
                'password' => 'TestPassword123!'
            ];
            
            $nonExistentResult = $this->authController->handleRequest('POST', 'auth/login', $nonExistentData);
            
            if ($nonExistentResult['success']) {
                throw new Exception("Non-existent user login should fail");
            }
            
            echo "  ✅ Non-existent user validation working\n";
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testPasswordResetAPI()
    {
        try {
            echo "🔑 Testing Password Reset API...\n";
            
            // Create test user
            $testUser = $this->createTestUser('_reset');
            $userId = $testUser['id'];
            
            // Test forgot password
            $forgotData = ['email' => $testUser['data']['email']];
            $forgotResult = $this->authController->handleRequest('POST', 'auth/forgot-password', $forgotData);
            
            if (!$forgotResult['success']) {
                throw new Exception("Forgot password failed: " . ($forgotResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Forgot password request successful\n";
            
            // Get reset token
            $user = $this->userService->getUser($userId);
            if (!$user || !$user['password_reset_token']) {
                throw new Exception("Password reset token not found");
            }
            
            $token = $user['password_reset_token'];
            
            // Test password reset
            $resetData = [
                'token' => $token,
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!'
            ];
            
            $resetResult = $this->authController->handleRequest('POST', 'auth/reset-password', $resetData);
            
            if (!$resetResult['success']) {
                throw new Exception("Password reset failed: " . ($resetResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Password reset successful\n";
            
            // Test login with new password
            $newLoginData = [
                'username' => $testUser['data']['username'],
                'password' => 'NewPassword123!'
            ];
            
            $newLoginResult = $this->authController->handleRequest('POST', 'auth/login', $newLoginData);
            
            if (!$newLoginResult['success']) {
                throw new Exception("Login with new password failed: " . ($newLoginResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Login with new password successful\n";
            
            // Test invalid reset token
            $invalidTokenData = [
                'token' => 'invalid_token_' . time(),
                'password' => 'AnotherPassword123!',
                'password_confirmation' => 'AnotherPassword123!'
            ];
            
            $invalidTokenResult = $this->authController->handleRequest('POST', 'auth/reset-password', $invalidTokenData);
            
            if ($invalidTokenResult['success']) {
                throw new Exception("Invalid reset token should fail");
            }
            
            echo "  ✅ Invalid reset token validation working\n";
            
            // Test password confirmation mismatch
            $mismatchData = [
                'token' => $token,
                'password' => 'MismatchPassword123!',
                'password_confirmation' => 'DifferentPassword123!'
            ];
            
            $mismatchResult = $this->authController->handleRequest('POST', 'auth/reset-password', $mismatchData);
            
            if ($mismatchResult['success']) {
                throw new Exception("Password confirmation mismatch should fail");
            }
            
            echo "  ✅ Password confirmation validation working\n";
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testUserProfileAPI()
    {
        try {
            echo "👤 Testing User Profile API...\n";
            
            // Create and verify test user
            $testUser = $this->createTestUser('_profile');
            $userId = $testUser['id'];
            
            // Verify email first
            $this->userService->verifyEmail($userId);
            
            // Login to get token
            $loginData = [
                'username' => $testUser['data']['username'],
                'password' => $testUser['data']['password']
            ];
            
            $loginResult = $this->authController->handleRequest('POST', 'auth/login', $loginData);
            if (!$loginResult['success']) {
                throw new Exception("Login failed for profile test");
            }
            
            $token = $loginResult['data']['token'];
            
            // Test get profile
            $profileData = ['token' => $token];
            $profileResult = $this->authController->handleRequest('GET', 'auth/profile', $profileData);
            
            if (!$profileResult['success']) {
                throw new Exception("Get profile failed: " . ($profileResult['error'] ?? 'Unknown error'));
            }
            
            if (!isset($profileResult['data']['id'])) {
                throw new Exception("Profile data not returned");
            }
            
            echo "  ✅ Get profile successful\n";
            
            // Test update profile
            $updateData = [
                'token' => $token,
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'bio' => 'Updated bio for testing'
            ];
            
            $updateResult = $this->authController->handleRequest('PUT', 'auth/update-profile', $updateData);
            
            if (!$updateResult['success']) {
                throw new Exception("Update profile failed: " . ($updateResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Update profile successful\n";
            
            // Verify profile update
            $updatedProfileResult = $this->authController->handleRequest('GET', 'auth/profile', $profileData);
            if (!$updatedProfileResult['success']) {
                throw new Exception("Failed to get updated profile");
            }
            
            $updatedProfile = $updatedProfileResult['data'];
            if ($updatedProfile['first_name'] !== 'Updated' || $updatedProfile['last_name'] !== 'Name') {
                throw new Exception("Profile update verification failed");
            }
            
            echo "  ✅ Profile update verification successful\n";
            
            // Test change password
            $changePasswordData = [
                'token' => $token,
                'current_password' => $testUser['data']['password'],
                'new_password' => 'ChangedPassword123!',
                'new_password_confirmation' => 'ChangedPassword123!'
            ];
            
            $changePasswordResult = $this->authController->handleRequest('PUT', 'auth/change-password', $changePasswordData);
            
            if (!$changePasswordResult['success']) {
                throw new Exception("Change password failed: " . ($changePasswordResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Change password successful\n";
            
            // Test login with new password
            $newLoginData = [
                'username' => $testUser['data']['username'],
                'password' => 'ChangedPassword123!'
            ];
            
            $newLoginResult = $this->authController->handleRequest('POST', 'auth/login', $newLoginData);
            
            if (!$newLoginResult['success']) {
                throw new Exception("Login with changed password failed: " . ($newLoginResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Login with changed password successful\n";
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function testLogoutAPI()
    {
        try {
            echo "🚪 Testing Logout API...\n";
            
            // Create and verify test user
            $testUser = $this->createTestUser('_logout');
            $userId = $testUser['id'];
            
            // Verify email first
            $this->userService->verifyEmail($userId);
            
            // Login to get token
            $loginData = [
                'username' => $testUser['data']['username'],
                'password' => $testUser['data']['password']
            ];
            
            $loginResult = $this->authController->handleRequest('POST', 'auth/login', $loginData);
            if (!$loginResult['success']) {
                throw new Exception("Login failed for logout test");
            }
            
            $token = $loginResult['data']['token'];
            
            // Test logout
            $logoutData = ['token' => $token];
            $logoutResult = $this->authController->handleRequest('POST', 'auth/logout', $logoutData);
            
            if (!$logoutResult['success']) {
                throw new Exception("Logout failed: " . ($logoutResult['error'] ?? 'Unknown error'));
            }
            
            echo "  ✅ Logout successful\n";
            
            // Test that token is no longer valid (try to get profile)
            $profileData = ['token' => $token];
            $profileResult = $this->authController->handleRequest('GET', 'auth/profile', $profileData);
            
            if ($profileResult['success']) {
                throw new Exception("Token should be invalid after logout");
            }
            
            echo "  ✅ Token invalidation after logout working\n";
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function runAllAPITests()
    {
        echo "🚀 Starting IslamWiki v0.0.5 API Test Suite\n";
        echo "==========================================\n";
        echo "This test suite will verify all v0.0.5 API endpoints:\n";
        echo "• User Registration API\n";
        echo "• Email Verification API\n";
        echo "• User Login API\n";
        echo "• Password Reset API\n";
        echo "• User Profile API\n";
        echo "• Logout API\n\n";
        
        try {
            // Run all API tests
            $this->runTest("User Registration API", [$this, 'testUserRegistrationAPI']);
            $this->runTest("Email Verification API", [$this, 'testEmailVerificationAPI']);
            $this->runTest("User Login API", [$this, 'testUserLoginAPI']);
            $this->runTest("Password Reset API", [$this, 'testPasswordResetAPI']);
            $this->runTest("User Profile API", [$this, 'testUserProfileAPI']);
            $this->runTest("Logout API", [$this, 'testLogoutAPI']);
            
            // Display results
            $this->displayResults();
            
        } finally {
            // Clean up test users
            $this->cleanupTestUsers();
        }
    }

    private function displayResults()
    {
        echo "\n" . str_repeat('=', 70) . "\n";
        echo "🏁 API TEST SUITE RESULTS\n";
        echo str_repeat('=', 70) . "\n";
        echo "Total API Tests: {$this->testCount}\n";
        echo "Passed: {$this->passedCount}\n";
        echo "Failed: " . ($this->testCount - $this->passedCount) . "\n";
        echo "Success Rate: " . round(($this->passedCount / $this->testCount) * 100, 2) . "%\n\n";
        
        if ($this->passedCount === $this->testCount) {
            echo "🎉 ALL API TESTS PASSED! v0.0.5 API is ready for production use.\n";
        } else {
            echo "⚠️  Some API tests failed. Please review the results above.\n";
        }
        
        echo "\nDetailed API Test Results:\n";
        foreach ($this->testResults as $result) {
            $status = $result['status'] === 'PASS' ? '✅' : '❌';
            echo "{$status} {$result['test']}: {$result['status']}\n";
            if ($result['status'] !== 'PASS') {
                echo "   Message: {$result['message']}\n";
            }
        }
        
        echo "\n" . str_repeat('=', 70) . "\n";
    }
}

// Run the API test suite if called directly
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    $apiTestSuite = new APITestSuiteV005();
    $apiTestSuite->runAllAPITests();
} else {
    echo "This API test suite can be run from command line or with ?run=1 parameter\n";
} 