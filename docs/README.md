# IslamWiki Documentation

Welcome to the comprehensive documentation for IslamWiki, a modern Islamic knowledge platform built with PHP.

## 🎯 Current Version: 0.0.0.18

**Latest Release:** January 2025  
**Status:** Production Ready ✅  
**Type:** Major Feature Enhancement - Wiki Editor & Reference System

## 🚀 What's New in v0.0.0.18

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

## 📚 Documentation Structure

### 📖 [User Guides](user/)
- **Installation Guide**: Step-by-step installation instructions
- **User Guide**: How to use IslamWiki features
- **Wiki Editor Guide**: Complete guide to the wiki editing system
- **Reference System Guide**: How to use the reference system
- **Template Guide**: Creating and using templates

### 🔧 [API Documentation](api/)
- **API Reference**: Complete API endpoint documentation
- **Authentication**: API authentication methods
- **Rate Limiting**: API usage limits and guidelines
- **Search API**: Search functionality documentation
- **Template API**: Template preview and management

### 🏗️ [Architecture](architecture/)
- **Architecture Overview**: System design and components
- **Database Schema**: Database structure and relationships
- **Security Guide**: Security features and best practices
- **Performance Guide**: Optimization and performance tips
- **Deployment Guide**: Production deployment instructions

### 🚀 [Features](features/)
- **Wiki Editor System**: Complete wiki editing documentation
- **Reference System**: Reference and citation system
- **Template System**: MediaWiki-style template system
- **Search System**: Advanced search capabilities
- **Social Features**: Social networking and community features
- **Admin System**: Administrative interface and tools

### 🔐 [Security](security/)
- **Permission System**: Role-based access control
- **Security Features**: Security implementation details
- **Best Practices**: Security recommendations
- **Audit Logging**: Security monitoring and logging

### 📝 [Changelogs](changelogs/)
- **CHANGELOG.md**: Complete version history
- **v0.0.0.18.md**: Latest release notes (Wiki Editor & Reference System)
- **v0.0.0.16.md**: Previous release notes (Admin Dashboard & Notifications)
- **v0.0.0.15.md**: Previous release notes (Documentation Updates)
- **v0.0.0.14.md**: Previous release notes (UI/UX Enhancements)
- **v0.0.0.13.md**: Previous release notes (Admin System Overhaul)
- **v0.0.0.12.md**: Previous release notes (Enhanced Wiki Experience)
- **v0.0.0.11.md**: Previous release notes (News Feed Dashboard)
- **v0.0.0.10.md**: Previous release notes (Bug Fixes & Enhancements)
- **v0.0.0.9.md**: Previous release notes (UI/UX Improvements)
- **v0.0.0.8.md**: Previous release notes (Community Groups & Events)
- **v0.0.0.7.md**: Previous release notes (Search System)
- **v0.0.0.6.md**: Previous release notes (Major Restructuring)
- **v0.0.0.5.md**: Previous release notes (Real-Time Messaging)
- **v0.0.0.4.md**: Previous release notes
- **v0.0.0.3.md**: Previous release notes
- **v0.0.0.2.md**: Previous release notes
- **v0.0.0.1.md**: Initial release notes

### 🚀 [Releases](releases/)
- **RELEASE_NOTES.md**: Current release information
- **Version History**: Complete release timeline

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
│   │   ├── friends/        # Friends module
│   │   └── messages/       # Messaging module
│   ├── api/                # API endpoints
│   │   ├── ajax/          # AJAX endpoints
│   │   ├── search/        # Search API
│   │   └── template_preview.php # Template preview API
│   ├── config/             # Configuration files
│   ├── includes/           # Shared includes
│   │   └── markdown/      # Markdown and Wiki parsers
│   ├── assets/             # Static assets
│   ├── skins/              # Theme system
│   │   └── bismillah/     # Default theme
│   └── .htaccess          # URL rewriting rules
├── docs/                   # Documentation
│   ├── admin/             # Admin system documentation
│   ├── api/               # API documentation
│   ├── architecture/      # System architecture
│   ├── changelogs/        # Version changelogs
│   ├── features/          # Feature documentation
│   ├── releases/          # Release notes
│   ├── security/          # Security documentation
│   └── user/              # User guides
├── database/              # Database migrations
├── scripts/               # Utility scripts
├── backups/               # Backup files
└── README.md              # Project overview
```

### Key Features
- **Clean URL System**: All routes use clean URLs without .php extensions
- **Modular Architecture**: Organized code structure for better maintainability
- **Real-Time Features**: Messaging and notifications with AJAX
- **Social Networking**: Friends system with requests and suggestions
- **Wiki System**: Complete wiki functionality with version control
- **Template System**: MediaWiki-style template system
- **Reference System**: Academic-style reference system with clickable links
- **Admin Panel**: Comprehensive administrative tools
- **Responsive Design**: Mobile-friendly interface
- **Security**: Proper authentication and authorization

## 🚀 Quick Start

### Prerequisites
- **Apache web server** with mod_rewrite enabled
- **PHP 7.4+** with standard extensions
- **MySQL 5.7+** database
- **Proper file permissions** for web server

### Installation
1. **Clone or download** the project files
2. **Upload to web server** in the appropriate directory
3. **Configure database** connection in `public/config/database.php`
4. **Run database migrations** using the provided SQL files
5. **Set proper permissions** for uploads and cache directories
6. **Test all routes** to ensure proper functionality

### Configuration
- **Database settings**: Update `public/config/database.php`
- **Site settings**: Configure in admin panel after installation
- **File permissions**: Ensure web server can read/write to necessary directories

## 🔧 Development

### File Organization
- **Pages**: All page files in `public/pages/` organized by category
- **Modules**: Reusable components in `public/modules/`
- **API**: AJAX endpoints in `public/api/`
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

### v0.0.0.18 (Current) - Wiki Editor & Reference System
- Revolutionary wiki editor with professional toolbar
- Enhanced reference system with clickable links
- Improved UI/UX with proper button sizing
- Mobile responsive design improvements
- Server-side parsing enhancements
- Template system improvements
- Security enhancements for link handling

### v0.0.0.16 - Admin Dashboard & Notifications
- Admin dashboard improvements with consistent theming
- Notification system fixes and error handling
- Better debugging and error logging
- Session handling improvements
- Enhanced user experience

### v0.0.0.15 - Documentation & Version Management
- Comprehensive documentation updates across all files
- Centralized version management system implementation
- Complete changelog and release notes overhaul
- Enhanced API documentation with examples
- Updated user guides with latest features
- Streamlined release management process
- Version consistency across all components
- Improved code documentation and comments

### v0.0.0.14 - Enhanced User Interface & Authentication
- Modern login/register pages with professional design
- Enhanced user menu with better hover effects
- Improved visual design with consistent branding
- Better responsive design for all devices
- Centralized styling with reduced code duplication
- Version management system implementation

### v0.0.0.13 - Comprehensive Admin System Overhaul
- Unified admin dashboard with organized sections
- Feature toggle system for core system features
- System health monitoring with real-time indicators
- Permission management with role-based access control
- Maintenance control with comprehensive system monitoring
- Modern UI with toast notifications and hover effects
- Tab persistence and form processing improvements
- Visual improvements and boolean handling fixes

### v0.0.0.12 - Enhanced Wiki Experience & Search System
- Three-column wiki layout with sticky sidebars
- Table of contents with smooth scrolling and active highlighting
- Wikipedia-style special pages (What Links Here, Page Information)
- Enhanced search overlay with keyboard shortcuts
- Multiple citation formats with APA 7th edition default
- Smart z-index management for proper UI layering
- ESC key support for all modals
- Full-width design for optimal reading experience

### v0.0.0.11 - News Feed Dashboard
- Revolutionary 3-column social media-style dashboard
- Unified content feed with smart filtering
- Interactive post creation with markdown editor
- Image upload system with copy/paste support
- Social engagement features (like, comment, share)
- Content management sections (My Content, Watchlist, Following)

### v0.0.0.10 - Bug Fixes & Enhancements
- Fixed critical 500 errors in post and article creation
- Side-by-side live preview for markdown editing
- User watchlist system for tracking article changes
- Enhanced error handling and user feedback
- Improved database functions and markdown parsing

### v0.0.0.9 - UI/UX Improvements
- Enhanced sidebar navigation with visual separators
- Search popup integration with real-time suggestions
- Article page redesign with transparent containers
- Fixed dropdown positioning and search results layout
- Improved mobile responsiveness and error handling

### v0.0.0.8 - Community Groups & Events
- Community groups with public/private/restricted access
- Group management with admin/moderator/member roles
- Community events (online/offline/hybrid) with attendance tracking
- Enhanced search engine with multi-content support
- Advanced filtering and search analytics

### v0.0.0.7 - Search System
- Comprehensive search system with multi-content search
- Advanced filtering by content type, category, and author
- Real-time search suggestions with AJAX
- Search analytics and history tracking
- Professional search interface with responsive design

### v0.0.0.6 - Major Restructuring
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
- **API**: Add new AJAX endpoints in api directory
- **Assets**: Organize CSS/JS files in assets directory

## 📞 Support

### Documentation
- **Complete documentation** available in this directory
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

---

**IslamWiki v0.0.0.18** - A modern Islamic knowledge platform with enhanced wiki editing and reference systems.

**Last Updated:** January 2025  
**Documentation Version:** 0.0.0.18  
**Status:** Production Ready ✅