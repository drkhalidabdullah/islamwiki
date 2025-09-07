# IslamWiki - Social Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.7-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-AGPL-blue.svg)](LICENSE)

A modern, social Islamic knowledge platform that combines the best of Wikipedia and social media. Built with PHP and featuring a comprehensive wiki system, social networking, and advanced search capabilities.

## 🎯 Current Version: 0.0.0.7

**Latest Release:** September 7, 2025  
**Status:** Production Ready ✅  
**Type:** Major Search System Release

## 🚀 What's New in v0.0.0.7

### 🔍 **Comprehensive Search System**
- **Multi-content search** across articles, users, messages, and more
- **Advanced filtering** by content type, category, date range, and author
- **Smart search suggestions** with real-time auto-complete
- **Professional search interface** with clean, responsive design
- **Search analytics** and result optimization
- **Universal search integration** across all platform modules

### 🎨 **Enhanced User Interface**
- **Conditional navigation** showing appropriate options based on login status
- **Messages and notifications dropdowns** with real-time updates
- **Professional header design** with consistent styling across all pages
- **Mobile-responsive search** with touch-optimized interface
- **Smart search highlighting** and result previews

### 🏗️ **Technical Improvements**
- **Database optimization** with full-text search indexes
- **Clean URL system** for search functionality (`/search`, `/search/suggestions`)
- **AJAX-powered features** for real-time search suggestions
- **Performance optimization** with efficient search queries
- **Comprehensive error handling** and user feedback

## 🚀 Features

### 🔍 **Advanced Search System**
- **Universal Search**: Search across articles, users, messages, and all content types
- **Smart Filtering**: Filter by content type, category, date range, author, and popularity
- **Real-time Suggestions**: Auto-complete search suggestions as you type
- **Search History**: Track and display recent searches for logged-in users
- **Result Highlighting**: Highlight search terms in results for better visibility
- **Professional Interface**: Clean, responsive design with advanced search options
- **Search Analytics**: Track popular searches and optimize content discovery

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
- **User Profiles**: Comprehensive user profiles with bio, interests, and activity

### 🎨 **Modern User Interface**
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Professional Styling**: Clean, modern interface with consistent branding
- **Interactive Elements**: Hover effects, smooth transitions, and engaging animations
- **Accessibility**: Screen reader support, keyboard navigation, and high contrast options
- **Dark/Light Themes**: User preference-based theme switching
- **Progressive Web App**: Offline capabilities and app-like experience

### 🔐 **Security & Performance**
- **Secure Authentication**: Robust user authentication and session management
- **SQL Injection Protection**: Prepared statements and input sanitization
- **XSS Prevention**: Output escaping and content security policies
- **Rate Limiting**: Protection against spam and abuse
- **Database Optimization**: Efficient queries and proper indexing
- **Caching System**: Redis-based caching for improved performance

## 🛠️ **Technical Stack**

### **Backend**
- **PHP 7.4+**: Modern PHP with object-oriented programming
- **MySQL 5.7+**: Robust relational database with full-text search
- **PDO**: Secure database abstraction layer
- **Composer**: Dependency management and autoloading

### **Frontend**
- **HTML5**: Semantic markup and modern web standards
- **CSS3**: Advanced styling with Flexbox and Grid layouts
- **JavaScript (ES6+)**: Modern JavaScript with async/await
- **Font Awesome**: Professional icon library
- **Responsive Design**: Mobile-first approach with media queries

### **Infrastructure**
- **Apache/Nginx**: Web server with clean URL support
- **SSL/TLS**: Secure HTTPS connections
- **Git**: Version control and collaboration
- **Docker**: Containerization support (optional)

## 📁 **Project Structure**

```
islamwiki/
├── public/                 # Web-accessible files
│   ├── api/               # API endpoints
│   │   ├── ajax/          # AJAX handlers
│   │   ├── messages/      # Message API
│   │   └── notifications/ # Notification API
│   ├── assets/            # Static assets
│   │   ├── css/           # Stylesheets
│   │   ├── js/            # JavaScript files
│   │   └── images/        # Images and icons
│   ├── config/            # Configuration files
│   ├── includes/          # Shared PHP includes
│   ├── modules/           # Feature modules
│   │   ├── friends/       # Friends system
│   │   └── wiki/          # Wiki functionality
│   ├── pages/             # Page controllers
│   │   ├── admin/         # Admin pages
│   │   ├── auth/          # Authentication
│   │   ├── social/        # Social features
│   │   ├── user/          # User management
│   │   └── wiki/          # Wiki pages
│   ├── search/            # Search functionality
│   └── uploads/           # User uploads
├── database/              # Database migrations
├── docs/                  # Documentation
│   ├── api/               # API documentation
│   ├── architecture/      # System architecture
│   ├── changelogs/        # Version changelogs
│   ├── guides/            # User guides
│   └── releases/          # Release notes
├── scripts/               # Utility scripts
├── backups/               # Backup files
└── README.md              # This file
```

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional)

### **Installation**

1. **Clone the repository**
   ```bash
   git clone https://github.com/drkhalidabdullah/islamwiki.git
   cd islamwiki
   ```

2. **Configure the database**
   ```bash
   # Edit public/config/database.php
   # Set your database credentials
   ```

3. **Run the setup script**
   ```bash
   php public/setup.php
   ```

4. **Set up the web server**
   ```bash
   # Point your web server to the public/ directory
   # Ensure mod_rewrite is enabled for clean URLs
   ```

5. **Access the application**
   ```
   http://your-domain.com
   ```

### **Configuration**

- **Database**: Edit `public/config/database.php`
- **Site Settings**: Configure via admin panel
- **Clean URLs**: Ensure mod_rewrite is enabled
- **File Permissions**: Set appropriate permissions for uploads/

## 📖 **Documentation**

- **[API Documentation](docs/api/)** - Complete API reference
- **[Architecture Guide](docs/architecture/)** - System design and structure
- **[User Guides](docs/guides/)** - How-to guides and tutorials
- **[Changelog](docs/changelogs/CHANGELOG.md)** - Version history
- **[Release Notes](docs/releases/RELEASE_NOTES.md)** - Detailed release information

## 🔍 **Search System**

### **Search Features**
- **Universal Search**: Search across all content types
- **Advanced Filters**: Content type, category, date, author filtering
- **Smart Suggestions**: Real-time search suggestions
- **Result Highlighting**: Highlight search terms in results
- **Search History**: Track recent searches
- **Mobile Optimized**: Touch-friendly search interface

### **Search API**
- **RESTful Endpoints**: Clean API for search functionality
- **AJAX Support**: Real-time search suggestions
- **JSON Responses**: Structured search results
- **Error Handling**: Comprehensive error responses

## 🤝 **Contributing**

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### **Development Setup**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 **License**

This project is licensed under the AGPL License - see the [LICENSE](LICENSE) file for details.

## 🆘 **Support**

- **Documentation**: Check the [docs/](docs/) directory
- **Issues**: Report bugs via [GitHub Issues](https://github.com/drkhalidabdullah/islamwiki/issues)
- **Discussions**: Join our [GitHub Discussions](https://github.com/drkhalidabdullah/islamwiki/discussions)

## 🎯 **Roadmap**

### **Version 0.0.0.8** (Planned)
- Advanced search features (AI-powered search, semantic search)
- Enhanced mobile experience
- Performance optimizations
- Additional social features

### **Version 0.1.0** (Future)
- Multi-language support
- Advanced analytics
- API improvements
- Plugin system

---

**Built with ❤️ for the Muslim community**

*IslamWiki - Connecting knowledge, building community*
