# IslamWiki - Comprehensive Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.3-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-8.3+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-AGPL--3.0-blue.svg)](LICENSE)

A comprehensive, modern Islamic knowledge platform built with PHP, featuring a full-featured wiki system, user management, and content management capabilities.

## 🚀 Features

### 📚 **Advanced Wiki System**
- **Markdown-First Editing**: Rich text editor with comprehensive toolbar
- **Wiki-Style Linking**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **Live Preview**: Real-time markdown rendering
- **Version Control**: Complete article history and restoration
- **Smart Link Detection**: Existing pages (blue) vs missing pages (red)
- **Pretty URLs**: SEO-friendly URLs like `/wiki/Islam` instead of `/wiki/article.php?slug=islam`

### 👥 **Enhanced User Management**
- **Multi-Role System**: Admin, Moderator, Editor, User, Guest, Scholar, Reviewer roles
- **User Authentication**: Secure login/registration with password hashing
- **Complete Admin Panel**: Full user editing, password reset, and role management
- **User Profiles**: Comprehensive profile management
- **Activity Tracking**: User actions and statistics
- **Draft Collaboration**: Multi-user collaboration on draft articles

### 🔍 **Advanced Search**
- **Full-Text Search**: Search across titles, content, and excerpts
- **Category Filtering**: Filter results by article categories
- **Multiple Sort Options**: By relevance, title, date, or views
- **Search Suggestions**: Popular articles when no results found
- **Highlighted Results**: Search terms highlighted in results

### 📝 **Content Management**
- **Article Creation**: Rich markdown editor with toolbar
- **Enhanced Article Editing**: Full-featured editing with category selection and featured articles
- **Category System**: Organized content categorization
- **Featured Articles**: Highlight important content
- **Draft System**: Save articles as drafts before publishing
- **Scholar Verification**: Content verification system for accuracy
- **Content Statistics**: View counts and engagement metrics

### 🎨 **Modern Interface**
- **Responsive Design**: Works on all devices
- **Professional Styling**: Clean, modern interface
- **Intuitive Navigation**: Easy-to-use wiki interface
- **Accessibility**: WCAG compliant design
- **"Powered by IslamWiki" Branding**: Professional footer badge

### 🛡️ **Security & Administration**
- **Enhanced Admin Panel**: Complete user management with modal-based editing
- **Password Reset**: Secure password reset functionality
- **Role-Based Access**: Comprehensive permission system
- **Input Validation**: Advanced sanitization and validation
- **Session Management**: Secure session handling

## 🛠️ Technology Stack

- **Backend**: PHP 8.3+ with PDO
- **Database**: MySQL 8.0+ with comprehensive schema
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with responsive design
- **Server**: Apache with URL rewriting support

## 📦 Installation

### Prerequisites
- PHP 8.3 or higher
- MySQL 8.0 or higher
- Apache web server with mod_rewrite enabled
- Composer (optional, for future package management)

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/drkhalidabdullah/islamwiki.git
   cd islamwiki
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < setup_database_fixed.sql
   mysql -u root -p islamwiki < database_migration_v0.0.0.3.sql
   ```

3. **Configure Apache**
   - Ensure mod_rewrite is enabled
   - Set DocumentRoot to the project directory
   - The included .htaccess files will handle URL rewriting

4. **Start the server**
   ```bash
   cd public
   php -S 0.0.0.0:80
   ```

5. **Access the platform**
   - Open your browser to `http://localhost`
   - Default admin credentials: `admin` / `password`

### Production Setup

For production deployment:

1. **Configure Apache Virtual Host**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /path/to/islamwiki/public
       
       <Directory /path/to/islamwiki/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. **Set proper file permissions**
   ```bash
   chmod -R 755 /path/to/islamwiki
   chown -R www-data:www-data /path/to/islamwiki
   ```

3. **Configure SSL (recommended)**
   - Use Let's Encrypt or your preferred SSL provider
   - Update Apache configuration for HTTPS

## 🎯 Key Features in v0.0.0.3

### 🌐 Pretty URL System
- Clean, SEO-friendly URLs for all articles
- Automatic slug capitalization for consistency
- Apache .htaccess configuration for URL rewriting

### 👥 Enhanced Admin Panel
- Complete user management with edit and reset password functionality
- Modal-based user editing interface
- User status management (Active/Inactive)
- Role management with new scholar and reviewer roles

### 📝 Draft Collaboration System
- Multi-user collaboration on draft articles
- Draft visibility controls (author-only, editors, all logged-in users)
- Scholar verification system for content accuracy
- Draft notifications and activity tracking

### 🔍 Article Not Found Handling
- User-friendly "Article Not Found" page
- Automatic article creation suggestions for editors
- Context-aware error messages based on permissions

## 📖 Documentation

- **[Installation Guide](docs/guides/INSTALLATION.md)**: Detailed setup instructions
- **[User Guide](docs/guides/USER_GUIDE.md)**: How to use IslamWiki
- **[API Reference](docs/api/API_REFERENCE.md)**: API documentation
- **[Changelog](docs/changelogs/CHANGELOG.md)**: Complete version history
- **[Release Notes](docs/releases/RELEASE_NOTES.md)**: Release overview

## 🔄 Version History

### v0.0.0.3 - "Enhanced Administration" (Current)
- Pretty URL system for SEO-friendly links
- Enhanced admin panel with complete user management
- Draft collaboration and scholar verification system
- Article not found handling with creation suggestions
- Fixed critical bugs and improved stability

### v0.0.0.2 - "Wiki Revolution"
- Complete markdown-based wiki system
- Rich text editor with visual toolbar
- Wiki linking system with smart detection
- Article version control and restoration
- Advanced search with full-text capabilities

### v0.0.0.1 - "Fresh Start"
- Complete PHP platform rebuild
- User authentication and role management
- Basic wiki system with article management
- Admin panel with comprehensive tools

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0) - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/drkhalidabdullah/islamwiki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/drkhalidabdullah/islamwiki/discussions)
- **Documentation**: [Wiki](https://github.com/drkhalidabdullah/islamwiki/wiki)

## 🎯 Roadmap

### Upcoming Features
- **v0.0.0.4**: Multi-language support and translation system
- **v0.0.0.5**: Advanced user permissions and content moderation
- **v0.0.0.6**: API endpoints and third-party integrations
- **v0.1.0**: Community features and collaboration tools
- **v1.0.0**: Production-ready stability and performance

---

**IslamWiki** - Empowering Islamic knowledge through technology

Built with ❤️ for the global Islamic community
