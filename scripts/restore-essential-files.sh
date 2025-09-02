#!/bin/bash

# Restore Essential Files Script
# This script restores critical files after builds

echo "🔄 Restoring essential files..."

# Check if backup directory exists
if [ ! -d ".backup" ]; then
    echo "❌ Backup directory not found. Run protect-essential-files.sh first."
    exit 1
fi

# Restore essential files
echo "📋 Restoring essential files..."
cp -r .backup/api public/
cp .backup/.htaccess public/
cp .backup/index.php public/
cp .backup/index.html public/

echo "✅ Essential files restored from .backup/ directory"
echo "🚀 Your application should now work correctly!" 