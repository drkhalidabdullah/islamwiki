# IslamWiki - Social Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.21-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-AGPL-blue.svg)](LICENSE)

A modern, social Islamic knowledge platform that combines the best of Wikipedia and social media. Built with PHP and featuring a comprehensive wiki system, social networking, advanced search capabilities, and a powerful admin system.

## 🎯 Current Version: 0.0.0.21

**Latest Release:** September 2025  
**Status:** Production Ready ✅  
**Type:** Feature Enhancement - UI Polish & User Experience

## 🚀 What's New in v0.0.0.21

### 🎨 **Enhanced Authentication Experience**
- **Clean Login/Register Pages**: Streamlined authentication interface with minimal distractions
- **Smart UI Hiding**: Navigation elements automatically hidden for non-authenticated users
- **Centered Branding**: Logo and site name properly centered on authentication pages
- **Newsbar Management**: Newsbar hidden on login/register pages for cleaner experience
- **Responsive Design**: Perfect layout on all screen sizes

### 🔧 **UI/UX Improvements**
- **Footer Layout Fixes**: Fixed footer visibility and positioning issues
- **Flexbox Redesign**: Complete layout redesign using modern flexbox for better stability
- **Button State Management**: Fixed login/signup button hiding logic
- **Overflow Handling**: Prevented horizontal scrolling and content overflow issues
- **Visual Hierarchy**: Improved spacing and alignment throughout the interface

### 🚀 **Technical Improvements**
- **Courses System Fix**: Fixed missing functions for course functionality
- **Layout Architecture**: Redesigned footer and header layouts for better maintainability
- **CSS Optimization**: Cleaner, more maintainable CSS with proper positioning
- **Error Prevention**: Added proper overflow handling and content containment

## 🚀 Previous Release: v0.0.0.19

### 🎯 **Header Dashboard & Sidebar System**
- **Modern Header Dashboard**: Fixed header with integrated search, create button, and user menu
- **Smart Sidebar Management**: Toggle-able left and right sidebars with state persistence
- **Enhanced Navigation**: Site logo, news toggle, and friends profile display
- **Mobile Responsive**: Optimized for all screen sizes with touch-friendly controls
- **State Persistence**: User preferences saved using localStorage
- **Z-Index Management**: Proper layering of all UI elements

### 🔧 **Technical Improvements**
- **CSS Architecture**: Complete framework for new header system
- **JavaScript State Management**: localStorage integration for user preferences
- **PHP Backend**: New API endpoints for friends profiles and sidebar data
- **Mobile Detection**: Screen size detection for responsive behavior
- **Event Handling**: Proper event listeners with preventDefault and stopPropagation

## 🚀 Previous Release: v0.0.0.18

### 📝 **Revolutionary Wiki Editor System**
- **Professional Toolbar**: Complete redesign with properly sized, clickable buttons
- **Rich Text Formatting**: Bold, italic, code, headings with intuitive toolbar
- **Wiki Link Support**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **External Link Support**: `[url]` and `[url text]` syntax with proper validation
- **Live Preview**: Real-time preview with server-side wiki syntax parsing
- **Mobile Responsive**: Optimized toolbar for mobile and desktop devices
- **Smart Button Sizing**: Content properly scales to fill button areas

### 🔗 **Enhanced Reference System**
- **Clickable Reference Links**: References now parse internal and external links as clickable
- **Internal Link Support**: `[[Page Name]]` in references become proper wiki links
- **External Link Support**: `[url]` in references become clickable external links
- **Security Features**: External links open in new tabs with proper security attributes
- **Slug Generation**: Internal links use proper slug generation for navigation
- **HTML Escaping**: All link text properly escaped for security

### 🎨 **UI/UX Improvements**
- **Professional Toolbar Design**: Clean, modern toolbar with proper spacing
- **Button Hover Effects**: Smooth animations and visual feedback
- **Consistent Styling**: Unified design language across all editor components
- **Accessibility**: Better contrast, keyboard navigation, and screen reader support
- **Mobile Optimization**: Touch-friendly buttons and responsive layout

### 🔧 **Technical Enhancements**
- **Server-Side Parsing**: References processed through comprehensive link parser
- **Template System**: Enhanced template parsing for complex wiki syntax
- **Error Handling**: Improved error handling and user feedback
- **Performance**: Optimized parsing and rendering for better performance
- **Code Quality**: Enhanced code organization and documentation

## 🚀 What's New in v0.0.0.16

### 🎨 **Admin Dashboard Improvements**
- **Simplified Color Scheme**: Implemented consistent CSS variables across admin interface
- **Modern Design**: Replaced complex gradients with clean, professional colors
- **Better Accessibility**: Improved contrast and readability throughout admin panels
- **Unified Theming**: All admin components now use the same color variables for consistency

### 🔔 **Notification System Fixes**
- **500 Error Resolution**: Fixed critical server errors in notifications API that prevented loading
- **Error Handling**: Added comprehensive try-catch blocks for all database queries
- **Graceful Degradation**: System continues working even when individual queries fail
- **Better Debugging**: Enhanced error logging and debug features for troubleshooting
- **Session Handling**: Fixed session cookie transmission in API requests
- **Improved UX**: Better error messages and fallback behavior for users

## 🚀 Features

### 📚 **Advanced Wiki System**
- **Three-Column Layout**: Contents sidebar, main content, and Tools sidebar for enhanced navigation
- **Sticky Sidebar Navigation**: Contents and Tools sidebars scroll with content and stick to viewport
- **Table of Contents (TOC)**: Auto-generated TOC with smooth scrolling and active section highlighting
- **Wikipedia-Style Special Pages**: "What Links Here" and "Page Information" pages with advanced filtering
- **Multiple Citation Formats**: MLA 9th, APA 7th, Chicago 17th, Harvard, and IEEE citation support
- **Professional Wiki Editor**: Rich text editor with comprehensive toolbar and live preview
- **Wiki-Style Linking**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **Reference System**: Clickable internal and external links in references
- **Version Control**: Complete article history and restoration
- **Smart Link Detection**: Existing pages (blue) vs missing pages (red)
- **Clean URLs**: SEO-friendly URLs like `/wiki/Islam` instead of `/wiki/article.php?slug=islam`
- **Template System**: Support for complex MediaWiki-style templates
- **Magic Words**: Support for `__NOTITLE__`, `__NOCAT__`, and other magic words
- **Category System**: Full category management with automatic categorization

### 🔍 **Advanced Search System**
- **Universal Search**: Search across articles, users, messages, and all content types
- **Search Overlay**: Full-screen search with proper z-index layering and backdrop effects
- **Keyboard Shortcuts**: Press `/` to quickly open search overlay from anywhere on the page
- **ESC Key Support**: Close search overlay and all modals with ESC key
- **Smart Filtering**: Filter by content type, category, date range, author, and popularity
- **Real-time Suggestions**: Auto-complete search suggestions as you type
- **Search History**: Track and display recent searches for logged-in users
- **Result Highlighting**: Highlight search terms in results for better visibility
- **Professional Interface**: Clean, responsive design with advanced search options
- **Search Analytics**: Track popular searches and optimize content discovery

### 👥 **Social Networking Features**
- **News Feed Dashboard**: Modern 3-column social media-style dashboard
- **Interactive Post Creation**: Inline post creation with markdown editor and live preview
- **Image Upload System**: Copy/paste image support with automatic scaling and preview
- **Social Engagement**: Like, comment, and share functionality for posts
- **Real-Time Messaging**: Facebook Messenger-style interface with three-column layout
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
- **Professional Toolbar**: Rich text editor with properly sized, clickable buttons
- **Live Preview**: Real-time content preview with server-side parsing

### 🔐 **Security & Performance**
- **Secure Authentication**: Robust user authentication and session management
- **SQL Injection Protection**: Prepared statements and input sanitization
- **XSS Prevention**: Output escaping and content security policies
- **Rate Limiting**: Protection against spam and abuse
- **Database Optimization**: Efficient queries and proper indexing
- **Caching System**: Redis-based caching for improved performance
- **Link Security**: External links open in new tabs with proper security attributes
- **Input Validation**: Comprehensive input validation and sanitization

### 🛠️ **Admin System**
- **Comprehensive Admin Dashboard**: Unified admin interface with organized sections
- **Feature Toggle System**: Complete control over core system features
- **System Health Monitoring**: Real-time health indicators for database, storage, memory
- **Permission Management**: Role-based access control with granular permissions
- **User Management**: Complete user administration with role assignment
- **Content Moderation**: Tools for moderating posts, articles, and user content
- **Analytics Dashboard**: Comprehensive analytics and reporting
- **System Settings**: Centralized configuration management
- **Maintenance Mode**: System-wide maintenance control

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
│   │   ├── notifications/ # Notification API
│   │   └── search/        # Search API
│   ├── assets/            # Static assets
│   │   ├── css/           # Stylesheets
│   │   ├── js/            # JavaScript files
│   │   └── images/        # Images and icons
│   ├── config/            # Configuration files
│   ├── includes/          # Shared PHP includes
│   │   └── markdown/      # Markdown and Wiki parsers
│   ├── modules/           # Feature modules
│   │   ├── friends/       # Friends system
│   │   ├── messages/      # Messaging system
│   │   └── wiki/          # Wiki functionality
│   ├── pages/             # Page controllers
│   │   ├── admin/         # Admin pages
│   │   ├── auth/          # Authentication
│   │   ├── social/        # Social features
│   │   ├── user/          # User management
│   │   └── wiki/          # Wiki pages
│   ├── search/            # Search functionality
│   ├── skins/             # Theme system
│   │   └── bismillah/     # Default theme
│   └── uploads/           # User uploads
├── database/              # Database migrations
├── docs/                  # Documentation
│   ├── api/               # API documentation
│   ├── architecture/      # System architecture
│   ├── changelogs/        # Version changelogs
│   ├── features/          # Feature documentation
│   ├── releases/          # Release notes
│   ├── security/          # Security documentation
│   └── user/              # User guides
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
- **[User Guides](docs/user/)** - How-to guides and tutorials
- **[Feature Documentation](docs/features/)** - Detailed feature explanations
- **[Security Guide](docs/security/)** - Security features and best practices
- **[Changelog](docs/changelogs/CHANGELOG.md)** - Version history
- **[Release Notes](docs/releases/RELEASE_NOTES.md)** - Detailed release information

## 🔍 **Wiki Editor Features**

### **Rich Text Toolbar**
- **Formatting**: Bold, italic, code with properly sized buttons
- **Headings**: H1, H2, H3 with appropriate sizing
- **Links**: Wiki links `[[Page]]` and external links `[url]`
- **Lists**: Bullet and numbered lists with proper formatting
- **Quotes**: Blockquote formatting
- **Preview**: Live preview with server-side parsing

### **Wiki Syntax Support**
- **Internal Links**: `[[Page Name]]` and `[[Page Name|Display Text]]`
- **External Links**: `[url]` and `[url text]`
- **Templates**: `{{Template Name}}` with parameter support
- **References**: `<ref>content</ref>` with clickable links
- **Categories**: `[[Category:Name]]` for article categorization
- **Magic Words**: `__NOTITLE__`, `__NOCAT__`, etc.

### **Reference System**
- **Clickable Links**: All links in references are clickable
- **Internal Links**: `[[Page]]` becomes proper wiki navigation
- **External Links**: `[url]` opens in new tab with security attributes
- **Link Validation**: URLs are validated before being made clickable
- **Security**: Proper HTML escaping and security attributes

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

### **Version 0.0.0.21** (Current)
- Enhanced authentication experience with clean UI
- Improved footer and layout architecture
- Fixed courses system functionality
- Better responsive design and overflow handling

### **Version 0.1.0** (Future)
- Multi-language support
- Advanced analytics
- API improvements
- Plugin system
- Mobile app development

---

**Built with ❤️ for the Muslim community**

*IslamWiki - Connecting knowledge, building community*