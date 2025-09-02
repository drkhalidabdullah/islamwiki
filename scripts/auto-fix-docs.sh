#!/bin/bash

# Auto-fix Documentation Issues Script
# This script automatically fixes common documentation problems

echo "🔧 Auto-fixing documentation issues..."

# Fix code blocks
echo "📝 Fixing code blocks..."
python3 scripts/fix_code_blocks.py

# Add missing metadata to files that need it
echo "📋 Adding missing metadata..."

# Function to add metadata to a file
add_metadata() {
    local file="$1"
    local title="$2"
    local version="$3"
    
    # Check if file already has metadata
    if ! grep -q "^\\*\\*Author:\\*\\*" "$file"; then
        echo "  Adding metadata to $file"
        
        # Create temporary file
        local temp_file=$(mktemp)
        
        # Add metadata after title
        sed "1a\\
\\
**Author:** Khalid Abdullah\\
**Version:** $version\\
**Date:** $(date +%B\ %d,\ %Y)\\
**License:** AGPL-3.0" "$file" > "$temp_file"
        
        # Replace original file
        mv "$temp_file" "$file"
    fi
}

# Add metadata to files that need it
find docs/ -name "*.md" -type f -exec grep -L "^\\*\\*Author:\\*\\*" {} \; | while read file; do
    # Extract title and version from filename or content
    title=$(head -1 "$file" | sed 's/^# //')
    version=$(echo "$file" | grep -o 'v[0-9]\+\.[0-9]\+\.[0-9]\+' | head -1)
    
    if [ -z "$version" ]; then
        version="0.0.5"  # Default version
    fi
    
    add_metadata "$file" "$title" "$version"
done

echo "✅ Auto-fix completed"
echo "💡 Run validation to check: ./scripts/validate_markdown.py" 