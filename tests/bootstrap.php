<?php

/**
 * Test Bootstrap File
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone for consistent testing
date_default_timezone_set('UTC');

// Create test storage directory if it doesn't exist
$testStorageDir = __DIR__ . '/../storage/test';
if (!is_dir($testStorageDir)) {
    mkdir($testStorageDir, 0755, true);
}

// Set test environment
putenv('APP_ENV=testing');
putenv('CACHE_DRIVER=file');
putenv('CACHE_PATH=' . $testStorageDir . '/cache');

echo "🧪 Test environment initialized\n";
echo "📁 Test storage: {$testStorageDir}\n";
echo "🔧 Environment: testing\n\n"; 