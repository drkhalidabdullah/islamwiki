# Changelog


## [0.0.0.7] - 2025-09-07

### Comprehensive Search System
- **Multi-Content Search**: Search across articles, users, messages, and more
- **Advanced Filtering**: Filter by content type, category, and sort options
- **Real-time Suggestions**: AJAX-powered search suggestions
- **Search Analytics**: Track search patterns and popular queries
- **Search History**: Personal search history for logged-in users
- **Enhanced UI/UX**: Modern search interface with responsive design
- **Database Optimization**: Added search indexes and analytics tables
- **API Enhancements**: New AJAX search suggestions endpoint
- **Privacy Protection**: Message search limited to participants
- **Performance Improvements**: Optimized queries and pagination

## [0.0.0.6] - 2025-09-07

### Major Restructuring & Clean URLs
- **Complete File System Reorganization**
  - Restructured entire project with industry-standard directory layout
  - Organized files into logical categories: pages/, modules/, api/, config/, includes/
  - Improved code maintainability and developer experience
  - Better separation of concerns and modular architecture

- **Clean URL Implementation**
  - Full clean URL system via .htaccess rewrite rules
  - Removed all .php extensions from user-facing URLs
  - SEO-friendly URLs for better search engine optimization
  - Consistent routing across the entire application

- **Enhanced Navigation & User Experience**
  - Conditional navigation display (hide friends/dashboard when not logged in)
  - Professional chat options dropdown with toggle switches
  - Improved header design with better icon organization
  - Smart navigation highlighting based on current page

- **Comprehensive Route Management**
  - 25+ clean URL routes implemented
  - Proper .htaccess rules for all pages and modules
  - API endpoint routing for AJAX calls
  - User profile routing with tab support
  - Wiki article routing with slug support

- **Friends Module Enhancement**
  - Complete friends sub-routing system
  - All friends pages accessible via clean URLs
  - Proper include path management for modular components
  - Enhanced friends functionality with better organization

- **Technical Improvements**
  - Fixed all include paths for restructured files
  - Updated all redirects to use clean URLs
  - Resolved file system permission issues
  - Improved error handling and route protection
  - Better database connection management

- **Bug Fixes**
  - Fixed all 404 errors on main routes
  - Resolved include path issues in moved files
  - Corrected redirect loops in authentication flow
  - Fixed API endpoint routing for AJAX calls
  - Resolved navigation highlighting problems
  - Fixed dropdown menu interactions and closing behavior

### Files Restructured
- **Pages Directory**: auth/, user/, social/, wiki/, admin/
- **Modules Directory**: wiki/, friends/
- **API Directory**: ajax/ with all endpoints
- **Configuration**: Centralized config management
- **Assets**: Organized CSS, JS, and image files

### Routes Implemented
- Authentication: /login, /register
- User: /dashboard, /profile, /settings, /user/{username}
- Social: /friends, /friends/requests, /friends/suggestions, /friends/all, /friends/lists, /messages, /create_post
- Wiki: /wiki, /wiki/search, /wiki/{slug}, /create_article, /edit_article, /delete_article, /restore_version, /manage_categories
- Admin: /admin, /manage_users, /system_settings
- API: /ajax/{endpoint}

### Security & Performance
- All protected routes properly secured
- Login requirement enforcement for sensitive pages
- Efficient .htaccess rules with proper conditions
- Optimized include paths reducing file system calls
- Clean URL structure improving SEO and user experience


# Changelog

## [0.0.0.5] - 2025-09-07

### Added
- **Real-Time Messaging & Notifications System**
  - Complete messaging infrastructure with database schema and AJAX endpoints
  - Real-time notifications for friend requests, messages, and system alerts
  - Dynamic header dropdowns for chats and notifications with live data
  - Auto-refresh functionality every 30 seconds for real-time updates
  - Notification badges with unread counts and pulse animations

- **Comprehensive Friends & Social Networking**
  - Friends system with request/accept/decline workflow
  - Friend suggestions based on mutual connections
  - Social navigation with dedicated friends pages and sub-sections
  - AJAX-powered interactions for seamless friend management
  - Database integration with proper foreign key constraints

- **Enhanced User Interface & Navigation**
  - Fixed header navigation with proper highlighting logic
  - Responsive design improvements for mobile and desktop
  - Clean URL structure for user profiles (`/user/{username}`)
  - Improved dropdown functionality for all header elements
  - Better error handling and user feedback

### Changed
- **Removed redundant feed functionality** - home page now serves as the main feed
- **Improved navigation highlighting** - fixed wiki page highlighting conflicts
- **Enhanced header layout** - messages and notifications icons now display side-by-side
- **Streamlined user interface** - removed unnecessary elements and improved organization

### Fixed
- **Wiki navigation highlighting** - no longer highlights both home and wiki icons
- **Header icon overlap** - messages and notifications now display properly
- **User dropdown functionality** - properly toggles and closes
- **User profile URLs** - `/user/{username}` now works correctly
- **Database foreign key constraints** - fixed migration issues
- **Missing PHP functions** - added functions causing 500 errors
- **AJAX endpoint functionality** - all real-time features now work properly

### Technical Improvements
- **Database enhancements** - new notifications and messages tables
- **Code quality improvements** - better error handling and validation
- **Performance optimizations** - efficient queries and responsive design
- **Security enhancements** - proper input sanitization and authentication



All notable changes to IslamWiki will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.0.4] - 2025-09-06

### Added
- **Comprehensive User Profile System**
  - Social media-style user profiles with cover photos and avatars
  - Clean URL structure (`/user/{username}`) for better SEO and sharing
  - Profile tabs: Posts, Photos, Events, About, and Activity sections
  - Social statistics: followers, following, articles, and posts counts

- **Social Networking Features**
  - Follow/unfollow system with real-time updates
  - User posts with privacy controls (public, community, followers, private)
  - Post interactions: like, comment, and share functionality
  - Personalized activity feed showing posts from followed users
  - AJAX-powered interactions for seamless user experience

- **Enhanced Database Schema**
  - New tables: `user_follows`, `user_posts`, `post_interactions`, `user_photos`, `user_events`, `user_achievements`, `post_comments`
  - Extended user profiles with interests, education, profession, expertise areas
  - Performance optimizations with proper indexing
  - Data integrity with foreign key constraints

- **User Interface Improvements**
  - Responsive design optimized for mobile devices
  - Real-time follow/unfollow and like/unlike functionality
  - Updated navigation with profile and feed links
  - Create post functionality for community engagement

### Enhanced
- **Wiki Integration**
  - Seamless integration with existing wiki system
  - Article attribution and sharing capabilities
  - User expertise recognition in Islamic knowledge areas

- **Security & Privacy**
  - Granular privacy controls for user content
  - Comprehensive input validation and sanitization
  - Proper access control for profile viewing
  - CSRF protection for all forms

### Technical
- **URL Routing**
  - Pretty URLs for user profiles with tab support
  - SEO-friendly structure for better search engine indexing
  - Backward compatibility with existing wiki URLs

- **Performance**
  - Optimized database queries for social interactions
  - Efficient AJAX handlers for real-time updates
  - Mobile-optimized responsive design


## [0.0.0.3] - 2025-09-06

### Added
- **Pretty URL System**
  - Clean URLs for wiki articles (`/wiki/Islam` instead of `/wiki/article.php?slug=islam`)
  - Automatic slug capitalization for consistent page access
  - URL rewriting with Apache .htaccess configuration
  - SEO-friendly article URLs

- **Enhanced Admin Panel**
  - Complete user management system with edit and reset password functionality
  - Enhanced article editing with full feature parity to create article
  - Modal-based user editing with form validation
  - Password reset functionality with confirmation
  - User status management (Active/Inactive)
  - Role management with new scholar and reviewer roles

- **Draft Management & Collaboration System**
  - Advanced draft visibility controls (author-only, editors, all logged-in users)
  - Multi-user collaboration on draft articles
  - Scholar verification system for content accuracy
  - Draft notifications and activity tracking
  - Enhanced permission-based content access

- **Article Not Found Handling**
  - User-friendly "Article Not Found" page
  - Automatic article creation suggestions for editors
  - Helpful navigation and search options
  - Context-aware error messages

- **Enhanced User Interface**
  - "Powered by IslamWiki" badge in footer
  - Improved admin dashboard with better user management
  - Enhanced form validation and error handling
  - Better responsive design for mobile devices

### Enhanced
- **Wiki Link System**
  - Fixed double wiki path issues (`/wiki/wiki/article` → `/wiki/article`)
  - Improved wiki link detection and rendering
  - Better handling of missing article links
  - Enhanced cross-referencing between articles

- **Article Management**
  - Full-featured edit article page with category selection
  - Featured article management
  - Enhanced article status controls
  - Better article metadata handling

- **User Management**
  - Comprehensive user editing capabilities
  - Password reset functionality
  - User role management with new roles
  - Activity logging for all user actions

- **Database Schema**
  - New roles: scholar and reviewer
  - Enhanced article collaboration features
  - Draft visibility and collaboration controls
  - Scholar verification system

### Technical Improvements
- **URL Rewriting**
  - Apache .htaccess configuration for pretty URLs
  - Proper handling of wiki article routing
  - SEO optimization with clean URLs

- **Database Integration**
  - Fixed missing database includes across all files
  - Enhanced query building with permission-based filtering
  - Improved error handling and validation

- **Code Organization**
  - Better file structure and includes
  - Enhanced error handling
  - Improved security measures

### Fixed
- **Critical Bug Fixes**
  - Fixed internal server errors in admin dashboard
  - Resolved edit user functionality issues
  - Fixed article editing with missing features
  - Corrected database connection issues

- **Path Resolution**
  - Fixed Apache DocumentRoot configuration issues
  - Corrected file path problems in admin pages
  - Improved include/require path handling

- **User Interface**
  - Fixed edit button functionality in admin dashboard
  - Resolved modal dialog issues
  - Improved form validation and error display

### Security
- **Enhanced Security**
  - Improved input validation and sanitization
  - Better password handling and reset functionality
  - Enhanced user permission checks
  - Improved session management

## [0.0.0.2] - 2025-09-06

### Added
- **Complete Wiki System Overhaul**
  - Markdown-first editing with rich text editor
  - Visual toolbar with buttons for all markdown features
  - Live preview functionality for real-time editing
  - Wiki-style linking with `[[Page Name]]` syntax
  - Smart link detection (existing vs missing pages)
  - Bidirectional linking between articles

- **Advanced Article Management**
  - Article version control and history tracking
  - Version restoration functionality
  - Change summaries for each edit
  - Draft system for work-in-progress articles
  - Featured articles system

- **Enhanced Search System**
  - Full-text search across titles, content, and excerpts
  - Category filtering for search results
  - Multiple sort options (relevance, title, date, views)
  - Search suggestions when no results found
  - Highlighted search terms in results

- **Rich Text Editor Features**
  - Visual toolbar with markdown shortcuts
  - Keyboard shortcuts (Ctrl+B, Ctrl+I, Ctrl+K)
  - Built-in help system with markdown reference
  - Auto-save functionality
  - Code syntax highlighting

- **Wiki Navigation & UI**
  - Enhanced wiki homepage with featured articles
  - Category sidebar with article counts
  - Popular articles section
  - Responsive design for all devices
  - Professional styling matching main site

- **Test Content**
  - Islam article with comprehensive content
  - Allah article with detailed information
  - Muslim article with cross-references
  - All articles include wiki links and markdown formatting

### Enhanced
- **Markdown Parser**
  - Support for headers, bold, italic, code blocks
  - Wiki link parsing with `[[Page Name]]` syntax
  - Link display text support `[[Page Name|Display Text]]`
  - Missing page detection and styling
  - HTML to markdown conversion for editing

- **Article Display**
  - Improved article rendering with proper styling
  - Wiki link styling (blue for existing, red for missing)
  - Related articles section
  - Article metadata display
  - View count tracking

- **User Interface**
  - Consistent styling across wiki pages
  - Improved navigation and breadcrumbs
  - Better mobile responsiveness
  - Enhanced accessibility features

### Technical Improvements
- **Database Schema**
  - Added `article_versions` table for version control
  - Enhanced article metadata tracking
  - Improved indexing for search performance

- **Code Organization**
  - Modular markdown parser system
  - Separated wiki-specific functionality
  - Improved error handling and validation
  - Better code documentation

- **Performance**
  - Optimized search queries
  - Improved page loading times
  - Better caching strategies
  - Enhanced database performance

### Fixed
- **Path Resolution Issues**
  - Fixed relative path problems in wiki pages
  - Corrected asset loading in subdirectories
  - Improved include/require path handling

- **Functions File Issues**
  - Fixed broken function definitions
  - Corrected PHP syntax errors
  - Improved error handling

- **Wiki Link Detection**
  - Fixed wiki link parsing and rendering
  - Corrected missing page detection
  - Improved link styling and behavior

## [0.0.0.1] - 2025-09-06

### Added
- **Complete Platform Rebuild**
  - Full PHP-only architecture (removed React)
  - Modern, responsive design
  - Clean file structure with public/ directory

- **User Authentication System**
  - Secure login/registration with password hashing
  - Multi-role system (admin, moderator, editor, user, guest)
  - User profiles and activity tracking
  - Session management and security

- **Admin Panel**
  - Comprehensive admin dashboard
  - User management tools
  - System statistics and monitoring
  - Database management interface

- **Basic Wiki System**
  - Article creation and editing
  - Category management
  - Basic search functionality
  - Article viewing and navigation

- **Content Management**
  - Article CRUD operations
  - Category system
  - Featured articles
  - Content statistics

- **Database Schema**
  - Complete user and role system
  - Article and category tables
  - Activity logging
  - System settings

- **Documentation**
  - Comprehensive README
  - Installation guides
  - API documentation
  - User guides

### Technical Features
- **Security**
  - Password hashing with PHP's password_hash()
  - CSRF protection
  - Input sanitization
  - SQL injection prevention

- **Performance**
  - Optimized database queries
  - Efficient file structure
  - Responsive design
  - Fast page loading

- **Accessibility**
  - WCAG compliant design
  - Keyboard navigation
  - Screen reader support
  - High contrast options

---

## Version Numbering

This project uses semantic versioning with the format `MAJOR.MINOR.PATCH`:

- **MAJOR**: Breaking changes or major feature additions
- **MINOR**: New features that are backward compatible
- **PATCH**: Bug fixes and minor improvements

### Current Development Phase

- **0.0.x**: Alpha releases - Core functionality and major features
- **0.1.x**: Beta releases - Feature completion and refinement
- **0.2.x**: Release candidate - Bug fixes and polish
- **1.0.0**: Stable release - Production ready

### Upcoming Features (Planned)

- **v0.0.4**: Multi-language support and translation system
- **v0.0.5**: Advanced user permissions and content moderation
- **v0.0.6**: API endpoints and third-party integrations
- **v0.1.0**: Mobile app and advanced features

### Fixed
- **User Dropdown**: Resolved CSS conflicts causing dropdown not to show for logged-in users
- **Admin Access**: Fixed 403 Forbidden error when accessing `/admin` panel
- **Search Functionality**: Resolved 500 errors in wiki search from header search box
- **Header Styling**: Fixed asset path issues and broken navigation links
- **Homepage Content**: Resolved blank content area below header
- **URL Routing**: Fixed admin panel routing and clean URL implementation
- **CSS Conflicts**: Removed duplicate and conflicting CSS rules
- **JavaScript Display**: Fixed JavaScript code appearing as text on pages
