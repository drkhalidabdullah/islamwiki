# Search System Architecture

## Overview

The IslamWiki Search System is a comprehensive, multi-content search platform designed to provide fast, relevant, and intelligent search capabilities across all content types within the platform. The architecture is built for scalability, performance, and user experience.

## System Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│                 │    │                 │    │                 │
│ • Search UI     │◄──►│ • Search API    │◄──►│ • Full-text     │
│ • Suggestions   │    │ • Query Engine  │    │   Indexes       │
│ • Results       │    │ • Analytics     │    │ • Search Tables │
│ • Filters       │    │ • Caching       │    │ • User Data     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Component Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Search System                            │
├─────────────────────────────────────────────────────────────────┤
│  Frontend Layer                                                 │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│  │ Search UI   │ │ Suggestions │ │ Results     │               │
│  │ Component   │ │ Component   │ │ Component   │               │
│  └─────────────┘ └─────────────┘ └─────────────┘               │
├─────────────────────────────────────────────────────────────────┤
│  API Layer                                                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│  │ Search API  │ │ Suggestions │ │ Analytics   │               │
│  │ Endpoint    │ │ Endpoint    │ │ Endpoint    │               │
│  └─────────────┘ └─────────────┘ └─────────────┘               │
├─────────────────────────────────────────────────────────────────┤
│  Business Logic Layer                                           │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│  │ Query       │ │ Result      │ │ Search      │               │
│  │ Engine      │ │ Processor   │ │ Analytics   │               │
│  └─────────────┘ └─────────────┘ └─────────────┘               │
├─────────────────────────────────────────────────────────────────┤
│  Data Access Layer                                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│  │ Article     │ │ User        │ │ Message     │               │
│  │ Repository  │ │ Repository  │ │ Repository  │               │
│  └─────────────┘ └─────────────┘ └─────────────┘               │
├─────────────────────────────────────────────────────────────────┤
│  Database Layer                                                 │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│  │ Full-text   │ │ Search      │ │ Content     │               │
│  │ Indexes     │ │ Analytics   │ │ Tables      │               │
│  └─────────────┘ └─────────────┘ └─────────────┘               │
└─────────────────────────────────────────────────────────────────┘
```

## Database Architecture

### Search-Related Tables

#### 1. Search Analytics Table
```sql
CREATE TABLE search_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    query VARCHAR(255) NOT NULL,
    user_id INT NULL,
    content_type ENUM('all', 'articles', 'users', 'messages') DEFAULT 'all',
    results_count INT DEFAULT 0,
    search_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_query (query),
    INDEX idx_user_id (user_id),
    INDEX idx_search_time (search_time),
    INDEX idx_content_type (content_type)
);
```

#### 2. User Search History Table
```sql
CREATE TABLE user_search_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    query VARCHAR(255) NOT NULL,
    content_type ENUM('all', 'articles', 'users', 'messages') DEFAULT 'all',
    filters JSON,
    results_count INT DEFAULT 0,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_searched_at (searched_at),
    INDEX idx_query (query)
);
```

#### 3. Saved Searches Table
```sql
CREATE TABLE saved_searches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    query VARCHAR(255) NOT NULL,
    content_type ENUM('all', 'articles', 'users', 'messages') DEFAULT 'all',
    filters JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_searched TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
```

### Full-Text Search Indexes

#### 1. Articles Full-Text Index
```sql
ALTER TABLE wiki_articles 
ADD FULLTEXT(title, content, excerpt) WITH PARSER ngram;
```

#### 2. Users Full-Text Index
```sql
ALTER TABLE users 
ADD FULLTEXT(username, display_name, bio) WITH PARSER ngram;
```

#### 3. Messages Full-Text Index
```sql
ALTER TABLE messages 
ADD FULLTEXT(content) WITH PARSER ngram;
```

## API Architecture

### RESTful API Design

#### 1. Search Endpoints
```
GET /search                    # Comprehensive search
GET /search/suggestions        # Real-time suggestions
GET /api/search/analytics      # Search analytics
GET /api/search/history        # User search history
POST /api/search/save          # Save search query
GET /api/search/saved          # Get saved searches
```

#### 2. Request/Response Flow
```
Client Request → API Gateway → Authentication → Rate Limiting → 
Business Logic → Database Query → Result Processing → 
Caching → Response Formatting → Client Response
```

### API Security

#### 1. Authentication
- Session-based authentication
- JWT tokens for API access
- Role-based access control

#### 2. Rate Limiting
- IP-based rate limiting
- User-based rate limiting
- Endpoint-specific limits

#### 3. Input Validation
- Query sanitization
- Parameter validation
- SQL injection prevention

## Search Engine Architecture

### Query Processing Pipeline

```
1. Input Validation
   ↓
2. Query Parsing
   ↓
3. Query Optimization
   ↓
4. Multi-Content Search
   ↓
5. Result Aggregation
   ↓
6. Relevance Scoring
   ↓
7. Result Formatting
   ↓
8. Response Generation
```

### Search Algorithms

#### 1. Relevance Scoring
```php
function calculateRelevanceScore($result, $query) {
    $score = 0;
    
    // Title match (highest weight)
    if (stripos($result['title'], $query) !== false) {
        $score += 100;
    }
    
    // Content match
    $contentMatches = substr_count(strtolower($result['content']), strtolower($query));
    $score += $contentMatches * 10;
    
    // Category relevance
    if ($result['category'] === $query) {
        $score += 50;
    }
    
    // Popularity boost
    $score += log($result['view_count'] + 1) * 5;
    
    return $score;
}
```

#### 2. Fuzzy Search
```php
function fuzzySearch($query, $text) {
    $similarity = similar_text(strtolower($query), strtolower($text), $percent);
    return $percent > 70; // 70% similarity threshold
}
```

#### 3. Search Suggestions
```php
function generateSuggestions($query) {
    $suggestions = [];
    
    // Article suggestions
    $articles = searchArticles($query, 5);
    foreach ($articles as $article) {
        $suggestions[] = [
            'type' => 'article',
            'title' => $article['title'],
            'slug' => $article['slug']
        ];
    }
    
    // User suggestions
    $users = searchUsers($query, 3);
    foreach ($users as $user) {
        $suggestions[] = [
            'type' => 'user',
            'username' => $user['username'],
            'display_name' => $user['display_name']
        ];
    }
    
    return $suggestions;
}
```

## Caching Architecture

### Cache Strategy

#### 1. Multi-Level Caching
```
Browser Cache (5 minutes)
    ↓
CDN Cache (10 minutes)
    ↓
Application Cache (15 minutes)
    ↓
Database Cache (30 minutes)
```

#### 2. Cache Keys
```php
// Search results cache
$cacheKey = "search:{$query}:{$type}:{$page}:{$limit}";

// Suggestions cache
$cacheKey = "suggestions:{$query}:{$type}";

// Analytics cache
$cacheKey = "analytics:{$period}:{$limit}";
```

#### 3. Cache Invalidation
```php
function invalidateSearchCache($query, $type = null) {
    $patterns = [
        "search:{$query}:*",
        "suggestions:{$query}:*"
    ];
    
    if ($type) {
        $patterns[] = "search:*:{$type}:*";
    }
    
    foreach ($patterns as $pattern) {
        $cache->deletePattern($pattern);
    }
}
```

## Performance Optimization

### Database Optimization

#### 1. Index Strategy
```sql
-- Composite indexes for common queries
CREATE INDEX idx_articles_search ON wiki_articles (status, published_at, view_count);
CREATE INDEX idx_users_search ON users (status, created_at);
CREATE INDEX idx_messages_search ON messages (created_at, sender_id, recipient_id);
```

#### 2. Query Optimization
```php
function optimizeSearchQuery($query, $filters) {
    $sql = "SELECT * FROM wiki_articles WHERE status = 'published'";
    $params = [];
    
    // Add full-text search
    if (!empty($query)) {
        $sql .= " AND MATCH(title, content, excerpt) AGAINST(? IN NATURAL LANGUAGE MODE)";
        $params[] = $query;
    }
    
    // Add filters
    if (!empty($filters['category'])) {
        $sql .= " AND category_id = ?";
        $params[] = $filters['category'];
    }
    
    // Add sorting
    $sql .= " ORDER BY published_at DESC LIMIT ? OFFSET ?";
    $params[] = $filters['limit'];
    $params[] = $filters['offset'];
    
    return [$sql, $params];
}
```

### Application Optimization

#### 1. Lazy Loading
```javascript
// Lazy load search results
function loadMoreResults(page) {
    fetch(`/search?q=${query}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            appendResults(data.results);
        });
}
```

#### 2. Debounced Search
```javascript
// Debounce search suggestions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

const debouncedSearch = debounce(getSearchSuggestions, 300);
```

## Security Architecture

### Input Security

#### 1. Query Sanitization
```php
function sanitizeSearchQuery($query) {
    // Remove dangerous characters
    $query = preg_replace('/[<>"\']/', '', $query);
    
    // Limit length
    $query = substr($query, 0, 255);
    
    // Trim whitespace
    $query = trim($query);
    
    return $query;
}
```

#### 2. SQL Injection Prevention
```php
function searchArticles($query, $filters) {
    $sql = "SELECT * FROM wiki_articles WHERE status = 'published'";
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND MATCH(title, content, excerpt) AGAINST(? IN NATURAL LANGUAGE MODE)";
        $params[] = $query;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}
```

### Privacy Protection

#### 1. Message Search Privacy
```php
function searchMessages($query, $user_id) {
    $sql = "SELECT * FROM messages WHERE (sender_id = ? OR recipient_id = ?) 
            AND MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE)";
    $params = [$user_id, $user_id, $query];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}
```

#### 2. User Privacy
```php
function searchUsers($query, $exclude_private = true) {
    $sql = "SELECT * FROM users WHERE status = 'active'";
    $params = [];
    
    if ($exclude_private) {
        $sql .= " AND privacy_level = 'public'";
    }
    
    if (!empty($query)) {
        $sql .= " AND MATCH(username, display_name, bio) AGAINST(? IN NATURAL LANGUAGE MODE)";
        $params[] = $query;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}
```

## Monitoring and Analytics

### Search Analytics

#### 1. Search Metrics
```php
function trackSearch($query, $user_id, $results_count, $content_type) {
    $sql = "INSERT INTO search_analytics (query, user_id, content_type, results_count, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $params = [
        $query,
        $user_id,
        $content_type,
        $results_count,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}
```

#### 2. Performance Monitoring
```php
function monitorSearchPerformance($query, $start_time, $end_time) {
    $duration = $end_time - $start_time;
    
    if ($duration > 2.0) { // Log slow queries
        error_log("Slow search query: {$query} took {$duration} seconds");
    }
    
    // Update performance metrics
    updateSearchMetrics($query, $duration);
}
```

### Error Handling

#### 1. Graceful Degradation
```php
function handleSearchError($error) {
    // Log error
    error_log("Search error: " . $error->getMessage());
    
    // Return fallback results
    return [
        'error' => 'Search temporarily unavailable',
        'fallback_results' => getPopularArticles(10)
    ];
}
```

#### 2. User Feedback
```javascript
function handleSearchError(error) {
    showNotification('Search temporarily unavailable. Please try again.', 'error');
    
    // Show popular content as fallback
    loadPopularContent();
}
```

## Scalability Considerations

### Horizontal Scaling

#### 1. Database Sharding
```php
function getShardForQuery($query) {
    $hash = crc32($query);
    $shard = $hash % $num_shards;
    return "search_shard_{$shard}";
}
```

#### 2. Load Balancing
```nginx
upstream search_backend {
    server search1.example.com;
    server search2.example.com;
    server search3.example.com;
}

location /search {
    proxy_pass http://search_backend;
}
```

### Vertical Scaling

#### 1. Memory Optimization
```php
// Use generators for large result sets
function searchArticlesGenerator($query, $limit) {
    $sql = "SELECT * FROM wiki_articles WHERE MATCH(title, content) AGAINST(?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$query]);
    
    while ($row = $stmt->fetch()) {
        yield $row;
    }
}
```

#### 2. CPU Optimization
```php
// Parallel processing for multiple content types
function parallelSearch($query) {
    $promises = [
        'articles' => searchArticlesAsync($query),
        'users' => searchUsersAsync($query),
        'messages' => searchMessagesAsync($query)
    ];
    
    return Promise\all($promises);
}
```

## Future Enhancements

### AI-Powered Search

#### 1. Semantic Search
```php
function semanticSearch($query) {
    // Use AI/ML models for semantic understanding
    $embeddings = getQueryEmbeddings($query);
    $similar_articles = findSimilarContent($embeddings);
    
    return $similar_articles;
}
```

#### 2. Natural Language Processing
```php
function parseNaturalLanguageQuery($query) {
    // Parse queries like "Find articles about prayer written last month"
    $parser = new NaturalLanguageParser();
    $parsed = $parser->parse($query);
    
    return [
        'keywords' => $parsed->getKeywords(),
        'filters' => $parsed->getFilters(),
        'intent' => $parsed->getIntent()
    ];
}
```

### Real-Time Search

#### 1. WebSocket Integration
```javascript
// Real-time search updates
const searchSocket = new WebSocket('ws://example.com/search');

searchSocket.onmessage = function(event) {
    const data = JSON.parse(event.data);
    updateSearchResults(data.results);
};
```

#### 2. Event-Driven Architecture
```php
// Publish search events
function publishSearchEvent($event_type, $data) {
    $event = new SearchEvent($event_type, $data);
    $eventBus->publish($event);
}
```

---

*IslamWiki Search Architecture - Scalable, intelligent, and user-focused search system*
