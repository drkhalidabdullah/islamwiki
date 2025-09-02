<?php
/**
 * SPA Routing Test for IslamWiki v0.0.5
 * 
 * Tests that Apache is properly configured for SPA routing
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

echo "🌐 **SPA Routing Test**\n";
echo "======================\n\n";

// Test 1: Check Apache configuration
echo "📋 **Apache Configuration Check**\n";
echo "===============================\n";

$apacheConfig = '/etc/apache2/sites-enabled/islamwiki.conf';
if (file_exists($apacheConfig)) {
    echo "✅ Apache config file found: {$apacheConfig}\n";
    
    $config = file_get_contents($apacheConfig);
    if (strpos($config, 'DocumentRoot /var/www/html/public') !== false) {
        echo "✅ DocumentRoot correctly set to /var/www/html/public\n";
    } else {
        echo "❌ DocumentRoot not set correctly\n";
    }
    
    if (strpos($config, 'AllowOverride All') !== false) {
        echo "✅ AllowOverride All enabled (allows .htaccess)\n";
    } else {
        echo "❌ AllowOverride not enabled\n";
    }
} else {
    echo "❌ Apache config file not found\n";
}

echo "\n";

// Test 2: Check .htaccess file
echo "📁 **Public Directory Check**\n";
echo "============================\n";

$publicDir = 'public';
if (is_dir($publicDir)) {
    echo "✅ Public directory exists: {$publicDir}\n";
    
    $htaccessFile = $publicDir . '/.htaccess';
    if (file_exists($htaccessFile)) {
        echo "✅ .htaccess file exists in public directory\n";
        
        $htaccessContent = file_get_contents($htaccessFile);
        if (strpos($htaccessContent, 'RewriteEngine On') !== false) {
            echo "✅ URL rewriting enabled in .htaccess\n";
        } else {
            echo "❌ URL rewriting not enabled\n";
        }
        
        if (strpos($htaccessContent, 'RewriteRule ^(.*)$ index.html') !== false) {
            echo "✅ SPA routing rule found in .htaccess\n";
        } else {
            echo "❌ SPA routing rule not found\n";
        }
    } else {
        echo "❌ .htaccess file not found in public directory\n";
    }
} else {
    echo "❌ Public directory not found\n";
}

echo "\n";

// Test 3: Check built React app files
echo "🔨 **Built React App Check**\n";
echo "============================\n";

$builtFiles = [
    'public/index.html',
    'public/assets/index-D0pPlMeA.js',
    'public/assets/index-BdweduFm.css'
];

foreach ($builtFiles as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} - Found\n";
    } else {
        echo "❌ {$file} - Missing\n";
    }
}

echo "\n";

// Test 4: Check for conflicting files
echo "🚫 **Conflicting Files Check**\n";
echo "=============================\n";

$conflictingFiles = [
    'index.html',
    'index.php'
];

foreach ($conflictingFiles as $file) {
    if (file_exists($file)) {
        echo "⚠️  {$file} - Found in root (may conflict with SPA routing)\n";
    } else {
        echo "✅ {$file} - Not found in root (good)\n";
    }
}

echo "\n";

// Test 5: Apache service status
echo "🔄 **Apache Service Status**\n";
echo "===========================\n";

$apacheStatus = shell_exec('systemctl is-active apache2 2>/dev/null');
if (trim($apacheStatus) === 'active') {
    echo "✅ Apache service is running\n";
} else {
    echo "❌ Apache service is not running\n";
}

echo "\n";

// Test 6: SPA routing verification
echo "🧪 **SPA Routing Verification**\n";
echo "==============================\n";
echo "To test SPA routing:\n\n";

echo "1. Open your browser and go to: http://localhost/\n";
echo "2. You should see the React app load\n";
echo "3. Login with testuser (test@islamwiki.org / password)\n";
echo "4. Navigate to: http://localhost/dashboard\n";
echo "5. The page should load without 404 errors\n";
echo "6. Refresh the page - it should still work\n";
echo "7. Check browser console for session restoration logs\n\n";

echo "Expected behavior:\n";
echo "- ✅ Home page loads correctly\n";
echo "- ✅ Login works and redirects to dashboard\n";
echo "- ✅ Dashboard loads without 404 errors\n";
echo "- ✅ Page refresh maintains authentication\n";
echo "- ✅ React Router handles all client-side routes\n";

echo "\n";

// Test 7: Troubleshooting steps
echo "🔧 **Troubleshooting Steps**\n";
echo "===========================\n";

echo "If SPA routing still doesn't work:\n\n";

echo "1. Check Apache error logs:\n";
echo "   sudo tail -f /var/log/apache2/islamwiki_error.log\n\n";

echo "2. Verify Apache configuration:\n";
echo "   sudo apache2ctl configtest\n\n";

echo "3. Check if mod_rewrite is enabled:\n";
echo "   sudo a2enmod rewrite\n";
echo "   sudo systemctl restart apache2\n\n";

echo "4. Verify .htaccess is being read:\n";
echo "   Check Apache access logs for .htaccess requests\n\n";

echo "5. Test with a simple route:\n";
echo "   Try accessing http://localhost/nonexistent-route\n";
echo "   Should serve index.html instead of 404\n";

echo "\n";

echo "🎯 **Test Complete**\n";
echo "==================\n";
echo "Follow the verification steps above to test SPA routing.\n";
echo "If issues persist, check the troubleshooting steps.\n";
?> 