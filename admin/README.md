# IslamWiki Framework - Admin Area

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🛠️ **Admin Area Structure**

This directory contains administrative tools and maintenance scripts for the IslamWiki Framework.

```
admin/
├── maintenance/             # System maintenance tools
├── tools/                   # Administrative utilities
├── backup/                  # Backup and restore tools
└── README.md               # This file
```

## 🔧 **Maintenance Tools**

### **System Health Check**
```bash
php admin/maintenance/health_check.php
```

### **Cache Management**
```bash
php admin/maintenance/cache_clear.php
php admin/maintenance/cache_warm.php
```

### **Database Maintenance**
```bash
php admin/maintenance/db_optimize.php
php admin/maintenance/db_backup.php
```

## 🛠️ **Administrative Tools**

### **User Management**
```bash
php admin/tools/user_manager.php
php admin/tools/role_manager.php
```

### **Content Management**
```bash
php admin/tools/content_analyzer.php
php admin/tools/seo_checker.php
```

### **System Configuration**
```bash
php admin/tools/config_manager.php
php admin/tools/security_checker.php
```

## 💾 **Backup & Restore**

### **Full System Backup**
```bash
php admin/backup/full_backup.php
```

### **Database Backup**
```bash
php admin/backup/db_backup.php
```

### **Restore System**
```bash
php admin/backup/restore.php
```

## 🔒 **Security Notes**

- **Access Control**: All admin scripts require proper authentication
- **File Permissions**: Ensure admin scripts are not publicly accessible
- **Logging**: All admin actions are logged for audit purposes
- **Backup Security**: Backup files contain sensitive data - secure appropriately

## 📋 **Usage Guidelines**

1. **Always backup** before running maintenance scripts
2. **Test scripts** in development environment first
3. **Monitor logs** during maintenance operations
4. **Schedule maintenance** during low-traffic periods
5. **Document changes** made through admin tools

## 🚨 **Emergency Procedures**

### **System Recovery**
```bash
# Emergency restore from latest backup
php admin/backup/emergency_restore.php

# Reset to factory defaults (WARNING: Data loss)
php admin/maintenance/factory_reset.php
```

### **Contact Information**
- **Developer**: Khalid Abdullah
- **Emergency**: Check logs in `storage/logs/`
- **Documentation**: See main project README

---

**Status:** 🚧 **Under Development**  
**Last Updated:** August 30, 2025  
**Maintainer:** Khalid Abdullah 