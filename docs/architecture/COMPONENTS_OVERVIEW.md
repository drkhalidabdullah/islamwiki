# IslamWiki Framework - Components Overview

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🔧 **Components Overview**

This document provides a detailed overview of all components within the IslamWiki Framework, their responsibilities, and how they interact with each other.

## 🏗️ **Core Framework Components**

### **1. Application Core (`src/Core/`)**

#### **Application.php**

**Purpose**: Main application class that orchestrates the entire framework
**Responsibilities**:

- Bootstrap the application
- Register service providers
- Initialize core services
- Handle request lifecycle
- Manage application state

**Key Methods**:

```php
class Application
{
    public function __construct()
    public function boot()
    public function handle()
    public function registerCoreServices()
    public function registerServiceProviders()
    public function registerRoutes()
    public function loadConfiguration()
    public function initializeServices()
    public function loadExtensions()
    public function getContainer()
    public function getRouter()
    public function getMiddleware()
}
```

#### **Container/Container.php**

**Purpose**: Dependency Injection Container for service management
**Responsibilities**:

- Register service bindings
- Resolve service dependencies
- Manage service lifecycles
- Support singleton and transient services

**Key Methods**:

```php
class Container
{
    public function bind(string $abstract, $concrete)
    public function singleton(string $abstract, $concrete)
    public function make(string $abstract)
    public function has(string $abstract): bool
    public function resolve($concrete, array $parameters = [])
}
```

#### **Http/Request.php**

**Purpose**: HTTP request abstraction and handling
**Responsibilities**:

- Parse HTTP request data
- Access GET, POST, SERVER, FILES, COOKIES
- Handle request headers
- Provide request validation methods

**Key Methods**:

```php
class Request
{
    public function get(string $key, $default = null)
    public function post(string $key, $default = null)
    public function file(string $key)
    public function header(string $key, $default = null)
    public function cookie(string $key, $default = null)
    public function method(): string
    public function uri(): string
    public function isMethod(string $method): bool
    public function isAjax(): bool
    public function isSecure(): bool
}
```

#### **Http/Response.php**

**Purpose**: HTTP response handling and generation
**Responsibilities**:

- Set response content
- Manage HTTP status codes
- Handle response headers
- Support different response types

**Key Methods**:

```php
class Response
{
    public function setContent($content)
    public function setStatusCode(int $code)
    public function setHeader(string $name, string $value)
    public function setCookie(string $name, string $value, array $options = [])
    public function json($data, int $status = 200)
    public function redirect(string $url, int $status = 302)
    public function send()
    public function setCache(int $seconds, array $options = [])
}
```

#### **Routing/Router.php**

**Purpose**: Route management and URL routing
**Responsibilities**:

- Register application routes
- Handle route groups and prefixes
- Match incoming requests to routes
- Support middleware assignment

**Key Methods**:

```php
class Router
{
    public function get(string $uri, $handler)
    public function post(string $uri, $handler)
    public function put(string $uri, $handler)
    public function delete(string $uri, $handler)
    public function any(string $uri, $handler)
    public function group(array $attributes, callable $callback)
    public function match(Request $request): ?Route
    public function addMiddleware(string $name, callable $middleware)
}
```

#### **Routing/Route.php**

**Purpose**: Individual route definition and handling
**Responsibilities**:

- Store route information
- Match URI patterns
- Handle route parameters
- Manage route middleware

**Key Methods**:

```php
class Route
{
    public function __construct(array $methods, string $uri, $handler)
    public function matches(string $uri): bool
    public function getParameters(): array
    public function addMiddleware(callable $middleware)
    public function getHandler()
    public function getMethods(): array
    public function getUri(): string
}
```

#### **Middleware/MiddlewareStack.php**

**Purpose**: Middleware pipeline management
**Responsibilities**:

- Manage middleware stack
- Process requests through middleware
- Handle middleware execution order
- Support global and route-specific middleware

**Key Methods**:

```php
class MiddlewareStack
{
    public function addGlobal(callable $middleware)
    public function addRoute(string $route, callable $middleware)
    public function process(Request $request, callable $handler)
    public function getGlobalMiddleware(): array
    public function getRouteMiddleware(string $route): array
}
```

### **2. Service Providers (`src/Providers/`)**

#### **DatabaseServiceProvider.php**

**Purpose**: Database service registration and configuration
**Responsibilities**:

- Register database connection
- Configure database settings
- Initialize database services
- Handle database migrations

**Registration**:

```php
class DatabaseServiceProvider
{
    public function register(Container $container)
    {
        $container->singleton('db', function ($container) {
            return new DatabaseConnection($container->get('config'));
        });
    }
}
```

#### **AuthServiceProvider.php**

**Purpose**: Authentication service registration
**Responsibilities**:

- Register authentication services
- Configure JWT handling
- Set up OAuth providers
- Initialize session management

**Registration**:

```php
class AuthServiceProvider
{
    public function register(Container $container)
    {
        $container->singleton('auth', function ($container) {
            return new AuthManager($container);
        });
        
        $container->singleton('jwt', function ($container) {
            return new JWTManager($container->get('config'));
        });
    }
}
```

#### **CacheServiceProvider.php**

**Purpose**: Caching service registration
**Responsibilities**:

- Register cache drivers
- Configure cache settings
- Initialize cache services
- Handle cache strategies

**Registration**:

```php
class CacheServiceProvider
{
    public function register(Container $container)
    {
        $container->singleton('cache', function ($container) {
            return new CacheManager($container->get('config'));
        });
    }
}
```

#### **SecurityServiceProvider.php**

**Purpose**: Security service registration
**Responsibilities**:

- Register security services
- Configure CSRF protection
- Set up encryption services
- Initialize rate limiting

**Registration**:

```php
class SecurityServiceProvider
{
    public function register(Container $container)
    {
        $container->singleton('csrf', function ($container) {
            return new CSRFProtection($container);
        });
        
        $container->singleton('encryption', function ($container) {
            return new EncryptionService($container->get('config'));
        });
    }
}
```

## 🌐 **Frontend Components**

### **1. React Application Structure**

#### **Component Hierarchy**

```
App.tsx
├── Router
│   ├── Layout
│   │   ├── Header
│   │   ├── Sidebar
│   │   └── Footer
│   ├── Pages
│   │   ├── Home
│   │   ├── Wiki
│   │   ├── Social
│   │   └── Learning
│   └── Admin
│       ├── Dashboard
│       ├── UserManager
│       └── ContentManager
└── Providers
    ├── AuthProvider
    ├── ThemeProvider
    └── QueryProvider
```

#### **Common Components (`src/components/common/`)**

##### **Button.tsx**

**Purpose**: Reusable button component
**Features**:

- Multiple variants (primary, secondary, danger)
- Different sizes (sm, md, lg)
- Loading states
- Icon support
- Accessibility features

**Usage**:

```tsx
<Button 
  variant="primary" 
  size="md" 
  loading={isLoading}
  onClick={handleClick}
>
  Click Me
</Button>
```

##### **Input.tsx**

**Purpose**: Form input component
**Features**:

- Text, email, password, textarea support
- Validation states
- Error messages
- Label and placeholder support
- Accessibility features

**Usage**:

```tsx
<Input
  type="email"
  label="Email Address"
  placeholder="Enter your email"
  error={errors.email}
  value={email}
  onChange={setEmail}
/>
```

##### **Modal.tsx**

**Purpose**: Modal dialog component
**Features**:

- Backdrop click to close
- ESC key to close
- Focus management
- Animation support
- Customizable content

**Usage**:

```tsx
<Modal
  isOpen={isModalOpen}
  onClose={() => setIsModalOpen(false)}
  title="Confirm Action"
>
  <p>Are you sure you want to proceed?</p>
</Modal>
```

#### **Layout Components (`src/components/layout/`)**

##### **Header.tsx**

**Purpose**: Application header component
**Features**:

- Navigation menu
- User authentication status
- Search functionality
- Notifications
- Mobile menu toggle

##### **Sidebar.tsx**

**Purpose**: Navigation sidebar component
**Features**:

- Main navigation menu
- User profile section
- Quick actions
- Collapsible sections
- Mobile responsive

##### **Footer.tsx**

**Purpose**: Application footer component
**Features**:

- Copyright information
- Links to legal pages
- Social media links
- Contact information
- Language selector

#### **Feature Components (`src/components/features/`)**

##### **Wiki Components (`src/components/features/wiki/`)**

- **ArticleEditor.tsx**: Markdown editor for articles
- **ArticleViewer.tsx**: Article display component
- **CategoryTree.tsx**: Category navigation tree
- **SearchResults.tsx**: Search results display
- **ArticleHistory.tsx**: Article version history

##### **Social Components (`src/components/features/social/`)**

- **PostCard.tsx**: Social post display
- **CommentThread.tsx**: Comment discussion thread
- **UserProfile.tsx**: User profile display
- **ActivityFeed.tsx**: User activity timeline
- **GroupCard.tsx**: Group information display

##### **Learning Components (`src/components/features/learning/`)**

- **CourseCard.tsx**: Course information display
- **LessonPlayer.tsx**: Lesson content player
- **ProgressBar.tsx**: Learning progress indicator
- **QuizComponent.tsx**: Interactive quiz component
- **Certificate.tsx**: Achievement certificate display

#### **Admin Components (`src/components/admin/`)**

##### **Dashboard.tsx**

**Purpose**: Admin dashboard overview
**Features**:

- System statistics
- Recent activity
- Quick actions
- System health indicators
- User activity charts

##### **UserManager.tsx**

**Purpose**: User management interface
**Features**:

- User list with search and filters
- User creation and editing
- Role assignment
- User status management
- Bulk operations

##### **ContentManager.tsx**

**Purpose**: Content management interface
**Features**:

- Content overview
- Content approval workflow
- Content statistics
- Content search and filters
- Bulk content operations

### **2. State Management (Zustand)**

#### **Store Structure**

```typescript
// Auth Store
interface AuthStore {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (credentials: LoginCredentials) => Promise<void>;
  logout: () => void;
  register: (userData: RegisterData) => Promise<void>;
}

// Content Store
interface ContentStore {
  articles: Article[];
  categories: Category[];
  isLoading: boolean;
  fetchArticles: () => Promise<void>;
  createArticle: (article: CreateArticleData) => Promise<void>;
  updateArticle: (id: string, data: UpdateArticleData) => Promise<void>;
}

// UI Store
interface UIStore {
  theme: 'light' | 'dark';
  sidebarOpen: boolean;
  notifications: Notification[];
  setTheme: (theme: 'light' | 'dark') => void;
  toggleSidebar: () => void;
  addNotification: (notification: Notification) => void;
}
```

### **3. API Services (`src/services/`)**

#### **api.ts**

**Purpose**: Base API client configuration
**Features**:

- Axios instance configuration
- Request/response interceptors
- Error handling
- Authentication token management
- Base URL configuration

#### **auth.ts**

**Purpose**: Authentication service
**Features**:

- User login/logout
- User registration
- Password reset
- Email verification
- Token refresh

#### **content.ts**

**Purpose**: Content management service
**Features**:

- Article CRUD operations
- Category management
- Content search
- File uploads
- Content versioning

## 🗄️ **Database Components**

### **1. Database Schema Components**

#### **Core Tables**

- **users**: User accounts and authentication
- **roles**: User roles and permissions
- **user_roles**: User-role relationships
- **user_profiles**: Extended user information

#### **Content Tables**

- **content_categories**: Content categorization
- **articles**: Wiki articles and content
- **article_versions**: Content versioning
- **comments**: User comments and discussions

#### **Social Tables**

- **posts**: Social media posts
- **likes**: Post and comment likes
- **follows**: User following relationships
- **messages**: Private messages

#### **Learning Tables**

- **courses**: Learning courses
- **lessons**: Course lessons
- **enrollments**: User course enrollments
- **progress**: Learning progress tracking

### **2. Database Abstraction Layer**

#### **Connection.php**

**Purpose**: Database connection management
**Features**:

- PDO connection handling
- Connection pooling
- Transaction management
- Query logging
- Error handling

#### **QueryBuilder.php**

**Purpose**: Database query building
**Features**:

- Fluent query interface
- Parameter binding
- Query optimization
- Result caching
- Pagination support

## 🔒 **Security Components**

### **1. Authentication Components**

#### **JWTManager.php**

**Purpose**: JWT token management
**Features**:

- Token generation
- Token validation
- Token refresh
- Blacklist management
- Expiration handling

#### **OAuthManager.php**

**Purpose**: OAuth 2.0 provider management
**Features**:

- Provider configuration
- Authorization flow
- Token exchange
- User information retrieval
- Provider integration

### **2. Security Components**

#### **CSRFProtection.php**

**Purpose**: CSRF attack prevention
**Features**:

- Token generation
- Token validation
- Token expiration
- Secure token storage
- Form protection

#### **EncryptionService.php**

**Purpose**: Data encryption and decryption
**Features**:

- AES encryption
- Key management
- Secure random generation
- Hash functions
- Salt generation

#### **RateLimiter.php**

**Purpose**: Request rate limiting
**Features**:

- Request counting
- Time window management
- IP-based limiting
- User-based limiting
- Custom rate rules

## 🚀 **Performance Components**

### **1. Caching Components**

#### **CacheManager.php**

**Purpose**: Multi-level caching management
**Features**:

- File-based caching
- Memory caching
- Database caching
- Cache invalidation
- Cache statistics

#### **FileCache.php**

**Purpose**: File-based caching implementation
**Features**:

- File storage management
- Cache expiration
- Cache compression
- Cache cleanup
- Performance optimization

### **2. Asset Management**

#### **AssetManager.php**

**Purpose**: Frontend asset management
**Features**:

- Asset compilation
- Asset optimization
- Asset versioning
- CDN integration
- Asset caching

## 📱 **Mobile & PWA Components**

### **1. Progressive Web App**

#### **Service Worker**

**Purpose**: Offline functionality and caching
**Features**:

- Offline content caching
- Background sync
- Push notifications
- Cache management
- Update handling

#### **PWA Manifest**

**Purpose**: App-like experience configuration
**Features**:

- App name and description
- Icons and splash screens
- Theme colors
- Display modes
- Orientation settings

### **2. Mobile Optimization**

#### **Responsive Components**

**Purpose**: Mobile-friendly interface components
**Features**:

- Touch-friendly controls
- Mobile navigation
- Responsive layouts
- Performance optimization
- Accessibility features

## 🔍 **Monitoring Components**

### **1. Application Monitoring**

#### **PerformanceMonitor.php**

**Purpose**: Application performance tracking
**Features**:

- Response time monitoring
- Memory usage tracking
- Database query monitoring
- Error rate tracking
- Performance alerts

#### **LogManager.php**

**Purpose**: Application logging management
**Features**:

- Log level management
- Log rotation
- Log formatting
- Log storage
- Log analysis

### **2. User Analytics**

#### **AnalyticsService.php**

**Purpose**: User behavior tracking
**Features**:

- Page view tracking
- User interaction tracking
- Conversion tracking
- Performance metrics
- Custom event tracking

---

## 📚 **Component Integration**

### **Component Communication**

- **Props**: Parent to child communication
- **Events**: Child to parent communication
- **Context**: Global state sharing
- **Services**: API and business logic
- **Store**: Centralized state management

### **Component Lifecycle**

- **Mounting**: Component initialization
- **Updating**: Component re-rendering
- **Unmounting**: Component cleanup
- **Error Handling**: Error boundaries
- **Performance**: Memoization and optimization

---

## 📚 **Related Documentation**

- **[Architecture Overview](ARCHITECTURE_OVERVIEW.md)** - High-level architecture
- **[Database Schema](DATABASE_SCHEMA.md)** - Database documentation
- **[API Reference](API_REFERENCE.md)** - API documentation
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation
- **[Performance Guide](PERFORMANCE_GUIDE.md)** - Performance optimization

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development
