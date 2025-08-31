#!/bin/bash

# IslamWiki Framework - Preserve .htaccess Script
# This script ensures the .htaccess file is preserved during builds

echo "🔒 Preserving .htaccess file..."

# Check if .htaccess exists in public directory
if [ -f "public/.htaccess" ]; then
    echo "✅ .htaccess found in public directory"
    # Create a backup
    cp public/.htaccess .htaccess.backup
    echo "📋 .htaccess backed up to .htaccess.backup"
else
    echo "⚠️  No .htaccess found in public directory"
fi 