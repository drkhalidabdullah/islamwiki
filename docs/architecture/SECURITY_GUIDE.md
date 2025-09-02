# IslamWiki Framework - Security Guide

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🔒 **Security Guide Overview**

This document provides comprehensive security implementation details for the IslamWiki Framework, covering authentication, authorization, data protection, and security best practices.

## 🎯 **Security Principles**

### **1. Defense in Depth**

- **Multiple Security Layers**: Implement security at every level
- **Fail-Safe Defaults**: Secure by default configuration
- **Principle of Least Privilege**: Minimal access required
- **Security Through Obscurity**: Not relied upon as primary defense

### **2. OWASP Top 10 Compliance**

- **Injection Prevention**: SQL, NoSQL, LDAP injection protection
- **Broken Authentication**: Secure authentication mechanisms
- **Sensitive Data Exposure**: Encryption and secure storage
- **XML External Entities**: XXE attack prevention
- **Broken Access Control**: Proper authorization checks
- **Security Misconfiguration**: Secure default configurations
- **Cross-Site Scripting**: XSS prevention
- **Insecure Deserialization**: Safe deserialization
- **Using Components with Known Vulnerabilities**: Dependency management
- **Insufficient Logging & Monitoring**: Comprehensive logging

## 🔐 **Authentication Security**

### **1. JWT Token Security**

#### **Token Configuration**

```php
// JWT Configuration
JWT_SECRET=your_very_long_random_secret_key_here
JWT_TTL=3600                    // 1 hour
JWT_REFRESH_TTL=1209600         // 14 days
JWT_ALGORITHM=HS256             // HMAC SHA-256
JWT_ISSUER=islamwiki.org        // Token issuer
JWT_AUDIENCE=islamwiki_users    // Token audience
```

#### **Token Security Features**

- **Strong Secret Keys**: Minimum 256-bit random keys
- **Short Expiration**: Limited token lifetime
- **Refresh Tokens**: Secure token renewal
- **Algorithm Validation**: Only secure algorithms allowed
- **Issuer Validation**: Verify token source

#### **Token Implementation**

```php
class JWTManager
{
    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => config('jwt.issuer'),
            'aud' => config('jwt.audience'),
            'iat' => time(),
            'exp' => time() + config('jwt.ttl'),
            'sub' => $user->id,
            'username' => $user->username,
            'roles' => $user->roles->pluck('name')->toArray()
        ];
        
        return JWT::encode($payload, config('jwt.secret'), config('jwt.algorithm'));
    }
    
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, config('jwt.secret'), [config('jwt.algorithm')]);
            
            // Validate issuer and audience
            if ($decoded->iss !== config('jwt.issuer') || 
                $decoded->aud !== config('jwt.audience')) {
                return null;
            }
            
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
```

### **2. Password Security**

#### **Password Requirements**

```php
// Password validation rules
'password' => [
    'required',
    'string',
    'min:8',                    // Minimum 8 characters
    'max:128',                  // Maximum 128 characters
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', // Complex password
    'confirmed'                  // Password confirmation required
]
```

#### **Password Hashing**

```php
class PasswordService
{
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,    // 64MB
            'time_cost' => 4,          // 4 iterations
            'threads' => 3             // 3 threads
        ]);
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
```

### **3. Two-Factor Authentication (2FA)**

#### **TOTP Implementation**

```php
class TwoFactorService
{
    public function generateSecret(): string
    {
        return (new Google2FA())->generateSecretKey();
    }
    
    public function generateQRCode(string $secret, string $email): string
    {
        $qrCode = new QrCode();
        $qrCode->setText("otpauth://totp/IslamWiki:{$email}?secret={$secret}&issuer=IslamWiki");
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        
        return $qrCode->writeDataUri();
    }
    
    public function verifyCode(string $secret, string $code): bool
    {
        return (new Google2FA())->verifyKey($secret, $code);
    }
}
```

#### **2FA Configuration**

```php
// 2FA Settings
TWO_FACTOR_ENABLED=true
TWO_FACTOR_METHOD=totp          // TOTP or SMS
TWO_FACTOR_REMEMBER_DAYS=30     // Remember device for 30 days
TWO_FACTOR_BACKUP_CODES=10      // Number of backup codes
```

## 🛡️ **Authorization Security**

### **1. Role-Based Access Control (RBAC)**

#### **Permission System**

```php
class PermissionService
{
    public function hasPermission(User $user, string $permission): bool
    {
        $userPermissions = $this->getUserPermissions($user);
        return in_array($permission, $userPermissions) || 
               in_array('*', $userPermissions);
    }
    
    public function hasRole(User $user, string $role): bool
    {
        return $user->roles->contains('name', $role);
    }
    
    public function requirePermission(string $permission): void
    {
        if (!$this->hasPermission(auth()->user(), $permission)) {
            throw new AuthorizationException("Insufficient permissions");
        }
    }
}
```

#### **Permission Definitions**

```php
// Permission constants
class Permissions
{
    // Content permissions
    const CONTENT_VIEW = 'content.view';
    const CONTENT_CREATE = 'content.create';
    const CONTENT_EDIT = 'content.edit';
    const CONTENT_DELETE = 'content.delete';
    const CONTENT_MODERATE = 'content.moderate';
    
    // User permissions
    const USER_VIEW = 'user.view';
    const USER_CREATE = 'user.create';
    const USER_EDIT = 'user.edit';
    const USER_DELETE = 'user.delete';
    
    // Admin permissions
    const ADMIN_ACCESS = 'admin.access';
    const SYSTEM_CONFIG = 'system.config';
    const USER_MANAGEMENT = 'user.management';
}
```

### **2. Middleware Security**

#### **Authentication Middleware**

```php
class AuthenticationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $payload = app(JWTManager::class)->validateToken($token);
        
        if (!$payload) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        // Set user in request
        $request->setUserResolver(function () use ($payload) {
            return User::find($payload['sub']);
        });
        
        return $next($request);
    }
}
```

#### **Authorization Middleware**

```php
class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        
        if (!$user || !app(PermissionService::class)->hasPermission($user, $permission)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        return $next($request);
    }
}
```

## 🚫 **Input Validation & Sanitization**

### **1. Request Validation**

#### **Validation Rules**

```php
class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,!?()]+$/'
            ],
            'content' => [
                'required',
                'string',
                'min:10',
                'max:50000'
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:content_categories,id'
            ],
            'tags' => [
                'sometimes',
                'array',
                'max:10'
            ],
            'tags.*' => [
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-Z0-9\-_]+$/'
            ]
        ];
    }
    
    public function sanitize(): void
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'content' => $this->sanitizeMarkdown($this->content),
            'tags' => array_map('strtolower', $this->tags ?? [])
        ]);
    }
}
```

#### **Markdown Sanitization**

```php
class MarkdownSanitizer
{
    private array $allowedTags = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'br', 'strong', 'em', 'u', 's',
        'ul', 'ol', 'li', 'blockquote', 'code',
        'pre', 'a', 'img', 'table', 'tr', 'td', 'th'
    ];
    
    private array $allowedAttributes = [
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'title'],
        'code' => ['class']
    ];
    
    public function sanitize(string $markdown): string
    {
        // Convert markdown to HTML
        $html = $this->markdownToHtml($markdown);
        
        // Sanitize HTML
        $cleanHtml = $this->sanitizeHtml($html);
        
        return $cleanHtml;
    }
    
    private function sanitizeHtml(string $html): string
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $this->removeUnsafeElements($dom);
        $this->sanitizeAttributes($dom);
        
        return $dom->saveHTML();
    }
}
```

### **2. SQL Injection Prevention**

#### **Prepared Statements**

```php
class ArticleRepository
{
    public function findByCategory(int $categoryId, int $limit = 20): Collection
    {
        $sql = "SELECT * FROM articles 
                WHERE category_id = ? AND status = 'published' 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId, $limit]);
        
        return collect($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    public function search(string $query, int $limit = 20): Collection
    {
        $sql = "SELECT * FROM articles 
                WHERE (title LIKE ? OR content LIKE ?) 
                AND status = 'published' 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $searchTerm = "%{$query}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        
        return collect($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
```

## 🚦 **Rate Limiting**

### **1. Rate Limiting Implementation**

#### **Rate Limiter Service**

```php
class RateLimiter
{
    private Redis $redis;
    private string $prefix = 'rate_limit:';
    
    public function attempt(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $key = $this->prefix . $key;
        $current = $this->redis->get($key);
        
        if ($current >= $maxAttempts) {
            return false;
        }
        
        $this->redis->incr($key);
        $this->redis->expire($key, $decayMinutes * 60);
        
        return true;
    }
    
    public function remaining(string $key, int $maxAttempts): int
    {
        $key = $this->prefix . $key;
        $current = $this->redis->get($key) ?: 0;
        
        return max(0, $maxAttempts - $current);
    }
    
    public function clear(string $key): void
    {
        $this->redis->del($this->prefix . $key);
    }
}
```

#### **Rate Limiting Rules**

```php
// Rate limiting configuration
RATE_LIMIT_AUTH = 5              // 5 attempts per minute
RATE_LIMIT_CONTENT = 100         // 100 requests per hour
RATE_LIMIT_API = 1000            // 1000 requests per hour
RATE_LIMIT_UPLOAD = 10           // 10 uploads per hour
RATE_LIMIT_COMMENT = 20          // 20 comments per hour
```

#### **Rate Limiting Middleware**

```php
class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, string $limit)
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = config("rate_limit.{$limit}");
        
        if (!app(RateLimiter::class)->attempt($key, $maxAttempts, 1)) {
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => 60
            ], 429);
        }
        
        $response = $next($request);
        
        return $response->header('X-RateLimit-Remaining', 
            app(RateLimiter::class)->remaining($key, $maxAttempts));
    }
}
```

## 🛡️ **CSRF Protection**

### **1. CSRF Token Implementation**

#### **CSRF Service**

```php
class CSRFProtection
{
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        session(['csrf_token' => $token]);
        
        return $token;
    }
    
    public function validateToken(string $token): bool
    {
        $sessionToken = session('csrf_token');
        
        if (!$sessionToken || !hash_equals($sessionToken, $token)) {
            return false;
        }
        
        return true;
    }
    
    public function refreshToken(): string
    {
        return $this->generateToken();
    }
}
```

#### **CSRF Middleware**

```php
class CSRFMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->isReading($request)) {
            return $next($request);
        }
        
        if (!$this->tokensMatch($request)) {
            throw new CSRFException('CSRF token mismatch');
        }
        
        return $next($request);
    }
    
    private function tokensMatch(Request $request): bool
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        
        return app(CSRFProtection::class)->validateToken($token);
    }
}
```

## 🔐 **Data Encryption**

### **1. Encryption Service**

#### **Encryption Implementation**

```php
class EncryptionService
{
    private string $key;
    private string $cipher = 'AES-256-CBC';
    
    public function __construct()
    {
        $this->key = base64_decode(config('app.key'));
    }
    
    public function encrypt(string $value): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($value, $this->cipher, $this->key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt(string $value): string
    {
        $value = base64_decode($value);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($value, 0, $ivLength);
        $encrypted = substr($value, $ivLength);
        
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
    
    public function hash(string $value): string
    {
        return hash('sha256', $value . $this->key);
    }
}
```

#### **Sensitive Data Encryption**

```php
// Encrypt sensitive user data
class User extends Model
{
    protected $casts = [
        'email' => 'encrypted',
        'phone' => 'encrypted',
        'address' => 'encrypted'
    ];
    
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = app(EncryptionService::class)->encrypt($value);
    }
    
    public function getEmailAttribute($value)
    {
        return app(EncryptionService::class)->decrypt($value);
    }
}
```

## 🚪 **Session Security**

### **1. Session Configuration**

#### **Secure Session Settings**

```php
// Session security configuration
SESSION_DRIVER = file                    // File-based sessions
SESSION_LIFETIME = 120                   // 2 hours
SESSION_EXPIRE_ON_CLOSE = true          // Expire on browser close
SESSION_SECURE_COOKIES = true           // HTTPS only
SESSION_HTTP_ONLY = true                // HTTP only (no JavaScript access)
SESSION_SAME_SITE = strict              // SameSite cookie policy
SESSION_ENCRYPT = true                  // Encrypt session data
```

#### **Session Management**

```php
class SessionService
{
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
    
    public function destroy(): void
    {
        session_destroy();
        session_start();
    }
    
    public function setSecure(): void
    {
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
    }
}
```

## 🌐 **HTTP Security Headers**

### **1. Security Headers Configuration**

#### **Apache Configuration (.htaccess)**

```apache
# Security Headers
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Prevent MIME type sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';"
    
    # Remove server signature
    Header unset Server
    Header unset X-Powered-By
    
    # HSTS (HTTP Strict Transport Security)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</IfModule>
```

#### **PHP Security Headers**

```php
class SecurityHeaders
{
    public function setHeaders(): void
    {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';");
        
        // HSTS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}
```

## 📊 **Security Monitoring & Logging**

### **1. Security Event Logging**

#### **Security Logger**

```php
class SecurityLogger
{
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $logData = [
            'event' => $event,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'context' => $context,
            'timestamp' => now()
        ];
        
        Log::channel('security')->info('Security Event', $logData);
        
        // Store in database for analysis
        SecurityLog::create($logData);
    }
    
    public function logFailedLogin(string $email, string $reason): void
    {
        $this->logSecurityEvent('failed_login', [
            'email' => $email,
            'reason' => $reason
        ]);
    }
    
    public function logSuspiciousActivity(string $activity, array $details): void
    {
        $this->logSecurityEvent('suspicious_activity', [
            'activity' => $activity,
            'details' => $details
        ]);
    }
}
```

#### **Security Monitoring**

```php
class SecurityMonitor
{
    public function checkFailedLogins(string $ip): bool
    {
        $failedAttempts = SecurityLog::where('ip_address', $ip)
            ->where('event', 'failed_login')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();
        
        return $failedAttempts >= 5;
    }
    
    public function checkSuspiciousPatterns(): array
    {
        $patterns = [];
        
        // Check for multiple failed logins
        $failedLogins = SecurityLog::where('event', 'failed_login')
            ->where('created_at', '>=', now()->subHour())
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 10')
            ->get();
        
        foreach ($failedLogins as $login) {
            $patterns[] = [
                'type' => 'multiple_failed_logins',
                'ip_address' => $login->ip_address,
                'count' => $login->count,
                'severity' => 'high'
            ];
        }
        
        return $patterns;
    }
}
```

## 🔍 **Security Testing**

### **1. Security Test Suite**

#### **Security Tests**

```php
class SecurityTest extends TestCase
{
    public function test_csrf_protection(): void
    {
        $response = $this->post('/api/articles', [
            'title' => 'Test Article',
            'content' => 'Test content'
        ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }
    
    public function test_sql_injection_prevention(): void
    {
        $response = $this->get('/api/articles?search=1\' OR 1=1--');
        
        $response->assertStatus(200);
        // Verify no SQL injection occurred
    }
    
    public function test_xss_prevention(): void
    {
        $response = $this->post('/api/articles', [
            'title' => '<script>alert("XSS")</script>',
            'content' => 'Content with <script>alert("XSS")</script>'
        ]);
        
        $response->assertStatus(422); // Validation error
    }
    
    public function test_rate_limiting(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong_password'
            ]);
        }
        
        $response->assertStatus(429); // Too many requests
    }
}
```

## 📋 **Security Checklist**

### **1. Pre-Deployment Security Checklist**

- [ ] **Authentication**: JWT tokens with strong secrets
- [ ] **Authorization**: RBAC with proper permissions
- [ ] **Input Validation**: All inputs validated and sanitized
- [ ] **SQL Injection**: Prepared statements used everywhere
- [ ] **XSS Prevention**: Output properly encoded
- [ ] **CSRF Protection**: Tokens implemented and validated
- [ ] **Rate Limiting**: Implemented for all endpoints
- [ ] **HTTPS**: SSL/TLS enabled
- [ ] **Security Headers**: All security headers set
- [ ] **Session Security**: Secure session configuration
- [ ] **File Uploads**: Secure file handling
- [ ] **Error Handling**: No sensitive information in errors
- [ ] **Logging**: Security events logged
- [ ] **Dependencies**: All dependencies updated
- [ ] **Environment**: Production environment secured

### **2. Ongoing Security Maintenance**

- [ ] **Regular Updates**: Keep dependencies updated
- [ ] **Security Audits**: Regular security reviews
- [ ] **Penetration Testing**: Periodic security testing
- [ ] **Monitoring**: Security event monitoring
- [ ] **Backup Security**: Secure backup procedures
- [ ] **Access Control**: Regular access reviews
- [ ] **Incident Response**: Security incident procedures

---

## 📚 **Related Documentation**

- **[Architecture Overview](ARCHITECTURE_OVERVIEW.md)** - High-level architecture
- **[Components Overview](COMPONENTS_OVERVIEW.md)** - Framework components
- **[Database Schema](DATABASE_SCHEMA.md)** - Database documentation
- **[API Reference](API_REFERENCE.md)** - API documentation
- **[Performance Guide](PERFORMANCE_GUIDE.md)** - Performance optimization

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development
