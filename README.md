# IslamWiki v0.0.0.1

A comprehensive Islamic knowledge platform built with pure PHP.

## 🎯 Overview

IslamWiki is a modern, secure, and user-friendly platform for Islamic knowledge sharing. Built from the ground up with pure PHP, it provides a complete solution for creating and managing Islamic content.

## ✨ Features

### Core Functionality
- **User Management**: Complete authentication and user system
- **Admin Panel**: Comprehensive administrative tools and statistics
- **Wiki System**: Full-featured knowledge base with articles and categories
- **Content Management**: Article creation, editing, and organization
- **Search**: Full-text search across all content
- **User Dashboard**: Personal user area with activity tracking

### Technical Features
- **Pure PHP**: No React dependencies, clean and fast
- **Secure**: Modern security practices and authentication
- **Responsive**: Works on all devices and screen sizes
- **Organized**: Clean file structure and proper separation
- **Scalable**: Built for growth and future enhancements

## 🚀 Quick Start

### Requirements
- PHP 8.3 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx or PHP built-in server)

### Installation
1. **Download**: Get the project files
2. **Database**: Create MySQL database and import `setup_database.sql`
3. **Configuration**: Update database settings in `public/config/database.php`
4. **Web Server**: Point document root to `public/` directory
5. **Access**: Visit your domain

### Default Credentials
- **Admin User**: admin / admin123
- **Database**: islamwiki (create as needed)

## 📁 Project Structure

```
/var/www/html/
├── docs/                    # Documentation and changelogs
├── public/                  # Web-accessible files
│   ├── *.php               # Main application files
│   ├── assets/             # CSS, JS, images
│   ├── config/             # Configuration files
│   ├── includes/           # PHP includes
│   ├── wiki/               # Wiki system
│   └── uploads/            # File uploads
├── setup_database.sql      # Database setup
└── README.md               # This file
```

## 🔧 Configuration

### Database Setup
1. Create MySQL database named `islamwiki`
2. Import `setup_database.sql` to create tables
3. Update `public/config/database.php` with your database credentials

### Web Server Configuration
- Point document root to `public/` directory
- Ensure PHP 8.3+ is installed
- Enable required PHP extensions: PDO, PDO_MySQL, Session, JSON

## 📖 Documentation

Complete documentation is available in the `docs/` directory:

- **[Changelog](docs/changelogs/CHANGELOG.md)** - Version history and changes
- **[Release Notes](docs/releases/RELEASE_NOTES.md)** - Quick release overview
- **[Documentation Index](docs/README.md)** - Complete documentation guide

## 🛡️ Security

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Role-based access control
- SQL injection prevention with prepared statements
- Input validation and sanitization
- CSRF protection framework

## 🎨 User Interface

- Modern, responsive design
- Consistent styling across all pages
- User-friendly navigation
- Admin dashboard with statistics
- Wiki interface with search and categories
- Settings page with user preferences

## 🔄 Version Information

- **Current Version**: 0.0.0.1
- **Release Date**: 2025-09-06
- **Type**: Initial Release
- **Next Version**: 0.0.0.2 (Planned)

## 🚀 Development

### Running Development Server
```bash
# From project root
sudo php -S 0.0.0.0:80 -t public
```

### File Structure
- All web-accessible files are in `public/` directory
- Configuration files in `public/config/`
- Documentation in `docs/` directory
- Database setup in root directory

## 📞 Support

- **Documentation**: Check `docs/` directory for guides
- **Issues**: Report issues through project issue tracker
- **Community**: Join the IslamWiki community
- **Updates**: Follow project updates and announcements

## 🔮 Roadmap

### Version 0.0.0.2 (Next)
- Bug fixes and minor improvements
- Enhanced user interface
- Performance optimizations

### Version 0.0.1.0 (Future)
- Multi-language support
- Advanced wiki features
- API development

### Version 0.1.0.0 (Future)
- Mobile app integration
- Advanced content management
- Plugin system

## 📄 License

This project is open source. See project repository for license details.

## 👥 Contributing

Contributions are welcome! Please see documentation for contributing guidelines.

---

**IslamWiki v0.0.0.1** - Fresh Start: Complete PHP Platform
