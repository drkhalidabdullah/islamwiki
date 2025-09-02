#!/usr/bin/env python3
"""
Markdown Validation Script for IslamWiki Documentation
This script validates all Markdown files for common errors and issues
"""

import os
import re
import glob
from pathlib import Path

def check_markdown_file(file_path):
    """Check a single markdown file for common issues"""
    print(f"\n📄 Checking: {file_path}")
    
    has_errors = False
    has_warnings = False
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
            lines = content.split('\n')
        
        # Check for malformed headers (headers without text)
        for i, line in enumerate(lines, 1):
            if re.match(r'^#{1,6}\s*$', line):
                print(f"  ❌ MALFORMED HEADER: Empty header found at line {i}")
                has_errors = True
        
        # Check for unclosed code blocks
        code_block_count = content.count('```')
        if code_block_count % 2 != 0:
            print(f"  ❌ UNCLOSED CODE BLOCK: Odd number of code block markers ({code_block_count})")
            has_errors = True
        
        # Check for missing file headers (metadata)
        if '**Author:**' not in content:
            print(f"  ⚠️  MISSING METADATA: No author information found")
            has_warnings = True
        
        if '**Version:**' not in content:
            print(f"  ⚠️  MISSING METADATA: No version information found")
            has_warnings = True
        
        # Report status
        if has_errors:
            print(f"  ❌ File has ERRORS")
        elif has_warnings:
            print(f"  ⚠️  File has WARNINGS")
        else:
            print(f"  ✅ File is VALID")
        
        return has_errors, has_warnings
        
    except Exception as e:
        print(f"  ❌ ERROR reading file: {e}")
        return True, False

def main():
    """Main validation function"""
    print("🔍 Validating Markdown Documentation...")
    print("=" * 50)
    
    # Counters
    total_files = 0
    error_files = 0
    warning_files = 0
    
    # Find all markdown files
    docs_dir = Path("docs")
    markdown_files = list(docs_dir.rglob("*.md"))
    
    print(f"Starting validation of {len(markdown_files)} Markdown files...")
    print("")
    
    # Process all markdown files
    for file_path in markdown_files:
        has_errors, has_warnings = check_markdown_file(file_path)
        
        if has_errors:
            error_files += 1
        elif has_warnings:
            warning_files += 1
        
        total_files += 1
    
    # Summary
    print("")
    print("=" * 50)
    print("📊 VALIDATION SUMMARY")
    print("=" * 50)
    print(f"Total files checked: {total_files}")
    print(f"Files with errors: {error_files}")
    print(f"Files with warnings: {warning_files}")
    print(f"Valid files: {total_files - error_files - warning_files}")
    
    if error_files == 0 and warning_files == 0:
        print("\n🎉 All documentation files are valid!")
        return 0
    elif error_files == 0:
        print("\n⚠️  Documentation has warnings but no errors")
        return 0
    else:
        print("\n❌ Documentation has errors that need fixing")
        return 1

if __name__ == "__main__":
    exit(main()) 