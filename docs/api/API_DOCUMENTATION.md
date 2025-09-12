# API Documentation

## Overview

The IslamWiki API provides comprehensive access to all platform functionality through RESTful endpoints. This documentation covers all available APIs, their parameters, responses, and usage examples.

## Table of Contents

1. [Authentication](#authentication)
2. [Admin APIs](#admin-apis)
3. [Feature Toggle APIs](#feature-toggle-apis)
4. [Permission APIs](#permission-apis)
5. [Content APIs](#content-apis)
6. [Social APIs](#social-apis)
7. [Analytics APIs](#analytics-apis)
8. [Error Handling](#error-handling)
9. [Rate Limiting](#rate-limiting)
10. [Examples](#examples)

## Authentication

### Session-Based Authentication
All APIs require valid user sessions. Users must be logged in to access most endpoints.

```php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}
```

### Admin Authentication
Admin APIs require admin privileges.

```php
// Check admin privileges
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}
```

### Permission-Based Authentication
Some APIs require specific permissions.

```php
// Check specific permission
if (!has_permission($_SESSION['user_id'], 'admin.manage_users')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit;
}
```

## Admin APIs

### System Settings API

#### Update System Settings
- **Endpoint**: `/admin/system_settings`
- **Method**: POST
- **Authentication**: Admin required
- **Parameters**:
  - `action`: Action type (update_general, update_security, update_email, etc.)
  - `form_section`: Form section identifier
  - Various setting parameters based on section

**Example Request**:
```json
{
    "action": "update_general",
    "form_section": "site_info",
    "site_name": "IslamWiki",
    "site_description": "Social Islamic Knowledge Platform"
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Settings updated successfully"
}
```

#### Get System Settings
- **Endpoint**: `/admin/system_settings`
- **Method**: GET
- **Authentication**: Admin required
- **Response**: All system settings

**Example Response**:
```json
{
    "success": true,
    "settings": {
        "site_name": "IslamWiki",
        "allow_registration": true,
        "enable_comments": true,
        "enable_wiki": true,
        "enable_social": true,
        "enable_analytics": true,
        "enable_notifications": true
    }
}
```

### User Management API

#### Get Users
- **Endpoint**: `/admin/manage_users`
- **Method**: GET
- **Authentication**: Admin required
- **Parameters**:
  - `search`: Search query (optional)
  - `role`: Filter by role (optional)
  - `status`: Filter by status (optional)
  - `page`: Page number (optional)

**Example Request**:
```
GET /admin/manage_users?search=john&role=user&page=1
```

**Example Response**:
```json
{
    "success": true,
    "users": [
        {
            "id": 1,
            "username": "john_doe",
            "email": "john@example.com",
            "role": "user",
            "status": "active",
            "created_at": "2025-01-01 12:00:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_users": 50
    }
}
```

#### Update User
- **Endpoint**: `/admin/manage_users`
- **Method**: POST
- **Authentication**: Admin required
- **Parameters**:
  - `action`: Action type (update_user, activate_user, deactivate_user, delete_user)
  - `user_id`: User ID
  - `data`: User data (for update_user)

**Example Request**:
```json
{
    "action": "update_user",
    "user_id": 1,
    "data": {
        "username": "john_doe_updated",
        "email": "john.updated@example.com",
        "role": "editor"
    }
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "User updated successfully"
}
```

### Permission Management API

#### Get Roles
- **Endpoint**: `/admin/manage_permissions`
- **Method**: GET
- **Authentication**: Admin required
- **Response**: All roles with permissions

**Example Response**:
```json
{
    "success": true,
    "roles": [
        {
            "id": 1,
            "name": "admin",
            "description": "System Administrator",
            "permissions": ["admin.access", "admin.manage_users", "admin.system_settings"]
        }
    ]
}
```

#### Create Role
- **Endpoint**: `/admin/manage_permissions`
- **Method**: POST
- **Authentication**: Admin required
- **Parameters**:
  - `action`: create_role
  - `name`: Role name
  - `description`: Role description
  - `permissions`: Array of permissions

**Example Request**:
```json
{
    "action": "create_role",
    "name": "content_manager",
    "description": "Content Management Role",
    "permissions": ["content.create_post", "content.edit_post", "content.moderate"]
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Role created successfully",
    "role_id": 5
}
```

#### Update Role Permissions
- **Endpoint**: `/admin/manage_permissions`
- **Method**: POST
- **Authentication**: Admin required
- **Parameters**:
  - `action`: update_role
  - `role_id`: Role ID
  - `permissions`: Array of permissions

**Example Request**:
```json
{
    "action": "update_role",
    "role_id": 5,
    "permissions": ["content.create_post", "content.edit_post", "content.moderate", "wiki.create"]
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Role permissions updated successfully"
}
```

## Feature Toggle APIs

### Comments API

#### Add Comment
- **Endpoint**: `/api/ajax/add_comment.php`
- **Method**: POST
- **Authentication**: Required
- **Feature Check**: `enable_comments`
- **Parameters**:
  - `post_id`: Post ID
  - `content`: Comment content
  - `parent_id`: Parent comment ID (optional)

**Example Request**:
```json
{
    "post_id": 123,
    "content": "Great article!",
    "parent_id": null
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Comment added successfully",
    "comment_id": 456
}
```

**Error Response (Feature Disabled)**:
```json
{
    "success": false,
    "message": "Comments are currently disabled"
}
```

#### Get Comments
- **Endpoint**: `/api/ajax/get_comments.php`
- **Method**: GET
- **Authentication**: Required
- **Feature Check**: `enable_comments`
- **Parameters**:
  - `post_id`: Post ID
  - `page`: Page number (optional)

**Example Request**:
```
GET /api/ajax/get_comments.php?post_id=123&page=1
```

**Example Response**:
```json
{
    "success": true,
    "comments": [
        {
            "id": 456,
            "content": "Great article!",
            "author": "john_doe",
            "created_at": "2025-01-01 12:00:00",
            "parent_id": null
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_comments": 25
    }
}
```

### Social APIs

#### Send Message
- **Endpoint**: `/api/ajax/send_message.php`
- **Method**: POST
- **Authentication**: Required
- **Feature Check**: `enable_social`
- **Parameters**:
  - `recipient_id`: Recipient user ID
  - `content`: Message content
  - `subject`: Message subject (optional)

**Example Request**:
```json
{
    "recipient_id": 2,
    "content": "Hello! How are you?",
    "subject": "Greeting"
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Message sent successfully",
    "message_id": 789
}
```

#### Get Messages
- **Endpoint**: `/api/ajax/get_messages.php`
- **Method**: GET
- **Authentication**: Required
- **Feature Check**: `enable_social`
- **Parameters**:
  - `conversation_id`: Conversation ID (optional)
  - `page`: Page number (optional)

**Example Request**:
```
GET /api/ajax/get_messages.php?conversation_id=123&page=1
```

**Example Response**:
```json
{
    "success": true,
    "messages": [
        {
            "id": 789,
            "content": "Hello! How are you?",
            "sender": "john_doe",
            "recipient": "jane_doe",
            "created_at": "2025-01-01 12:00:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 2,
        "total_messages": 15
    }
}
```

### Analytics APIs

#### Track Search
- **Endpoint**: `/api/ajax/track_search.php`
- **Method**: POST
- **Authentication**: Required
- **Feature Check**: `enable_analytics`
- **Parameters**:
  - `query`: Search query
  - `results_count`: Number of results
  - `page`: Page number

**Example Request**:
```json
{
    "query": "islamic history",
    "results_count": 25,
    "page": 1
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Search tracked successfully"
}
```

### Notification APIs

#### Get Notifications
- **Endpoint**: `/api/ajax/get_notifications.php`
- **Method**: GET
- **Authentication**: Required
- **Feature Check**: `enable_notifications`
- **Parameters**:
  - `page`: Page number (optional)
  - `unread_only`: Only unread notifications (optional)

**Example Request**:
```
GET /api/ajax/get_notifications.php?page=1&unread_only=true
```

**Example Response**:
```json
{
    "success": true,
    "notifications": [
        {
            "id": 101,
            "type": "comment",
            "message": "John Doe commented on your post",
            "read": false,
            "created_at": "2025-01-01 12:00:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_notifications": 20
    }
}
```

#### Mark Notification Read
- **Endpoint**: `/api/ajax/mark_notification_read.php`
- **Method**: POST
- **Authentication**: Required
- **Feature Check**: `enable_notifications`
- **Parameters**:
  - `notification_id`: Notification ID

**Example Request**:
```json
{
    "notification_id": 101
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Notification marked as read"
}
```

## Content APIs

### Article APIs

#### Create Article
- **Endpoint**: `/pages/wiki/create_article.php`
- **Method**: POST
- **Authentication**: Required
- **Permission**: `wiki.create`
- **Feature Check**: `enable_wiki`
- **Parameters**:
  - `title`: Article title
  - `content`: Article content (markdown)
  - `category_id`: Category ID
  - `tags`: Article tags (optional)
  - `status`: Article status (draft/published)

**Example Request**:
```json
{
    "title": "Islamic History",
    "content": "# Islamic History\n\nThis article covers...",
    "category_id": 1,
    "tags": ["history", "islam"],
    "status": "published"
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Article created successfully",
    "article_id": 123,
    "url": "/wiki/islamic-history"
}
```

#### Update Article
- **Endpoint**: `/pages/wiki/edit_article.php`
- **Method**: POST
- **Authentication**: Required
- **Permission**: `wiki.edit`
- **Feature Check**: `enable_wiki`
- **Parameters**:
  - `article_id`: Article ID
  - `title`: Article title
  - `content`: Article content (markdown)
  - `category_id`: Category ID
  - `tags`: Article tags (optional)

**Example Request**:
```json
{
    "article_id": 123,
    "title": "Islamic History - Updated",
    "content": "# Islamic History\n\nThis updated article covers...",
    "category_id": 1,
    "tags": ["history", "islam", "updated"]
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Article updated successfully"
}
```

### Post APIs

#### Create Post
- **Endpoint**: `/pages/social/create_post.php`
- **Method**: POST
- **Authentication**: Required
- **Permission**: `content.create_post`
- **Feature Check**: `enable_social`
- **Parameters**:
  - `content`: Post content
  - `visibility`: Post visibility (public/friends/private)
  - `tags`: Post tags (optional)
  - `images`: Image files (optional)

**Example Request**:
```json
{
    "content": "Just finished reading an amazing article about Islamic history!",
    "visibility": "public",
    "tags": ["islam", "history", "learning"]
}
```

**Example Response**:
```json
{
    "success": true,
    "message": "Post created successfully",
    "post_id": 456
}
```

## Error Handling

### HTTP Status Codes

#### Success Codes
- **200 OK**: Request successful
- **201 Created**: Resource created successfully

#### Client Error Codes
- **400 Bad Request**: Invalid request parameters
- **401 Unauthorized**: Authentication required
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors

#### Server Error Codes
- **500 Internal Server Error**: Server error
- **503 Service Unavailable**: Service temporarily unavailable

### Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "code": "ERROR_CODE",
    "details": {
        "field": "Specific field error",
        "validation": "Validation error details"
    }
}
```

### Common Error Codes

#### Authentication Errors
- **AUTH_REQUIRED**: Authentication required
- **AUTH_INVALID**: Invalid authentication
- **AUTH_EXPIRED**: Authentication expired

#### Permission Errors
- **PERMISSION_DENIED**: Insufficient permissions
- **ROLE_REQUIRED**: Specific role required
- **FEATURE_DISABLED**: Feature currently disabled

#### Validation Errors
- **VALIDATION_FAILED**: Input validation failed
- **REQUIRED_FIELD**: Required field missing
- **INVALID_FORMAT**: Invalid data format

## Rate Limiting

### Rate Limit Headers
- **X-RateLimit-Limit**: Request limit per time window
- **X-RateLimit-Remaining**: Remaining requests in current window
- **X-RateLimit-Reset**: Time when rate limit resets

### Rate Limit Responses
```json
{
    "success": false,
    "message": "Rate limit exceeded",
    "code": "RATE_LIMIT_EXCEEDED",
    "retry_after": 60
}
```

### Rate Limit Configuration
- **API Endpoints**: 100 requests per minute
- **Admin Endpoints**: 50 requests per minute
- **File Uploads**: 10 requests per minute
- **Search Endpoints**: 200 requests per minute

## Examples

### Complete API Workflow

#### 1. User Registration
```bash
curl -X POST http://localhost/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "secure_password",
    "confirm_password": "secure_password"
  }'
```

#### 2. User Login
```bash
curl -X POST http://localhost/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "password": "secure_password"
  }'
```

#### 3. Create Article
```bash
curl -X POST http://localhost/pages/wiki/create_article.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=session_id" \
  -d '{
    "title": "Islamic History",
    "content": "# Islamic History\n\nThis article covers...",
    "category_id": 1,
    "status": "published"
  }'
```

#### 4. Add Comment
```bash
curl -X POST http://localhost/api/ajax/add_comment.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=session_id" \
  -d '{
    "post_id": 123,
    "content": "Great article!"
  }'
```

#### 5. Send Message
```bash
curl -X POST http://localhost/api/ajax/send_message.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=session_id" \
  -d '{
    "recipient_id": 2,
    "content": "Hello! How are you?",
    "subject": "Greeting"
  }'
```

### JavaScript Examples

#### AJAX Request with Error Handling
```javascript
function apiRequest(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(null, data);
        } else {
            callback(new Error(data.message), null);
        }
    })
    .catch(error => {
        callback(error, null);
    });
}

// Usage
apiRequest('/api/ajax/add_comment.php', {
    post_id: 123,
    content: 'Great article!'
}, (error, data) => {
    if (error) {
        console.error('Error:', error.message);
    } else {
        console.log('Success:', data.message);
    }
});
```

#### Feature Check Example
```javascript
function checkFeature(feature, callback) {
    fetch('/admin/system_settings')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.settings[feature]) {
            callback(true);
        } else {
            callback(false);
        }
    })
    .catch(error => {
        console.error('Feature check failed:', error);
        callback(false);
    });
}

// Usage
checkFeature('enable_comments', (enabled) => {
    if (enabled) {
        // Show comment functionality
        document.getElementById('comment-section').style.display = 'block';
    } else {
        // Hide comment functionality
        document.getElementById('comment-section').style.display = 'none';
    }
});
```

---

This API documentation provides comprehensive coverage of all IslamWiki APIs. For additional technical details or implementation questions, please refer to the specific endpoint documentation or contact the development team.
