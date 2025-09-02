# 📚 Documentation Management Guide

**Author:** Khalid Abdullah  
**Version:** 0.0.5  
**Date:** September 2, 2025  
**License:** AGPL-3.0

## 🎯 **Overview**

This guide provides comprehensive instructions for maintaining high-quality documentation in the IslamWiki Framework project. It includes automated tools, validation processes, and best practices to prevent documentation errors.

## 🔧 **Documentation Tools**

### **Validation Scripts**

#### **1. Markdown Validation (`scripts/validate_markdown.py`)**
- **Purpose**: Comprehensive validation of all Markdown files
- **Checks**: Code blocks, headers, metadata, syntax
- **Usage**: `python3 scripts/validate_markdown.py`
- **Output**: Detailed report of errors and warnings

#### **2. Code Block Fixer (`scripts/fix_code_blocks.py`)**
- **Purpose**: Automatically fix unclosed code blocks
- **Usage**: `python3 scripts/fix_code_blocks.py`
- **Target**: Specific files with code block issues

#### **3. Auto-fix Script (`scripts/auto-fix-docs.sh`)**
- **Purpose**: Automatically fix common documentation issues
- **Usage**: `./scripts/auto-fix-docs.sh`
- **Features**: Code block fixing, metadata addition

### **Protection Scripts**

#### **4. Protect Documentation (`scripts/protect-docs.sh`)**
- **Purpose**: Make documentation files read-only
- **Usage**: `./scripts/protect-docs.sh`
- **Features**: Creates backup, sets read-only permissions

#### **5. Unlock Documentation (`scripts/unlock-docs.sh`)**
- **Purpose**: Allow editing of documentation files
- **Usage**: `./scripts/unlock-docs.sh`
- **Features**: Restores write permissions

#### **6. Restore Documentation (`scripts/restore-docs.sh`)**
- **Purpose**: Restore documentation from backup
- **Usage**: `./scripts/restore-docs.sh`
- **Features**: Restores from `.backup/docs/` directory

### **Git Integration**

#### **7. Pre-commit Hook (`scripts/pre-commit-docs.sh`)**
- **Purpose**: Validate documentation before commits
- **Usage**: Run manually or integrate with Git hooks
- **Features**: Prevents commits with documentation errors

## 📋 **Documentation Standards**

### **Required Metadata**
Every documentation file must include:
```markdown
**Author:** Khalid Abdullah  
**Version:** [version number]  
**Date:** [current date]  
**License:** AGPL-3.0
```

### **File Structure**
- **Title**: Single `#` heading
- **Metadata**: Author, version, date, license
- **Content**: Organized with proper heading hierarchy
- **Code Blocks**: Properly opened and closed with ` ``` `

### **Naming Conventions**
- **Files**: Descriptive names with underscores
- **Directories**: Lowercase with descriptive names
- **Versions**: Follow semantic versioning (v0.0.x)

## 🚀 **Workflow for Documentation Changes**

### **1. Unlock Documentation**
```bash
./scripts/unlock-docs.sh
```

### **2. Make Changes**
Edit the documentation files as needed.

### **3. Validate Changes**
```bash
python3 scripts/validate_markdown.py
```

### **4. Fix Any Issues**
```bash
./scripts/auto-fix-docs.sh
```

### **5. Re-validate**
```bash
python3 scripts/validate_markdown.py
```

### **6. Protect Documentation**
```bash
./scripts/protect-docs.sh
```

### **7. Commit Changes**
```bash
git add .
git commit -m "Update documentation: [description]"
```

## 🔍 **Common Issues and Solutions**

### **Unclosed Code Blocks**
- **Symptom**: Odd number of ` ``` ` markers
- **Solution**: Run `./scripts/auto-fix-docs.sh`

### **Missing Metadata**
- **Symptom**: Files without author/version information
- **Solution**: Run `./scripts/auto-fix-docs.sh`

### **Malformed Headers**
- **Symptom**: Empty headers or consecutive headers
- **Solution**: Manual review and correction

### **Broken Links**
- **Symptom**: Internal links pointing to non-existent files
- **Solution**: Update link paths or create missing files

## 📊 **Quality Metrics**

### **Validation Results**
- **Total Files**: 41 Markdown files
- **Target**: 0 errors, 0 warnings
- **Current Status**: ✅ All files valid

### **Coverage Requirements**
- **Metadata**: 100% of files must have complete metadata
- **Code Blocks**: All code blocks must be properly closed
- **Headers**: Proper heading hierarchy and formatting
- **Links**: All internal links must be valid

## 🛡️ **Prevention Measures**

### **Automated Validation**
- Pre-commit hooks prevent bad documentation
- Regular validation runs catch issues early
- Automated fixing scripts resolve common problems

### **File Protection**
- Read-only permissions prevent accidental changes
- Backup system preserves original content
- Version control tracks all changes

### **Quality Gates**
- No commits allowed with documentation errors
- Validation required before deployment
- Regular audits ensure compliance

## 🔄 **Maintenance Schedule**

### **Daily**
- Run validation before any documentation changes
- Check for new files that need metadata

### **Weekly**
- Full documentation validation
- Review and fix any issues found
- Update backup copies

### **Monthly**
- Comprehensive documentation audit
- Update outdated information
- Review and improve standards

## 📞 **Support and Troubleshooting**

### **Getting Help**
1. Run validation to identify issues
2. Check this guide for solutions
3. Use auto-fix scripts for common problems
4. Review backup files if needed

### **Emergency Recovery**
```bash
# Restore from backup
./scripts/restore-docs.sh

# Re-validate
python3 scripts/validate_markdown.py

# Re-protect
./scripts/protect-docs.sh
```

## 🎯 **Best Practices**

### **Before Editing**
1. Always unlock documentation first
2. Review existing standards
3. Plan changes carefully

### **During Editing**
1. Follow established patterns
2. Include required metadata
3. Test code blocks and links

### **After Editing**
1. Validate all changes
2. Fix any issues found
3. Re-protect documentation
4. Commit with descriptive messages

---

**Last Updated:** September 2, 2025  
**Next Update:** With v0.0.6 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0 