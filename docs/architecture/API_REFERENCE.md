# IslamWiki Framework - API Reference

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🌐 **API Reference Overview**

The IslamWiki Framework provides a comprehensive RESTful API for building Islamic content platforms, social networking applications, and learning management systems.

## 🎯 **API Design Principles**

### **1. RESTful Design**
- **Resource-Based URLs**: Clear, hierarchical resource structure
- **HTTP Methods**: Proper use of GET, POST, PUT, DELETE
- **Status Codes**: Standard HTTP status codes
- **JSON Responses**: Consistent JSON response format

### **2. Authentication & Security**
- **JWT Tokens**: Secure token-based authentication
- **OAuth 2.0**: Third-party authentication support
- **Rate Limiting**: Request throttling and abuse prevention
- **CORS Support**: Cross-origin resource sharing

### **3. Versioning Strategy**
- **URL Versioning**: `/api/v1/`, `/api/v2/`
- **Backward Compatibility**: Maintain compatibility between versions
- **Deprecation Policy**: Clear deprecation timelines
- **Migration Guides**: Upgrade path documentation

## 🔐 **Authentication**

### **JWT Authentication**

#### **Login Endpoint**
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "display_name": "John Doe"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refresh_token": "refresh_token_here",
        "expires_in": 3600
    },
    "message": "Login successful"
}
```

#### **Register Endpoint**
```http
POST /api/v1/auth/register
Content-Type: application/json

{
    "username": "johndoe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "first_name": "John",
    "last_name": "Doe"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "display_name": "John Doe"
        },
        "message": "Please check your email to verify your account"
    }
}
```

#### **Refresh Token Endpoint**
```http
POST /api/v1/auth/refresh
Authorization: Bearer {refresh_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "token": "new_jwt_token_here",
        "expires_in": 3600
    }
}
```

#### **Logout Endpoint**
```http
POST /api/v1/auth/logout
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

### **OAuth 2.0 Authentication**

#### **OAuth Providers**
- **Google**: Google OAuth 2.0
- **Facebook**: Facebook OAuth 2.0
- **Twitter**: Twitter OAuth 2.0
- **GitHub**: GitHub OAuth 2.0

#### **OAuth Flow**
```http
GET /api/v1/auth/{provider}/redirect
```

**Response**: Redirects to provider's authorization page

```http
GET /api/v1/auth/{provider}/callback?code={authorization_code}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "user@example.com"
        },
        "token": "jwt_token_here",
        "expires_in": 3600
    }
}
```

## 👥 **User Management API**

### **User Profile Endpoints**

#### **Get User Profile**
```http
GET /api/v1/users/profile
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "johndoe",
        "email": "user@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "display_name": "John Doe",
        "bio": "Software developer and Islamic content creator",
        "avatar": "https://example.com/avatars/johndoe.jpg",
        "email_verified_at": "2025-08-30T10:00:00Z",
        "created_at": "2025-08-30T10:00:00Z",
        "updated_at": "2025-08-30T10:00:00Z"
    }
}
```

#### **Update User Profile**
```http
PUT /api/v1/users/profile
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Smith",
    "bio": "Updated bio information",
    "location": "New York, USA"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Smith",
        "bio": "Updated bio information",
        "location": "New York, USA",
        "updated_at": "2025-08-30T11:00:00Z"
    },
    "message": "Profile updated successfully"
}
```

#### **Upload Avatar**
```http
POST /api/v1/users/avatar
Authorization: Bearer {jwt_token}
Content-Type: multipart/form-data

avatar: [file]
```

**Response**:
```json
{
    "success": true,
    "data": {
        "avatar": "https://example.com/avatars/new_avatar.jpg"
    },
    "message": "Avatar uploaded successfully"
}
```

### **User Management (Admin)**

#### **Get All Users**
```http
GET /api/v1/admin/users?page=1&per_page=20&search=john&role=editor
Authorization: Bearer {admin_jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "username": "johndoe",
                "email": "user@example.com",
                "first_name": "John",
                "last_name": "Doe",
                "is_active": true,
                "created_at": "2025-08-30T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 100,
            "last_page": 5
        }
    }
}
```

#### **Update User Status**
```http
PUT /api/v1/admin/users/{id}/status
Authorization: Bearer {admin_jwt_token}
Content-Type: application/json

{
    "is_active": false,
    "reason": "Violation of community guidelines"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "is_active": false,
        "updated_at": "2025-08-30T11:00:00Z"
    },
    "message": "User status updated successfully"
}
```

## 📝 **Content Management API**

### **Articles Endpoints**

#### **Get All Articles**
```http
GET /api/v1/content/articles?page=1&per_page=20&category=islamic-law&search=halal&status=published
```

**Response**:
```json
{
    "success": true,
    "data": {
        "articles": [
            {
                "id": 1,
                "title": "Understanding Halal and Haram",
                "slug": "understanding-halal-and-haram",
                "excerpt": "A comprehensive guide to Islamic dietary laws...",
                "author": {
                    "id": 1,
                    "display_name": "John Doe"
                },
                "category": {
                    "id": 2,
                    "name": "Islamic Law",
                    "slug": "islamic-law"
                },
                "status": "published",
                "view_count": 1250,
                "created_at": "2025-08-30T10:00:00Z",
                "updated_at": "2025-08-30T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 500,
            "last_page": 25
        }
    }
}
```

#### **Get Single Article**
```http
GET /api/v1/content/articles/{slug}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Understanding Halal and Haram",
        "slug": "understanding-halal-and-haram",
        "content": "# Understanding Halal and Haram\n\nThis article provides...",
        "excerpt": "A comprehensive guide to Islamic dietary laws...",
        "author": {
            "id": 1,
            "display_name": "John Doe",
            "avatar": "https://example.com/avatars/johndoe.jpg"
        },
        "category": {
            "id": 2,
            "name": "Islamic Law",
            "slug": "islamic-law"
        },
        "tags": ["halal", "haram", "dietary-laws", "islamic-law"],
        "status": "published",
        "view_count": 1250,
        "created_at": "2025-08-30T10:00:00Z",
        "updated_at": "2025-08-30T10:00:00Z"
    }
}
```

#### **Create Article**
```http
POST /api/v1/content/articles
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "title": "New Article Title",
    "content": "# New Article\n\nArticle content in Markdown...",
    "excerpt": "Article excerpt...",
    "category_id": 2,
    "tags": ["tag1", "tag2"],
    "status": "draft"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 2,
        "title": "New Article Title",
        "slug": "new-article-title",
        "status": "draft",
        "created_at": "2025-08-30T12:00:00Z"
    },
    "message": "Article created successfully"
}
```

#### **Update Article**
```http
PUT /api/v1/content/articles/{id}
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "title": "Updated Article Title",
    "content": "# Updated Article\n\nUpdated content...",
    "status": "published"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 2,
        "title": "Updated Article Title",
        "updated_at": "2025-08-30T13:00:00Z"
    },
    "message": "Article updated successfully"
}
```

#### **Delete Article**
```http
DELETE /api/v1/content/articles/{id}
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "message": "Article deleted successfully"
}
```

### **Categories Endpoints**

#### **Get All Categories**
```http
GET /api/v1/content/categories
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Islamic Beliefs",
            "slug": "islamic-beliefs",
            "description": "Core Islamic beliefs and theology",
            "parent_id": null,
            "children": [
                {
                    "id": 5,
                    "name": "Tawhid",
                    "slug": "tawhid",
                    "description": "Islamic monotheism"
                }
            ]
        }
    ]
}
```

### **Comments Endpoints**

#### **Get Article Comments**
```http
GET /api/v1/content/articles/{slug}/comments?page=1&per_page=20
```

**Response**:
```json
{
    "success": true,
    "data": {
        "comments": [
            {
                "id": 1,
                "content": "Excellent article! Very informative.",
                "author": {
                    "id": 2,
                    "display_name": "Jane Smith"
                },
                "created_at": "2025-08-30T14:00:00Z",
                "replies": [
                    {
                        "id": 2,
                        "content": "I agree completely!",
                        "author": {
                            "id": 3,
                            "display_name": "Bob Johnson"
                        },
                        "created_at": "2025-08-30T15:00:00Z"
                    }
                ]
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 50,
            "last_page": 3
        }
    }
}
```

#### **Create Comment**
```http
POST /api/v1/content/articles/{slug}/comments
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "content": "Great article! Thank you for sharing.",
    "parent_id": null
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 3,
        "content": "Great article! Thank you for sharing.",
        "created_at": "2025-08-30T16:00:00Z"
    },
    "message": "Comment created successfully"
}
```

## 👥 **Social Networking API**

### **Posts Endpoints**

#### **Get Social Feed**
```http
GET /api/v1/social/posts?page=1&per_page=20&type=text
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "posts": [
            {
                "id": 1,
                "content": "Just finished reading an amazing article about Islamic architecture!",
                "type": "text",
                "author": {
                    "id": 1,
                    "display_name": "John Doe",
                    "avatar": "https://example.com/avatars/johndoe.jpg"
                },
                "like_count": 15,
                "comment_count": 3,
                "share_count": 2,
                "is_liked": false,
                "created_at": "2025-08-30T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 200,
            "last_page": 10
        }
    }
}
```

#### **Create Post**
```http
POST /api/v1/social/posts
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "content": "Sharing my thoughts on Islamic education...",
    "type": "text",
    "is_public": true
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 2,
        "content": "Sharing my thoughts on Islamic education...",
        "type": "text",
        "is_public": true,
        "created_at": "2025-08-30T17:00:00Z"
    },
    "message": "Post created successfully"
}
```

#### **Like/Unlike Post**
```http
POST /api/v1/social/posts/{id}/like
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "is_liked": true,
        "like_count": 16
    },
    "message": "Post liked successfully"
}
```

### **User Relationships**

#### **Follow User**
```http
POST /api/v1/social/users/{id}/follow
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "is_following": true,
        "follower_count": 25
    },
    "message": "User followed successfully"
}
```

#### **Get User Followers**
```http
GET /api/v1/social/users/{id}/followers?page=1&per_page=20
```

**Response**:
```json
{
    "success": true,
    "data": {
        "followers": [
            {
                "id": 2,
                "display_name": "Jane Smith",
                "avatar": "https://example.com/avatars/janesmith.jpg",
                "followed_at": "2025-08-30T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 25,
            "last_page": 2
        }
    }
}
```

## 📚 **Learning Management API**

### **Courses Endpoints**

#### **Get All Courses**
```http
GET /api/v1/learning/courses?page=1&per_page=20&difficulty=beginner&instructor=1
```

**Response**:
```json
{
    "success": true,
    "data": {
        "courses": [
            {
                "id": 1,
                "title": "Introduction to Islamic Law",
                "slug": "introduction-to-islamic-law",
                "description": "Learn the basics of Islamic jurisprudence...",
                "instructor": {
                    "id": 1,
                    "display_name": "Dr. Ahmed Hassan"
                },
                "difficulty_level": "beginner",
                "duration": 120,
                "price": 29.99,
                "enrollment_count": 150,
                "rating": 4.8,
                "is_enrolled": false,
                "created_at": "2025-08-30T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 75,
            "last_page": 4
        }
    }
}
```

#### **Get Course Details**
```http
GET /api/v1/learning/courses/{slug}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Introduction to Islamic Law",
        "slug": "introduction-to-islamic-law",
        "description": "Learn the basics of Islamic jurisprudence...",
        "instructor": {
            "id": 1,
            "display_name": "Dr. Ahmed Hassan",
            "bio": "Expert in Islamic law with 20+ years of experience"
        },
        "difficulty_level": "beginner",
        "duration": 120,
        "price": 29.99,
        "enrollment_count": 150,
        "rating": 4.8,
        "lessons": [
            {
                "id": 1,
                "title": "Introduction to Shariah",
                "duration": 15,
                "is_free": true
            }
        ],
        "is_enrolled": false,
        "created_at": "2025-08-30T10:00:00Z"
    }
}
```

#### **Enroll in Course**
```http
POST /api/v1/learning/courses/{id}/enroll
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "enrollment_id": 1,
        "enrolled_at": "2025-08-30T18:00:00Z"
    },
    "message": "Successfully enrolled in course"
}
```

### **Lessons Endpoints**

#### **Get Lesson Content**
```http
GET /api/v1/learning/lessons/{id}
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Introduction to Shariah",
        "content": "# Introduction to Shariah\n\nShariah is the Islamic legal system...",
        "video_url": "https://example.com/videos/lesson1.mp4",
        "duration": 15,
        "course": {
            "id": 1,
            "title": "Introduction to Islamic Law"
        },
        "next_lesson": {
            "id": 2,
            "title": "Sources of Islamic Law"
        },
        "progress": {
            "completed": true,
            "completed_at": "2025-08-30T19:00:00Z"
        }
    }
}
```

## 🔍 **Search API**

### **Global Search**
```http
GET /api/v1/search?q=islamic+law&type=articles&category=islamic-law&page=1&per_page=20
```

**Response**:
```json
{
    "success": true,
    "data": {
        "query": "islamic law",
        "results": {
            "articles": [
                {
                    "id": 1,
                    "title": "Understanding Islamic Law",
                    "excerpt": "A comprehensive guide to...",
                    "type": "article",
                    "score": 0.95
                }
            ],
            "users": [
                {
                    "id": 1,
                    "display_name": "Dr. Ahmed Hassan",
                    "bio": "Expert in Islamic law...",
                    "type": "user",
                    "score": 0.87
                }
            ]
        },
        "total_results": 150,
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "last_page": 8
        }
    }
}
```

## 📊 **Analytics API**

### **User Analytics**
```http
GET /api/v1/analytics/user?period=30d
Authorization: Bearer {jwt_token}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "period": "30d",
        "metrics": {
            "articles_read": 45,
            "comments_made": 12,
            "posts_created": 8,
            "courses_enrolled": 3,
            "lessons_completed": 25
        },
        "trends": {
            "daily_activity": [
                {"date": "2025-08-01", "activity": 5},
                {"date": "2025-08-02", "activity": 8}
            ]
        }
    }
}
```

## 🚀 **Real-time API (WebSocket)**

### **WebSocket Connection**
```javascript
const ws = new WebSocket('wss://api.islamwiki.org/ws');

ws.onopen = function() {
    // Subscribe to channels
    ws.send(JSON.stringify({
        action: 'subscribe',
        channels: ['notifications', 'chat', 'updates']
    }));
};

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    switch(data.type) {
        case 'notification':
            handleNotification(data.payload);
            break;
        case 'chat_message':
            handleChatMessage(data.payload);
            break;
        case 'content_update':
            handleContentUpdate(data.payload);
            break;
    }
};
```

### **WebSocket Events**

#### **Notifications**
```json
{
    "type": "notification",
    "payload": {
        "id": 1,
        "title": "New Comment",
        "message": "Someone commented on your article",
        "type": "comment",
        "data": {
            "article_id": 1,
            "comment_id": 5
        },
        "created_at": "2025-08-30T20:00:00Z"
    }
}
```

#### **Chat Messages**
```json
{
    "type": "chat_message",
    "payload": {
        "id": 1,
        "content": "Hello! How are you?",
        "sender": {
            "id": 2,
            "display_name": "Jane Smith"
        },
        "room_id": 1,
        "created_at": "2025-08-30T20:00:00Z"
    }
}
```

## 📋 **Error Handling**

### **Error Response Format**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "email": ["The email field is required."],
            "password": ["The password must be at least 8 characters."]
        }
    }
}
```

### **Common Error Codes**
- **`AUTHENTICATION_ERROR`**: Invalid or expired token
- **`AUTHORIZATION_ERROR`**: Insufficient permissions
- **`VALIDATION_ERROR`**: Invalid input data
- **`NOT_FOUND_ERROR`**: Resource not found
- **`RATE_LIMIT_ERROR`**: Too many requests
- **`SERVER_ERROR`**: Internal server error

## 📚 **Rate Limiting**

### **Rate Limit Headers**
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

### **Rate Limit Rules**
- **Authentication Endpoints**: 5 requests per minute
- **Content Endpoints**: 100 requests per hour
- **Social Endpoints**: 200 requests per hour
- **Admin Endpoints**: 50 requests per hour

## 🔧 **SDK & Libraries**

### **JavaScript SDK**
```javascript
import { IslamWikiAPI } from '@islamwiki/sdk';

const api = new IslamWikiAPI({
    baseURL: 'https://api.islamwiki.org',
    token: 'your_jwt_token'
});

// Get articles
const articles = await api.content.articles.list({
    page: 1,
    per_page: 20,
    category: 'islamic-law'
});

// Create article
const article = await api.content.articles.create({
    title: 'New Article',
    content: '# Content here...',
    status: 'draft'
});
```

### **PHP SDK**
```php
use IslamWiki\SDK\IslamWikiAPI;

$api = new IslamWikiAPI([
    'base_url' => 'https://api.islamwiki.org',
    'token' => 'your_jwt_token'
]);

// Get articles
$articles = $api->content->articles->list([
    'page' => 1,
    'per_page' => 20,
    'category' => 'islamic-law'
]);

// Create article
$article = $api->content->articles->create([
    'title' => 'New Article',
    'content' => '# Content here...',
    'status' => 'draft'
]);
```

---

## 📚 **Related Documentation**

- **[Architecture Overview](ARCHITECTURE_OVERVIEW.md)** - High-level architecture
- **[Components Overview](COMPONENTS_OVERVIEW.md)** - Framework components
- **[Database Schema](DATABASE_SCHEMA.md)** - Database documentation
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation
- **[Performance Guide](PERFORMANCE_GUIDE.md)** - Performance optimization

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development 