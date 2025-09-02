#!/usr/bin/env python3
"""
Fix unclosed code blocks in Markdown files
"""

import re

def fix_code_blocks(filename):
    """Fix unclosed code blocks in a markdown file"""
    print(f"Fixing code blocks in {filename}")
    
    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Count code block markers
    code_block_count = content.count('```')
    print(f"Found {code_block_count} code block markers")
    
    if code_block_count % 2 != 0:
        print(f"Odd number of code blocks detected. Adding closing marker...")
        # Add a closing marker at the end
        content += '\n```\n'
        print("Added closing code block marker")
    else:
        print("Code blocks are balanced")
    
    # Write back to file
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print("File updated successfully")

if __name__ == "__main__":
    fix_code_blocks("docs/plans/ISLAMWIKI_PLATFORM_COMPREHENSIVE_PLAN.md") 