# Feature Toggle System Documentation

## Overview

The Feature Toggle System provides administrators with granular control over core platform functionality, allowing them to enable or disable features as needed without code changes. This system ensures a flexible, configurable platform that can adapt to different use cases and requirements.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Available Features](#available-features)
3. [Implementation Details](#implementation-details)
4. [User Experience](#user-experience)
5. [API Integration](#api-integration)
6. [Database Schema](#database-schema)
7. [Configuration](#configuration)
8. [Troubleshooting](#troubleshooting)

## System Architecture

### Core Components

#### Database Layer
- **Table**: `system_settings`
- **Storage**: Key-value pairs with type information
- **Types**: boolean, string, integer, json
- **Default Values**: Sensible defaults for all features

#### Application Layer
- **Function**: `get_system_setting($key, $default)`
- **Caching**: Settings cached for performance
- **Type Handling**: Proper type conversion and validation

#### UI Layer
- **Admin Interface**: System settings page with toggles
- **Conditional Rendering**: UI elements show/hide based on settings
- **User Feedback**: Clear messaging when features are disabled

### Data Flow

```
Admin Toggle → Database Update → Cache Invalidation → UI Update → User Experience
```

## Available Features

### 1. User Registration Control

#### Setting
- **Key**: `allow_registration`
- **Type**: boolean
- **Default**: `true` (enabled)

#### Behavior
- **When Enabled**: Normal registration process
- **When Disabled**: 
  - Registration form hidden
  - Custom message displayed
  - Link to login page provided
  - No automatic redirect

#### Implementation
```php
$allow_registration = get_system_setting('allow_registration', true);
if (!$allow_registration) {
    // Show custom message instead of form
    echo '<div class="registration-closed">Registration Currently Closed</div>';
} else {
    // Show registration form
    include 'registration_form.php';
}
```

#### User Experience
- **Clear Messaging**: Users understand why registration is closed
- **Alternative Path**: Direct link to login page
- **No Confusion**: No automatic redirects or hidden behavior

### 2. Comments System

#### Setting
- **Key**: `enable_comments`
- **Type**: boolean
- **Default**: `true` (enabled)

#### Behavior
- **When Enabled**: Full comment functionality
- **When Disabled**: 
  - Comment buttons hidden
  - Comment APIs return errors
  - Comment sections not displayed

#### Implementation
```php
$enable_comments = get_system_setting('enable_comments', true);
if ($enable_comments) {
    echo '<button class="comment-btn">Add Comment</button>';
}
```

#### API Protection
```php
// In comment API endpoints
$enable_comments = get_system_setting('enable_comments', true);
if (!$enable_comments) {
    echo json_encode(['success' => false, 'message' => 'Comments are disabled']);
    exit;
}
```

### 3. Wiki System

#### Setting
- **Key**: `enable_wiki`
- **Type**: boolean
- **Default**: `true` (enabled)

#### Behavior
- **When Enabled**: Full wiki functionality
- **When Disabled**: 
  - Wiki pages redirect to dashboard
  - Wiki navigation links hidden
  - Article creation disabled

#### Implementation
```php
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'info');
    redirect('/dashboard');
}
```

#### Navigation Control
```php
$enable_wiki = get_system_setting('enable_wiki', true);
if ($enable_wiki) {
    echo '<a href="/wiki">Wiki</a>';
    echo '<a href="/pages/wiki/create_article.php">Create Article</a>';
}
```

### 4. Social Features

#### Setting
- **Key**: `enable_social`
- **Type**: boolean
- **Default**: `true` (enabled)

#### Behavior
- **When Enabled**: Full social functionality
- **When Disabled**: 
  - Friends system disabled
  - Messaging disabled
  - Social navigation hidden

#### Implementation
```php
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    show_message('Social features are currently disabled.', 'info');
    redirect('/dashboard');
}
```

#### API Protection
```php
// In social API endpoints
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    echo json_encode(['success' => false, 'message' => 'Social features are disabled']);
    exit;
}
```

### 5. Analytics Tracking

#### Setting
- **Key**: `enable_analytics`
- **Type**: boolean
- **Default**: `true` (enabled)

#### Behavior
- **When Enabled**: Full analytics tracking
- **When Disabled**: 
  - Analytics tracking skipped
  - Admin analytics page disabled
  - Performance tracking disabled

#### Implementation
```php
$enable_analytics = get_system_setting('enable_analytics', true);
if ($enable_analytics) {
    auto_track_performance();
    auto_track_page_view();
}
```

#### Admin Access Control
```php
$enable_analytics = get_system_setting('enable_analytics', true);
if (!$enable_analytics) {
    show_message('Analytics are currently disabled.', 'info');
    redirect('/admin');
}
```

### 6. Notifications System

#### Setting
- **Key**: `enable_notifications`
- **Type**: boolean
- **Default**: `true` (enabled)

#### Behavior
- **When Enabled**: Toast notifications displayed
- **When Disabled**: 
  - Toast notifications hidden
  - Notification APIs return errors
  - Silent operation mode

#### Implementation
```php
function show_message($message, $type = 'info') {
    if (get_system_setting('enable_notifications', true)) {
        $_SESSION['toast_message'] = ['message' => $message, 'type' => $type];
    }
}
```

## Implementation Details

### Database Schema

#### system_settings Table
```sql
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    type ENUM('boolean', 'string', 'integer', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Default Settings
```sql
INSERT INTO system_settings (`key`, value, type, description) VALUES
('allow_registration', '1', 'boolean', 'Allow new user registrations'),
('enable_comments', '1', 'boolean', 'Enable comment system'),
('enable_wiki', '1', 'boolean', 'Enable wiki system'),
('enable_social', '1', 'boolean', 'Enable social features'),
('enable_analytics', '1', 'boolean', 'Enable analytics tracking'),
('enable_notifications', '1', 'boolean', 'Enable notification system');
```

### Core Functions

#### get_system_setting()
```php
function get_system_setting($key, $default = null) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value, type FROM system_settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();

    if (!$result) {
        return $default;
    }

    switch ($result['type']) {
        case 'boolean':
            return (bool) $result['value'];
        case 'integer':
            return (int) $result['value'];
        case 'json':
            return json_decode($result['value'], true);
        default:
            if ($result['type'] === 'string' && empty($result['value'])) {
                return $default;
            }
            return $result['value'];
    }
}
```

#### show_message()
```php
function show_message($message, $type = 'info') {
    if (get_system_setting('enable_notifications', true)) {
        $_SESSION['toast_message'] = ['message' => $message, 'type' => $type];
    }
}
```

### Frontend Integration

#### JavaScript Toast System
```javascript
function showToast(message, type = 'info') {
    if (!get_system_setting('enable_notifications', true)) {
        return; // Skip if notifications disabled
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
```

#### Conditional Rendering
```php
$enable_wiki = get_system_setting('enable_wiki', true);
if ($enable_wiki) {
    echo '<nav class="wiki-nav">';
    echo '<a href="/wiki">Wiki</a>';
    echo '<a href="/pages/wiki/create_article.php">Create Article</a>';
    echo '</nav>';
}
```

## User Experience

### When Features Are Disabled

#### Clear Messaging
- **Registration**: "Registration Currently Closed" with login link
- **Comments**: Comment buttons hidden, no confusion
- **Wiki**: "Wiki system is currently disabled" message
- **Social**: "Social features are currently disabled" message
- **Analytics**: "Analytics are currently disabled" message

#### Graceful Degradation
- **No Errors**: Features fail gracefully without errors
- **Alternative Paths**: Users directed to available features
- **Consistent UI**: Interface remains clean and functional

#### Admin Feedback
- **Toast Notifications**: Modern notification system
- **Status Indicators**: Clear visual feedback
- **Confirmation Messages**: Action confirmations

### When Features Are Enabled

#### Full Functionality
- **Complete Access**: All features work as expected
- **Normal UI**: All interface elements visible
- **API Access**: All APIs function normally

#### Performance
- **Cached Settings**: Settings cached for performance
- **Efficient Checks**: Minimal performance impact
- **Fast Loading**: Quick feature checks

## API Integration

### Protection Pattern
```php
// Standard pattern for all feature-protected APIs
$feature_enabled = get_system_setting('feature_name', true);
if (!$feature_enabled) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Feature is currently disabled'
    ]);
    exit;
}
```

### Comment APIs
- **add_comment.php**: Checks `enable_comments`
- **get_comments.php**: Checks `enable_comments`

### Social APIs
- **send_message.php**: Checks `enable_social`
- **get_messages.php**: Checks `enable_social`
- **follow_user.php**: Checks `enable_social`

### Analytics APIs
- **track_search.php**: Checks `enable_analytics`

### Notification APIs
- **get_notifications.php**: Checks `enable_notifications`
- **mark_notification_read.php**: Checks `enable_notifications`

## Configuration

### Admin Interface
- **Location**: `/admin/system_settings`
- **Tab**: Features tab
- **Interface**: Toggle switches
- **Persistence**: Tab persistence after saving

### Database Management
- **Migrations**: Automatic database migrations
- **Defaults**: Sensible default values
- **Types**: Proper data type handling

### Caching
- **Settings Cache**: Settings cached for performance
- **Cache Invalidation**: Automatic cache clearing on updates
- **Performance**: Minimal database queries

## Troubleshooting

### Common Issues

#### Feature Not Disabling
- **Check**: Database setting value
- **Verify**: `get_system_setting()` return value
- **Debug**: Add logging to check values

#### UI Elements Still Showing
- **Check**: Conditional rendering logic
- **Verify**: Setting value in template
- **Clear**: Browser cache

#### API Errors
- **Check**: API protection code
- **Verify**: Setting value in API
- **Test**: API endpoints directly

#### Performance Issues
- **Check**: Database query performance
- **Verify**: Caching implementation
- **Monitor**: Query execution time

### Debug Mode
```php
// Enable debug mode for feature toggles
define('FEATURE_DEBUG', true);

if (FEATURE_DEBUG) {
    error_log("Feature check: " . $feature_name . " = " . $feature_value);
}
```

### Testing
```php
// Test feature toggle functionality
function test_feature_toggle($feature_name) {
    $enabled = get_system_setting($feature_name, true);
    $disabled = get_system_setting($feature_name, false);
    
    assert($enabled === true, "Feature should be enabled by default");
    assert($disabled === false, "Feature should be disabled when set to false");
}
```

## Best Practices

### Development
- **Always Check**: Check feature settings before implementing functionality
- **Graceful Degradation**: Ensure features fail gracefully when disabled
- **User Feedback**: Provide clear messaging when features are disabled
- **Performance**: Cache settings for better performance

### Administration
- **Gradual Rollout**: Enable features gradually
- **User Communication**: Inform users of feature changes
- **Testing**: Test features before enabling
- **Monitoring**: Monitor feature usage and performance

### Maintenance
- **Regular Review**: Periodically review feature settings
- **Documentation**: Document feature changes
- **Backup**: Backup settings before major changes
- **Rollback**: Have rollback plans for feature changes

---

This documentation provides comprehensive coverage of the Feature Toggle System. For additional support or questions, please refer to the technical documentation or contact the development team.
