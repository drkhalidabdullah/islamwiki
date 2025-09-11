# IslamWiki Documentation

Welcome to the comprehensive documentation for IslamWiki, a modern Islamic knowledge platform built with PHP.

## 🎯 Current Version: 0.0.0.12

**Latest Release:** September 11, 2025  
**Status:** Production Ready ✅  
**Type:** Major Feature Release - Enhanced Wiki Experience & Search System

## 🚀 What's New in v0.0.0.12

### 🎨 **Revolutionary Wiki Experience**
- **Three-Column Wiki Layout**: Contents sidebar (left), main content (center), and Tools sidebar (right)
- **Sticky Sidebar Navigation**: Contents and Tools sidebars scroll with content and stick to viewport
- **Table of Contents (TOC)**: Auto-generated TOC with smooth scrolling and active section highlighting
- **Comprehensive Tools Panel**: What links here, Page information, Cite this page, and Download as PDF
- **Full-Width Design**: Removed constraining containers for true full-width wiki experience

### 🔍 **Enhanced Search & Navigation**
- **Search Overlay System**: Full-screen search with proper z-index layering and backdrop effects
- **Keyboard Shortcuts**: Press `/` to quickly open search overlay from anywhere on the page
- **ESC Key Support**: All modals (search, citation, report) can be closed with ESC key
- **Smart Z-Index Management**: Dynamic z-index control ensures search overlay appears above all content

### 📚 **Wikipedia-Style Special Pages**
- **"What Links Here" Page**: Comprehensive page showing all articles that link to a specific page
  - Advanced filtering options (hide transclusions, links, redirects)
  - Namespace filtering and sorting options
  - Professional pagination system
- **"Page Information" Page**: Detailed metadata and statistics about wiki articles
  - Creator information and edit history
  - Page protection status and properties
  - Word count, character count, and reading time

### 📖 **Citation System Enhancement**
- **Multiple Citation Formats**: Support for MLA 9th, APA 7th, Chicago 17th, Harvard, and IEEE
- **APA 7th Edition Default**: Updated to current academic standards
- **Professional Modal**: Clean interface with format selection and copy functionality

## 🚀 What's New in v0.0.0.11

### 🎨 **Revolutionary News Feed Dashboard**
- **3-Column Responsive Layout**: Modern social media-style dashboard with left sidebar, main feed, and right sidebar
- **Unified Content Feed**: Single feed displaying posts and articles with smart filtering
- **Interactive Post Creation**: Inline post creation with markdown editor and live preview
- **Image Upload System**: Copy/paste image support with automatic scaling and preview
- **Social Engagement**: Like, comment, and share functionality for posts
- **Content Management**: Personal content sections (My Content, Watchlist, Following)

## 🚀 What's New in v0.0.0.10

### Critical Bug Fixes
- **Fixed create_post.php 500 Error**: Resolved internal server error when creating posts
- **Fixed create_article.php Draft Status**: Resolved 500 error when creating draft articles
- **Fixed Profile Posts Display**: Resolved issue where user posts weren't showing in profiles
- **Fixed Markdown Parsing**: Resolved regex error preventing markdown rendering

### New Features & Enhancements
- **Side-by-Side Live Preview**: Real-time markdown preview for create_post page
- **User Watchlist System**: Complete watchlist functionality for tracking article changes
- **Article History Timestamps**: Added detailed timestamps with relative time display
- **Enhanced Error Handling**: Better user feedback throughout the application

### Technical Improvements
- **Enhanced Database Functions**: Added comprehensive user post management
- **Markdown Parser Improvements**: Enhanced markdown processing capabilities
- **Responsive Design**: Improved mobile and tablet experience
- **Better Error Handling**: Comprehensive error logging and debugging

## 🚀 What's New in v0.0.0.9

### Enhanced User Interface & Experience
- **Sidebar Navigation Improvements**: Added visual separators between navigation sections
- **Search Popup Enhancement**: Full-screen search overlay with real-time suggestions
- **Dropdown Menu Positioning**: Fixed sidebar dropdown positioning to eliminate gaps
- **Search Results Optimization**: Made entire result containers clickable for better UX
- **Article Page Redesign**: Transparent content containers for cleaner appearance
- **Category Button Fixes**: Ensured category buttons are properly clickable

### Search System Enhancements
- **Search Popup Integration**: Inline search popup with AJAX-powered suggestions
- **Search Results Layout**: Improved horizontal alignment of icons and titles
- **Content Type Filters**: Converted radio buttons to clean link-based filters
- **Search Page Styling**: Consolidated conflicting CSS files into organized structure
- **Real-time Suggestions**: Enhanced search suggestions with proper API endpoints

### Bug Fixes & Improvements
- **URL Routing**: Fixed all navigation links and clean URL implementation
- **PHP Path Issues**: Corrected include paths using `__DIR__` for better reliability
- **CSS Conflicts**: Resolved multiple CSS file conflicts affecting styling
- **Mobile Responsiveness**: Improved mobile experience across all components
- **Error Handling**: Enhanced error handling for better user experience

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

### Comprehensive Route Management
- **Authentication routes**: /login, /register
- **User routes**: /dashboard, /profile, /settings, /user/{username}
- **Social routes**: /friends, /friends/requests, /friends/suggestions, /friends/all, /friends/lists, /messages, /create_post
- **Wiki routes**: /wiki, /wiki/search, /wiki/{slug}, /create_article, /edit_article, /delete_article, /restore_version, /manage_categories
- **Admin routes**: /admin, /manage_users, /system_settings
- **API routes**: /ajax/{endpoint}

## 📚 Documentation Structure

### 📖 [User Guides](guides/)
- **Installation Guide**: Step-by-step installation instructions
- **User Guide**: How to use IslamWiki features
- **Admin Guide**: Administrative functions and settings
- **Wiki Guide**: Creating and editing wiki content

### 🔧 [API Documentation](api/)
- **API Reference**: Complete API endpoint documentation
- **Authentication**: API authentication methods
- **Rate Limiting**: API usage limits and guidelines

### 🏗️ [Architecture](architecture/)
- **Architecture Overview**: System design and components
- **Database Schema**: Database structure and relationships
- **Security Guide**: Security features and best practices
- **Performance Guide**: Optimization and performance tips
- **Deployment Guide**: Production deployment instructions

### 📝 [Changelogs](changelogs/)
- **CHANGELOG.md**: Complete version history
- **v0.0.0.12.md**: Latest release notes (Enhanced Wiki Experience & Search System)
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

### Key Features
- **Clean URL System**: All routes use clean URLs without .php extensions
- **Modular Architecture**: Organized code structure for better maintainability
- **Real-Time Features**: Messaging and notifications with AJAX
- **Social Networking**: Friends system with requests and suggestions
- **Wiki System**: Complete wiki functionality with version control
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

### v0.0.0.12 (Current) - Enhanced Wiki Experience & Search System
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
- **API**: Add new AJAX endpoints in api/ajax directory
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

**IslamWiki v0.0.0.12** - A modern Islamic knowledge platform with enhanced wiki experience and advanced search capabilities.

**Last Updated:** September 11, 2025  
**Documentation Version:** 0.0.0.12  
**Status:** Production Ready ✅
