#!/bin/bash

# Unlock Documentation Files Script
# This script allows editing of documentation files

echo "🔓 Unlocking documentation files for editing..."

# Make documentation files writable
echo "✏️  Making documentation files writable..."
find docs/ -name "*.md" -type f -exec chmod 644 {} \;

echo "✅ Documentation files are now writable"
echo "💡 Remember to run validation after editing: ./scripts/validate_markdown.py"
echo "💡 To protect again, run: ./scripts/protect-docs.sh" 