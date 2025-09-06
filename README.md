# IslamWiki - Comprehensive Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.0.2-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)
[![PHP](https://img.shields.io/badge/PHP-8.3+-green.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/license-MIT-red.svg)](LICENSE)

A comprehensive, modern Islamic knowledge platform built with PHP, featuring a full-featured wiki system, user management, and content management capabilities.

## 🚀 Features

### 📚 **Advanced Wiki System**
- **Markdown-First Editing**: Rich text editor with comprehensive toolbar
- **Wiki-Style Linking**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **Live Preview**: Real-time markdown rendering
- **Version Control**: Complete article history and restoration
- **Smart Link Detection**: Existing pages (blue) vs missing pages (red)

### 👥 **User Management**
- **Multi-Role System**: Admin, Moderator, Editor, User, Guest roles
- **User Authentication**: Secure login/registration with password hashing
- **User Profiles**: Comprehensive profile management
- **Activity Tracking**: User actions and statistics

### 🔍 **Advanced Search**
- **Full-Text Search**: Search across titles, content, and excerpts
- **Category Filtering**: Filter results by article categories
- **Multiple Sort Options**: By relevance, title, date, or views
- **Search Suggestions**: Popular articles when no results found
- **Highlighted Results**: Search terms highlighted in results

### 📝 **Content Management**
- **Article Creation**: Rich markdown editor with toolbar
- **Category System**: Organized content categorization
- **Featured Articles**: Highlight important content
- **Draft System**: Save articles as drafts before publishing
- **Content Statistics**: View counts and engagement metrics

### 🎨 **Modern Interface**
- **Responsive Design**: Works on all devices
- **Professional Styling**: Clean, modern interface
- **Intuitive Navigation**: Easy-to-use wiki interface
- **Accessibility**: WCAG compliant design

## 🛠️ Technology Stack

- **Backend**: PHP 8.3+ with PDO
- **Database**: MySQL 8.0+ with comprehensive schema
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with responsive design
- **Security**: Password hashing, CSRF protection, input sanitization

## 📦 Installation

### Prerequisites
- PHP 8.3 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/drkhalidabdullah/islamwiki.git
   cd islamwiki
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < setup_database_fixed.sql
   ```

3. **Configure the application**
   ```bash
   cp env.example .env
   # Edit .env with your database credentials
   ```

4. **Start the development server**
   ```bash
   cd public
   php -S 0.0.0.0:80
   ```

5. **Access the application**
   - Open your browser to `http://localhost`
   - Default admin credentials: `admin` / `admin123`

## 📖 Wiki Usage

### Creating Articles
1. Navigate to the Wiki section
2. Click "Create New Article" (requires editor role)
3. Use the rich markdown editor with toolbar
4. Add wiki links using `[[Page Name]]` syntax
5. Preview your content in real-time
6. Save as draft or publish immediately

### Wiki Linking
- **Basic Link**: `[[Islam]]` - Links to Islam article
- **Display Text**: `[[Allah|God]]` - Links to Allah article, displays "God"
- **Missing Links**: Non-existent pages show as red links

### Markdown Features
- **Headers**: `# H1`, `## H2`, `### H3`
- **Text Formatting**: `**bold**`, `*italic*`, `` `code` ``
- **Lists**: `* bullet` and `1. numbered`
- **Links**: `[text](url)` and `[[wiki links]]`
- **Quotes**: `> quoted text`
- **Code Blocks**: ` ```code``` `

## 🔧 Configuration

### Database Configuration
Edit `public/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'islamwiki');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Site Configuration
Edit `public/config/config.php`:
```php
define('SITE_NAME', 'IslamWiki');
define('SITE_URL', 'http://localhost');
define('SITE_VERSION', '0.0.0.2');
```

## 📁 Project Structure

```
islamwiki/
├── public/                 # Web-accessible files
│   ├── config/            # Configuration files
│   ├── includes/          # PHP includes and functions
│   │   └── markdown/      # Markdown parser
│   ├── assets/            # CSS, JS, images
│   ├── wiki/              # Wiki system
│   ├── admin.php          # Admin panel
│   ├── dashboard.php      # User dashboard
│   └── index.php          # Main page
├── docs/                  # Documentation
│   ├── changelogs/        # Version changelogs
│   ├── releases/          # Release notes
│   └── guides/            # User guides
├── setup_database_fixed.sql # Database schema
└── README.md              # This file
```

## 🎯 User Roles

- **Admin**: Full system access, user management, system settings
- **Moderator**: Content moderation, user management
- **Editor**: Create and edit articles, manage categories
- **User**: View articles, create comments
- **Guest**: View published articles only

## 🔍 Search Features

- **Full-Text Search**: Search across all article content
- **Category Filter**: Narrow results by topic
- **Sort Options**: By relevance, title, date, or views
- **Search Suggestions**: Popular articles when no matches
- **Highlighted Results**: Search terms highlighted

## 📊 Version History

### v0.0.0.2 (Current)
- **Major Wiki Enhancement**: Complete markdown-based wiki system
- **Rich Text Editor**: Visual toolbar with live preview
- **Wiki Linking**: `[[Page Name]]` syntax with smart detection
- **Version Control**: Article history and restoration
- **Advanced Search**: Full-text search with filters
- **Test Content**: Islam, Allah, Muslim articles with cross-links

### v0.0.0.1
- **Initial Release**: Complete PHP platform rebuild
- **User System**: Authentication and role management
- **Basic Wiki**: Article creation and management
- **Admin Panel**: Comprehensive admin tools
- **Database Schema**: Complete user and content system

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built for the Islamic community
- Inspired by modern wiki systems
- Designed for accessibility and usability

## 📞 Support

For support, email support@islamwiki.org or create an issue on GitHub.

---

**IslamWiki** - Empowering Islamic knowledge through technology
