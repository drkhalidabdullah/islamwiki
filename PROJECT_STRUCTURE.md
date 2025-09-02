# IslamWiki Framework - Project Structure

**Author:** Khalid Abdullah  
**Version:** 0.0.5  
**Date:** January 27, 2025  
**License:** AGPL-3.0  

## 🏗️ **Clean Project Structure**

The project has been reorganized for better maintainability and organization. Here's the current structure:

## 📁 **Root Directory (Clean & Organized)**

### **Essential Files (Keep in Root)**
```
islamwiki/
├── .env                    # Environment configuration
├── .env.example           # Environment template
├── .eslintrc.cjs          # ESLint configuration
├── .gitignore             # Git ignore rules
├── .htaccess              # Root Apache configuration
├── composer.json           # PHP dependencies
├── composer.lock           # PHP dependency lock
├── package.json            # Node.js dependencies
├── package-lock.json       # Node.js dependency lock
├── phpunit.xml            # PHPUnit configuration
├── postcss.config.js       # PostCSS configuration
├── tailwind.config.js      # Tailwind CSS configuration
├── tsconfig.json           # TypeScript configuration
├── tsconfig.node.json      # Node.js TypeScript config
└── vite.config.ts          # Vite build configuration
```

## 📁 **Organized Directories**

### **1. Source Code (`src/`)**
```
src/
├── Admin/                  # Admin backend services
├── Controllers/            # HTTP controllers
├── Core/                   # Core framework classes
├── Middleware/             # HTTP middleware
├── Models/                 # Data models
├── Providers/              # Service providers
└── Services/               # Business logic services
```

### **2. Frontend Resources (`resources/`)**
```
resources/
├── js/                     # React application source
│   ├── components/         # UI components
│   ├── pages/             # Page components
│   ├── services/           # API services
│   ├── store/              # State management
│   └── styles/             # CSS styles
├── views/                  # HTML templates
└── lang/                   # Language files
```

### **3. Public Web Root (`public/`)**
```
public/
├── api/                    # API endpoints
│   └── index.php          # Main API entry point
├── assets/                 # Built frontend assets
├── index.html              # Main HTML entry point
└── .htaccess               # SPA routing configuration
```

### **4. Documentation (`docs/`)**
```
docs/
├── architecture/           # Technical architecture docs
├── plans/                  # Development plans
├── releases/               # Release documentation
└── legacy/                 # Legacy documentation files
```

### **5. Testing (`tests/`)**
```
tests/
├── Unit/                   # Unit tests
├── Feature/                # Feature tests
├── Integration/            # Integration tests
└── legacy/                 # Legacy test files
```

### **6. Configuration (`config/`)**
```
config/
├── admin_database_routes.php
├── admin_routes.php
├── admin_user_routes.php
└── legacy/                 # Legacy configuration files
```

### **7. Database (`database/`)**
```
database/
├── migrations/             # Database migrations
└── schema.sql              # Database schema
```

### **8. Scripts (`scripts/`)**
```
scripts/
├── build-and-preserve-htaccess.sh    # Safe build script
├── preserve-htaccess.sh              # Backup essential files
├── restore-htaccess.sh               # Restore essential files
├── protect-essential-files.sh        # Protect files before build
├── restore-essential-files.sh        # Restore files after build
└── legacy/                           # Legacy script files
```

### **9. Storage (`storage/`)**
```
storage/
├── backups/                # Backup files
├── cache/                  # Cache files
├── logs/                   # Log files
├── temp/                   # Temporary files
├── test/                   # Test files
└── uploads/                # User uploads
```

## 🔧 **Build & Development**

### **Frontend Build**
```bash
# Safe build (preserves .htaccess)
npm run build:safe

# Regular build
npm run build

# Development server
npm run dev
```

### **Backend Development**
```bash
# Install PHP dependencies
composer install

# Run tests
composer test

# Code quality checks
composer run cs
composer run stan
```

## 🌐 **API Access**

### **API Endpoints**
- **Base URL**: `/api/`
- **Main Entry**: `public/api/index.php`
- **Authentication**: `POST /api/` with `action: "login"`

### **Example API Call**
```bash
curl -X POST http://localhost/api/ \
  -H "Content-Type: application/json" \
  -d '{"action": "login", "email": "admin@islamwiki.org", "password": "password"}'
```

## 🚀 **Deployment Structure**

### **Production Ready**
- **Frontend**: Built assets in `public/assets/`
- **Backend**: PHP source in `src/`
- **API**: Accessible at `/api/`
- **SPA Routing**: Configured in `public/.htaccess`

### **Shared Hosting Compatible**
- **Minimal Requirements**: PHP 8.2+, MySQL, Apache
- **No External Dependencies**: Self-contained framework
- **Optimized Assets**: Minified CSS/JS with hashing
- **Security Headers**: Comprehensive security configuration

## 📊 **Organization Benefits**

### **Before (Messy Root)**
- ❌ 30+ files in root directory
- ❌ Test files scattered everywhere
- ❌ Documentation mixed with code
- ❌ Scripts in random locations
- ❌ Hard to find specific files

### **After (Clean Structure)**
- ✅ Only essential config files in root
- ✅ Logical directory organization
- ✅ Easy to navigate and maintain
- ✅ Professional project structure
- ✅ Clear separation of concerns

## 🎯 **Next Steps**

### **Immediate Actions**
1. ✅ **Project structure cleaned up**
2. ✅ **Files organized into logical folders**
3. ✅ **API properly structured**
4. ✅ **Documentation organized**

### **Future Improvements**
- [ ] Add README files to each major directory
- [ ] Create development workflow documentation
- [ ] Add code style guides
- [ ] Implement automated testing pipeline

---

**Last Updated:** January 27, 2025  
**Status:** ✅ **Project Structure Cleaned & Organized**  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0 