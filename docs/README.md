# IslamWiki Framework - Documentation

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 📚 Documentation Overview

Welcome to the IslamWiki Framework documentation. This comprehensive guide covers everything you need to know about setting up, developing, and deploying the IslamWiki platform.

## 🗂️ Documentation Structure

### **📋 Core Documentation**
- **[Framework Overview](IslamWiki_Framework_Overview.md)** - Complete implementation guide and architecture details
- **[Comprehensive Plan](ISLAMWIKI_PLATFORM_COMPREHENSIVE_PLAN.md)** - Detailed development roadmap and platform vision

### **🚀 Deployment Guides**
- **[Shared Hosting Deployment](SHARED_HOSTING_DEPLOYMENT.md)** - General shared hosting setup instructions
- **[Gandi.net Hosting Setup](GANDI_HOSTING_SETUP.md)** - Specific instructions for Gandi.net shared hosting

### **📦 Release Information**
- **[Release Notes](release/RELEASE_NOTES_0.0.1.md)** - Detailed information about v0.0.1 release
- **[Changelog](release/CHANGELOG.md)** - Complete list of changes and features
- **[Version History](release/VERSION_HISTORY.md)** - Timeline and roadmap of all releases

## 🎯 Quick Start

### **For Developers**
1. Read the [Framework Overview](IslamWiki_Framework_Overview.md) for architecture understanding
2. Follow the [Shared Hosting Deployment](SHARED_HOSTING_DEPLOYMENT.md) guide
3. Check [Release Notes](release/RELEASE_NOTES_0.0.1.md) for current features

### **For System Administrators**
1. Review [Shared Hosting Deployment](SHARED_HOSTING_DEPLOYMENT.md) for general setup
2. If using Gandi.net, follow [Gandi.net Hosting Setup](GANDI_HOSTING_SETUP.md)
3. Check system requirements in [Framework Overview](IslamWiki_Framework_Overview.md)

### **For Project Managers**
1. Review [Comprehensive Plan](ISLAMWIKI_PLATFORM_COMPREHENSIVE_PLAN.md) for project scope
2. Check [Version History](release/VERSION_HISTORY.md) for release timeline
3. Review [Release Notes](release/RELEASE_NOTES_0.0.1.md) for current status

## 🏗️ Architecture Overview

### **Backend Framework**
- **PHP 8.2+** with custom lightweight framework
- **Symfony/Laravel components** for enterprise features
- **Dependency Injection Container** for service management
- **Custom Router** with middleware support
- **Service Providers** for modular architecture

### **Frontend Application**
- **React 18 SPA** with TypeScript
- **Tailwind CSS** for modern styling
- **Vite** for fast development and building
- **Zustand** for state management
- **React Router** for navigation

### **Database & Storage**
- **MySQL 8.0+ / MariaDB 10.5+** support
- **Comprehensive schema** for Islamic content
- **Multi-language support** with RTL languages
- **File storage** with media management
- **Caching system** for performance

## 🔧 Development Setup

### **Prerequisites**
- PHP 8.2+
- Node.js 18+
- MySQL 8.0+ or MariaDB 10.5+
- Apache with mod_rewrite
- Composer and NPM

### **Local Development**
```bash
# Clone repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install dependencies
composer install
npm install

# Build frontend
npm run build:shared-hosting

# Start development server
php -S localhost:8000 -t public/
```

### **Testing**
```bash
# Run PHP tests
php test.php

# Run markdown linting
markdownlint docs/*.md

# Check code quality
composer run cs
composer run stan
```

## 🌐 Deployment

### **Shared Hosting Strategy**
- **"Build Locally, Deploy Built Assets"** approach
- Frontend assets built and optimized locally
- Only built files and PHP source uploaded
- Web-based installation wizard included

### **Production Considerations**
- Environment-based configuration
- Security headers and hardening
- Performance optimization
- Error handling and logging
- Backup and monitoring

## 📖 Documentation Standards

### **File Headers**
All documentation files include:
- Author: Khalid Abdullah
- Version: 0.0.1
- Date: 2025-08-30
- License: AGPL-3.0

### **Markdown Standards**
- Consistent heading structure
- Code blocks with language specification
- Proper link formatting
- Table of contents where appropriate
- Regular updates and maintenance

## 🤝 Contributing

### **Documentation Updates**
- Follow existing format and structure
- Include required metadata headers
- Use clear, concise language
- Provide practical examples
- Update related documents when needed

### **Code Documentation**
- PHPDoc comments for PHP classes
- JSDoc comments for JavaScript/TypeScript
- Inline comments for complex logic
- README files for each major component

## 📞 Support

### **Getting Help**
- Check this documentation first
- Review [Release Notes](release/RELEASE_NOTES_0.0.1.md) for known issues
- Check [Changelog](release/CHANGELOG.md) for recent changes
- Review deployment guides for common issues

### **Reporting Issues**
- Document the exact error message
- Include your environment details
- Check if the issue is documented
- Provide steps to reproduce

## 📅 Documentation Updates

### **Version 0.0.1** (Current)
- Initial documentation set
- Complete framework overview
- Deployment guides for shared hosting
- Release notes and changelog
- Architecture and implementation details

### **Future Updates**
- User guides and tutorials
- API documentation
- Admin panel documentation
- Mobile app documentation
- Performance optimization guides

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0 