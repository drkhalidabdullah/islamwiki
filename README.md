# IslamWiki - Social Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.7-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-AGPL-blue.svg)](LICENSE)

A modern, social Islamic knowledge platform that combines the best of Wikipedia and social media. Built with PHP and featuring a comprehensive wiki system, social networking, and advanced search capabilities.

## 🎯 Current Version: 0.0.0.8

**Latest Release:** January 27, 2025  
**Status:** Production Ready ✅  
**Type:** Major Community Features Release

## 🚀 What's New in v0.0.0.8

### 👥 **Community Groups & Events System**
- **Community Groups**: Create and join public, private, or restricted groups
- **Group Management**: Admin, moderator, and member roles with proper permissions
- **Community Events**: Organize online, offline, and hybrid events
- **Event Attendance**: Track and manage event attendees
- **Group Posts**: Share content within specific community groups

### 🔍 **Enhanced Search Engine**
- **Multi-Content Search**: Search across Wiki Pages, Posts, People, Groups, Events, and Ummah content
- **Advanced Filtering**: Left sidebar with content type filters and category selection
- **Smart Suggestions**: Popular searches and trending topics in sidebar
- **Search Analytics**: Comprehensive search query tracking and optimization
- **Professional Interface**: Modern design with rich result previews

### 🎨 **Improved User Experience**
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Smart No-Results**: Helpful suggestions when no results found
- **Search History**: Track and display recent searches for logged-in users
- **Content Type Icons**: Visual icons for each content type in search results
- **Rich Result Previews**: Detailed result cards with metadata and engagement metrics

### 🏗️ **Technical Improvements**
- **New Database Schema**: Groups, events, and enhanced search analytics tables
- **Full-Text Search**: Optimized database indexes for better search performance
- **AJAX Integration**: Real-time search with debounced input and instant results
- **Performance Optimization**: Efficient queries with proper indexing and caching
- **Enhanced Security**: Improved input validation and SQL injection protection

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
- **Community Groups**: Join and create groups with different privacy levels
- **Community Events**: Organize and attend online, offline, and hybrid events
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

### **Version 0.0.0.8** (Released)
- Community groups and events system
- Enhanced search engine with multi-content support
- Advanced filtering and search analytics
- Improved mobile experience and responsive design

### **Version 0.1.0** (Future)
- Multi-language support
- Advanced analytics
- API improvements
- Plugin system

---

**Built with ❤️ for the Muslim community**

*IslamWiki - Connecting knowledge, building community*
