#!/bin/bash

# Pre-commit Documentation Validation Hook
# This script validates documentation before allowing commits

echo "🔍 Pre-commit documentation validation..."

# Run markdown validation
if ./scripts/validate_markdown.py; then
    echo "✅ Documentation validation passed"
    echo "🚀 Proceeding with commit..."
    exit 0
else
    echo "❌ Documentation validation failed"
    echo "🔧 Please fix the issues before committing"
    echo "💡 Run: ./scripts/validate_markdown.py for details"
    exit 1
fi 