<?php

/**
 * Unit Test Runner for IslamWiki Framework
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Running IslamWiki Framework Unit Tests...\n\n";

// Test results
$passed = 0;
$failed = 0;
$total = 0;

// Test classes to run
$testClasses = [
    'IslamWiki\Tests\Unit\Core\ContainerTest',
    'IslamWiki\Tests\Unit\Core\RouterTest',
    'IslamWiki\Tests\Unit\Core\FileCacheTest',
    'IslamWiki\Tests\Unit\Core\DatabaseTest'
];

foreach ($testClasses as $testClass) {
    echo "Testing {$testClass}...\n";
    
    try {
        $reflection = new ReflectionClass($testClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $test = new $testClass();
                
                try {
                    $test->setUp();
                    $test->{$method->getName()}();
                    $test->tearDown();
                    
                    echo "  ✅ {$method->getName()}\n";
                    $passed++;
                } catch (Exception $e) {
                    echo "  ❌ {$method->getName()}: {$e->getMessage()}\n";
                    $failed++;
                }
                
                $total++;
            }
        }
    } catch (Exception $e) {
        echo "  ❌ Class not found: {$e->getMessage()}\n";
        $failed++;
    }
    
    echo "\n";
}

// Summary
echo "📊 Test Summary:\n";
echo "Total Tests: {$total}\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";

if ($failed === 0) {
    echo "🎉 All tests passed! Framework is ready for development.\n";
} else {
    echo "⚠️  Some tests failed. Please review and fix the issues.\n";
} 