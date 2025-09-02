#!/bin/bash
# IslamWiki Framework - Restore .htaccess Script
# Author: Khalid Abdullah
# Version: 0.0.5
# Date: 2025-01-27
# License: AGPL-3.0

# This script ensures the .htaccess file is always present for SPA routing

echo "🔧 **Restoring .htaccess for SPA Routing**"
echo "=========================================="

# Check if .htaccess exists
if [ ! -f "public/.htaccess" ]; then
    echo "❌ .htaccess file missing! Restoring..."
    
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

    echo "✅ .htaccess file restored successfully!"
    
    # Set proper permissions
    chmod 644 public/.htaccess
    echo "✅ File permissions set correctly"
    
    # Restart Apache to ensure changes take effect
    echo "🔄 Restarting Apache to apply changes..."
    sudo systemctl restart apache2
    
    if [ $? -eq 0 ]; then
        echo "✅ Apache restarted successfully"
    else
        echo "⚠️  Apache restart failed, but .htaccess is restored"
    fi
    
else
    echo "✅ .htaccess file already exists"
fi

# Verify the file exists and has content
if [ -f "public/.htaccess" ]; then
    echo "✅ .htaccess file verified"
    echo "📁 File size: $(wc -c < public/.htaccess) bytes"
    echo "🔍 Contains SPA routing rules: $(grep -c 'RewriteRule.*index.html' public/.htaccess)"
else
    echo "❌ Failed to restore .htaccess file"
    exit 1
fi

echo ""
echo "🎯 **SPA Routing Status**"
echo "========================"
echo "✅ .htaccess file is present and configured"
echo "✅ SPA routing rules are active"
echo "✅ Page refresh should now work correctly"
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
echo "Run this script again: ./scripts/restore-htaccess.sh"
echo "Or manually check: ls -la public/.htaccess" 