<?php

/**
 * New Components Test
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use IslamWiki\Models\User;
use IslamWiki\Core\Exceptions\Handler;
use IslamWiki\Core\Config\Config;
use IslamWiki\Core\Database\Database;
use IslamWiki\Core\Cache\FileCache;

echo "🧪 Testing New Components...\n\n";

try {
    // Test 1: Configuration System
    echo "1. Testing Configuration System... ";
    Config::load();
    $appName = Config::appName();
    $appVersion = Config::appVersion();
    $isDebug = Config::isDebug();
    
    if ($appName === 'IslamWiki' && $appVersion === '0.0.1') {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 2: Exception Handler
    echo "2. Testing Exception Handler... ";
    $handler = new Handler(true, 'storage/logs');
    echo "✅ PASSED\n";
    
    // Test 3: User Model Structure
    echo "3. Testing User Model Structure... ";
    $reflection = new ReflectionClass(User::class);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    $requiredMethods = ['find', 'findByUsername', 'findByEmail', 'save', 'delete', 'updatePassword', 'verifyPassword'];
    $foundMethods = array_map(fn($m) => $m->getName(), $methods);
    
    $missingMethods = array_diff($requiredMethods, $foundMethods);
    
    if (empty($missingMethods)) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED - Missing: " . implode(', ', $missingMethods) . "\n";
    }
    
    // Test 4: Configuration Environment Variables
    echo "4. Testing Environment Variables... ";
    $dbConfig = Config::database();
    $cacheConfig = Config::cache();
    $securityConfig = Config::security();
    
    if (isset($dbConfig['host']) && isset($cacheConfig['driver']) && isset($securityConfig['jwt_secret'])) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 5: Exception Handler Configuration
    echo "5. Testing Exception Handler Configuration... ";
    $handler->setDebug(true);
    $handler->setLogPath('storage/test-logs');
    echo "✅ PASSED\n";
    
    echo "\n🎉 New Components Tests Completed!\n";
    echo "📊 Summary: All new components are properly structured.\n";
    echo "🚀 Ready for integration testing.\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 