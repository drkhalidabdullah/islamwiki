# Search API Documentation

## Overview

The IslamWiki Search API provides comprehensive search functionality across all content types within the platform. The API supports real-time search suggestions, advanced filtering, and multi-content search capabilities.

## Base URL

```
https://your-domain.com
```

## Authentication

Most search endpoints require user authentication. Include the session cookie in your requests.

## Endpoints

### 1. Search Suggestions

**Endpoint:** `GET /search/suggestions`

**Description:** Get real-time search suggestions as the user types.

**Parameters:**
- `q` (string, required): Search query (minimum 2 characters)
- `type` (string, optional): Content type filter (`articles`, `users`, `categories`)

**Example Request:**
```bash
GET /search/suggestions?q=islam&type=articles
```

**Example Response:**
```json
{
  "suggestions": [
    {
      "type": "article",
      "title": "Islam: The Complete Guide",
      "slug": "islam-complete-guide",
      "excerpt": "A comprehensive guide to Islamic beliefs and practices..."
    },
    {
      "type": "user",
      "username": "islamic_scholar",
      "display_name": "Dr. Ahmad Hassan",
      "bio": "Islamic scholar and researcher"
    },
    {
      "type": "category",
      "name": "Islamic Beliefs",
      "id": 1,
      "article_count": 25
    }
  ],
  "total": 3
}
```

### 2. Comprehensive Search

**Endpoint:** `GET /search`

**Description:** Perform a comprehensive search across all content types.

**Parameters:**
- `q` (string, required): Search query
- `type` (string, optional): Content type filter (`all`, `articles`, `users`, `messages`)
- `category` (integer, optional): Category ID filter
- `sort` (string, optional): Sort order (`relevance`, `title`, `date`, `views`)
- `page` (integer, optional): Page number for pagination (default: 1)
- `limit` (integer, optional): Results per page (default: 20, max: 100)

**Example Request:**
```bash
GET /search?q=prayer&type=articles&category=1&sort=relevance&page=1&limit=10
```

**Example Response:**
```json
{
  "query": "prayer",
  "total_results": 45,
  "page": 1,
  "limit": 10,
  "results": {
    "articles": [
      {
        "id": 123,
        "title": "The Five Daily Prayers",
        "slug": "five-daily-prayers",
        "excerpt": "A detailed guide to the five daily prayers in Islam...",
        "content": "Full article content...",
        "category": {
          "id": 1,
          "name": "Islamic Practices"
        },
        "author": {
          "id": 456,
          "username": "scholar_ahmad",
          "display_name": "Dr. Ahmad Hassan"
        },
        "published_at": "2025-09-01T10:30:00Z",
        "view_count": 1250,
        "url": "/wiki/five-daily-prayers"
      }
    ],
    "users": [
      {
        "id": 789,
        "username": "prayer_guide",
        "display_name": "Prayer Guide",
        "bio": "Expert in Islamic prayer practices",
        "avatar": "/assets/images/avatars/789.jpg",
        "join_date": "2025-01-15T08:00:00Z",
        "url": "/user/prayer_guide"
      }
    ],
    "messages": [
      {
        "id": 101,
        "content": "Discussion about prayer times...",
        "participants": ["user1", "user2"],
        "created_at": "2025-09-06T14:20:00Z",
        "url": "/messages/conversation/101"
      }
    ]
  },
  "filters": {
    "categories": [
      {
        "id": 1,
        "name": "Islamic Practices",
        "count": 25
      },
      {
        "id": 2,
        "name": "Islamic Beliefs",
        "count": 18
      }
    ],
    "authors": [
      {
        "id": 456,
        "username": "scholar_ahmad",
        "display_name": "Dr. Ahmad Hassan",
        "count": 12
      }
    ]
  }
}
```

### 3. Search Analytics

**Endpoint:** `GET /api/search/analytics`

**Description:** Get search analytics and popular searches (Admin only).

**Parameters:**
- `period` (string, optional): Time period (`day`, `week`, `month`, `year`)
- `limit` (integer, optional): Number of results (default: 50)

**Example Request:**
```bash
GET /api/search/analytics?period=week&limit=20
```

**Example Response:**
```json
{
  "period": "week",
  "total_searches": 1250,
  "unique_searches": 890,
  "popular_searches": [
    {
      "query": "prayer",
      "count": 45,
      "trend": "up"
    },
    {
      "query": "quran",
      "count": 38,
      "trend": "stable"
    }
  ],
  "search_types": {
    "articles": 850,
    "users": 200,
    "messages": 150,
    "all": 50
  },
  "top_categories": [
    {
      "id": 1,
      "name": "Islamic Practices",
      "searches": 320
    }
  ]
}
```

### 4. User Search History

**Endpoint:** `GET /api/search/history`

**Description:** Get user's search history (Authenticated users only).

**Parameters:**
- `limit` (integer, optional): Number of results (default: 20)

**Example Request:**
```bash
GET /api/search/history?limit=10
```

**Example Response:**
```json
{
  "searches": [
    {
      "id": 1,
      "query": "prayer times",
      "type": "articles",
      "results_count": 15,
      "searched_at": "2025-09-07T10:30:00Z"
    },
    {
      "id": 2,
      "query": "islamic calendar",
      "type": "all",
      "results_count": 8,
      "searched_at": "2025-09-07T09:15:00Z"
    }
  ],
  "total": 25
}
```

### 5. Save Search

**Endpoint:** `POST /api/search/save`

**Description:** Save a search query for later reference (Authenticated users only).

**Parameters:**
- `query` (string, required): Search query
- `type` (string, optional): Content type filter
- `filters` (object, optional): Additional filters
- `name` (string, optional): Custom name for the saved search

**Example Request:**
```bash
POST /api/search/save
Content-Type: application/json

{
  "query": "prayer times",
  "type": "articles",
  "filters": {
    "category": 1
  },
  "name": "Prayer Times Articles"
}
```

**Example Response:**
```json
{
  "success": true,
  "saved_search": {
    "id": 5,
    "name": "Prayer Times Articles",
    "query": "prayer times",
    "type": "articles",
    "filters": {
      "category": 1
    },
    "created_at": "2025-09-07T10:30:00Z"
  }
}
```

### 6. Get Saved Searches

**Endpoint:** `GET /api/search/saved`

**Description:** Get user's saved searches (Authenticated users only).

**Example Request:**
```bash
GET /api/search/saved
```

**Example Response:**
```json
{
  "saved_searches": [
    {
      "id": 5,
      "name": "Prayer Times Articles",
      "query": "prayer times",
      "type": "articles",
      "filters": {
        "category": 1
      },
      "created_at": "2025-09-07T10:30:00Z",
      "last_searched": "2025-09-07T10:30:00Z"
    }
  ],
  "total": 1
}
```

## Error Responses

All endpoints return appropriate HTTP status codes and error messages:

### 400 Bad Request
```json
{
  "error": "Invalid parameters",
  "message": "Query parameter 'q' is required and must be at least 2 characters long"
}
```

### 401 Unauthorized
```json
{
  "error": "Authentication required",
  "message": "Please log in to access this feature"
}
```

### 403 Forbidden
```json
{
  "error": "Access denied",
  "message": "You don't have permission to access this resource"
}
```

### 404 Not Found
```json
{
  "error": "Not found",
  "message": "The requested resource was not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error",
  "message": "An unexpected error occurred. Please try again later."
}
```

## Rate Limiting

Search endpoints are rate-limited to prevent abuse:

- **Search suggestions**: 100 requests per minute per IP
- **Comprehensive search**: 50 requests per minute per user
- **Analytics**: 10 requests per minute per user (Admin only)

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1631020800
```

## Caching

Search results are cached for improved performance:

- **Search suggestions**: Cached for 5 minutes
- **Search results**: Cached for 10 minutes
- **Analytics**: Cached for 1 hour

Cache headers are included in responses:
```
Cache-Control: public, max-age=300
ETag: "abc123def456"
```

## Security Considerations

1. **Input Validation**: All search queries are sanitized and validated
2. **SQL Injection Protection**: All database queries use prepared statements
3. **XSS Prevention**: All output is properly escaped
4. **Privacy Protection**: Message search is limited to participants only
5. **Rate Limiting**: Protection against search abuse and spam

## Examples

### JavaScript/AJAX Example

```javascript
// Search suggestions
function getSearchSuggestions(query) {
    fetch(`/search/suggestions?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySuggestions(data.suggestions);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Comprehensive search
function performSearch(query, filters = {}) {
    const params = new URLSearchParams({
        q: query,
        ...filters
    });
    
    fetch(`/search?${params}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
```

### cURL Examples

```bash
# Get search suggestions
curl -X GET "https://your-domain.com/search/suggestions?q=islam" \
     -H "Accept: application/json"

# Perform comprehensive search
curl -X GET "https://your-domain.com/search?q=prayer&type=articles&sort=relevance" \
     -H "Accept: application/json" \
     -H "Cookie: session_id=your_session_cookie"

# Get search analytics (Admin only)
curl -X GET "https://your-domain.com/api/search/analytics?period=week" \
     -H "Accept: application/json" \
     -H "Cookie: session_id=your_session_cookie"
```

## Versioning

The Search API is versioned. Current version is v1.0.

- **Version 1.0**: Initial release with comprehensive search functionality
- **Future versions**: Will maintain backward compatibility

## Support

For API support and questions:
- **Documentation**: Check this documentation
- **Issues**: Report bugs via GitHub Issues
- **Discussions**: Join GitHub Discussions

---

*IslamWiki Search API v1.0 - Comprehensive search across all content types*
