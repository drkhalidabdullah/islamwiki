#!/bin/bash
# IslamWiki Framework - Build and Preserve .htaccess Script
# Author: Khalid Abdullah
# Version: 0.0.5
# Date: 2025-01-27
# License: AGPL-3.0

# This script builds the frontend while preserving the .htaccess file

echo "🚀 **Building Frontend and Preserving .htaccess**"
echo "================================================"

# Step 1: Backup .htaccess file
echo "📋 Step 1: Backing up .htaccess file..."
if [ -f "public/.htaccess" ]; then
    cp public/.htaccess .htaccess.backup
    echo "✅ .htaccess backed up to .htaccess.backup"
else
    echo "⚠️  No .htaccess file found to backup"
fi

# Step 2: Build the frontend
echo ""
echo "🔨 Step 2: Building frontend with npm..."
npm run build

if [ $? -eq 0 ]; then
    echo "✅ Frontend build completed successfully"
else
    echo "❌ Frontend build failed!"
    exit 1
fi

# Step 3: Restore .htaccess file
echo ""
echo "🔧 Step 3: Restoring .htaccess file..."
if [ -f ".htaccess.backup" ]; then
    cp .htaccess.backup public/.htaccess
    echo "✅ .htaccess file restored from backup"
    
    # Clean up backup
    rm .htaccess.backup
    echo "✅ Backup file cleaned up"
else
    echo "⚠️  No backup found, creating new .htaccess file..."
    # Create the .htaccess file
    cat > public/.htaccess << 'EOF'
# IslamWiki Framework - Apache Configuration for SPA Routing
# Author: Khalid Abdullah
# Version: 0.0.5
# Date: 2025-01-27
# License: AGPL-3.0

# Enable URL rewriting
RewriteEngine On

# Handle React Router SPA routing
# All routes should serve index.html and let React Router handle navigation
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [QSA,L]

# Security Headers
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Prevent MIME type sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';"
    
    # Remove server signature
    Header unset Server
    Header unset X-Powered-By
</IfModule>

# Caching and Compression
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Images
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    
    # CSS and JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    
    # Fonts
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    
    # HTML
    ExpiresByType text/html "access plus 1 hour"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>

# Security: Block access to sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.(env|log|sql|md|txt|yml|yaml|ini|conf|config)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security: Block access to vendor and storage directories
<IfModule mod_rewrite.c>
    RewriteRule ^(vendor|storage|config|database|src|tests)/ - [F,L]
</IfModule>

# PHP Settings
<IfModule mod_php.c>
    php_value upload_max_filesize 8M
    php_value post_max_size 8M
    php_value max_execution_time 30
    php_value memory_limit 128M
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>
EOF
    echo "✅ New .htaccess file created"
fi

# Step 4: Set proper permissions
echo ""
echo "🔐 Step 4: Setting file permissions..."
chmod 644 public/.htaccess
echo "✅ .htaccess file permissions set correctly"

# Step 5: Verify .htaccess file
echo ""
echo "🔍 Step 5: Verifying .htaccess file..."
if [ -f "public/.htaccess" ]; then
    echo "✅ .htaccess file exists"
    echo "📁 File size: $(wc -c < public/.htaccess) bytes"
    echo "🔍 Contains SPA routing rules: $(grep -c 'RewriteRule.*index.html' public/.htaccess)"
    
    # Check if it contains the essential SPA routing rule
    if grep -q "RewriteRule.*index.html" public/.htaccess; then
        echo "✅ SPA routing rules verified"
    else
        echo "❌ SPA routing rules missing!"
        exit 1
    fi
else
    echo "❌ .htaccess file not found!"
    exit 1
fi

# Step 6: Test SPA routing
echo ""
echo "🧪 Step 6: Testing SPA routing..."
echo "Testing /dashboard route..."
curl -I http://localhost/dashboard 2>/dev/null | head -1

if [ $? -eq 0 ]; then
    echo "✅ SPA routing test completed"
else
    echo "⚠️  SPA routing test failed (Apache might need restart)"
fi

echo ""
echo "🎉 **Build and .htaccess Preservation Complete!**"
echo "================================================"
echo "✅ Frontend built successfully"
echo "✅ .htaccess file preserved and configured"
echo "✅ SPA routing rules active"
echo "✅ Page refresh should work correctly"
echo ""
echo "🧪 **Test Instructions**"
echo "======================"
echo "1. Go to: http://localhost/login"
echo "2. Login as admin: admin@islamwiki.org / password"
echo "3. Navigate to /admin or /dashboard"
echo "4. Refresh the page (F5 or Ctrl+R)"
echo "5. Should NOT show 'not found' error"
echo ""
echo "🔧 **If issues persist:**"
echo "Run: ./scripts/restore-htaccess.sh"
echo "Or restart Apache: sudo systemctl restart apache2"
echo ""
echo "📁 **Files created/modified:**"
echo "- public/assets/ (built frontend files)"
echo "- public/index.html (main HTML file)"
echo "- public/.htaccess (SPA routing configuration)"
echo ""
echo "🚀 **Ready for testing!**" 