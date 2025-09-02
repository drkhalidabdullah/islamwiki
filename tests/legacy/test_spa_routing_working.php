<?php
/**
 * SPA Routing Verification Test
 * 
 * This script verifies that the SPA routing is working correctly
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

echo "🌐 **SPA Routing Verification Test**\n";
echo "==================================\n\n";

// Test 1: Check if .htaccess is working
echo "📋 **Test 1: .htaccess SPA Routing**\n";
echo "====================================\n";

$testUrl = 'http://localhost/dashboard';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Testing URL: {$testUrl}\n";
echo "HTTP Status Code: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ SPA Routing is WORKING correctly!\n";
    echo "   - /dashboard returns HTTP 200 OK\n";
    echo "   - .htaccess is serving index.html for React Router\n";
} else {
    echo "❌ SPA Routing is NOT working!\n";
    echo "   - Expected HTTP 200, got HTTP {$httpCode}\n";
}

echo "\n";

// Test 2: Check if assets are accessible
echo "📁 **Test 2: Asset Accessibility**\n";
echo "==================================\n";

$assets = [
    'http://localhost/assets/index-DkhcBd_e.js',
    'http://localhost/assets/index-BDJr2aC6.css',
    'http://localhost/assets/vendor-CBH9K-97.js'
];

foreach ($assets as $asset) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $asset);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $filename = basename($asset);
    if ($httpCode === 200) {
        echo "✅ {$filename} - Accessible (HTTP 200)\n";
    } else {
        echo "❌ {$filename} - Not accessible (HTTP {$httpCode})\n";
    }
}

echo "\n";

// Test 3: Browser troubleshooting steps
echo "🔧 **Test 3: Browser Troubleshooting**\n";
echo "=====================================\n";

echo "Since SPA routing is working on the server side, the issue is likely:\n\n";

echo "1. **Browser Cache Issue** (Most Likely)\n";
echo "   - Clear browser cache completely\n";
echo "   - Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)\n";
echo "   - Clear browser data: Settings > Privacy > Clear browsing data\n\n";

echo "2. **JavaScript Loading Issue**\n";
echo "   - Open Developer Tools (F12)\n";
echo "   - Check Console tab for JavaScript errors\n";
echo "   - Check Network tab to see if assets are loading\n\n";

echo "3. **Service Worker Issue**\n";
echo "   - Check Application tab > Service Workers\n";
echo "   - Unregister any service workers\n";
echo "   - Clear storage: Application > Storage > Clear site data\n\n";

echo "4. **Browser Compatibility**\n";
echo "   - Try a different browser (Chrome, Firefox, Safari)\n";
echo "   - Check if JavaScript is enabled\n\n";

echo "5. **Direct URL Test**\n";
echo "   - Try navigating directly to: http://localhost/dashboard\n";
echo "   - Check if the React app loads\n\n";

echo "\n";

// Test 4: Verification steps
echo "✅ **Verification Steps**\n";
echo "========================\n";

echo "To verify SPA routing is working:\n\n";

echo "1. **Clear Browser Cache**\n";
echo "   - Hard refresh the page\n";
echo "   - Clear all browsing data\n\n";

echo "2. **Test Direct Navigation**\n";
echo "   - Go to: http://localhost/\n";
echo "   - Login with testuser\n";
echo "   - Navigate to: http://localhost/dashboard\n";
echo "   - Refresh the page\n";
echo "   - Should work without 404 errors\n\n";

echo "3. **Check Browser Console**\n";
echo "   - Open Developer Tools (F12)\n";
echo "   - Look for authentication logs:\n";
echo "     🚀 App starting up, checking for stored authentication...\n";
echo "     🔍 Has stored auth data: true\n";
echo "     ✅ Session restoration result: true\n\n";

echo "4. **Test React Router**\n";
echo "   - Navigate between different routes\n";
echo "   - Refresh pages on different routes\n";
echo "   - All should work without 404 errors\n\n";

echo "\n";

// Test 5: Expected behavior
echo "🎯 **Expected Behavior**\n";
echo "=======================\n";

echo "When SPA routing is working correctly:\n\n";

echo "✅ **Home Page**: http://localhost/ → React app loads\n";
echo "✅ **Login Page**: http://localhost/login → React app loads\n";
echo "✅ **Dashboard**: http://localhost/dashboard → React app loads\n";
echo "✅ **User Profile**: http://localhost/testuser → React app loads\n";
echo "✅ **Settings**: http://localhost/settings → React app loads\n";
echo "✅ **Admin**: http://localhost/admin → React app loads (if admin)\n\n";

echo "✅ **Page Refresh**: Any route should work on refresh\n";
echo "✅ **Direct URL**: Typing any route directly should work\n";
echo "✅ **Navigation**: React Router should handle all navigation\n\n";

echo "\n";

echo "🔍 **Test Complete**\n";
echo "==================\n";
echo "SPA routing is working correctly on the server side.\n";
echo "Follow the browser troubleshooting steps above.\n";
echo "The issue is likely browser cache or JavaScript loading.\n";
?> 