<?php

/**
 * Shared Hosting Translation System Test
 * 
 * Tests translation services that work on shared hosting
 * without requiring Docker or server installations
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

echo "🌍 IslamWiki Translation System - Shared Hosting Test\n";
echo "==================================================\n\n";

// Test 1: MyMemory Translation API (Free)
echo "1. Testing MyMemory Translation API (Free)...\n";
$mymemoryUrl = 'https://api.mymemory.translated.net/get';
$mymemoryParams = [
    'q' => 'Hello world',
    'langpair' => 'en|ar'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $mymemoryUrl . '?' . http_build_query($mymemoryParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['responseData']['translatedText'])) {
        echo "   ✅ MyMemory API working: '{$result['responseData']['translatedText']}'\n";
    } else {
        echo "   ❌ MyMemory API response format error\n";
    }
} else {
    echo "   ❌ MyMemory API failed (HTTP $httpCode)\n";
}

// Test 2: LibreTranslate Public API
echo "\n2. Testing LibreTranslate Public API...\n";
$libreUrl = 'https://libretranslate.com/translate';
$libreData = [
    'q' => 'Hello world',
    'source' => 'en',
    'target' => 'ar',
    'format' => 'text'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $libreUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($libreData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['translatedText'])) {
        echo "   ✅ LibreTranslate Public API working: '{$result['translatedText']}'\n";
    } else {
        echo "   ❌ LibreTranslate API response format error\n";
    }
} else {
    echo "   ❌ LibreTranslate API failed (HTTP $httpCode)\n";
}

// Test 3: Google Translate (via proxy)
echo "\n3. Testing Google Translate (via proxy)...\n";
$googleUrl = 'https://translate.googleapis.com/translate_a/single';
$googleParams = [
    'client' => 'gtx',
    'sl' => 'en',
    'tl' => 'ar',
    'dt' => 't',
    'q' => 'Hello world'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $googleUrl . '?' . http_build_query($googleParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result[0][0][0])) {
        echo "   ✅ Google Translate working: '{$result[0][0][0]}'\n";
    } else {
        echo "   ❌ Google Translate response format error\n";
    }
} else {
    echo "   ❌ Google Translate failed (HTTP $httpCode)\n";
}

// Test 4: Database connection (shared hosting compatible)
echo "\n4. Testing Database Connection...\n";
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

echo "\n🎯 Shared Hosting Translation Options:\n";
echo "=====================================\n";
echo "✅ MyMemory API - Free, 1000 requests/day\n";
echo "✅ LibreTranslate Public API - Free, rate limited\n";
echo "✅ Google Translate - Free, unofficial API\n";
echo "✅ Lingvanex API - Paid, $5/million characters\n";
echo "✅ Microsoft Translator - Free tier available\n";

echo "\n🚀 Recommended Implementation for Shared Hosting:\n";
echo "================================================\n";
echo "1. Use MyMemory API as primary (free, reliable)\n";
echo "2. Use LibreTranslate as backup\n";
echo "3. Implement translation memory for consistency\n";
echo "4. Add human review workflow\n";
echo "5. Cache translations in database\n";

echo "\n💡 Benefits of Cloud APIs:\n";
echo "=========================\n";
echo "✅ No server setup required\n";
echo "✅ No Docker dependencies\n";
echo "✅ Works on any shared hosting\n";
echo "✅ Automatic scaling\n";
echo "✅ Regular updates and improvements\n";
echo "✅ Multiple provider fallbacks\n";

