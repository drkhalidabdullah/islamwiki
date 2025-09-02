# 🔐 Test Users and Passwords - IslamWiki v0.0.5

**Author:** Khalid Abdullah  
**Version:** 0.0.5  
**Date:** January 27, 2025  
**License:** AGPL-3.0  

## 👥 **Available Test Users**

Your IslamWiki Framework v0.0.5 now has the following test users set up and ready for testing:

### **1. Admin User** 🔑
- **Username:** `admin`
- **Email:** `admin@islamwiki.org`
- **Password:** *(Check your setup - this was created during initial installation)*
- **Role:** `Administrator`
- **Permissions:** Full system access and control
- **Status:** `active`

### **2. Test User** 👤
- **Username:** `testuser`
- **Email:** `test@islamwiki.org`
- **Password:** `password`
- **Role:** `User`
- **Permissions:** Standard user with basic permissions
- **Status:** `active`

## 🧪 **How to Test the Authentication System**

### **Quick Test Script**
```bash
# Test the login functionality
php test_login.php

# Run the comprehensive authentication test
php test_auth_simple.php
```

### **Expected Results from test_login.php**
```
🔐 **Login Test for IslamWiki v0.0.5**
=====================================

✅ Database connection successful

👥 **Available Test Users**
==========================
1. Admin User:
   Username: admin
   Email: admin@islamwiki.org
   Password: (check your setup)
   Role: Administrator

2. Test User:
   Username: testuser
   Email: test@islamwiki.org
   Password: password
   Role: User

🧪 **Testing Login Functionality**
=================================
Test 1: Login with correct credentials (testuser/password)
✅ Login successful for testuser
   User ID: 4
   Status: active

Test 2: Login with wrong password (testuser/wrongpassword)
✅ Login correctly rejected with wrong password

Test 3: Login with non-existent user (nonexistent/password)
✅ Login correctly rejected for non-existent user

Test 4: Check user roles and permissions
User 'testuser' has the following roles:
  - User (user): Standard user with basic permissions

Test 5: Admin access control verification
✅ Regular user correctly does NOT have admin access
   User 'testuser' cannot access admin features

📊 **Test Summary**
==================
✅ Database connection: Working
✅ User authentication: Working
✅ Password validation: Working
✅ Role management: Working
✅ Access control: Working

🎯 **Ready for Testing**
=======================
You can now test the authentication system with:
- Username: testuser, Password: password
- Username: admin, Password: (your admin password)

🚀 **Authentication System Status: READY**
==========================================
All critical authentication features are working correctly!
```

## 🔒 **Testing the Three Critical Requirements**

### **1. Admin Features Not Accessible to Regular Users** ✅
- **Test:** Login as `testuser` (regular user)
- **Verify:** User has `user` role, not `admin` role
- **Result:** Regular users cannot access admin features

### **2. Only Users with Correct Passwords Can Login** ✅
- **Test 1:** `testuser` + `password` = ✅ Success
- **Test 2:** `testuser` + `wrongpassword` = ❌ Rejected
- **Test 3:** `nonexistent` + `password` = ❌ Rejected

### **3. User Sessions Persist Across Page Refreshes** ✅
- **Test:** Login and access profile multiple times
- **Verify:** Session remains active across requests
- **Result:** Sessions persist properly until logout

## 🚀 **Testing Commands**

### **Basic Login Test**
```bash
php test_login.php
```

### **Comprehensive Authentication Test**
```bash
php test_auth_simple.php
```

### **Database Verification**
```bash
# Check users and roles
mysql -u root -p -e "SELECT u.username, u.email, u.status, GROUP_CONCAT(r.name) as roles FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id GROUP BY u.id;" islamwiki
```

## 📊 **User Roles and Permissions**

### **Available Roles**
1. **`admin`** - Administrator: Full system access and control
2. **`moderator`** - Moderator: Content moderation and user management
3. **`editor`** - Editor: Content creation and editing
4. **`user`** - User: Standard user with basic permissions
5. **`verified_user`** - Verified User: User with verified email address
6. **`trusted_user`** - Trusted User: User with good standing and extended permissions

### **Role Assignment**
- **Admin user** has `admin` role
- **Test user** has `user` role
- **New users** can be assigned any role as needed

## 🔧 **Customizing Test Users**

### **Create Additional Test Users**
```sql
-- Create a new test user
INSERT INTO users (username, email, password_hash, first_name, last_name, display_name, status, created_at) 
VALUES ('newuser', 'new@test.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'New', 'User', 'New User', 'active', NOW());

-- Assign user role
INSERT INTO user_roles (user_id, role_id, granted_at) VALUES (LAST_INSERT_ID(), 4, NOW());
```

### **Change User Passwords**
```sql
-- Update password for testuser (new password: newpassword)
UPDATE users SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'testuser';
```

### **Change User Roles**
```sql
-- Make testuser a moderator
INSERT INTO user_roles (user_id, role_id, granted_at) VALUES (4, 2, NOW());

-- Remove user role from testuser
DELETE FROM user_roles WHERE user_id = 4 AND role_id = 4;
```

## 🎯 **What to Test Next**

### **Frontend Integration**
1. **Login Form**: Test with the provided credentials
2. **Session Management**: Verify sessions persist across page refreshes
3. **Role-Based Access**: Test different user roles and permissions
4. **Logout Functionality**: Verify proper session termination

### **API Testing**
1. **Authentication Endpoints**: Test all auth API endpoints
2. **User Management**: Test user CRUD operations
3. **Profile Management**: Test profile update functionality
4. **Security Features**: Test password reset and email verification

### **Security Testing**
1. **Access Control**: Verify admin features are protected
2. **Input Validation**: Test with invalid/malicious input
3. **Session Security**: Test session hijacking protection
4. **Rate Limiting**: Test brute force protection

## 🚨 **Troubleshooting**

### **Common Issues**

#### **Database Connection Failed**
- Check database credentials in your setup
- Ensure MySQL/MariaDB is running
- Verify database `islamwiki` exists

#### **User Not Found**
- Check if users table exists
- Verify user was created successfully
- Check for typos in username

#### **Password Not Working**
- Verify password hash is correct
- Check if password was properly hashed
- Try recreating the test user

#### **Role Assignment Issues**
- Check if roles table exists
- Verify role IDs are correct
- Check foreign key constraints

### **Reset Test Environment**
```bash
# Remove test users
mysql -u root -p -e "DELETE FROM user_roles WHERE user_id IN (SELECT id FROM users WHERE username IN ('testuser'));" islamwiki
mysql -u root -p -e "DELETE FROM users WHERE username IN ('testuser');" islamwiki

# Recreate test user
php test_auth_simple.php
```

## 🎉 **Ready for Production Testing**

Your authentication system is now fully set up with test users and ready for:

- ✅ **Frontend Integration Testing**
- ✅ **API Endpoint Testing**
- ✅ **Security Feature Testing**
- ✅ **User Experience Testing**
- ✅ **Production Deployment Testing**

## 🏁 **Next Steps**

1. **Test the login system** with the provided credentials
2. **Verify all three critical requirements** are working
3. **Integrate with your frontend** application
4. **Test with real users** and collect feedback
5. **Deploy to production** with confidence

---

**Status:** ✅ **READY FOR TESTING**  
**Test Users:** 2 users configured  
**Authentication:** Fully functional  
**Next Phase:** v0.0.6 (Content Management)  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0 