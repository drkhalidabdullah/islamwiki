# IslamWiki - Social Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.6-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-AGPL-blue.svg)](LICENSE)

A modern, social Islamic knowledge platform that combines the best of Wikipedia and social media. Built with PHP and featuring a comprehensive wiki system, social networking, and community features.

## 🎯 Current Version: 0.0.0.6

**Latest Release:** September 7, 2025  
**Status:** Production Ready ✅  
**Type:** Major Restructuring & Enhancement Release

## 🚀 What's New in v0.0.0.6

### Complete File Restructuring
- **Industry-standard directory layout** with organized file structure
- **Modular architecture** with pages/, modules/, api/, config/, includes/ directories
- **Better code maintainability** and developer experience
- **Improved separation of concerns** for future development

### Clean URL Implementation
- **Full clean URL system** via .htaccess rewrite rules
- **No more .php extensions** in user-facing URLs
- **SEO-friendly URLs** for better search engine optimization
- **25+ clean URL routes** implemented across the application

### Enhanced User Experience
- **Conditional navigation** showing/hiding based on login status
- **Professional chat options dropdown** with toggle switches
- **Improved header design** with better icon organization
- **Smart navigation highlighting** based on current page

## 🚀 Features

### 📚 **Advanced Wiki System**
- **Markdown-First Editing**: Rich text editor with comprehensive toolbar
- **Wiki-Style Linking**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **Live Preview**: Real-time markdown rendering
- **Version Control**: Complete article history and restoration
- **Smart Link Detection**: Existing pages (blue) vs missing pages (red)
- **Clean URLs**: SEO-friendly URLs like `/wiki/Islam` instead of `/wiki/article.php?slug=islam`

### 👥 **Social Networking Features**
- **Real-Time Messaging**: Instant messaging with live updates
- **Friends System**: Add friends, send requests, and manage connections
- **Friend Suggestions**: AI-powered friend recommendations
- **Social Posts**: Create and share posts with the community
- **Notifications**: Real-time notifications for messages, friend requests, and interactions
- **User Profiles**: Comprehensive user profiles with customizable information

### 🎨 **Modern User Interface**
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Clean Navigation**: Intuitive navigation with conditional display
- **Professional Styling**: Modern, clean design with consistent theming
- **Interactive Elements**: Smooth animations and transitions
- **Accessibility**: Built with accessibility best practices

### 🔧 **Technical Features**
- **Clean URL System**: All routes use clean URLs without .php extensions
- **Modular Architecture**: Organized code structure for better maintainability
- **Real-Time Features**: Messaging and notifications with AJAX
- **Security**: Proper authentication and authorization
- **Performance**: Optimized for speed and efficiency

## 🏗️ Project Structure

### Directory Layout
```
html/
├── public/                    # Web-accessible files
│   ├── pages/                # Page files organized by category
│   │   ├── auth/            # Authentication pages
│   │   ├── user/            # User-related pages
│   │   ├── social/          # Social features
│   │   ├── wiki/            # Wiki management
│   │   └── admin/           # Administrative pages
│   ├── modules/             # Modular components
│   │   ├── wiki/           # Wiki module
│   │   └── friends/        # Friends module
│   ├── api/                # API endpoints
│   │   └── ajax/          # AJAX endpoints
│   ├── config/             # Configuration files
│   ├── includes/           # Shared includes
│   ├── assets/             # Static assets
│   └── .htaccess          # URL rewriting rules
├── docs/                   # Documentation
├── database_migration_*.sql # Database migration files
└── README.md              # Project overview
```

## 🚀 Quick Start

### Prerequisites
- **Apache web server** with mod_rewrite enabled
- **PHP 7.4+** with standard extensions
- **MySQL 5.7+** database
- **Proper file permissions** for web server

### Installation
1. **Clone or download** the project files
2. **Upload to web server** in the appropriate directory
3. **Configure database** connection in `public/config/config.php`
4. **Run database migrations** using the provided SQL files
5. **Set proper permissions** for uploads and cache directories
6. **Test all routes** to ensure proper functionality

### Configuration
- **Database settings**: Update `public/config/config.php`
- **Site settings**: Configure in admin panel after installation
- **File permissions**: Ensure web server can read/write to necessary directories

## 🔧 Development

### File Organization
- **Pages**: All page files in `public/pages/` organized by category
- **Modules**: Reusable components in `public/modules/`
- **API**: AJAX endpoints in `public/api/ajax/`
- **Config**: Configuration files in `public/config/`
- **Includes**: Shared PHP files in `public/includes/`

### URL Routing
- **Clean URLs**: All routes use clean URLs via .htaccess
- **Rewrite Rules**: Comprehensive .htaccess configuration
- **Route Protection**: Proper authentication for protected routes
- **API Endpoints**: AJAX endpoints properly routed

### Code Standards
- **PHP**: Follow PSR standards where applicable
- **HTML**: Semantic markup with proper accessibility
- **CSS**: Organized stylesheets with consistent naming
- **JavaScript**: Modern ES6+ with proper error handling

## 📊 Version History

### v0.0.0.6 (Current) - Major Restructuring
- Complete file system reorganization
- Clean URL implementation
- Enhanced navigation and user experience
- Comprehensive route management
- Friends module enhancement
- Technical improvements and bug fixes

### v0.0.0.5 - Real-Time Messaging
- Real-time messaging and notifications system
- Comprehensive friends and social networking
- Enhanced user interface and navigation
- AJAX-powered interactions
- Database integration improvements

### v0.0.0.4 - Previous Features
- Additional features and improvements
- Bug fixes and optimizations

### v0.0.0.3 - Previous Features
- Feature additions and enhancements
- Performance improvements

### v0.0.0.2 - Previous Features
- Early feature development
- Core functionality implementation

### v0.0.0.1 - Initial Release
- Basic wiki functionality
- User authentication system
- Core features implementation

## 🤝 Contributing

### Development Guidelines
- **Follow the established file structure**
- **Use clean URLs for all new routes**
- **Maintain proper include paths**
- **Test all functionality thoroughly**
- **Update documentation for new features**

### Code Organization
- **Pages**: Place new pages in appropriate category directory
- **Modules**: Create reusable components in modules directory
- **API**: Add new AJAX endpoints in api/ajax directory
- **Assets**: Organize CSS/JS files in assets directory

## 📞 Support

### Documentation
- **Complete documentation** available in docs/ directory
- **API reference** for developers
- **User guides** for end users
- **Architecture guides** for system administrators

### Issues and Bugs
- **Report issues** through appropriate channels
- **Include version information** when reporting bugs
- **Provide detailed reproduction steps**
- **Check existing documentation** before reporting

## 📄 License

This project is licensed under the terms specified in the LICENSE file.

## �� Achievement Summary

Version 0.0.0.6 represents a **major milestone** in the IslamWiki project development:

✅ **Complete file restructuring** with industry-standard organization  
✅ **Full clean URL implementation** for better SEO and UX  
✅ **Comprehensive route management** with proper .htaccess rules  
✅ **Enhanced user interface** with conditional navigation  
✅ **Improved chat system** with professional options menu  
✅ **All bugs resolved** and functionality preserved  
✅ **Better code organization** for future development  
✅ **Enhanced security** with proper route protection  

---

**IslamWiki v0.0.0.6** - A modern Islamic knowledge platform with clean architecture and enhanced user experience.

**Last Updated:** September 7, 2025  
**Documentation Version:** 0.0.0.6  
**Status:** Production Ready ✅
