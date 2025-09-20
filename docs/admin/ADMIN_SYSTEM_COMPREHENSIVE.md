# Admin System Comprehensive Documentation

## Overview

The IslamWiki Admin System is a comprehensive administrative interface that provides complete control over the platform's functionality, user management, content moderation, and system configuration. The system features a modern, responsive design with role-based access control and real-time monitoring capabilities.

## 🎯 Current Version: 0.0.0.17

**Last Updated:** January 2025  
**Status:** Production Ready ✅

## 🚀 Key Features

### 🎛️ **Unified Admin Dashboard**
- **Centralized Control**: Single interface for all administrative functions
- **Organized Sections**: Categorized admin tools for easy navigation
- **Real-time Monitoring**: Live system health indicators and statistics
- **Quick Actions**: Fast access to common administrative tasks
- **Modern UI**: Clean, professional interface with consistent styling

### 🔧 **Feature Toggle System**
- **Core Feature Control**: Enable/disable major system features
- **Registration Control**: Manage user registration settings
- **Content Control**: Control comments, wiki, and social features
- **Analytics Control**: Enable/disable analytics and tracking
- **Notification Control**: Manage notification system settings

### 👥 **User Management**
- **User Administration**: Complete user account management
- **Role Assignment**: Assign and modify user roles and permissions
- **User Search**: Advanced user search and filtering
- **Bulk Operations**: Mass user operations and management
- **User Statistics**: Comprehensive user analytics and reporting

### 🛡️ **Permission Management**
- **Role-Based Access Control**: Granular permission system
- **Permission Groups**: Organized permission categories
- **User Roles**: Predefined roles with appropriate permissions
- **Custom Permissions**: Create custom permission sets
- **Access Logging**: Track permission changes and access

### 📊 **System Health Monitoring**
- **Database Status**: Real-time database health indicators
- **Storage Monitoring**: Disk space and file system monitoring
- **Memory Usage**: PHP memory usage and optimization
- **PHP Version**: Current PHP version and compatibility
- **Performance Metrics**: System performance indicators

### 🔧 **System Settings**
- **General Settings**: Basic site configuration
- **Security Settings**: Security and authentication options
- **Email Settings**: Email configuration and templates
- **File Upload Settings**: Upload limits and file type restrictions
- **Cache Settings**: Caching configuration and management

## 🛠️ Technical Implementation

### **File Structure**
```
public/
├── pages/admin/
│   ├── index.php                 # Main admin dashboard
│   ├── manage_users.php          # User management
│   ├── system_settings.php       # System configuration
│   ├── content_moderation.php    # Content moderation
│   ├── analytics.php             # Analytics dashboard
│   ├── permissions.php           # Permission management
│   ├── maintenance.php           # Maintenance mode
│   ├── logs.php                  # System logs
│   ├── backup.php                # Backup management
│   ├── security.php              # Security settings
│   ├── notifications.php         # Notification management
│   ├── themes.php                # Theme management
│   └── extensions.php            # Extension management
├── api/admin/
│   ├── user_management.php       # User management API
│   ├── system_health.php         # System health API
│   ├── feature_toggles.php       # Feature toggle API
│   └── analytics_data.php        # Analytics API
└── skins/bismillah/assets/
    ├── css/admin.css             # Admin styling
    └── js/admin.js               # Admin JavaScript
```

### **Core Components**

#### 1. **Admin Dashboard Layout**
```php
// Main admin dashboard structure
<div class="admin-dashboard">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <div class="admin-actions">
            <button class="btn btn-primary">Quick Actions</button>
        </div>
    </div>
    
    <div class="admin-content">
        <div class="admin-sidebar">
            <!-- Navigation menu -->
        </div>
        
        <div class="admin-main">
            <!-- Main content area -->
        </div>
    </div>
</div>
```

#### 2. **Feature Toggle System**
```php
class FeatureToggleSystem {
    private $features = [
        'registration' => true,
        'comments' => true,
        'wiki' => true,
        'social' => true,
        'analytics' => true,
        'notifications' => true
    ];
    
    public function toggleFeature($feature, $enabled) {
        // Update feature status
        $this->features[$feature] = $enabled;
        
        // Save to database
        $this->saveFeatureSettings();
        
        // Log the change
        $this->logFeatureChange($feature, $enabled);
    }
    
    public function isFeatureEnabled($feature) {
        return isset($this->features[$feature]) ? $this->features[$feature] : false;
    }
}
```

#### 3. **User Management System**
```php
class UserManagement {
    public function getUsers($filters = []) {
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id";
        
        if (!empty($filters['search'])) {
            $query .= " WHERE u.username LIKE :search OR u.email LIKE :search";
        }
        
        if (!empty($filters['role'])) {
            $query .= " AND r.name = :role";
        }
        
        return $this->db->query($query, $filters);
    }
    
    public function updateUserRole($userId, $roleId) {
        // Update user role
        $this->db->query(
            "UPDATE user_roles SET role_id = :role_id WHERE user_id = :user_id",
            ['role_id' => $roleId, 'user_id' => $userId]
        );
        
        // Log the change
        $this->logUserRoleChange($userId, $roleId);
    }
}
```

## 📊 **Admin Dashboard Sections**

### **1. Overview Dashboard**
- **System Statistics**: User count, article count, post count
- **Recent Activity**: Latest user registrations, content updates
- **System Health**: Database, storage, memory status
- **Quick Actions**: Common administrative tasks
- **Alerts**: System alerts and notifications

### **2. User Management**
- **User List**: Paginated list of all users
- **User Search**: Advanced search and filtering
- **Role Assignment**: Assign roles to users
- **User Details**: View and edit user information
- **Bulk Operations**: Mass user operations

### **3. Content Moderation**
- **Pending Content**: Content awaiting approval
- **Reported Content**: Content reported by users
- **Content Review**: Review and moderate content
- **Moderation Queue**: Queue of content to be moderated
- **Moderation Logs**: History of moderation actions

### **4. System Settings**
- **General Settings**: Basic site configuration
- **Feature Toggles**: Enable/disable system features
- **Security Settings**: Security and authentication
- **Email Settings**: Email configuration
- **File Upload Settings**: Upload limits and restrictions

### **5. Analytics Dashboard**
- **User Analytics**: User registration and activity
- **Content Analytics**: Article and post statistics
- **Search Analytics**: Search query analysis
- **Performance Metrics**: System performance data
- **Custom Reports**: Generate custom reports

### **6. Permission Management**
- **Role Management**: Create and manage user roles
- **Permission Assignment**: Assign permissions to roles
- **Permission Groups**: Organize permissions by category
- **Access Control**: Fine-grained access control
- **Permission Logs**: Track permission changes

## 🎨 **UI/UX Design**

### **Color Scheme**
```css
:root {
    --admin-primary: #007bff;
    --admin-secondary: #6c757d;
    --admin-success: #28a745;
    --admin-warning: #ffc107;
    --admin-danger: #dc3545;
    --admin-info: #17a2b8;
    --admin-light: #f8f9fa;
    --admin-dark: #343a40;
}
```

### **Layout Structure**
- **Header**: Site title, user info, quick actions
- **Sidebar**: Navigation menu with icons
- **Main Content**: Dynamic content area
- **Footer**: System status and version info

### **Responsive Design**
- **Desktop**: Full sidebar and main content layout
- **Tablet**: Collapsible sidebar with overlay
- **Mobile**: Stacked layout with mobile navigation

## 🔒 **Security Features**

### **Authentication & Authorization**
- **Role-Based Access**: Different access levels for different admin functions
- **Session Management**: Secure session handling
- **CSRF Protection**: Cross-site request forgery protection
- **Input Validation**: Comprehensive input validation and sanitization

### **Permission System**
```php
class PermissionSystem {
    private $permissions = [
        'admin.dashboard' => 'Access admin dashboard',
        'admin.users' => 'Manage users',
        'admin.content' => 'Moderate content',
        'admin.settings' => 'Manage system settings',
        'admin.analytics' => 'View analytics',
        'admin.permissions' => 'Manage permissions'
    ];
    
    public function hasPermission($userId, $permission) {
        $userRoles = $this->getUserRoles($userId);
        foreach ($userRoles as $role) {
            if ($this->roleHasPermission($role, $permission)) {
                return true;
            }
        }
        return false;
    }
}
```

### **Audit Logging**
- **Action Logging**: Log all administrative actions
- **User Tracking**: Track which admin performed which action
- **Change History**: Complete history of system changes
- **Security Events**: Log security-related events

## 📊 **System Health Monitoring**

### **Database Health**
```php
class DatabaseHealth {
    public function getHealthStatus() {
        return [
            'status' => $this->checkConnection(),
            'uptime' => $this->getUptime(),
            'connections' => $this->getConnectionCount(),
            'queries' => $this->getQueryStats(),
            'size' => $this->getDatabaseSize()
        ];
    }
}
```

### **Storage Monitoring**
```php
class StorageHealth {
    public function getStorageStatus() {
        return [
            'total_space' => $this->getTotalSpace(),
            'used_space' => $this->getUsedSpace(),
            'free_space' => $this->getFreeSpace(),
            'usage_percentage' => $this->getUsagePercentage()
        ];
    }
}
```

### **Memory Usage**
```php
class MemoryHealth {
    public function getMemoryStatus() {
        return [
            'memory_limit' => ini_get('memory_limit'),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'usage_percentage' => $this->getUsagePercentage()
        ];
    }
}
```

## 🚀 **API Endpoints**

### **User Management API**
```php
// GET /api/admin/users
// Get list of users with pagination and filtering
{
    "users": [...],
    "pagination": {...},
    "filters": {...}
}

// POST /api/admin/users/{id}/role
// Update user role
{
    "role_id": 2,
    "success": true
}
```

### **System Health API**
```php
// GET /api/admin/health
// Get system health status
{
    "database": {...},
    "storage": {...},
    "memory": {...},
    "overall": "healthy"
}
```

### **Feature Toggle API**
```php
// POST /api/admin/features/{feature}
// Toggle feature on/off
{
    "feature": "registration",
    "enabled": true,
    "success": true
}
```

## 🧪 **Testing**

### **Unit Tests**
```php
class AdminSystemTest extends PHPUnit\Framework\TestCase {
    public function testFeatureToggle() {
        $admin = new AdminSystem();
        $admin->toggleFeature('registration', false);
        $this->assertFalse($admin->isFeatureEnabled('registration'));
    }
    
    public function testUserRoleAssignment() {
        $userMgmt = new UserManagement();
        $userMgmt->updateUserRole(1, 2);
        $this->assertEquals(2, $userMgmt->getUserRole(1));
    }
}
```

### **Integration Tests**
- Test admin dashboard functionality
- Test user management operations
- Test permission system
- Test feature toggle system

## 📚 **Usage Examples**

### **Enabling/Disabling Features**
```php
// Disable user registration
$admin = new AdminSystem();
$admin->toggleFeature('registration', false);

// Enable wiki system
$admin->toggleFeature('wiki', true);
```

### **Managing User Roles**
```php
// Assign admin role to user
$userMgmt = new UserManagement();
$userMgmt->updateUserRole($userId, $adminRoleId);

// Check user permissions
$permissions = new PermissionSystem();
if ($permissions->hasPermission($userId, 'admin.users')) {
    // User can manage users
}
```

### **System Health Check**
```php
// Check system health
$health = new SystemHealth();
$status = $health->getOverallStatus();

if ($status['overall'] === 'healthy') {
    // System is running well
} else {
    // Handle system issues
}
```

## 🔧 **Configuration**

### **Admin Settings**
```php
// config/admin.php
return [
    'features' => [
        'registration' => true,
        'comments' => true,
        'wiki' => true,
        'social' => true,
        'analytics' => true,
        'notifications' => true
    ],
    'permissions' => [
        'admin.dashboard' => 'Access admin dashboard',
        'admin.users' => 'Manage users',
        'admin.content' => 'Moderate content',
        'admin.settings' => 'Manage system settings'
    ],
    'roles' => [
        'super_admin' => ['admin.*'],
        'admin' => ['admin.dashboard', 'admin.users', 'admin.content'],
        'moderator' => ['admin.content']
    ]
];
```

### **Database Schema**
```sql
-- Admin system tables
CREATE TABLE admin_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    role_id INT,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(255),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);
```

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Admin Dashboard Not Loading**
- Check user permissions
- Verify database connection
- Check for PHP errors in logs

#### **Feature Toggles Not Working**
- Verify database settings table
- Check cache settings
- Ensure proper permissions

#### **User Management Issues**
- Check user role assignments
- Verify permission system
- Check database constraints

### **Debug Mode**
Enable debug mode to see:
- Detailed error messages
- Database query logs
- Permission checks
- System health details

## 🚀 **Future Enhancements**

### **Planned Features**
- **Advanced Analytics**: More detailed analytics and reporting
- **Automated Moderation**: AI-powered content moderation
- **Bulk Operations**: Enhanced bulk user and content operations
- **Custom Dashboards**: Customizable admin dashboards
- **API Management**: Advanced API management tools

### **Technical Improvements**
- **Real-time Updates**: WebSocket-based real-time updates
- **Advanced Caching**: Better caching for admin operations
- **Performance Monitoring**: Enhanced performance monitoring
- **Security Enhancements**: Additional security features

---

**Last Updated:** January 2025  
**Version:** 0.0.0.17  
**Status:** Production Ready ✅
