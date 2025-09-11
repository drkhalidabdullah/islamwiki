# IslamWiki - Social Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.11-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-AGPL-blue.svg)](LICENSE)

A modern, social Islamic knowledge platform that combines the best of Wikipedia and social media. Built with PHP and featuring a comprehensive wiki system, social networking, and advanced search capabilities.

## 🎯 Current Version: 0.0.0.11

**Latest Release:** September 11, 2025  
**Status:** Production Ready ✅  
**Type:** Major Feature Release - News Feed Dashboard

## 🚀 What's New in v0.0.0.11

### 🎨 **Revolutionary News Feed Dashboard**
- **3-Column Responsive Layout**: Modern social media-style dashboard with left sidebar, main feed, and right sidebar
- **Unified Content Feed**: Single feed displaying posts and articles with smart filtering
- **Interactive Post Creation**: Inline post creation with markdown editor and live preview
- **Image Upload System**: Copy/paste image support with automatic scaling and preview
- **Social Engagement**: Like, comment, and share functionality for posts
- **Content Management**: Personal content sections (My Content, Watchlist, Following)

### 📱 **Enhanced User Experience**
- **Real-time Interactions**: AJAX-powered likes, comments, and social features
- **Smart Content Filtering**: All, Posts, Articles, and Following filters with state persistence
- **Visual Trending Section**: Interactive trending topics with visual indicators
- **Quick Actions Panel**: Streamlined access to common actions (New Article, New Post, etc.)
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Toast Notifications**: Real-time feedback for user actions

### 🔧 **Technical Improvements**
- **Markdown Parser Enhancement**: Server-side and client-side markdown processing
- **Image Processing**: Automatic image scaling and optimization for uploads
- **Database Optimization**: Enhanced queries with proper joins and indexing
- **JavaScript Architecture**: Modular, maintainable frontend code structure
- **API Endpoints**: RESTful APIs for all social interactions
- **Error Handling**: Comprehensive error handling and user feedback

### 🐛 **Bug Fixes & Stability**
- **Image Upload Fixes**: Resolved persistent image upload failures
- **JavaScript Scope Issues**: Fixed variable scope problems in complex functions
- **Markdown Rendering**: Fixed markdown display in content previews
- **UI Layout Fixes**: Resolved button truncation and hover effects
- **Content Filtering**: Fixed Following filter to show only followed users' content

## 🚀 What's New in v0.0.0.10

### 🐛 **Critical Bug Fixes**
- **Fixed create_post.php 500 Error**: Resolved internal server error when creating posts
- **Fixed create_article.php Draft Status**: Resolved 500 error when creating draft articles
- **Fixed Profile Posts Display**: Resolved issue where user posts weren't showing in profiles
- **Fixed Markdown Parsing**: Resolved regex error preventing markdown rendering

### ✨ **New Features & Enhancements**
- **Side-by-Side Live Preview**: Real-time markdown preview for create_post page
- **User Watchlist System**: Complete watchlist functionality for tracking article changes
- **Article History Timestamps**: Added detailed timestamps with relative time display
- **Enhanced Error Handling**: Better user feedback throughout the application

### 🔧 **Technical Improvements**
- **Enhanced Database Functions**: Added comprehensive user post management
- **Markdown Parser Improvements**: Enhanced markdown processing capabilities
- **Responsive Design**: Improved mobile and tablet experience
- **Better Error Handling**: Comprehensive error logging and debugging

## 🚀 What's New in v0.0.0.9

### 🎨 **Enhanced User Interface & Experience**
- **Sidebar Navigation Improvements**: Added visual separators between navigation sections
- **Search Popup Enhancement**: Full-screen search overlay with real-time suggestions
- **Dropdown Menu Positioning**: Fixed sidebar dropdown positioning to eliminate gaps
- **Search Results Optimization**: Made entire result containers clickable for better UX
- **Article Page Redesign**: Transparent content containers for cleaner appearance
- **Category Button Fixes**: Ensured category buttons are properly clickable

### 🔍 **Search System Enhancements**
- **Search Popup Integration**: Inline search popup with AJAX-powered suggestions
- **Search Results Layout**: Improved horizontal alignment of icons and titles
- **Content Type Filters**: Converted radio buttons to clean link-based filters
- **Search Page Styling**: Consolidated conflicting CSS files into organized structure
- **Real-time Suggestions**: Enhanced search suggestions with proper API endpoints

### 🐛 **Bug Fixes & Improvements**
- **URL Routing**: Fixed all navigation links and clean URL implementation
- **PHP Path Issues**: Corrected include paths using `__DIR__` for better reliability
- **CSS Conflicts**: Resolved multiple CSS file conflicts affecting styling
- **Mobile Responsiveness**: Improved mobile experience across all components
- **Error Handling**: Enhanced error handling for better user experience

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
- **News Feed Dashboard**: Modern 3-column social media-style dashboard
- **Interactive Post Creation**: Inline post creation with markdown editor and live preview
- **Image Upload System**: Copy/paste image support with automatic scaling and preview
- **Social Engagement**: Like, comment, and share functionality for posts
- **Real-Time Messaging**: Instant messaging with live updates
- **Friends System**: Add friends, send requests, and manage connections
- **Friend Suggestions**: AI-powered friend recommendations
- **Social Posts**: Create and share posts with the community
- **Community Groups**: Join and create groups with different privacy levels
- **Community Events**: Organize and attend online, offline, and hybrid events
- **Notifications**: Real-time notifications for messages, friend requests, and interactions
- **User Profiles**: Comprehensive user profiles with bio, interests, and activity
- **Content Management**: Personal content sections (My Content, Watchlist, Following)

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
