#!/bin/bash

# Simple Markdown Validation Script for IslamWiki Documentation

echo "🔍 Validating Markdown Documentation..."
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Counters
total_files=0
error_files=0
warning_files=0

# Function to check a single markdown file
check_markdown_file() {
    local file="$1"
    local has_errors=false
    local has_warnings=false
    
    echo -e "\n📄 Checking: $file"
    
    # Check for malformed headers (headers without text)
    if grep -q "^#{1,6}\s*$" "$file"; then
        echo -e "  ${RED}❌ MALFORMED HEADER:${NC} Empty header found"
        has_errors=true
    fi
    
    # Check for unclosed code blocks
    local code_block_count=$(grep -c "^```" "$file")
    if [ $((code_block_count % 2)) -ne 0 ]; then
        echo -e "  ${RED}❌ UNCLOSED CODE BLOCK:${NC} Odd number of code block markers"
        has_errors=true
    fi
    
    # Check for missing file headers (metadata)
    if ! grep -q "^\\*\\*Author:\\*\\*" "$file"; then
        echo -e "  ${YELLOW}⚠️  MISSING METADATA:${NC} No author information found"
        has_warnings=true
    fi
    
    if ! grep -q "^\\*\\*Version:\\*\\*" "$file"; then
        echo -e "  ${YELLOW}⚠️  MISSING METADATA:${NC} No version information found"
        has_warnings=true
    fi
    
    # Update counters
    if [ "$has_errors" = true ]; then
        ((error_files++))
        echo -e "  ${RED}❌ File has ERRORS${NC}"
    elif [ "$has_warnings" = true ]; then
        ((warning_files++))
        echo -e "  ${YELLOW}⚠️  File has WARNINGS${NC}"
    else
        echo -e "  ${GREEN}✅ File is VALID${NC}"
    fi
    
    ((total_files++))
}

# Main validation loop
echo "Starting validation of all Markdown files..."
echo ""

# Process all markdown files
while IFS= read -r -d '' file; do
    check_markdown_file "$file"
done < <(find docs/ -name "*.md" -type f -print0)

# Summary
echo ""
echo "======================================"
echo "📊 VALIDATION SUMMARY"
echo "======================================"
echo -e "Total files checked: ${GREEN}$total_files${NC}"
echo -e "Files with errors: ${RED}$error_files${NC}"
echo -e "Files with warnings: ${YELLOW}$warning_files${NC}"
echo -e "Valid files: ${GREEN}$((total_files - error_files - warning_files))${NC}"

if [ $error_files -eq 0 ] && [ $warning_files -eq 0 ]; then
    echo -e "\n${GREEN}🎉 All documentation files are valid!${NC}"
    exit 0
elif [ $error_files -eq 0 ]; then
    echo -e "\n${YELLOW}⚠️  Documentation has warnings but no errors${NC}"
    exit 0
else
    echo -e "\n${RED}❌ Documentation has errors that need fixing${NC}"
    exit 1
fi
