#!/bin/bash

# Protect Documentation Files Script
# This script ensures documentation quality and prevents common issues

echo "🔒 Setting up documentation protection..."

# Create backup directory
mkdir -p .backup/docs

# Backup all documentation files
echo "📋 Backing up documentation files..."
cp -r docs/ .backup/docs/

# Make documentation files read-only to prevent accidental changes
echo "🔒 Making documentation files read-only..."
find docs/ -name "*.md" -type f -exec chmod 444 {} \;

echo "✅ Documentation files are now protected (read-only)"
echo "💡 To edit documentation, run: ./scripts/unlock-docs.sh"
echo "💡 To restore from backup, run: ./scripts/restore-docs.sh" 