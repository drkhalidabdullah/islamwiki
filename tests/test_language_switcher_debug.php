<?php

/**
 * Language Switcher Debug Test
 * 
 * Helps debug why the language switcher isn't visible
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

echo "🔍 Language Switcher Debug Test\n";
echo "==============================\n\n";

echo "📁 File Structure Check:\n";
echo "========================\n";

// Check if main files exist
$files = [
    'public/index.html' => 'Main HTML file',
    'public/assets/index-56f7ec94.js' => 'Main JavaScript bundle',
    'public/api/language.php' => 'Language API endpoint',
    'src/components/language/LanguageSwitcher.tsx' => 'Language Switcher component',
    'src/components/layout/Header.tsx' => 'Header component',
    'src/pages/SettingsPage.tsx' => 'Settings page'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ $description: $file (MISSING)\n";
    }
}

echo "\n📄 HTML Content Check:\n";
echo "======================\n";

if (file_exists('public/index.html')) {
    $html = file_get_contents('public/index.html');
    
    if (strpos($html, 'script') !== false) {
        echo "✅ Script tags found in HTML\n";
        
        // Extract script src
        preg_match('/src="([^"]*\.js)"/', $html, $matches);
        if (!empty($matches[1])) {
            echo "✅ Script source: {$matches[1]}\n";
            
            $scriptFile = 'public' . $matches[1];
            if (file_exists($scriptFile)) {
                echo "✅ Script file exists: $scriptFile\n";
                echo "✅ Script file size: " . filesize($scriptFile) . " bytes\n";
            } else {
                echo "❌ Script file missing: $scriptFile\n";
            }
        }
    } else {
        echo "❌ No script tags found in HTML\n";
    }
    
    if (strpos($html, 'LanguageSwitcher') !== false) {
        echo "✅ LanguageSwitcher component found in HTML\n";
    } else {
        echo "⚠️  LanguageSwitcher component not found in HTML (may be bundled)\n";
    }
} else {
    echo "❌ HTML file not found\n";
}

echo "\n🔧 API Endpoint Check:\n";
echo "======================\n";

if (file_exists('public/api/language.php')) {
    echo "✅ Language API file exists\n";
    
    // Check if it's accessible via web server
    $apiUrl = 'http://localhost/api/language/current';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $result = @file_get_contents($apiUrl, false, $context);
    if ($result !== false) {
        echo "✅ API endpoint accessible: $apiUrl\n";
        echo "✅ API response: " . substr($result, 0, 100) . "...\n";
    } else {
        echo "⚠️  API endpoint not accessible via web server\n";
        echo "   This is normal if no web server is running\n";
    }
} else {
    echo "❌ Language API file missing\n";
}

echo "\n🎯 Next Steps:\n";
echo "==============\n";
echo "1. Open your website in a browser\n";
echo "2. Check browser console for JavaScript errors (F12)\n";
echo "3. Look for the flag dropdown in the header\n";
echo "4. If not visible, check if React components are loading\n";
echo "5. Test the API at: http://localhost/api/language/current\n";
echo "6. Test the language switcher at: http://localhost/test-language.html\n\n";

echo "🔍 Common Issues:\n";
echo "=================\n";
echo "• JavaScript not loading: Check script tags in HTML\n";
echo "• API not working: Check web server configuration\n";
echo "• Components not visible: Check React bundle and console errors\n";
echo "• CORS issues: Check API headers and browser console\n\n";

echo "✅ Debug test completed!\n";
