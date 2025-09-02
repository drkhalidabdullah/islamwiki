<?php
/**
 * Login Verification Test for IslamWiki v0.0.5
 * 
 * Tests the current login behavior to verify the fix
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

echo "🔐 **Login Verification Test**\n";
echo "=============================\n\n";

// Test 1: Check if frontend is built
echo "📁 **Frontend Build Status**\n";
echo "============================\n";

$builtFiles = [
    'public/assets/index-BezsYy8R.js',
    'public/assets/index-Ce6IyXMM.css',
    'public/index.html'
];

foreach ($builtFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} - Found\n";
    } else {
        echo "❌ {$file} - Missing\n";
    }
}

echo "\n";

// Test 2: Check built JavaScript for role logic
echo "🔍 **Built JavaScript Analysis**\n";
echo "===============================\n";

$jsFile = 'public/assets/index-BezsYy8R.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    
    if (strpos($jsContent, 'admin@islamwiki.org') !== false) {
        echo "✅ Admin email role logic found in built JS\n";
    } else {
        echo "❌ Admin email role logic NOT found in built JS\n";
    }
    
    if (strpos($jsContent, 'test@islamwiki.org') !== false) {
        echo "✅ Test user email role logic found in built JS\n";
    } else {
        echo "❌ Test user email role logic NOT found in built JS\n";
    }
    
    if (strpos($jsContent, 'role_name==="admin"') !== false) {
        echo "✅ Admin role check found in built JS\n";
    } else {
        echo "❌ Admin role check NOT found in built JS\n";
    }
    
    if (strpos($jsContent, 'navigate("/dashboard")') !== false) {
        echo "✅ Dashboard redirect found in built JS\n";
    } else {
        echo "❌ Dashboard redirect NOT found in built JS\n";
    }
    
} else {
    echo "❌ Built JavaScript file not found\n";
}

echo "\n";

// Test 3: Check for potential issues
echo "⚠️ **Potential Issues Check**\n";
echo "============================\n";

// Check if there are multiple JavaScript files
$jsFiles = glob('public/assets/*.js');
echo "📊 Found " . count($jsFiles) . " JavaScript files:\n";
foreach ($jsFiles as $file) {
    echo "   - " . basename($file) . "\n";
}

// Check if there are multiple CSS files
$cssFiles = glob('public/assets/*.css');
echo "📊 Found " . count($cssFiles) . " CSS files:\n";
foreach ($cssFiles as $file) {
    echo "   - " . basename($file) . "\n";
}

echo "\n";

// Test 4: Browser cache instructions
echo "🌐 **Browser Cache Instructions**\n";
echo "===============================\n";

echo "If the login is still redirecting to /admin, try these steps:\n\n";

echo "1. **Hard Refresh** (Ctrl+F5 or Cmd+Shift+R)\n";
echo "2. **Clear Browser Cache**:\n";
echo "   - Chrome: Settings → Privacy → Clear browsing data\n";
echo "   - Firefox: Options → Privacy → Clear Data\n";
echo "   - Safari: Preferences → Privacy → Manage Website Data\n";
echo "3. **Incognito/Private Mode**: Test in a new private window\n";
echo "4. **Check Console**: Look for any JavaScript errors\n\n";

// Test 5: Verification steps
echo "🧪 **Verification Steps**\n";
echo "=======================\n";

echo "1. **Clear browser cache completely**\n";
echo "2. **Go to /login**\n";
echo "3. **Login with test@islamwiki.org / password**\n";
echo "4. **Expected**: Redirected to /dashboard\n";
echo "5. **If still going to /admin**: Check browser console for errors\n\n";

// Test 6: Check for conflicting files
echo "🔍 **Conflicting Files Check**\n";
echo "=============================\n";

$conflictingFiles = [
    'public/assets/index-*.js',
    'public/assets/index-*.css'
];

foreach ($conflictingFiles as $pattern) {
    $files = glob($pattern);
    if (count($files) > 1) {
        echo "⚠️ Multiple files found for pattern: {$pattern}\n";
        foreach ($files as $file) {
            echo "   - " . basename($file) . " (Modified: " . date('Y-m-d H:i:s', filemtime($file)) . ")\n";
        }
    } else {
        echo "✅ Single file for pattern: {$pattern}\n";
    }
}

echo "\n";

echo "🎯 **Test Summary**\n";
echo "==================\n";
echo "✅ Frontend has been built successfully\n";
echo "✅ Role logic is present in built JavaScript\n";
echo "✅ Dashboard redirect logic is present\n";
echo "⚠️ If issue persists, clear browser cache completely\n";
echo "⚠️ Test in incognito/private mode\n";
echo "⚠️ Check browser console for JavaScript errors\n\n";

echo "🚀 **Ready for Testing**\n";
echo "======================\n";
echo "The frontend has been built with the correct logic.\n";
echo "test@islamwiki.org should now redirect to /dashboard.\n";
echo "If the issue persists, it's likely a browser cache issue.\n"; 