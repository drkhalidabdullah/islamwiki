# IslamWiki Framework - Performance Guide

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🚀 **Performance Guide Overview**

This document provides comprehensive performance optimization strategies for the IslamWiki Framework, covering caching, database optimization, asset management, and monitoring.

## 🎯 **Performance Goals**

### **1. Response Time Targets**

- **Page Load Time**: < 2 seconds
- **API Response Time**: < 200ms
- **Database Query Time**: < 100ms
- **Asset Load Time**: < 1 second

### **2. Throughput Targets**

- **Concurrent Users**: 1000+ simultaneous users
- **Requests per Second**: 500+ RPS
- **Database Connections**: Efficient connection pooling
- **Memory Usage**: < 512MB per request

### **3. Scalability Targets**

- **Horizontal Scaling**: Support for multiple servers
- **Vertical Scaling**: Efficient resource utilization
- **Load Balancing**: Distributed request handling
- **Auto-scaling**: Automatic resource adjustment

## 🗄️ **Database Performance**

### **1. Query Optimization**

#### **Indexing Strategy**

```sql
-- Primary indexes for all tables
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_is_active ON users(is_active);

-- Composite indexes for common queries
CREATE INDEX idx_articles_status_category ON articles(status, category_id);
CREATE INDEX idx_articles_author_created ON articles(author_id, created_at);
CREATE INDEX idx_comments_article_created ON comments(article_id, created_at);

-- Full-text search indexes
CREATE FULLTEXT INDEX idx_articles_search ON articles(title, content);
CREATE FULLTEXT INDEX idx_users_search ON users(username, first_name, last_name);

-- Partial indexes for active content
CREATE INDEX idx_articles_active ON articles(id) WHERE status = 'published';
CREATE INDEX idx_users_active ON users(id) WHERE is_active = 1;
```

#### **Query Optimization Examples**

```php
// Optimized article query with eager loading
class ArticleRepository
{
    public function getPublishedArticles(int $limit = 20): Collection
    {
        return Article::with(['author:id,username,display_name,avatar', 'category:id,name,slug'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function searchArticles(string $query, int $limit = 20): Collection
    {
        return Article::where('status', 'published')
            ->where(function($q) use ($query) {
                $q->whereRaw("MATCH(title, content) AGAINST(? IN BOOLEAN MODE)", [$query])
                  ->orWhere('title', 'LIKE', "%{$query}%");
            })
            ->with(['author:id,username,display_name', 'category:id,name,slug'])
            ->orderByRaw("MATCH(title, content) AGAINST(?) DESC", [$query])
            ->limit($limit)
            ->get();
    }
}
```

#### **Database Connection Optimization**

```php
// Database configuration optimization
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=islamwiki
DB_USERNAME=islamwiki_user
DB_PASSWORD=secure_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Connection pooling
DB_POOL_SIZE=20
DB_MAX_CONNECTIONS=100
DB_MIN_CONNECTIONS=5
DB_CONNECTION_TIMEOUT=30
DB_IDLE_TIMEOUT=300

# Query optimization
DB_STRICT=false
DB_ENGINE=InnoDB
DB_INNODB_BUFFER_POOL_SIZE=1G
DB_INNODB_LOG_FILE_SIZE=256M
DB_INNODB_FLUSH_LOG_AT_TRX_COMMIT=2
```

### **2. Database Caching**

#### **Query Result Caching**

```php
class CachedArticleRepository
{
    private Cache $cache;
    private ArticleRepository $repository;
    
    public function getPublishedArticles(int $limit = 20): Collection
    {
        $cacheKey = "articles:published:{$limit}";
        
        return $this->cache->remember($cacheKey, 3600, function () use ($limit) {
            return $this->repository->getPublishedArticles($limit);
        });
    }
    
    public function getArticleBySlug(string $slug): ?Article
    {
        $cacheKey = "article:slug:{$slug}";
        
        return $this->cache->remember($cacheKey, 1800, function () use ($slug) {
            return $this->repository->getArticleBySlug($slug);
        });
    }
    
    public function invalidateArticleCache(int $articleId): void
    {
        $article = Article::find($articleId);
        if ($article) {
            $this->cache->forget("article:slug:{$article->slug}");
            $this->cache->forget("articles:published:20");
        }
    }
}
```

#### **Database Query Caching**

```php
// MySQL Query Cache Configuration
query_cache_type=1
query_cache_size=128M
query_cache_limit=2M
query_cache_min_res_unit=4K

# Redis for advanced caching
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DATABASE=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
```

## 💾 **Application Caching**

### **1. Multi-Level Caching Strategy**

#### **Cache Layers**

```php
class CacheManager
{
    private array $drivers;
    
    public function __construct()
    {
        $this->drivers = [
            'memory' => new MemoryCache(),      // Fastest (in-memory)
            'redis' => new RedisCache(),        // Fast (Redis)
            'file' => new FileCache(),          // Medium (file system)
            'database' => new DatabaseCache()   // Slowest (database)
        ];
    }
    
    public function get(string $key, $default = null)
    {
        // Try memory cache first
        if ($value = $this->drivers['memory']->get($key)) {
            return $value;
        }
        
        // Try Redis cache
        if ($value = $this->drivers['redis']->get($key)) {
            $this->drivers['memory']->set($key, $value, 300); // Cache in memory for 5 minutes
            return $value;
        }
        
        // Try file cache
        if ($value = $this->drivers['file']->get($key)) {
            $this->drivers['redis']->set($key, $value, 1800); // Cache in Redis for 30 minutes
            $this->drivers['memory']->set($key, $value, 300);
            return $value;
        }
        
        return $default;
    }
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        // Set in all cache layers
        $this->drivers['memory']->set($key, $value, min($ttl, 300));
        $this->drivers['redis']->set($key, $value, min($ttl, 1800));
        $this->drivers['file']->set($key, $value, $ttl);
    }
}
```

#### **Cache Implementation Examples**

```php
// Memory cache (APCu)
class MemoryCache
{
    public function get(string $key)
    {
        return apcu_fetch($key);
    }
    
    public function set(string $key, $value, int $ttl = 300): void
    {
        apcu_store($key, $value, $ttl);
    }
    
    public function delete(string $key): void
    {
        apcu_delete($key);
    }
}

// Redis cache
class RedisCache
{
    private Redis $redis;
    
    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect(
            config('cache.redis.host'),
            config('cache.redis.port')
        );
        
        if (config('cache.redis.password')) {
            $this->redis->auth(config('cache.redis.password'));
        }
        
        $this->redis->select(config('cache.redis.database'));
    }
    
    public function get(string $key)
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }
    
    public function set(string $key, $value, int $ttl = 1800): void
    {
        $this->redis->setex($key, $ttl, json_encode($value));
    }
}
```

### **2. Cache Invalidation Strategies**

#### **Tag-Based Cache Invalidation**

```php
class TaggedCache
{
    private Cache $cache;
    
    public function tags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        $this->cache->set($key, $value, $ttl);
        
        // Store key with tags
        foreach ($this->tags as $tag) {
            $taggedKeys = $this->cache->get("tag:{$tag}", []);
            $taggedKeys[] = $key;
            $this->cache->set("tag:{$tag}", $taggedKeys, 86400); // 24 hours
        }
    }
    
    public function flush(array $tags): void
    {
        foreach ($tags as $tag) {
            $taggedKeys = $this->cache->get("tag:{$tag}", []);
            
            foreach ($taggedKeys as $key) {
                $this->cache->delete($key);
            }
            
            $this->cache->delete("tag:{$tag}");
        }
    }
}

// Usage example
$cache = new TaggedCache();
$cache->tags(['articles', 'user:1'])->set('user_articles:1', $articles);

// Invalidate all article caches
$cache->flush(['articles']);
```

## 🌐 **Frontend Performance**

### **1. Asset Optimization**

#### **Asset Build Configuration**

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    target: 'es2015',
    minify: 'terser',
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom'],
          router: ['react-router-dom'],
          ui: ['tailwindcss', 'framer-motion'],
          utils: ['date-fns', 'zod']
        }
      }
    },
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    }
  },
  css: {
    postcss: {
      plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
        require('cssnano')({
          preset: ['default', {
            discardComments: { removeAll: true },
            normalizeWhitespace: true
          }]
        })
      ]
    }
  }
});
```

#### **Image Optimization**

```php
class ImageOptimizer
{
    public function optimize(string $imagePath, array $options = []): string
    {
        $image = Image::make($imagePath);
        
        // Resize if needed
        if (isset($options['width']) || isset($options['height'])) {
            $image->resize($options['width'] ?? null, $options['height'] ?? null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Quality optimization
        $quality = $options['quality'] ?? 85;
        
        // Convert to WebP if supported
        if ($this->supportsWebP()) {
            $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $imagePath);
            $image->save($webpPath, $quality, 'webp');
            return $webpPath;
        }
        
        // Save optimized image
        $image->save($imagePath, $quality);
        return $imagePath;
    }
    
    public function generateThumbnails(string $imagePath): array
    {
        $sizes = [
            'thumb' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200]
        ];
        
        $thumbnails = [];
        
        foreach ($sizes as $size => $dimensions) {
            $thumbnailPath = $this->generateThumbnail($imagePath, $size, $dimensions);
            $thumbnails[$size] = $thumbnailPath;
        }
        
        return $thumbnails;
    }
}
```

### **2. Code Splitting & Lazy Loading**

#### **React Component Lazy Loading**

```tsx
// Lazy load components
const ArticleEditor = lazy(() => import('./components/ArticleEditor'));
const UserProfile = lazy(() => import('./components/UserProfile'));
const AdminPanel = lazy(() => import('./components/AdminPanel'));

// Route-based code splitting
function App() {
  return (
    <Router>
      <Suspense fallback={<LoadingSpinner />}>
        <Routes>
          <Route path="/articles/edit" element={<ArticleEditor />} />
          <Route path="/profile" element={<UserProfile />} />
          <Route path="/admin" element={<AdminPanel />} />
        </Routes>
      </Suspense>
    </Router>
  );
}

// Component-level lazy loading
function ArticleList() {
  const [articles, setArticles] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetchArticles().then(setArticles).finally(() => setLoading(false));
  }, []);
  
  if (loading) return <LoadingSpinner />;
  
  return (
    <div>
      {articles.map(article => (
        <Suspense key={article.id} fallback={<ArticleSkeleton />}>
          <ArticleCard article={article} />
        </Suspense>
      ))}
    </div>
  );
}
```

## 📊 **Performance Monitoring**

### **1. Application Performance Monitoring**

#### **Performance Metrics Collection**

```php
class PerformanceMonitor
{
    private array $metrics = [];
    private float $startTime;
    
    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->metrics['memory_start'] = memory_get_usage();
    }
    
    public function end(): array
    {
        $this->metrics['execution_time'] = microtime(true) - $this->startTime;
        $this->metrics['memory_end'] = memory_get_usage();
        $this->metrics['memory_peak'] = memory_get_peak_usage();
        $this->metrics['memory_usage'] = $this->metrics['memory_end'] - $this->metrics['memory_start'];
        
        return $this->metrics;
    }
    
    public function logMetrics(string $operation): void
    {
        $metrics = $this->end();
        
        Log::info('Performance Metrics', [
            'operation' => $operation,
            'execution_time' => round($metrics['execution_time'] * 1000, 2) . 'ms',
            'memory_usage' => $this->formatBytes($metrics['memory_usage']),
            'memory_peak' => $this->formatBytes($metrics['memory_peak'])
        ]);
        
        // Store in database for analysis
        PerformanceLog::create([
            'operation' => $operation,
            'execution_time' => $metrics['execution_time'],
            'memory_usage' => $metrics['memory_usage'],
            'memory_peak' => $metrics['memory_peak'],
            'created_at' => now()
        ]);
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
```

#### **Database Query Monitoring**

```php
class QueryMonitor
{
    private array $queries = [];
    private float $totalTime = 0;
    
    public function logQuery(string $sql, float $time, array $bindings = []): void
    {
        $this->queries[] = [
            'sql' => $sql,
            'time' => $time,
            'bindings' => $bindings,
            'timestamp' => microtime(true)
        ];
        
        $this->totalTime += $time;
        
        // Log slow queries
        if ($time > 0.1) { // 100ms threshold
            Log::warning('Slow Query Detected', [
                'sql' => $sql,
                'time' => round($time * 1000, 2) . 'ms',
                'bindings' => $bindings
            ]);
        }
    }
    
    public function getSummary(): array
    {
        return [
            'total_queries' => count($this->queries),
            'total_time' => round($this->totalTime * 1000, 2) . 'ms',
            'average_time' => count($this->queries) > 0 ? 
                round(($this->totalTime / count($this->queries)) * 1000, 2) . 'ms' : '0ms',
            'slow_queries' => count(array_filter($this->queries, fn($q) => $q['time'] > 0.1))
        ];
    }
}
```

### **2. Real-Time Performance Dashboard**

#### **Performance Dashboard API**

```php
class PerformanceController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $metrics = [
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'application' => $this->getApplicationMetrics()
        ];
        
        return response()->json($metrics);
    }
    
    private function getSystemMetrics(): array
    {
        return [
            'cpu_usage' => sys_getloadavg(),
            'memory_usage' => [
                'total' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit')
            ],
            'disk_usage' => [
                'free' => disk_free_space('/'),
                'total' => disk_total_space('/')
            ]
        ];
    }
    
    private function getDatabaseMetrics(): array
    {
        return [
            'connections' => DB::connection()->getPdo()->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'slow_queries' => PerformanceLog::where('execution_time', '>', 0.1)->count(),
            'query_count' => PerformanceLog::where('created_at', '>=', now()->subHour())->count()
        ];
    }
}
```

## 🔧 **Performance Configuration**

### **1. PHP Performance Settings**

#### **PHP Configuration (php.ini)**

```ini
; Memory and execution
memory_limit = 512M
max_execution_time = 30
max_input_time = 60

; OPcache settings
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 0
opcache.revalidate_path = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

; Session settings
session.gc_maxlifetime = 7200
session.gc_probability = 1
session.gc_divisor = 100

; File upload settings
upload_max_filesize = 8M
post_max_size = 8M
max_file_uploads = 20
```

#### **Apache Performance Settings**

```apache
# Performance settings
<IfModule mpm_prefork_module>
    StartServers          5
    MinSpareServers       5
    MaxSpareServers       10
    MaxRequestWorkers     150
    MaxConnectionsPerChild   0
</IfModule>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### **2. Environment-Specific Optimization**

#### **Development Environment**

```php
// Development performance settings
APP_DEBUG = true
APP_ENV = development
CACHE_DRIVER = file
SESSION_DRIVER = file
QUEUE_CONNECTION = sync

# Enable development tools
DEVELOPER_TOOLS = true
QUERY_LOGGING = true
PERFORMANCE_MONITORING = true
```

#### **Production Environment**

```php
// Production performance settings
APP_DEBUG = false
APP_ENV = production
CACHE_DRIVER = redis
SESSION_DRIVER = redis
QUEUE_CONNECTION = redis

# Disable development features
DEVELOPER_TOOLS = false
QUERY_LOGGING = false
PERFORMANCE_MONITORING = true

# Enable production optimizations
OPCACHE_ENABLED = true
COMPRESSION_ENABLED = true
CDN_ENABLED = true
```

## 📈 **Performance Testing**

### **1. Load Testing**

#### **Load Test Script**

```php
class LoadTester
{
    private string $baseUrl;
    private int $concurrentUsers;
    private int $requestsPerUser;
    
    public function __construct(string $baseUrl, int $concurrentUsers = 100, int $requestsPerUser = 10)
    {
        $this->baseUrl = $baseUrl;
        $this->concurrentUsers = $concurrentUsers;
        $this->requestsPerUser = $requestsPerUser;
    }
    
    public function runLoadTest(): array
    {
        $startTime = microtime(true);
        $results = [];
        
        // Simulate concurrent users
        for ($i = 0; $i < $this->concurrentUsers; $i++) {
            $results[] = $this->simulateUser($i);
        }
        
        $endTime = microtime(true);
        
        return $this->analyzeResults($results, $endTime - $startTime);
    }
    
    private function simulateUser(int $userId): array
    {
        $userResults = [];
        
        for ($j = 0; $j < $this->requestsPerUser; $j++) {
            $startTime = microtime(true);
            
            // Simulate different types of requests
            $response = $this->makeRequest($this->getRandomEndpoint());
            
            $endTime = microtime(true);
            
            $userResults[] = [
                'endpoint' => $response['endpoint'],
                'response_time' => $endTime - $startTime,
                'status_code' => $response['status_code'],
                'response_size' => $response['size']
            ];
        }
        
        return $userResults;
    }
    
    private function analyzeResults(array $results, float $totalTime): array
    {
        $allRequests = [];
        $responseTimes = [];
        $statusCodes = [];
        
        foreach ($results as $userResults) {
            foreach ($userResults as $request) {
                $allRequests[] = $request;
                $responseTimes[] = $request['response_time'];
                $statusCodes[$request['status_code']] = ($statusCodes[$request['status_code']] ?? 0) + 1;
            }
        }
        
        return [
            'total_requests' => count($allRequests),
            'total_time' => round($totalTime, 2) . 's',
            'requests_per_second' => round(count($allRequests) / $totalTime, 2),
            'average_response_time' => round(array_sum($responseTimes) / count($responseTimes) * 1000, 2) . 'ms',
            'min_response_time' => round(min($responseTimes) * 1000, 2) . 'ms',
            'max_response_time' => round(max($responseTimes) * 1000, 2) . 'ms',
            'status_codes' => $statusCodes,
            'success_rate' => round(($statusCodes[200] ?? 0) / count($allRequests) * 100, 2) . '%'
        ];
    }
}
```

### **2. Performance Benchmarking**

#### **Benchmark Suite**

```php
class PerformanceBenchmark
{
    public function runBenchmarks(): array
    {
        return [
            'database' => $this->benchmarkDatabase(),
            'cache' => $this->benchmarkCache(),
            'file_operations' => $this->benchmarkFileOperations(),
            'memory_usage' => $this->benchmarkMemoryUsage()
        ];
    }
    
    private function benchmarkDatabase(): array
    {
        $startTime = microtime(true);
        
        // Test database read performance
        $articles = Article::with(['author', 'category'])->limit(100)->get();
        
        $readTime = microtime(true) - $startTime;
        
        // Test database write performance
        $startTime = microtime(true);
        
        $article = Article::create([
            'title' => 'Benchmark Test Article',
            'content' => 'This is a test article for benchmarking.',
            'author_id' => 1,
            'status' => 'draft'
        ]);
        
        $writeTime = microtime(true) - $startTime;
        
        // Cleanup
        $article->delete();
        
        return [
            'read_100_articles' => round($readTime * 1000, 2) . 'ms',
            'write_1_article' => round($writeTime * 1000, 2) . 'ms'
        ];
    }
    
    private function benchmarkCache(): array
    {
        $cache = app(Cache::class);
        $testData = ['test' => 'data', 'number' => 123, 'array' => [1, 2, 3]];
        
        // Test write performance
        $startTime = microtime(true);
        
        for ($i = 0; $i < 1000; $i++) {
            $cache->set("benchmark_key_{$i}", $testData, 60);
        }
        
        $writeTime = microtime(true) - $startTime;
        
        // Test read performance
        $startTime = microtime(true);
        
        for ($i = 0; $i < 1000; $i++) {
            $cache->get("benchmark_key_{$i}");
        }
        
        $readTime = microtime(true) - $startTime;
        
        // Cleanup
        for ($i = 0; $i < 1000; $i++) {
            $cache->delete("benchmark_key_{$i}");
        }
        
        return [
            'write_1000_keys' => round($writeTime * 1000, 2) . 'ms',
            'read_1000_keys' => round($readTime * 1000, 2) . 'ms'
        ];
    }
}
```

---

## 📚 **Related Documentation**

- **[Architecture Overview](ARCHITECTURE_OVERVIEW.md)** - High-level architecture
- **[Components Overview](COMPONENTS_OVERVIEW.md)** - Framework components
- **[Database Schema](DATABASE_SCHEMA.md)** - Database documentation
- **[API Reference](API_REFERENCE.md)** - API documentation
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development
