<?php

/**
 * Main Test Runner
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */

// Bootstrap the test environment
require_once __DIR__ . '/bootstrap.php';

echo "🚀 Starting IslamWiki Framework Test Suite...\n";
echo "==============================================\n\n";

$startTime = microtime(true);
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Test categories
$testCategories = [
    'Core Framework' => 'tests/Unit/Core/CoreFrameworkTest.php',
    'API Endpoints' => 'tests/Feature/ApiEndpointsTest.php',
    'New Components' => 'tests/Unit/Core/NewComponentsTest.php'
];

// Run each test category
foreach ($testCategories as $category => $testFile) {
    echo "📋 Testing: {$category}\n";
    echo "📍 File: {$testFile}\n";
    echo "----------------------------------------\n";
    
    if (file_exists($testFile)) {
        $output = [];
        $returnCode = 0;
        
        // Capture output and return code
        ob_start();
        include $testFile;
        $output = ob_get_clean();
        
        if ($returnCode === 0) {
            echo "✅ {$category} tests completed successfully\n";
            $passedTests++;
        } else {
            echo "❌ {$category} tests failed\n";
            $failedTests++;
        }
        
        echo $output . "\n";
    } else {
        echo "⚠️  Test file not found: {$testFile}\n";
    }
    
    echo "\n";
}

// Calculate execution time
$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2);

// Summary
echo "==============================================\n";
echo "📊 Test Summary\n";
echo "==============================================\n";
echo "⏱️  Execution Time: {$executionTime}ms\n";
echo "📁 Total Test Categories: " . count($testCategories) . "\n";
echo "✅ Passed: {$passedTests}\n";
echo "❌ Failed: {$failedTests}\n";
echo "🎯 Success Rate: " . round(($passedTests / count($testCategories)) * 100, 1) . "%\n";

if ($failedTests === 0) {
    echo "\n🎉 All tests passed successfully!\n";
    exit(0);
} else {
    echo "\n⚠️  Some tests failed. Please review the output above.\n";
    exit(1);
} 