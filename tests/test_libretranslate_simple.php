<?php

/**
 * Simple LibreTranslate Test
 * 
 * Test LibreTranslate service when it's running
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

echo "🔧 LibreTranslate Service Test\n";
echo "=============================\n\n";

// Test LibreTranslate health
echo "Testing LibreTranslate health endpoint...\n";
$healthUrl = 'http://localhost:5000/health';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $healthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ LibreTranslate is healthy!\n\n";
    
    // Test translation
    echo "Testing translation (English to Arabic)...\n";
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
            echo "✅ Translation successful!\n";
            echo "   English: Hello world\n";
            echo "   Arabic:  {$result['translatedText']}\n";
            echo "\n🎉 LibreTranslate is working perfectly!\n";
        } else {
            echo "❌ Translation response format error\n";
            echo "Response: " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "❌ Translation failed (HTTP $httpCode)\n";
    }
} else {
    echo "❌ LibreTranslate is not responding (HTTP $httpCode)\n";
    echo "\n💡 To start LibreTranslate:\n";
    echo "   sudo docker run -d --name islamwiki_libretranslate -p 5000:5000 libretranslate/libretranslate:latest\n";
    echo "   Wait 1-2 minutes for it to fully start up\n";
}

