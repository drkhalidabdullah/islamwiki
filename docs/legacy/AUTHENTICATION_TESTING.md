# Authentication Testing Guide for IslamWiki v0.0.5

**Author:** Khalid Abdullah  
**Version:** 0.0.5  
**Date:** 2025-01-27  
**License:** AGPL-3.0  

## 🔐 **Testing Overview**

This guide explains how to test the three critical authentication requirements for IslamWiki v0.0.5:

1. **Admin features are not accessible to regular users**
2. **Only users with correct passwords can login**
3. **User sessions persist across page refreshes**

## 🧪 **Test Scripts Available**

### **1. Core Authentication Test (`test_auth_core.php`)**
- **Purpose**: Basic database connectivity and table verification
- **Use Case**: Quick verification that the database is properly set up
- **Command**: `php test_auth_core.php`

### **2. Manual Authentication Test (`manual_auth_test.php`)**
- **Purpose**: Comprehensive testing of all three requirements
- **Use Case**: Full authentication system validation
- **Command**: `php manual_auth_test.php`

### **3. Full Security Test Suite (`test_authentication_security.php`)**
- **Purpose**: Complete security testing with detailed reporting
- **Use Case**: Production deployment validation
- **Command**: `php test_authentication_security.php`

## 🚀 **Quick Start Testing**

### **Step 1: Verify Database Setup**
```bash
# Run the core test to verify database connectivity
php test_auth_core.php
```

**Expected Output:**
```
🔐 **Core Authentication Test v0.0.5**
=====================================

🧪 **Test 1: Database Connection**
----------------------------------
✅ Database connection successful

🧪 **Test 2: Users Table Check**
--------------------------------
✅ Users table exists
📊 Total users in database: X

🧪 **Test 3: Roles Table Check**
--------------------------------
✅ Roles table exists
📊 Total roles in database: X
```

### **Step 2: Run Full Authentication Test**
```bash
# Run the comprehensive authentication test
php manual_auth_test.php
```

**Expected Output:**
```
🔐 **Manual Authentication Test v0.0.5**
=======================================

✅ Services initialized successfully

🧪 **Test 1: Admin Access Control**
==================================
✅ Test admin user created successfully
✅ Admin role assigned to test admin user
✅ Test regular user created successfully

🔒 **Test 2: Login Security**
============================
✅ Correct credentials: Login successful
✅ Wrong password: Login correctly rejected
✅ Non-existent user: Login correctly rejected

🔄 **Test 3: Session Persistence**
==================================
✅ Profile access successful immediately after login
✅ Profile access successful after delay
✅ Profile access successful for multiple consecutive requests
✅ Logout successful
✅ Session correctly terminated after logout

🔒 **Test 4: Admin Access Control**
==================================
✅ Regular user correctly does not have admin role
User roles: user

🧹 **Cleaning Up Test Data**
============================
✅ Test admin user deleted
✅ Test regular user deleted
✅ All test data cleaned up successfully

📊 **Test Summary**
==================
✅ Test users created and deleted
✅ Login security verified
✅ Session persistence tested
✅ Admin access control verified

🎯 **Authentication System Status**
==================================
🔐 Login Security: VERIFIED
🔄 Session Persistence: VERIFIED
🔒 Admin Access Control: VERIFIED
✅ All critical authentication features working correctly!
```

## 🔍 **What Each Test Verifies**

### **Test 1: Admin Access Control**
- ✅ Creates test admin user with admin role
- ✅ Creates test regular user with user role
- ✅ Verifies regular users cannot access admin features
- ✅ Confirms role-based access control works

### **Test 2: Login Security**
- ✅ Correct username + password = successful login
- ✅ Correct username + wrong password = login rejected
- ✅ Wrong username + correct password = login rejected
- ✅ Non-existent user = login rejected
- ✅ Empty credentials = login rejected

### **Test 3: Session Persistence**
- ✅ Profile access immediately after login
- ✅ Profile access after simulated page refresh
- ✅ Multiple consecutive profile accesses
- ✅ Proper logout functionality
- ✅ Session termination after logout

### **Test 4: Admin Feature Protection**
- ✅ Regular users cannot access admin endpoints
- ✅ Role verification for access control
- ✅ Proper permission separation

## 🛠️ **Prerequisites**

### **Database Setup**
1. **Run v0.0.5 migration**: `php setup_database_v0_0_5.php`
2. **Verify database connection** in `.env` file
3. **Ensure all tables exist** with proper structure

### **Environment Configuration**
```env
DB_HOST=localhost
DB_DATABASE=islamwiki
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### **File Permissions**
```bash
# Ensure test scripts are executable
chmod +x test_auth_core.php
chmod +x manual_auth_test.php
chmod +x test_authentication_security.php
```

## 📊 **Interpreting Test Results**

### **✅ All Tests Pass**
- Authentication system is working correctly
- Ready for production use
- All security requirements met

### **⚠️ Some Tests Fail**
- Check database connectivity
- Verify migration was run successfully
- Check environment configuration
- Review error messages for specific issues

### **❌ Critical Tests Fail**
- Do not deploy to production
- Fix identified issues first
- Re-run tests after fixes
- Contact development team if issues persist

## 🔒 **Security Verification Checklist**

### **Login Security**
- [ ] Only correct credentials allow login
- [ ] Wrong passwords are rejected
- [ ] Non-existent users are rejected
- [ ] Empty credentials are rejected
- [ ] Password strength validation works

### **Session Management**
- [ ] Sessions persist across page refreshes
- [ ] Sessions terminate on logout
- [ ] Tokens expire properly
- [ ] Multiple concurrent sessions work

### **Access Control**
- [ ] Regular users cannot access admin features
- [ ] Role-based permissions work correctly
- [ ] Admin users have proper access
- [ ] Permission inheritance works

## 🚨 **Common Issues & Solutions**

### **Database Connection Failed**
```bash
# Check database credentials
mysql -u your_username -p your_database

# Verify database exists
SHOW DATABASES;

# Check user permissions
SHOW GRANTS FOR 'your_username'@'localhost';
```

### **Tables Missing**
```bash
# Run migration script
php setup_database_v0_0_5.php

# Check table structure
DESCRIBE users;
DESCRIBE roles;
DESCRIBE user_roles;
```

### **Permission Denied**
```bash
# Check file permissions
ls -la *.php

# Fix permissions if needed
chmod 644 *.php
```

## 📈 **Performance Testing**

### **Session Persistence Test**
```bash
# Test with multiple concurrent users
for i in {1..10}; do
  php manual_auth_test.php &
done
wait
```

### **Load Testing**
```bash
# Test with multiple rapid requests
for i in {1..100}; do
  curl -s "http://localhost/auth/profile" > /dev/null &
done
wait
```

## 🎯 **Production Deployment Checklist**

Before deploying to production, ensure:

- [ ] All authentication tests pass
- [ ] Database migration completed successfully
- [ ] Environment variables configured correctly
- [ ] File permissions set properly
- [ ] SSL/HTTPS configured
- [ ] Security headers enabled
- [ ] Rate limiting configured
- [ ] Monitoring and logging enabled

## 📞 **Support & Troubleshooting**

### **Getting Help**
1. **Check error messages** in test output
2. **Verify database setup** with core test
3. **Review environment configuration**
4. **Check file permissions and paths**

### **Debug Mode**
Enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### **Log Files**
Check application logs for detailed error information:
```bash
tail -f storage/logs/app.log
```

---

## 🎉 **Success Criteria**

**Authentication System is Ready When:**
- ✅ All tests pass without errors
- ✅ Login security verified
- ✅ Session persistence confirmed
- ✅ Admin access control working
- ✅ No critical security vulnerabilities
- ✅ Performance meets requirements

**Ready for Production Use! 🚀**

---

**Last Updated:** January 27, 2025  
**Next Update:** With v0.0.6 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** ✅ **READY FOR TESTING** 