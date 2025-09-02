# IslamWiki Framework - Admin Area

**Author:** Khalid Abdullah  
**Version:** 0.0.5  
**Date:** 2025-09-02  
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

## 🎉 **Admin Dashboard - Real-Time Data Integration**

### **✅ COMPLETED - Live Admin Dashboard**
The admin dashboard now provides real-time data integration with live database statistics:

- **Live User Statistics**: Total users, active users, inactive users, new users today
- **Role Distribution**: Real-time role-based user counts and distribution
- **System Information**: Live PHP version, MySQL version, server time
- **Memory Usage**: Real-time current and peak memory usage
- **User Activity**: Recent user login times, last seen, and role information
- **Performance Metrics**: Live system performance and resource usage

### **API Endpoints**
- **`GET /api/admin`**: Comprehensive admin dashboard data
- **Real-Time Updates**: Data refreshes automatically
- **Database Integration**: Direct MySQL connection with optimized queries
- **Performance Optimized**: Efficient queries with proper indexing

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

## 📊 **Real-Time Monitoring Features**

### **Live System Metrics**
- **User Activity**: Real-time tracking of user login times and activity
- **System Performance**: Live memory usage and performance monitoring
- **Database Health**: Real-time database connection and query performance
- **Security Events**: Live security event monitoring and logging

### **Admin Dashboard Components**
- **Overview Stats**: Live user counts and system statistics
- **System Status**: Real-time system health indicators
- **User Role Distribution**: Dynamic role-based user analysis
- **Recent Activity**: Live user activity timeline
- **Performance Metrics**: Real-time resource usage monitoring

---

**Status:** ✅ **ADMIN DASHBOARD COMPLETE - Real-Time Data Integration Active**  
**Last Updated:** September 2, 2025  
**Maintainer:** Khalid Abdullah 