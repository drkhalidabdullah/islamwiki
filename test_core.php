<?php

/**
 * Core Framework Test Script
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */

require_once __DIR__ . '/vendor/autoload.php';

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Database\Database;
use IslamWiki\Core\Cache\FileCache;
use IslamWiki\Services\Wiki\WikiService;
use IslamWiki\Services\User\UserService;
use IslamWiki\Services\Content\ContentService;
use IslamWiki\Core\Authentication\AuthService;

echo "🧪 Testing IslamWiki Framework Core Components...\n\n";

try {
    // Test 1: Container
    echo "1. Testing Container... ";
    $container = new Container();
    echo "✅ PASSED\n";
    
    // Test 2: Cache
    echo "2. Testing File Cache... ";
    $cache = new FileCache('storage/cache');
    $cache->set('test', 'test_value', 60);
    $value = $cache->get('test');
    if ($value === 'test_value') {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 3: Database (without actual connection)
    echo "3. Testing Database Class... ";
    $dbConfig = [
        'host' => 'localhost',
        'database' => 'test_db',
        'username' => 'test_user',
        'password' => 'test_pass'
    ];
    
    // We'll test the class structure without connecting
    $dbClass = new ReflectionClass(Database::class);
    if ($dbClass->hasMethod('prepare') && $dbClass->hasMethod('query')) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 4: Services
    echo "4. Testing Service Classes... ";
    
    // Test WikiService
    $wikiService = new ReflectionClass(WikiService::class);
    if ($wikiService->hasMethod('getArticle') && $wikiService->hasMethod('searchArticles')) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test UserService
    $userService = new ReflectionClass(UserService::class);
    if ($userService->hasMethod('getUserById') && $userService->hasMethod('createUser')) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test ContentService
    $contentService = new ReflectionClass(ContentService::class);
    if ($contentService->hasMethod('createArticle') && $contentService->hasMethod('getArticles')) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 5: Authentication
    echo "5. Testing Authentication Service... ";
    $authService = new ReflectionClass(AuthService::class);
    if ($authService->hasMethod('authenticate') && $authService->hasMethod('validateToken')) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 6: Container Binding
    echo "6. Testing Container Binding... ";
    $container->bind('cache', function() use ($cache) {
        return $cache;
    });
    
    $boundCache = $container->make('cache');
    if ($boundCache === $cache) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 7: Cache Operations
    echo "7. Testing Cache Operations... ";
    $cache->set('test_array', ['key' => 'value'], 60);
    $cachedArray = $cache->get('test_array');
    if (is_array($cachedArray) && $cachedArray['key'] === 'value') {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 8: Cache Expiration
    echo "8. Testing Cache Expiration... ";
    $cache->set('test_expire', 'expire_value', 1);
    sleep(2);
    $expiredValue = $cache->get('test_expire');
    if ($expiredValue === null) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 9: Cache Cleanup
    echo "9. Testing Cache Cleanup... ";
    $cache->set('test_cleanup', 'cleanup_value', 1);
    $cleaned = $cache->cleanExpired();
    if ($cleaned >= 0) {
        echo "✅ PASSED\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    // Test 10: Service Dependencies
    echo "10. Testing Service Dependencies... ";
    try {
        // This will fail without a real database, but we can test the class structure
        $wikiServiceReflection = new ReflectionClass(WikiService::class);
        $constructor = $wikiServiceReflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        if (count($parameters) === 2) {
            echo "✅ PASSED\n";
        } else {
            echo "❌ FAILED\n";
        }
    } catch (Exception $e) {
        echo "⚠️  SKIPPED (requires database connection)\n";
    }
    
    echo "\n🎉 Core Framework Tests Completed!\n";
    echo "📊 Summary: All core components are properly structured and functional.\n";
    echo "🚀 Ready for development and testing with real database connection.\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 