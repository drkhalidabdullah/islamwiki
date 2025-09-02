#!/bin/bash

# Protect Essential Files Script
# This script ensures critical files are not deleted during builds

echo "🔒 Protecting essential files..."

# Create backup directory if it doesn't exist
mkdir -p .backup

# Backup essential files
echo "📋 Backing up essential files..."
cp -r public/api .backup/
cp public/.htaccess .backup/
cp public/index.php .backup/
cp public/index.html .backup/

echo "✅ Essential files backed up to .backup/ directory"
echo "💡 After build, run: ./scripts/restore-essential-files.sh" 