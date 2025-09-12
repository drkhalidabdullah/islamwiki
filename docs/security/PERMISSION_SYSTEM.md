# Permission System Documentation

## Overview

The IslamWiki Permission System provides comprehensive role-based access control (RBAC) with granular permissions for all platform features. This system ensures secure access control while maintaining flexibility for different user types and organizational needs.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Roles and Permissions](#roles-and-permissions)
3. [Permission Categories](#permission-categories)
4. [Implementation Details](#implementation-details)
5. [Admin Interface](#admin-interface)
6. [API Security](#api-security)
7. [Database Schema](#database-schema)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

## System Architecture

### Core Components

#### Database Layer
- **Roles Table**: User role definitions
- **Permissions Table**: Available system permissions
- **Role Permissions Table**: Role-permission mappings
- **User Roles Table**: User-role assignments

#### Application Layer
- **Permission Functions**: Core permission checking functions
- **Role Management**: Role creation and management
- **User Assignment**: User role assignment functions

#### UI Layer
- **Admin Interface**: Permission management interface
- **User Interface**: Role-based UI rendering
- **Access Control**: Page and feature access control

### Permission Flow

```
User Request → Role Check → Permission Check → Access Granted/Denied
```

## Roles and Permissions

### Available Roles

#### Administrator
- **Description**: Full system access and control
- **Permissions**: 20 comprehensive permissions
- **Use Case**: System administrators and super users

**Permissions:**
- `admin.access` - Access admin panel
- `admin.manage_users` - Manage user accounts
- `admin.manage_roles` - Manage roles and permissions
- `admin.system_settings` - Configure system settings
- `admin.view_logs` - View system logs
- `admin.manage_files` - Manage system files
- `admin.manage_categories` - Manage content categories
- `admin.content_moderation` - Moderate content
- `admin.manage_redirects` - Manage URL redirects
- `admin.analytics` - View analytics
- `admin.maintenance` - Access maintenance features
- `wiki.create` - Create wiki articles
- `wiki.edit` - Edit wiki articles
- `wiki.delete` - Delete wiki articles
- `wiki.protect` - Protect wiki articles
- `wiki.upload` - Upload files to wiki
- `wiki.manage_files` - Manage wiki files
- `wiki.manage_redirects` - Manage wiki redirects
- `content.create_post` - Create user posts
- `content.edit_post` - Edit user posts

#### Scholar
- **Description**: Research and content creation focused
- **Permissions**: 8 permissions
- **Use Case**: Academic researchers and content creators

**Permissions:**
- `wiki.create` - Create wiki articles
- `wiki.edit` - Edit wiki articles
- `wiki.upload` - Upload files to wiki
- `content.create_post` - Create user posts
- `content.edit_post` - Edit user posts
- `social.send_messages` - Send private messages
- `social.manage_friends` - Manage friend connections
- `social.view_analytics` - View social analytics

#### Editor
- **Description**: Content editing and management
- **Permissions**: 7 permissions
- **Use Case**: Content editors and moderators

**Permissions:**
- `wiki.create` - Create wiki articles
- `wiki.edit` - Edit wiki articles
- `wiki.upload` - Upload files to wiki
- `content.create_post` - Create user posts
- `content.edit_post` - Edit user posts
- `content.moderate` - Moderate content
- `social.send_messages` - Send private messages

#### Content Reviewer
- **Description**: Content moderation and review
- **Permissions**: 4 permissions
- **Use Case**: Content reviewers and quality assurance

**Permissions:**
- `content.moderate` - Moderate content
- `content.edit_post` - Edit user posts
- `social.send_messages` - Send private messages
- `social.manage_friends` - Manage friend connections

#### Moderator
- **Description**: Content moderation
- **Permissions**: 4 permissions
- **Use Case**: Community moderators

**Permissions:**
- `content.moderate` - Moderate content
- `content.edit_post` - Edit user posts
- `social.send_messages` - Send private messages
- `social.manage_friends` - Manage friend connections

#### Contributor
- **Description**: Basic content contribution
- **Permissions**: 4 permissions
- **Use Case**: Regular content contributors

**Permissions:**
- `content.create_post` - Create user posts
- `content.edit_post` - Edit user posts
- `social.send_messages` - Send private messages
- `social.manage_friends` - Manage friend connections

#### User
- **Description**: Basic user functionality
- **Permissions**: 3 permissions
- **Use Case**: Regular platform users

**Permissions:**
- `content.create_post` - Create user posts
- `social.send_messages` - Send private messages
- `social.manage_friends` - Manage friend connections

#### Guest
- **Description**: Minimal access
- **Permissions**: 1 permission
- **Use Case**: Unregistered or limited access users

**Permissions:**
- `content.view` - View content (basic read access)

## Permission Categories

### Admin Permissions
- **admin.access**: Access admin panel
- **admin.manage_users**: Manage user accounts
- **admin.manage_roles**: Manage roles and permissions
- **admin.system_settings**: Configure system settings
- **admin.view_logs**: View system logs
- **admin.manage_files**: Manage system files
- **admin.manage_categories**: Manage content categories
- **admin.content_moderation**: Moderate content
- **admin.manage_redirects**: Manage URL redirects
- **admin.analytics**: View analytics
- **admin.maintenance**: Access maintenance features

### Wiki Permissions
- **wiki.create**: Create wiki articles
- **wiki.edit**: Edit wiki articles
- **wiki.delete**: Delete wiki articles
- **wiki.protect**: Protect wiki articles
- **wiki.upload**: Upload files to wiki
- **wiki.manage_files**: Manage wiki files
- **wiki.manage_redirects**: Manage wiki redirects

### Content Permissions
- **content.create_post**: Create user posts
- **content.edit_post**: Edit user posts
- **content.delete_post**: Delete user posts
- **content.moderate**: Moderate content
- **content.view**: View content

### Social Permissions
- **social.send_messages**: Send private messages
- **social.manage_friends**: Manage friend connections
- **social.view_analytics**: View social analytics

## Implementation Details

### Core Functions

#### has_role()
```php
function has_role($user_id, $role_name) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM user_roles ur 
        JOIN roles r ON ur.role_id = r.id 
        WHERE ur.user_id = ? AND r.name = ?
    ");
    $stmt->execute([$user_id, $role_name]);
    return $stmt->fetchColumn() > 0;
}
```

#### has_permission()
```php
function has_permission($user_id, $permission) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM user_roles ur 
        JOIN role_permissions rp ON ur.role_id = rp.role_id 
        WHERE ur.user_id = ? AND rp.permission = ?
    ");
    $stmt->execute([$user_id, $permission]);
    return $stmt->fetchColumn() > 0;
}
```

#### is_admin()
```php
function is_admin($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    return $user_id && has_role($user_id, 'admin');
}
```

#### require_admin()
```php
function require_admin() {
    if (!is_admin()) {
        show_message('Access denied. Admin privileges required.', 'error');
        redirect('/dashboard');
    }
}
```

### Permission Checking

#### Page Access Control
```php
// At the top of admin pages
require_admin();

// For specific permissions
if (!has_permission($_SESSION['user_id'], 'admin.manage_users')) {
    show_message('Access denied. Insufficient permissions.', 'error');
    redirect('/admin');
}
```

#### Feature Access Control
```php
// For wiki features
if (!has_permission($_SESSION['user_id'], 'wiki.create')) {
    show_message('You do not have permission to create articles.', 'error');
    redirect('/wiki');
}
```

#### API Access Control
```php
// In API endpoints
if (!has_permission($_SESSION['user_id'], 'content.create_post')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}
```

### Role Management

#### Create Role
```php
function create_role($name, $description, $permissions = []) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        // Create role
        $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $role_id = $pdo->lastInsertId();
        
        // Assign permissions
        foreach ($permissions as $permission) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)");
            $stmt->execute([$role_id, $permission]);
        }
        
        $pdo->commit();
        return $role_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

#### Update Role Permissions
```php
function set_role_permissions($role_id, $permissions) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        // Remove existing permissions
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        
        // Add new permissions
        foreach ($permissions as $permission) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)");
            $stmt->execute([$role_id, $permission]);
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

#### Assign Role to User
```php
function assign_role_to_user($user_id, $role_id) {
    global $pdo;
    
    // Check if user already has this role
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_roles WHERE user_id = ? AND role_id = ?");
    $stmt->execute([$user_id, $role_id]);
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $role_id]);
    }
}
```

## Admin Interface

### Permission Management Page
- **URL**: `/admin/manage_permissions`
- **Access**: Admin privileges required
- **Features**: Role and permission management

### Interface Components

#### Role List
- **Display**: All available roles with descriptions
- **Actions**: Edit, delete, view permissions
- **Status**: Active/inactive role status

#### Permission Grid
- **Layout**: 4-column grid layout
- **Categories**: Organized by permission category
- **Checkboxes**: Perfect vertical alignment
- **Responsive**: Mobile-optimized layout

#### Role Creation
- **Form**: Role name and description
- **Permissions**: Checkbox selection
- **Validation**: Required field validation
- **Feedback**: Success/error messages

#### Role Editing
- **Modal**: Edit role in modal dialog
- **Permissions**: Pre-checked current permissions
- **Updates**: Real-time permission updates
- **Confirmation**: Save confirmation

### Visual Design

#### Checkbox Alignment
```css
.permission-item {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    min-height: 1.5rem;
    padding: 0.25rem 0;
    line-height: 1.4;
}

.permission-item input[type="checkbox"] {
    order: 2;
    width: 16px;
    height: 16px;
    margin: 0;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.permission-item span {
    order: 1;
    flex: 1;
    margin-right: 0.75rem;
}
```

#### Grid Layout
```css
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .permissions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .permissions-grid {
        grid-template-columns: 1fr;
    }
}
```

## API Security

### Admin API Protection
```php
// Standard admin API protection
function require_admin_api() {
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }
}
```

### Permission-Based API Protection
```php
// Permission-specific API protection
function require_permission_api($permission) {
    if (!has_permission($_SESSION['user_id'], $permission)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
}
```

### API Usage Examples
```php
// User management API
require_permission_api('admin.manage_users');

// Content creation API
require_permission_api('content.create_post');

// Wiki management API
require_permission_api('wiki.create');
```

## Database Schema

### Tables

#### roles
```sql
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### permissions
```sql
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### role_permissions
```sql
CREATE TABLE role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission)
);
```

#### user_roles
```sql
CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_id)
);
```

### Default Data

#### Default Roles
```sql
INSERT INTO roles (name, description) VALUES
('admin', 'System Administrator'),
('scholar', 'Research Scholar'),
('editor', 'Content Editor'),
('content_reviewer', 'Content Reviewer'),
('moderator', 'Community Moderator'),
('contributor', 'Content Contributor'),
('user', 'Regular User'),
('guest', 'Guest User');
```

#### Default Permissions
```sql
INSERT INTO permissions (name, description, category) VALUES
('admin.access', 'Access admin panel', 'admin'),
('admin.manage_users', 'Manage user accounts', 'admin'),
('admin.manage_roles', 'Manage roles and permissions', 'admin'),
('admin.system_settings', 'Configure system settings', 'admin'),
('admin.view_logs', 'View system logs', 'admin'),
('admin.manage_files', 'Manage system files', 'admin'),
('admin.manage_categories', 'Manage content categories', 'admin'),
('admin.content_moderation', 'Moderate content', 'admin'),
('admin.manage_redirects', 'Manage URL redirects', 'admin'),
('admin.analytics', 'View analytics', 'admin'),
('admin.maintenance', 'Access maintenance features', 'admin'),
('wiki.create', 'Create wiki articles', 'wiki'),
('wiki.edit', 'Edit wiki articles', 'wiki'),
('wiki.delete', 'Delete wiki articles', 'wiki'),
('wiki.protect', 'Protect wiki articles', 'wiki'),
('wiki.upload', 'Upload files to wiki', 'wiki'),
('wiki.manage_files', 'Manage wiki files', 'wiki'),
('wiki.manage_redirects', 'Manage wiki redirects', 'wiki'),
('content.create_post', 'Create user posts', 'content'),
('content.edit_post', 'Edit user posts', 'content'),
('content.delete_post', 'Delete user posts', 'content'),
('content.moderate', 'Moderate content', 'content'),
('content.view', 'View content', 'content'),
('social.send_messages', 'Send private messages', 'social'),
('social.manage_friends', 'Manage friend connections', 'social'),
('social.view_analytics', 'View social analytics', 'social');
```

## Best Practices

### Security
- **Principle of Least Privilege**: Grant minimum necessary permissions
- **Regular Review**: Periodically review user permissions
- **Role Templates**: Use predefined role templates
- **Access Logging**: Log permission checks and access attempts

### Development
- **Always Check**: Check permissions before implementing functionality
- **Graceful Degradation**: Ensure features fail gracefully when permissions denied
- **User Feedback**: Provide clear messaging when access denied
- **Performance**: Cache permission checks for better performance

### Administration
- **Role Hierarchy**: Maintain clear role hierarchy
- **Documentation**: Document permission changes
- **Testing**: Test permission changes before deployment
- **Monitoring**: Monitor permission usage and access patterns

### Maintenance
- **Regular Cleanup**: Remove unused roles and permissions
- **Permission Audit**: Regular permission audits
- **User Training**: Train users on permission system
- **Backup**: Backup permission data before changes

## Troubleshooting

### Common Issues

#### Permission Not Working
- **Check**: User role assignment
- **Verify**: Role permission mapping
- **Debug**: Add logging to permission checks

#### Role Assignment Issues
- **Check**: User-role relationship
- **Verify**: Role exists and is active
- **Test**: Role assignment functions

#### UI Not Updating
- **Check**: Permission check in template
- **Verify**: Setting value in template
- **Clear**: Browser cache

#### Performance Issues
- **Check**: Database query performance
- **Verify**: Index usage
- **Monitor**: Query execution time

### Debug Mode
```php
// Enable debug mode for permissions
define('PERMISSION_DEBUG', true);

if (PERMISSION_DEBUG) {
    error_log("Permission check: " . $permission . " for user " . $user_id . " = " . $result);
}
```

### Testing
```php
// Test permission system
function test_permission_system() {
    // Test role creation
    $role_id = create_role('test_role', 'Test Role', ['content.create_post']);
    assert($role_id > 0, "Role creation failed");
    
    // Test permission assignment
    assign_role_to_user(1, $role_id);
    assert(has_role(1, 'test_role'), "Role assignment failed");
    
    // Test permission check
    assert(has_permission(1, 'content.create_post'), "Permission check failed");
}
```

---

This documentation provides comprehensive coverage of the Permission System. For additional support or questions, please refer to the technical documentation or contact the development team.
