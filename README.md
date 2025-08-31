# IslamWiki Framework

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

A comprehensive, lightweight PHP framework for Islamic content platforms, optimized for shared hosting environments.

## 🚀 Quick Start

### Requirements

- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.6+
- Apache with mod_rewrite enabled
- Composer
- Node.js 18+ (for frontend development)

### Installation

#### **For Development (Local Machine):**

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-org/islamwiki.git
   cd islamwiki
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install Node.js dependencies**

   ```bash
   npm install
   ```

4. **Build frontend assets**

   ```bash
   npm run build:shared-hosting
   ```

#### **For Shared Hosting Deployment:**

1. **Build assets locally** (see step 4 above)
2. **Upload all files** to your shared hosting `public_html/` directory
3. **Set up database** using `database/schema.sql`
4. **Configure environment** by copying `env.example` to `.env`
5. **Run the installer** at `yourdomain.com/install.php`

**📖 See [SHARED_HOSTING_DEPLOYMENT.md](SHARED_HOSTING_DEPLOYMENT.md) for detailed shared
hosting instructions.**

1. **Configure environment**

   ```bash
   cp env.example .env
   # Edit .env with your database and app settings
   ```

2. **Set up the database**

   ```bash
   # Import the schema
   mysql -u your_user -p your_database < database/schema.sql
   ```

3. **Set permissions**

   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/uploads/
   ```

## 🏗️ Architecture

### Backend (PHP)

- **Custom Lightweight Framework**: Built from scratch for optimal performance
- **Dependency Injection Container**: Simple but powerful service management
- **Custom Router**: Fast, flexible routing with parameter support
- **Middleware System**: Request/response processing pipeline
- **Service Providers**: Modular service registration

### Frontend (React)

- **React 18 SPA**: Modern, responsive user interface
- **TypeScript**: Type-safe development
- **Tailwind CSS**: Utility-first styling
- **Vite**: Fast build tool and dev server
- **State Management**: Zustand for simple state management

## 🔑 Key Features

### Core Systems

- **User Management**: Registration, authentication, profiles
- **Content Management**: Wiki articles, categories, versions
- **Social Features**: User connections, activity feeds
- **Learning Management**: Courses, lessons, progress tracking
- **Q&A System**: Questions, answers, moderation
- **Communication**: Messaging, notifications, forums

### Advanced Admin Systems

- **Content Moderation**: Review, approve, reject content
- **User Management**: Admin panel, role management
- **Analytics Dashboard**: Usage statistics, insights
- **System Configuration**: Settings, maintenance tools
- **Backup & Recovery**: Automated backups, restore tools

### Islamic Content Features

- **Scholar Verification**: Authenticate Islamic scholars
- **Fatwa Database**: Islamic rulings and guidance
- **Hadith Verification**: Authenticate prophetic traditions
- **Multi-language Support**: Arabic, English, Urdu, Turkish, Malay
- **RTL Support**: Right-to-left language support

## 📁 Project Structure

```bash
islamwiki/
├── src/                    # PHP source code
│   ├── Core/              # Framework core classes
│   ├── Controllers/        # HTTP controllers
│   ├── Models/            # Data models
│   ├── Services/          # Business logic services
│   ├── Providers/         # Service providers
│   └── Admin/             # Admin-specific code
├── public/                 # Web-accessible files
│   ├── index.php          # Front controller
│   ├── .htaccess          # Apache configuration
│   └── assets/            # Compiled assets
├── storage/                # Application storage
│   ├── cache/             # Cache files
│   ├── logs/              # Log files
│   └── uploads/           # User uploads
├── config/                 # Configuration files
├── database/               # Database schemas
├── docs/                   # Documentation
├── composer.json           # PHP dependencies
└── package.json            # Node.js dependencies
```
