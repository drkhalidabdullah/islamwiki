# Admin System Documentation

## Overview

The IslamWiki admin system provides comprehensive control over all platform features, user management, and system configuration. This documentation covers all admin features, their functionality, and usage instructions.

## Table of Contents

1. [Admin Dashboard](#admin-dashboard)
2. [System Settings](#system-settings)
3. [User Management](#user-management)
4. [Permission Management](#permission-management)
5. [Maintenance Control](#maintenance-control)
6. [Analytics Dashboard](#analytics-dashboard)
7. [Feature Toggle System](#feature-toggle-system)
8. [API Documentation](#api-documentation)
9. [Security Features](#security-features)
10. [Troubleshooting](#troubleshooting)

## Admin Dashboard

### Access
- **URL**: `/admin`
- **Requirements**: Admin privileges
- **Features**: Unified admin interface with system overview

### Main Sections

#### System Statistics
- **Total Users**: Current user count with active user breakdown
- **Total Articles**: Published and draft article counts
- **Categories**: Content organization statistics
- **Comments**: User interaction metrics
- **Files**: File storage statistics
- **Recent Growth**: 30-day user growth metrics

#### Maintenance Mode Status
- **Current Status**: Real-time maintenance mode indicator
- **Quick Toggle**: Enable/disable maintenance mode
- **Configuration**: Link to detailed maintenance settings
- **Status Details**: Maintenance message and estimated downtime

#### Admin Actions
Organized into three categories:

##### Most Used
- **Create Article**: Quick access to article creation
- **Manage Users**: User account management
- **Analytics**: Site statistics and metrics
- **Settings**: System configuration

##### System Management
- **Maintenance**: System maintenance control
- **Manage Files**: File management
- **Manage Categories**: Content categorization
- **Content Moderation**: Content review and moderation

##### Advanced Tools
- **Manage Redirects**: URL redirect management
- **Manage Permissions**: Role and permission control

#### Recent Activity
- **User Registrations**: New user signups
- **Article Creation**: Recent article additions
- **Activity Timeline**: Chronological activity feed

## System Settings

### Access
- **URL**: `/admin/system_settings`
- **Requirements**: Admin privileges
- **Features**: Comprehensive system configuration

### Tab Organization

#### General Tab
- **Site Information**: Site name, description, keywords
- **Contact Details**: Admin email, contact email
- **Content Settings**: Posts per page, articles per page
- **Copyright**: Copyright text configuration

#### Features Tab
- **Registration Control**: Enable/disable user registration
- **Comments System**: Enable/disable comments
- **Wiki System**: Enable/disable wiki functionality
- **Social Features**: Enable/disable social interactions
- **Analytics**: Enable/disable analytics tracking
- **Notifications**: Enable/disable notification system

#### Security Tab
- **Password Policies**: Minimum length, complexity requirements
- **Session Management**: Timeout settings, security options
- **Login Protection**: Attempt limits, lockout duration
- **Two-Factor Authentication**: 2FA configuration
- **Rate Limiting**: API and form rate limiting

#### Email Tab
- **SMTP Configuration**: Server settings, authentication
- **Email Templates**: Customizable email messages
- **Test Email**: Email functionality testing
- **Notification Settings**: Email notification preferences

#### Statistics Tab
- **Content Statistics**: Visual statistics dashboard
- **System Health**: Real-time system monitoring
- **Storage Information**: Disk usage and capacity
- **Performance Metrics**: System performance indicators

### Tab Persistence
- **Feature**: Maintains current tab after saving changes
- **Implementation**: Session-based tab tracking
- **Benefits**: Improved user experience and workflow

## User Management

### Access
- **URL**: `/admin/manage_users`
- **Requirements**: Admin privileges
- **Features**: Comprehensive user account management

### User List
- **Search**: Real-time user search functionality
- **Filters**: Filter by status, role, registration date
- **Pagination**: Efficient handling of large user lists
- **Sorting**: Sort by name, email, registration date

### User Actions
- **Edit User**: Modify user details and settings
- **Activate/Deactivate**: Enable or disable user accounts
- **Delete User**: Remove user accounts (with confirmation)
- **Role Assignment**: Assign or remove user roles

### Role Management
- **Available Roles**: Administrator, Editor, Moderator, User, Guest
- **Role Assignment**: Easy role assignment interface
- **Permission Inheritance**: Automatic permission inheritance from roles

## Permission Management

### Access
- **URL**: `/admin/manage_permissions`
- **Requirements**: Admin privileges
- **Features**: Granular permission control system

### Role Management
- **Create Roles**: Define new user roles
- **Edit Roles**: Modify existing role permissions
- **Delete Roles**: Remove unused roles
- **Role Hierarchy**: Understand permission inheritance

### Permission Categories

#### Admin Permissions
- **admin.access**: Access admin panel
- **admin.manage_users**: Manage user accounts
- **admin.manage_roles**: Manage roles and permissions
- **admin.system_settings**: Configure system settings
- **admin.view_logs**: View system logs

#### Wiki Permissions
- **wiki.create**: Create wiki articles
- **wiki.edit**: Edit wiki articles
- **wiki.delete**: Delete wiki articles
- **wiki.protect**: Protect wiki articles
- **wiki.upload**: Upload files to wiki
- **wiki.manage_files**: Manage wiki files
- **wiki.manage_redirects**: Manage wiki redirects

#### Content Permissions
- **content.create_post**: Create user posts
- **content.edit_post**: Edit user posts
- **content.delete_post**: Delete user posts
- **content.moderate**: Moderate content

#### Social Permissions
- **social.send_messages**: Send private messages
- **social.manage_friends**: Manage friend connections
- **social.view_analytics**: View social analytics

### Permission Assignment
- **Checkbox Interface**: Visual permission assignment
- **Perfect Alignment**: Vertically aligned checkboxes
- **Bulk Assignment**: Assign multiple permissions at once
- **Role Templates**: Pre-defined permission sets

## Maintenance Control

### Access
- **URL**: `/admin/maintenance`
- **Requirements**: Admin privileges
- **Features**: System maintenance and monitoring

### Maintenance Mode Control
- **Enable/Disable**: Quick maintenance mode toggle
- **Status Indicators**: Visual maintenance mode status
- **Configuration**: Custom maintenance messages
- **Downtime Estimation**: Estimated maintenance duration

### System Health Monitoring
- **Database Status**: Database connection and health
- **Storage Status**: Disk space and usage monitoring
- **Memory Status**: Memory usage and availability
- **PHP Version**: PHP version and configuration

### Cache Management
- **System Cache**: Clear application cache
- **Session Cache**: Clear user session data
- **Log Cache**: Clear system logs
- **All Caches**: Clear all cached data

### Database Optimization
- **Table Optimization**: Optimize database tables
- **Index Maintenance**: Database index optimization
- **Performance Tuning**: Database performance improvements

## Analytics Dashboard

### Access
- **URL**: `/admin/analytics`
- **Requirements**: Admin privileges and analytics enabled
- **Features**: Comprehensive site analytics

### Metrics Available
- **Page Views**: Site traffic and page popularity
- **User Actions**: User interaction tracking
- **Content Analytics**: Article and content performance
- **Search Analytics**: Search query analysis
- **System Performance**: Site performance metrics

### Time Periods
- **Last 24 Hours**: Recent activity
- **Last 7 Days**: Weekly trends
- **Last 30 Days**: Monthly analysis
- **Last 90 Days**: Quarterly overview

### Visualizations
- **Charts**: Interactive data visualizations
- **Trends**: Performance trend analysis
- **Comparisons**: Period-over-period comparisons
- **Export**: Data export capabilities

## Feature Toggle System

### Overview
The feature toggle system provides granular control over core platform functionality, allowing administrators to enable or disable features as needed.

### Available Toggles

#### User Registration
- **Setting**: `allow_registration`
- **Default**: Enabled
- **Behavior**: When disabled, shows custom message instead of redirect
- **User Experience**: Clear feedback about registration status

#### Comments System
- **Setting**: `enable_comments`
- **Default**: Enabled
- **Behavior**: Controls comment functionality across the platform
- **API Protection**: Comment APIs respect this setting

#### Wiki System
- **Setting**: `enable_wiki`
- **Default**: Enabled
- **Behavior**: Controls access to all wiki functionality
- **Navigation**: Wiki links hidden when disabled

#### Social Features
- **Setting**: `enable_social`
- **Default**: Enabled
- **Behavior**: Controls friends, messages, and social interactions
- **API Protection**: Social APIs respect this setting

#### Analytics
- **Setting**: `enable_analytics`
- **Default**: Enabled
- **Behavior**: Controls analytics tracking and admin access
- **Privacy**: Respects user privacy preferences

#### Notifications
- **Setting**: `enable_notifications`
- **Default**: Enabled
- **Behavior**: Controls toast notifications and messaging
- **User Experience**: Consistent notification system

### Implementation
- **Database Storage**: Settings stored in `system_settings` table
- **API Protection**: All APIs check relevant settings
- **UI Conditional**: Interface elements show/hide based on settings
- **Default Values**: Sensible defaults for all settings

## API Documentation

### Admin APIs

#### System Settings API
- **Endpoint**: `/admin/system_settings`
- **Methods**: POST
- **Parameters**: Various based on setting type
- **Response**: Success/error messages

#### User Management API
- **Endpoint**: `/admin/manage_users`
- **Methods**: POST
- **Actions**: create, update, delete, activate, deactivate
- **Response**: User data or error messages

#### Permission Management API
- **Endpoint**: `/admin/manage_permissions`
- **Methods**: POST
- **Actions**: create_role, update_role, assign_role, remove_role
- **Response**: Permission data or error messages

### Feature Toggle APIs

#### Comments API
- **Endpoint**: `/api/ajax/add_comment.php`
- **Check**: `enable_comments` setting
- **Behavior**: Returns error if comments disabled

#### Social API
- **Endpoint**: `/api/ajax/send_message.php`
- **Check**: `enable_social` setting
- **Behavior**: Returns error if social features disabled

#### Analytics API
- **Endpoint**: `/api/ajax/track_search.php`
- **Check**: `enable_analytics` setting
- **Behavior**: Skips tracking if analytics disabled

## Security Features

### Admin Access Control
- **require_admin() Function**: Verifies admin privileges
- **Session Management**: Secure session handling
- **Permission Checks**: Granular permission verification

### API Security
- **Authentication**: All admin APIs require authentication
- **Authorization**: Permission-based access control
- **Rate Limiting**: Protection against abuse
- **Input Validation**: Comprehensive input sanitization

### Feature Security
- **Toggle Protection**: Feature toggles control system access
- **API Protection**: All feature APIs respect toggle settings
- **User Experience**: Clear messaging when features disabled

## Troubleshooting

### Common Issues

#### Maintenance Mode Not Working
- **Check**: Database setting type (should be boolean)
- **Fix**: Update database migration or manually fix type
- **Verify**: `is_maintenance_mode()` function

#### Feature Toggles Not Saving
- **Check**: Form section identification
- **Fix**: Ensure correct `form_section` values
- **Verify**: Form processing logic

#### Permission Checkboxes Misaligned
- **Check**: CSS class conflicts
- **Fix**: Use unique class names
- **Verify**: Grid layout properties

#### Toast Notifications Not Showing
- **Check**: `enable_notifications` setting
- **Fix**: Enable notifications in system settings
- **Verify**: JavaScript console for errors

### Debug Mode
- **Enable**: Set debug mode in configuration
- **Logs**: Check system logs for errors
- **Database**: Verify database connections and queries

### Performance Issues
- **Cache**: Clear all caches
- **Database**: Optimize database tables
- **Logs**: Check for excessive logging
- **Resources**: Monitor server resources

## Best Practices

### Admin Management
- **Regular Backups**: Maintain regular system backups
- **User Monitoring**: Monitor user activity and behavior
- **Security Updates**: Keep system updated with security patches
- **Performance Monitoring**: Regular performance checks

### Feature Toggles
- **Gradual Rollout**: Enable features gradually
- **User Communication**: Inform users of feature changes
- **Testing**: Test features before enabling
- **Monitoring**: Monitor feature usage and performance

### Permission Management
- **Principle of Least Privilege**: Grant minimum necessary permissions
- **Regular Review**: Periodically review user permissions
- **Role Templates**: Use predefined role templates
- **Documentation**: Document permission changes

---

This documentation provides comprehensive coverage of the IslamWiki admin system. For additional support or questions, please refer to the technical documentation or contact the development team.
