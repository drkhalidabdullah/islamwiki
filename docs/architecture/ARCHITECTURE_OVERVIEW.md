# IslamWiki Framework - Architecture Overview

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🏗️ **Architecture Overview**

The IslamWiki Framework is built on a modern, scalable architecture that combines the best practices from enterprise frameworks while maintaining the simplicity required for shared hosting environments.

## 🎯 **Core Architecture Principles**

### **1. Unified Islamic Ecosystem**
- **Single Application**: One codebase, one database, unified experience
- **Modular Design**: Independent components that work together seamlessly
- **Scalable Foundation**: Built to grow from shared hosting to enterprise deployment

### **2. Shared Hosting First**
- **Minimal Requirements**: PHP 8.2+, MySQL, Apache with mod_rewrite
- **Efficient Resource Usage**: Optimized for limited server resources
- **No External Dependencies**: Self-contained framework with optional enhancements

### **3. Modern Web Standards**
- **React 18 Frontend**: Modern, responsive user interface
- **PHP 8.2+ Backend**: Latest PHP features and performance
- **Real-time Capabilities**: WebSocket support with fallbacks for shared hosting

## 🏛️ **System Architecture**

### **High-Level Architecture**
```
┌─────────────────────────────────────────────────────────────┐
│                    Client Layer                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │   Web App   │  │ Mobile App  │  │   Desktop App       │ │
│  │  (React)    │  │ (React Native)│  │  (Electron)        │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Presentation Layer                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │   Web UI    │  │   Admin UI  │  │   API Gateway       │ │
│  │  (Tailwind) │  │  (Dashboard)│  │  (REST/GraphQL)     │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Application Layer                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │   Controllers│  │   Services  │  │   Middleware        │ │
│  │  (HTTP)     │  │ (Business)  │  │  (Pipeline)         │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Domain Layer                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │    Models   │  │   Entities  │  │   Value Objects     │ │
│  │  (Data)     │  │ (Business)  │  │  (Validation)       │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Infrastructure Layer                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │  Database   │  │    Cache    │  │   File Storage      │ │
│  │  (MySQL)    │  │  (File/Redis)│  │  (Local/CDN)       │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 **Backend Architecture**

### **Core Framework Components**

#### **1. Application Core (`src/Core/`)**
```
src/Core/
├── Application.php          # Main application class
├── Container/               # Dependency injection container
│   └── Container.php       # Service container implementation
├── Http/                    # HTTP abstraction layer
│   ├── Request.php         # HTTP request handling
│   └── Response.php        # HTTP response handling
├── Routing/                 # Routing system
│   ├── Router.php          # Main router class
│   └── Route.php           # Individual route definition
├── Middleware/              # Middleware pipeline
│   └── MiddlewareStack.php # Middleware management
└── Database/                # Database abstraction
    ├── Connection.php       # Database connection management
    └── QueryBuilder.php     # Query builder implementation
```

#### **2. Service Providers (`src/Providers/`)**
```
src/Providers/
├── DatabaseServiceProvider.php    # Database service registration
├── AuthServiceProvider.php        # Authentication services
├── CacheServiceProvider.php       # Caching services
├── SecurityServiceProvider.php    # Security services
├── MailServiceProvider.php        # Email services
├── QueueServiceProvider.php       # Background job services
└── WebSocketServiceProvider.php   # Real-time services
```

#### **3. Business Logic (`src/Services/`)**
```
src/Services/
├── WikiService.php              # Wiki functionality
├── SocialService.php            # Social networking
├── LearningService.php          # Learning management
├── QAService.php                # Q&A platform
├── CommunicationService.php     # Real-time communication
├── ContentService.php           # Content management
├── UserService.php              # User management
└── NotificationService.php      # Notification system
```

### **Framework Design Patterns**

#### **1. Dependency Injection Container**
- **Service Registration**: Bind services to the container
- **Automatic Resolution**: Resolve dependencies automatically
- **Singleton Management**: Manage service lifecycles
- **Interface Binding**: Bind implementations to interfaces

#### **2. Service Provider Pattern**
- **Modular Registration**: Register services by feature
- **Boot Process**: Initialize services after registration
- **Configuration Loading**: Load service-specific configuration
- **Extension Support**: Allow third-party extensions

#### **3. Middleware Pipeline**
- **Request Processing**: Process requests through middleware stack
- **Response Processing**: Process responses through middleware stack
- **Global Middleware**: Apply to all requests
- **Route Middleware**: Apply to specific routes

## 🌐 **Frontend Architecture**

### **React Application Structure**

#### **1. Component Architecture**
```
src/
├── components/                 # Reusable UI components
│   ├── common/                # Common UI elements
│   │   ├── Button.tsx        # Button component
│   │   ├── Input.tsx         # Input component
│   │   └── Modal.tsx         # Modal component
│   ├── layout/                # Layout components
│   │   ├── Header.tsx        # Application header
│   │   ├── Sidebar.tsx       # Navigation sidebar
│   │   └── Footer.tsx        # Application footer
│   ├── features/              # Feature-specific components
│   │   ├── wiki/             # Wiki components
│   │   ├── social/           # Social components
│   │   └── learning/         # Learning components
│   └── admin/                 # Admin components
│       ├── Dashboard.tsx     # Admin dashboard
│       ├── UserManager.tsx   # User management
│       └── ContentManager.tsx # Content management
├── pages/                     # Page components
│   ├── Home.tsx              # Home page
│   ├── Wiki.tsx              # Wiki page
│   ├── Social.tsx            # Social page
│   └── Learning.tsx          # Learning page
├── services/                  # API services
│   ├── api.ts                # API client
│   ├── auth.ts               # Authentication service
│   └── content.ts            # Content service
├── hooks/                     # Custom React hooks
│   ├── useAuth.ts            # Authentication hook
│   ├── useContent.ts         # Content management hook
│   └── useWebSocket.ts       # WebSocket hook
├── store/                     # State management
│   ├── authStore.ts          # Authentication state
│   ├── contentStore.ts       # Content state
│   └── uiStore.ts            # UI state
└── utils/                     # Utility functions
    ├── validation.ts         # Form validation
    ├── formatting.ts         # Data formatting
    └── constants.ts          # Application constants
```

#### **2. State Management with Zustand**
- **Lightweight Store**: Simple state management
- **TypeScript Support**: Full type safety
- **Middleware Support**: Extensible with middleware
- **Performance**: Optimized re-renders

#### **3. Routing with React Router**
- **Declarative Routing**: Route definitions in components
- **Nested Routes**: Support for complex routing
- **Route Guards**: Protected route functionality
- **Dynamic Routing**: Dynamic route parameters

## 🗄️ **Database Architecture**

### **Database Schema Design**

#### **1. Core Tables**
```
users                    # User accounts and profiles
├── id (Primary Key)
├── username (Unique)
├── email (Unique)
├── password_hash
├── first_name
├── last_name
├── display_name
├── bio
├── avatar
├── email_verified_at
├── is_active
├── created_at
└── updated_at

roles                    # User roles and permissions
├── id (Primary Key)
├── name (Unique)
├── display_name
├── description
├── permissions (JSON)
└── is_system

user_roles              # User-role relationships
├── id (Primary Key)
├── user_id (Foreign Key)
├── role_id (Foreign Key)
├── granted_by
└── granted_at
```

#### **2. Content Management Tables**
```
content_categories       # Content categorization
├── id (Primary Key)
├── parent_id (Self-referencing)
├── name
├── slug (Unique)
├── description
├── image
├── sort_order
└── is_active

articles                 # Wiki articles and content
├── id (Primary Key)
├── title
├── slug (Unique)
├── content (Markdown)
├── excerpt
├── author_id (Foreign Key)
├── category_id (Foreign Key)
├── status
├── featured
├── view_count
├── created_at
└── updated_at

article_versions         # Content versioning
├── id (Primary Key)
├── article_id (Foreign Key)
├── version_number
├── content
├── changes_summary
├── created_by (Foreign Key)
└── created_at
```

#### **3. Social and Learning Tables**
```
user_profiles            # Extended user information
├── id (Primary Key)
├── user_id (Foreign Key, Unique)
├── date_of_birth
├── gender
├── location
├── website
├── social_links (JSON)
└── preferences (JSON)

comments                 # User comments and discussions
├── id (Primary Key)
├── content
├── author_id (Foreign Key)
├── parent_id (Self-referencing)
├── article_id (Foreign Key)
├── is_approved
├── created_at
└── updated_at

courses                  # Learning management
├── id (Primary Key)
├── title
├── description
├── instructor_id (Foreign Key)
├── difficulty_level
├── duration
├── is_published
└── created_at
```

### **Database Design Principles**

#### **1. Normalization**
- **Third Normal Form**: Eliminate data redundancy
- **Referential Integrity**: Foreign key constraints
- **Indexing Strategy**: Optimize query performance

#### **2. Scalability Considerations**
- **Partitioning**: Support for large datasets
- **Sharding**: Horizontal scaling support
- **Read Replicas**: Separate read/write operations

#### **3. Performance Optimization**
- **Query Optimization**: Efficient SQL queries
- **Indexing**: Strategic database indexes
- **Caching**: Multi-level caching strategy

## 🔒 **Security Architecture**

### **Security Layers**

#### **1. Application Security**
- **Input Validation**: Strict input validation and sanitization
- **Output Encoding**: Prevent XSS attacks
- **SQL Injection Protection**: Prepared statements and parameter binding
- **CSRF Protection**: Cross-site request forgery prevention

#### **2. Authentication & Authorization**
- **JWT Tokens**: Secure token-based authentication
- **OAuth 2.0**: Third-party authentication support
- **Two-Factor Authentication**: Additional security layer
- **Role-Based Access Control**: Granular permission system

#### **3. Infrastructure Security**
- **HTTPS Enforcement**: Secure communication
- **Security Headers**: Comprehensive HTTP security headers
- **Rate Limiting**: Prevent abuse and attacks
- **IP Whitelisting**: Restrict access to trusted sources

### **Security Features**

#### **1. OWASP Top 10 Compliance**
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

## 🚀 **Performance Architecture**

### **Performance Optimization Strategies**

#### **1. Caching Strategy**
```
┌─────────────────────────────────────────────────────────────┐
│                    Caching Layers                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │   Browser   │  │   CDN       │  │   Application       │ │
│  │   Cache     │  │   Cache     │  │   Cache             │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
│           │               │               │                 │
│           ▼               ▼               ▼                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │   Database  │  │   File      │  │   Memory            │ │
│  │   Cache     │  │   Cache     │  │   Cache             │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

#### **2. Asset Optimization**
- **Minification**: CSS and JavaScript minification
- **Compression**: Gzip/Brotli compression
- **Bundling**: Efficient asset bundling
- **Lazy Loading**: On-demand resource loading

#### **3. Database Optimization**
- **Query Optimization**: Efficient SQL queries
- **Indexing Strategy**: Strategic database indexes
- **Connection Pooling**: Database connection management
- **Read Replicas**: Separate read/write operations

## 🌍 **Deployment Architecture**

### **Deployment Strategies**

#### **1. Shared Hosting Deployment**
```
Development Machine          Shared Hosting
┌─────────────────┐        ┌─────────────────┐
│                 │        │                 │
│  Build Assets   │───────▶│  Deploy Built   │
│  npm run build  │        │  Assets Only    │
│                 │        │                 │
│  PHP Source     │───────▶│  PHP Source     │
│                 │        │                 │
└─────────────────┘        └─────────────────┘
```

#### **2. VPS/Cloud Deployment**
```
Development Machine          VPS/Cloud Server
┌─────────────────┐        ┌─────────────────┐
│                 │        │                 │
│  Full Source    │───────▶│  Full Source    │
│  Code           │        │  Code           │
│                 │        │                 │
│  Dependencies   │───────▶│  Dependencies   │
│                 │        │                 │
└─────────────────┘        └─────────────────┘
```

### **Environment Configuration**

#### **1. Environment Variables**
- **Database Configuration**: Connection settings
- **Application Settings**: App name, debug mode, timezone
- **Security Settings**: JWT secrets, encryption keys
- **External Services**: Mail, cache, search services

#### **2. Configuration Management**
- **Environment-Specific**: Development, staging, production
- **Secure Storage**: Sensitive data in environment variables
- **Validation**: Configuration validation on startup
- **Fallbacks**: Default values for missing configuration

## 📱 **Mobile & PWA Architecture**

### **Progressive Web App Features**

#### **1. PWA Capabilities**
- **Offline Support**: Service worker for offline functionality
- **Push Notifications**: Real-time notifications
- **App-like Experience**: Native app feel in browser
- **Installable**: Add to home screen functionality

#### **2. Mobile Optimization**
- **Responsive Design**: Mobile-first design approach
- **Touch Optimization**: Touch-friendly interface
- **Performance**: Optimized for mobile devices
- **Accessibility**: Mobile accessibility features

## 🔄 **API Architecture**

### **RESTful API Design**

#### **1. API Structure**
```
/api/v1/
├── auth/                    # Authentication endpoints
│   ├── login               # POST /api/v1/auth/login
│   ├── register            # POST /api/v1/auth/register
│   └── refresh             # POST /api/v1/auth/refresh
├── users/                   # User management
│   ├── profile             # GET /api/v1/users/profile
│   ├── update              # PUT /api/v1/users/profile
│   └── avatar              # POST /api/v1/users/avatar
├── content/                 # Content management
│   ├── articles            # GET /api/v1/content/articles
│   ├── create              # POST /api/v1/content/articles
│   └── update              # PUT /api/v1/content/articles/:id
└── social/                  # Social features
    ├── posts               # GET /api/v1/social/posts
    ├── create              # POST /api/v1/social/posts
    └── like                # POST /api/v1/social/posts/:id/like
```

#### **2. API Features**
- **Versioning**: API version management
- **Rate Limiting**: Request throttling
- **Authentication**: JWT token validation
- **Documentation**: OpenAPI/Swagger documentation

## 🔍 **Monitoring & Observability**

### **Monitoring Architecture**

#### **1. Application Monitoring**
- **Performance Metrics**: Response times, throughput
- **Error Tracking**: Error rates and types
- **User Experience**: Page load times, user interactions
- **Business Metrics**: User engagement, content creation

#### **2. Infrastructure Monitoring**
- **Server Metrics**: CPU, memory, disk usage
- **Database Performance**: Query performance, connection counts
- **Network Metrics**: Bandwidth, latency
- **Security Monitoring**: Failed login attempts, suspicious activity

---

## 📚 **Related Documentation**

- **[Components Overview](COMPONENTS_OVERVIEW.md)** - Detailed component documentation
- **[Database Schema](DATABASE_SCHEMA.md)** - Complete database documentation
- **[API Reference](API_REFERENCE.md)** - API documentation and examples
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation details
- **[Performance Guide](PERFORMANCE_GUIDE.md)** - Performance optimization guide

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development 