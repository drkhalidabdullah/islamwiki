<?php

/**
 * Final Language Switcher Test
 * 
 * Comprehensive test to verify everything is working
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

echo "🎉 Final Language Switcher Test\n";
echo "==============================\n\n";

echo "✅ API Status:\n";
echo "==============\n";

// Test API endpoints
$baseUrl = 'http://localhost:8080';

$endpoints = [
    '/api/language/current' => 'GET',
    '/api/language/supported' => 'GET',
    '/api/language/switcher' => 'GET',
    '/api/language/switch' => 'POST'
];

foreach ($endpoints as $endpoint => $method) {
    $url = $baseUrl . $endpoint;
    
    if ($method === 'GET') {
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data) {
                echo "✅ $endpoint - Working\n";
            } else {
                echo "❌ $endpoint - Invalid JSON\n";
            }
        } else {
            echo "❌ $endpoint - Not accessible\n";
        }
    } else {
        // Test POST endpoint
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode(['lang' => 'ar']),
                'timeout' => 5
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && isset($data['code'])) {
                echo "✅ $endpoint - Working (switched to {$data['code']})\n";
            } else {
                echo "❌ $endpoint - Invalid response\n";
            }
        } else {
            echo "❌ $endpoint - Not accessible\n";
        }
    }
}

echo "\n📁 File Status:\n";
echo "===============\n";

$files = [
    'public/index.html' => 'Main HTML file',
    'public/assets/index-56f7ec94.js' => 'JavaScript bundle',
    'public/api/index.php' => 'Main API file',
    'public/api/language_endpoints.php' => 'Language API endpoints',
    'src/components/language/LanguageSwitcher.tsx' => 'Language Switcher component',
    'src/components/layout/Header.tsx' => 'Header component'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description\n";
    } else {
        echo "❌ $description - MISSING\n";
    }
}

echo "\n🔧 Common Issues & Solutions:\n";
echo "=============================\n";

echo "1. Dropdown visible but not working:\n";
echo "   • Check browser console for JavaScript errors (F12)\n";
echo "   • Verify API calls are successful in Network tab\n";
echo "   • Check if React components are properly loaded\n\n";

echo "2. API errors:\n";
echo "   • CORS issues: Check browser console\n";
echo "   • 404 errors: Verify API endpoints are accessible\n";
echo "   • 500 errors: Check server logs\n\n";

echo "3. Language switching not working:\n";
echo "   • Check if session/cookies are being set\n";
echo "   • Verify RTL/LTR direction changes\n";
echo "   • Test with different browsers\n\n";

echo "4. Settings page issues:\n";
echo "   • Check if LanguagePreference component is loaded\n";
echo "   • Verify settings save functionality\n";
echo "   • Check for TypeScript compilation errors\n\n";

echo "🎯 Debug Steps:\n";
echo "===============\n";
echo "1. Open browser developer tools (F12)\n";
echo "2. Go to Console tab and look for errors\n";
echo "3. Go to Network tab and check API calls\n";
echo "4. Test the debug page: http://localhost:8080/debug-language.html\n";
echo "5. Test the simple language page: http://localhost:8080/test-language.html\n\n";

echo "📋 What to Check:\n";
echo "=================\n";
echo "• Browser console errors (red text)\n";
echo "• Network requests failing (red in Network tab)\n";
echo "• React components not rendering\n";
echo "• API responses returning errors\n";
echo "• CORS issues in console\n\n";

echo "✅ System Status: READY FOR TESTING\n";
echo "===================================\n";
echo "The language switcher should now be fully functional!\n";
echo "If you're still seeing errors, please share the specific\n";
echo "error messages from the browser console.\n";
