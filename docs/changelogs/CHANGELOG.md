# Changelog

All notable changes to IslamWiki will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

- **v0.0.3**: Multi-language support and translation system
- **v0.0.4**: Advanced user permissions and content moderation
- **v0.0.5**: API endpoints and third-party integrations
- **v0.1.0**: Mobile app and advanced features
