#!/bin/bash

# Restore Documentation from Backup Script
# This script restores documentation files from backup

echo "🔄 Restoring documentation from backup..."

# Check if backup exists
if [ ! -d ".backup/docs" ]; then
    echo "❌ No backup found. Run protect-docs.sh first."
    exit 1
fi

# Restore documentation files
echo "📋 Restoring documentation files..."
cp -r .backup/docs/* docs/

# Make files writable again
echo "✏️  Making files writable..."
find docs/ -name "*.md" -type f -exec chmod 644 {} \;

echo "✅ Documentation restored from backup"
echo "💡 Run validation to check: ./scripts/validate_markdown.py" 