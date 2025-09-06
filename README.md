# IslamWiki - Social Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.4-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-8.1+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A modern, social Islamic knowledge platform that combines the best of Wikipedia and social media. Built with PHP and featuring a comprehensive wiki system, social networking, and community features.

## 🚀 Features

### 📚 **Advanced Wiki System**
- **Markdown-First Editing**: Rich text editor with comprehensive toolbar
- **Wiki-Style Linking**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **Live Preview**: Real-time markdown rendering
- **Version Control**: Complete article history and restoration
- **Smart Link Detection**: Existing pages (blue) vs missing pages (red)
- **Clean URLs**: SEO-friendly URLs like `/wiki/Islam` instead of `/wiki/article.php?slug=islam`

### 👥 **Social Networking Features**
- **User Profiles**: Facebook-style profiles with cover photos, avatars, and social stats
- **Follow System**: Follow/unfollow other users with real-time AJAX updates
- **User Posts**: Create and share text posts with privacy controls
- **Activity Feed**: Personalized feed showing posts from followed users
- **Post Interactions**: Like, comment, and share posts with engagement tracking
- **Privacy Controls**: Public, community, followers-only, and private content visibility
- **Social Statistics**: Track followers, following, articles, and posts counts

### 🎨 **Modern User Interface**
- **Facebook-Style Header**: Clean, modern header with global search and user dropdown
- **Responsive Design**: Mobile-first design that works on all devices
- **Dark/Light Themes**: User preference-based theme switching
- **Interactive Elements**: Smooth animations and hover effects
- **Accessibility**: ARIA labels and keyboard navigation support

### 🔧 **Enhanced User Management**
- **Role-Based Access**: Admin, moderator, and user roles with different permissions
- **User Profiles**: Comprehensive profiles with bio, interests, and expertise areas
- **Activity Tracking**: User activity logs and engagement metrics
- **Privacy Settings**: Granular privacy controls for user data
- **Account Management**: Profile editing, password changes, and account settings

### 🛡️ **Security & Performance**
- **Secure Authentication**: Password hashing, session management, and CSRF protection
- **Input Validation**: Comprehensive input sanitization and validation
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Proper output escaping and content filtering
- **Performance Optimization**: Database indexing, query optimization, and caching

### 📱 **Mobile-First Design**
- **Responsive Layout**: Optimized for mobile, tablet, and desktop
- **Touch-Friendly**: Large touch targets and gesture support
- **Fast Loading**: Optimized assets and lazy loading
- **Offline Support**: Basic offline functionality for reading

## 🎯 **What's New in v0.0.0.4**

### 🆕 **Major Features**
- **Social Media Integration**: Complete social networking system with profiles, posts, and feeds
- **Facebook-Style Interface**: Modern, clean design inspired by popular social platforms
- **User Dropdown**: Fixed and fully functional user dropdown with admin panel access
- **Clean URL System**: All URLs now use clean format without .php extensions
- **Enhanced Admin Panel**: Fixed admin access issues and improved functionality

### 🐛 **Critical Bug Fixes**
- **User Dropdown**: Resolved CSS conflicts causing dropdown not to show
- **Admin Access**: Fixed 403 Forbidden error when accessing admin panel
- **Search Functionality**: Resolved 500 errors in wiki search
- **Header Styling**: Fixed asset path issues and broken navigation links
- **Homepage Content**: Resolved blank content area below header

### 🔧 **Technical Improvements**
- **Database Schema**: New tables for social features with proper indexing
- **CSS Architecture**: Unified styling system with resolved conflicts
- **JavaScript Enhancement**: Modern event handling and AJAX functionality
- **URL Routing**: Clean URL system with proper .htaccess configuration

## 📋 **System Requirements**

### Minimum Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Apache**: 2.4 with mod_rewrite enabled
- **Memory**: 256MB RAM minimum
- **Storage**: 100MB free space

### Required PHP Extensions
- PDO
- PDO_MySQL
- JSON
- Session
- OpenSSL
- mbstring
- fileinfo

## 🚀 **Quick Start**

### Installation
1. **Clone the repository**:
   ```bash
   git clone https://github.com/drkhalidabdullah/islamwiki.git
   cd islamwiki
   ```

2. **Set up the database**:
   ```bash
   mysql -u root -p < setup_database.sql
   mysql -u root -p < database_migration_v0.0.0.4.sql
   ```

3. **Configure the application**:
   ```bash
   cp public/config/config.php.example public/config/config.php
   # Edit config.php with your database credentials
   ```

4. **Set up the web server**:
   - Point your web server document root to the `public/` directory
   - Ensure mod_rewrite is enabled for clean URLs
   - Set proper file permissions

5. **Access the application**:
   - Visit your domain in a web browser
   - Default admin credentials: `admin` / `password`

### Default Admin Account
- **Username**: `admin`
- **Password**: `password`

## 📖 **Documentation**

- **[Installation Guide](docs/guides/INSTALLATION.md)** - Detailed setup instructions
- **[User Manual](docs/guides/USER_MANUAL.md)** - How to use IslamWiki features
- **[Admin Guide](docs/guides/ADMIN_GUIDE.md)** - Administration and management
- **[API Documentation](docs/api/)** - RESTful API endpoints
- **[Architecture](docs/architecture/)** - System architecture and design
- **[Changelog](docs/changelogs/CHANGELOG.md)** - Complete version history

## 🏗️ **Architecture**

### Technology Stack
- **Backend**: PHP 8.1+ with PDO for database access
- **Database**: MySQL 8.0+ with optimized schema
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.0
- **Server**: Apache 2.4 with mod_rewrite

### Project Structure
```
islamwiki/
├── public/                 # Web-accessible files
│   ├── admin/             # Admin panel files
│   ├── assets/            # CSS, JS, images
│   ├── config/            # Configuration files
│   ├── includes/          # Shared PHP includes
│   ├── wiki/              # Wiki functionality
│   └── *.php              # Main application files
├── docs/                  # Documentation
│   ├── api/               # API documentation
│   ├── architecture/      # System architecture
│   ├── changelogs/        # Version changelogs
│   ├── guides/            # User guides
│   └── releases/          # Release notes
└── *.sql                  # Database files
```

## 🤝 **Contributing**

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Setup
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and test thoroughly
4. Commit your changes: `git commit -m 'Add amazing feature'`
5. Push to the branch: `git push origin feature/amazing-feature`
6. Open a Pull Request

### Code Standards
- Follow PSR-12 coding standards for PHP
- Use semantic versioning for releases
- Write comprehensive tests
- Document all new features
- Follow security best practices

## 📊 **Roadmap**

### v0.0.0.5 (Planned)
- **Real-time Notifications**: WebSocket-based notifications
- **Advanced Search**: Full-text search with filters
- **File Uploads**: Image and document upload system
- **Email Integration**: Email notifications and verification
- **API Endpoints**: RESTful API for mobile apps

### v0.1.0 (Future)
- **Mobile App**: Native mobile application
- **Multi-language**: Internationalization support
- **Advanced Analytics**: User behavior tracking
- **Content Moderation**: Automated content filtering
- **Performance Monitoring**: Real-time performance metrics

## 🐛 **Bug Reports & Feature Requests**

- **Issues**: [GitHub Issues](https://github.com/drkhalidabdullah/islamwiki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/drkhalidabdullah/islamwiki/discussions)
- **Security**: Report security issues privately to security@islamwiki.com

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- **Contributors**: Thanks to all contributors who help improve IslamWiki
- **Community**: The Islamic community for feedback and support
- **Open Source**: Built on the shoulders of open source technologies

## 📞 **Support**

- **Documentation**: Check our comprehensive documentation
- **Community**: Join our community discussions
- **Email**: Contact support for technical assistance
- **GitHub**: Open an issue for bugs or feature requests

---

**Version**: 0.0.0.4  
**Release Date**: September 6, 2025  
**Status**: Latest Release  
**Codename**: "Social Community Platform"

[![GitHub stars](https://img.shields.io/github/stars/drkhalidabdullah/islamwiki?style=social)](https://github.com/drkhalidabdullah/islamwiki/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/drkhalidabdullah/islamwiki?style=social)](https://github.com/drkhalidabdullah/islamwiki/network)
[![GitHub issues](https://img.shields.io/github/issues/drkhalidabdullah/islamwiki)](https://github.com/drkhalidabdullah/islamwiki/issues)
