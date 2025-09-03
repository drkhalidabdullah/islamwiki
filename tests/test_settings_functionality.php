<?php
/**
 * Test Settings Functionality
 * 
 * This script tests the settings API endpoints to ensure they work correctly
 */

// Simple test script - no external dependencies needed

class SettingsTest {
    private $dbConnection;
    private $pdo;
    
    public function __construct() {
        // No database connection needed for API testing
    }
    
    /**
     * Generate a mock JWT token for testing
     */
    private function generateMockJWT($userId, $username, $role) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'sub' => $userId,
            'username' => $username,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + 3600
        ]);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        // Mock signature
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, 'test_secret');
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Test getting user settings
     */
    public function testGetUserSettings() {
        echo "🧪 Testing Get User Settings...\n";
        
        // Use a test user ID for testing
        $userId = 4;
        $username = 'testuser';
        $token = $this->generateMockJWT($userId, $username, 'user');
        
        // Simulate API call
        $url = 'http://localhost:8080/api/user/settings';
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data['success'] && isset($data['data'])) {
                echo "✅ Get user settings successful\n";
                echo "📊 Settings sections: " . implode(', ', array_keys($data['data'])) . "\n";
                return true;
            } else {
                echo "❌ Get user settings failed: " . ($data['error'] ?? 'Unknown error') . "\n";
                return false;
            }
        } else {
            echo "❌ Get user settings HTTP error: $httpCode\n";
            echo "Response: $response\n";
            return false;
        }
    }
    
    /**
     * Test updating user settings
     */
    public function testUpdateUserSettings() {
        echo "\n🧪 Testing Update User Settings...\n";
        
        // Use a test user ID for testing
        $userId = 4;
        $username = 'testuser';
        $token = $this->generateMockJWT($userId, $username, 'user');
        
        // Test updating account settings
        $updateData = [
            'section' => 'account',
            'data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'bio' => 'This is a test bio',
                'username' => 'testuser', // Keep existing username
                'email' => 'test@islamwiki.org' // Keep existing email
            ]
        ];
        
        $url = 'http://localhost:8080/api/user/settings';
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data['success']) {
                echo "✅ Update user settings successful\n";
                return true;
            } else {
                echo "❌ Update user settings failed: " . ($data['error'] ?? 'Unknown error') . "\n";
                return false;
            }
        } else {
            echo "❌ Update user settings HTTP error: $httpCode\n";
            echo "Response: $response\n";
            return false;
        }
    }
    
    /**
     * Test resetting user settings
     */
    public function testResetUserSettings() {
        echo "\n🧪 Testing Reset User Settings...\n";
        
        // Use a test user ID for testing
        $userId = 4;
        $username = 'testuser';
        $token = $this->generateMockJWT($userId, $username, 'user');
        
        $url = 'http://localhost:8080/api/user/settings/reset';
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data['success']) {
                echo "✅ Reset user settings successful\n";
                return true;
            } else {
                echo "❌ Reset user settings failed: " . ($data['error'] ?? 'Unknown error') . "\n";
                return false;
            }
        } else {
            echo "❌ Reset user settings HTTP error: $httpCode\n";
            echo "Response: $response\n";
            return false;
        }
    }
    
    /**
     * Test exporting user data
     */
    public function testExportUserData() {
        echo "\n🧪 Testing Export User Data...\n";
        
        // Use a test user ID for testing
        $userId = 4;
        $username = 'testuser';
        $token = $this->generateMockJWT($userId, $username, 'user');
        
        $url = 'http://localhost:8080/api/user/data/export';
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            echo "Debug: Response length: " . strlen($response) . " characters\n";
            echo "Debug: JSON decode result: " . ($data ? 'success' : 'failed') . "\n";
            if ($data) {
                echo "Debug: Available keys: " . implode(', ', array_keys($data)) . "\n";
                echo "Debug: Username value: " . ($data['username'] ?? 'NOT_SET') . "\n";
            }
            
            if ($data && isset($data['username']) && $data['username'] === 'testuser') {
                echo "✅ Export user data successful\n";
                echo "📊 Exported data for user: " . $data['username'] . "\n";
                return true;
            } else {
                echo "❌ Export user data failed: Invalid response format\n";
                echo "Response: " . substr($response, 0, 200) . "...\n";
                return false;
            }
        } else {
            echo "❌ Export user data HTTP error: $httpCode\n";
            echo "Response: " . substr($response, 0, 200) . "...\n";
            return false;
        }
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "🚀 Starting Settings Functionality Tests\n";
        echo "=====================================\n";
        
        $results = [];
        $results[] = $this->testGetUserSettings();
        $results[] = $this->testUpdateUserSettings();
        $results[] = $this->testResetUserSettings();
        $results[] = $this->testExportUserData();
        
        echo "\n📊 Test Results Summary\n";
        echo "======================\n";
        $passed = count(array_filter($results));
        $total = count($results);
        
        echo "✅ Passed: $passed/$total\n";
        echo "❌ Failed: " . ($total - $passed) . "/$total\n";
        
        if ($passed === $total) {
            echo "\n🎉 All settings tests passed! The settings functionality is working correctly.\n";
        } else {
            echo "\n⚠️  Some tests failed. Please check the implementation.\n";
        }
        
        return $passed === $total;
    }
}

// Run tests if script is executed directly
if (php_sapi_name() === 'cli') {
    $test = new SettingsTest();
    $test->runAllTests();
}
?> 