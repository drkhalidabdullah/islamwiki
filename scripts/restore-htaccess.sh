#!/bin/bash

# IslamWiki Framework - Restore .htaccess Script
# This script restores the .htaccess file after builds

echo "🔒 Restoring .htaccess file..."

# Check if backup exists
if [ -f ".htaccess.backup" ]; then
    echo "📋 .htaccess backup found, restoring..."
    cp .htaccess.backup public/.htaccess
    rm .htaccess.backup
    echo "✅ .htaccess restored successfully"
else
    echo "⚠️  No .htaccess backup found, creating default .htaccess..."
    # Create a default .htaccess file
    cat > public/.htaccess << 'EOF'
# Enable SPA routing - redirect all requests to index.html
RewriteEngine On

# Basic security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Handle SPA routing - redirect all non-file requests to index.html
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [L,QSA]

# Prevent access to sensitive files
<FilesMatch "\.(env|htaccess|htpasswd|ini|log|sh|sql|bak)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes
EOF
    echo "✅ Default .htaccess created"
fi 