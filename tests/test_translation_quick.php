<?php

/**
 * Quick Translation System Test
 * 
 * Simple test to verify basic translation functionality
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

echo "🌍 IslamWiki Translation System - Quick Test\n";
echo "==========================================\n\n";

// Test 1: Check if LibreTranslate is running
echo "1. Testing LibreTranslate Service...\n";
$libreUrl = 'http://localhost:5000/health';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $libreUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "   ✅ LibreTranslate is running\n";
} else {
    echo "   ❌ LibreTranslate is not running (HTTP $httpCode)\n";
    echo "   💡 Start it with: docker-compose -f docker-compose.translation.yml up -d\n";
}

// Test 2: Test basic translation
echo "\n2. Testing Basic Translation...\n";
$translateUrl = 'http://localhost:5000/translate';
$translateData = [
    'q' => 'Hello world',
    'source' => 'en',
    'target' => 'ar',
    'format' => 'text'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $translateUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($translateData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['translatedText'])) {
        echo "   ✅ Translation working: '{$result['translatedText']}'\n";
    } else {
        echo "   ❌ Translation response format error\n";
        echo "   Response: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ Translation failed (HTTP $httpCode)\n";
}

// Test 3: Check database connection
echo "\n3. Testing Database Connection...\n";
try {
    require_once __DIR__ . '/../src/Core/Config/Config.php';
    require_once __DIR__ . '/../src/Core/Database/Database.php';
    
    $config = new \IslamWiki\Core\Config\Config();
    $dbConfig = $config->get('database');
    
    if ($dbConfig) {
        $database = new \IslamWiki\Core\Database\Database($dbConfig);
        
        $result = $database->query("SELECT 1 as test");
        if ($result) {
            echo "   ✅ Database connection working\n";
        } else {
            echo "   ❌ Database query failed\n";
        }
    } else {
        echo "   ❌ Database configuration not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 4: Check if translation tables exist
echo "\n4. Testing Translation Tables...\n";
try {
    if (isset($database)) {
        $tables = ['languages', 'translations', 'translation_memory', 'translation_jobs'];
        $allExist = true;
        
        foreach ($tables as $table) {
            $result = $database->query("SHOW TABLES LIKE '$table'");
            if ($result && count($result) > 0) {
                echo "   ✅ Table '$table' exists\n";
            } else {
                echo "   ❌ Table '$table' missing\n";
                $allExist = false;
            }
        }
        
        if (!$allExist) {
            echo "   💡 Run migration: php scripts/run_migrations.php\n";
        }
    } else {
        echo "   ⚠️ Skipping table check - database not available\n";
    }
} catch (Exception $e) {
    echo "   ❌ Table check error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Next Steps:\n";
echo "=============\n";
echo "1. Start LibreTranslate: docker-compose -f docker-compose.translation.yml up -d\n";
echo "2. Run full test suite: php tests/test_translation_system.php\n";
echo "3. Test API endpoints: curl http://localhost/api/translation/languages\n";
echo "4. Test frontend: Open http://localhost and check language switcher\n";

