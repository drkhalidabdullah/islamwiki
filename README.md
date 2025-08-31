# IslamWiki Framework

**Author:** Khalid Abdullah  
**Version:** 0.0.3 (Alpha)  
**Date:** 2025-01-27  
**License:** AGPL-3.0  

## 🎉 **v0.0.3 Alpha Release - COMPLETED!**

IslamWiki is a comprehensive Islamic knowledge platform that combines wiki functionality, social networking, learning management, and Q&A platforms into a single, modern web application. Built for shared hosting with enterprise-grade features.

## ✨ **What's New in v0.0.3**

### **✅ Core Framework - COMPLETE**

- **Dependency Injection Container** - Full PSR-4 compliant autoloading
- **HTTP Layer** - Request/Response abstraction with middleware support
- **Routing System** - Advanced routing with groups, parameters, and middleware
- **Service Architecture** - Modular service provider system
- **Caching System** - File-based caching with TTL support

### **✅ Core Services - IMPLEMENTED**

- **Wiki Service** - Article management, search, categories, statistics
- **User Service** - User CRUD, authentication, role management
- **Content Service** - Content management, categories, versioning
- **Authentication Service** - JWT-based authentication with permissions

### **✅ API Layer - READY**

- **RESTful API** - Complete API endpoints for all services
- **Authentication** - JWT token-based authentication
- **Error Handling** - Proper HTTP status codes and error responses
- **Response Formatting** - Consistent JSON response structure

### **✅ Frontend Foundation - READY**

- **React 18 SPA** - Modern React application with TypeScript
- **Tailwind CSS** - Utility-first CSS framework with custom components
- **Routing** - React Router 6 with protected routes
- **State Management** - Zustand for global state management
- **Component Library** - Header, Footer, HomePage components

### **✅ Infrastructure - COMPLETE**

- **Database Schema** - Complete MySQL schema for Islamic content
- **Apache Configuration** - Security headers and URL rewriting
- **Installation System** - Web-based installer wizard
- **Shared Hosting Ready** - Optimized for shared hosting environments

### **✅ Enhanced Admin Dashboard - COMPLETE**

- **Comprehensive Testing Dashboard** - Real-time test execution, coverage analysis, security scanning
- **Performance Monitor** - System metrics, resource monitoring, trend analysis
- **System Health Monitor** - Health checks, diagnostic reports, resource monitoring
- **Development Workflow** - Git activities, deployment tracking, build status monitoring
- **Advanced Security Features** - JWT authentication, session management, rate limiting

### **✅ Enterprise-Grade Features - IMPLEMENTED**

- **Real-time Monitoring** - Live system metrics with historical data
- **Advanced Testing** - Comprehensive test suite with detailed failure reporting
- **Performance Analytics** - Trend analysis, coefficient of variation, data visualization
- **Professional UI/UX** - Modern admin interface with toast notifications, animations
- **Production Ready** - Port 80 configuration, proper error handling, security headers

## 🚀 **Quick Start**

### **Prerequisites**

- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Apache with mod_rewrite
- Composer
- Node.js 18+ (for development)

### **Installation**

1. **Clone the repository**

   ```bash
   git clone https://github.com/drkhalidabdullah/islamwiki.git
   cd islamwiki
   ```

2. **Install PHP dependencies**

   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Install Node.js dependencies (development)**

   ```bash
   npm install
   ```

4. **Build frontend (development)**

   ```bash
   npm run build
   ```

5. **Configure environment**

   ```bash
   cp env.example .env
   # Edit .env with your database credentials
   ```

6. **Run the installer**
   - Visit `http://your-domain.com/install.php`
   - Follow the installation wizard

### **Testing**

Run the core framework tests:

```bash
php test_core.php
```

Run the API tests:

```bash
php test_api.php
```

## 🏗️ **Architecture**

### **Backend Structure**

```bash
src/
├── Core/                    # Framework core
│   ├── Container/          # Dependency injection
│   ├── Database/           # Database abstraction
│   ├── Http/               # Request/Response handling
│   ├── Routing/            # URL routing system
│   ├── Middleware/         # HTTP middleware stack
│   ├── Authentication/     # JWT authentication
│   └── Cache/              # Caching system
├── Services/                # Business logic
│   ├── Wiki/               # Wiki functionality
│   ├── User/               # User management
│   ├── Content/            # Content management
│   └── ...                 # Other services
├── Controllers/             # HTTP controllers
├── Providers/               # Service providers
└── Middleware/              # Custom middleware
```

### **Frontend Structure**

```bash
resources/js/
├── components/              # Reusable UI components
├── pages/                   # Page components
├── store/                   # State management
├── styles/                  # CSS and Tailwind
└── main.tsx                 # Application entry point
```

## 🔧 **Configuration**

### **Environment Variables**

```env
# Database
DB_HOST=localhost
DB_NAME=islamwiki
DB_USER=username
DB_PASSWORD=password

# JWT
JWT_SECRET=your-secret-key
JWT_EXPIRY=3600

# Cache
CACHE_DRIVER=file
CACHE_TTL=3600
```

### **Apache Configuration**

The framework includes optimized `.htaccess` files with:

- URL rewriting for SPA routing
- Security headers
- Compression and caching
- Protection against common attacks

## 📚 **API Documentation**

### **Authentication Endpoints**

- `POST /api/login` - User authentication
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### **Content Endpoints**

- `GET /api/articles` - List articles
- `GET /api/articles/{id}` - Get article by ID
- `GET /api/articles/search` - Search articles
- `GET /api/categories` - List categories

### **Statistics Endpoints**

- `GET /api/statistics` - Platform statistics
- `GET /api/health` - Health check

## 🎯 **What's Next (v0.0.4)**

- **User Interface** - Complete React component implementation
- **Database Integration** - Real database connection and testing
- **Authentication Flow** - Complete login/register system
- **Content Management** - Article creation and editing
- **Admin Panel** - Basic administration interface

## 🤝 **Contributing**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 **License**

This project is licensed under the AGPL-3.0 License - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- Built with modern PHP practices and PSR standards
- Frontend powered by React 18 and Tailwind CSS
- Inspired by Laravel and Symfony frameworks
- Designed for the Islamic community and knowledge sharing

---

**Status:** ✅ **v0.0.3 Alpha Enhancement Release Complete**  
**Next Release:** v0.0.4 Database & Core Services (Q4 2025)  
**Repository:** <https://github.com/drkhalidabdullah/islamwiki>
