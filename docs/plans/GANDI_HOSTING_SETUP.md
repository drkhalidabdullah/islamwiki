# 🌐 IslamWiki Framework - Gandi.net Shared Hosting Setup

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 📋 **Gandi.net Hosting Overview**

Gandi.net shared hosting typically provides:

- **PHP 8.0+** (check your plan)
- **MySQL/MariaDB** databases
- **Apache web server**
- **SSH access** (on some plans)
- **Git deployment** (on some plans)
- **cPanel or custom control panel**

## 🔧 **Step-by-Step Gandi.net Setup**

### **Step 1: Verify Your Gandi.net Plan**

Log into your Gandi.net control panel and check:

- **PHP version** (need 8.2+ for optimal performance)
- **Database limits** (MySQL storage and connections)
- **SSH access** (for command-line deployment)
- **Git support** (for automated deployments)

### **Step 2: Local Development Setup**

```bash
# On your development machine
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build production assets
npm run build:shared-hosting
```

### **Step 3: Prepare for Gandi.net Upload**

After building, your structure should be:

```bash
islamwiki/
├── dist/                   # Built frontend assets
│   ├── assets/
│   │   ├── css/           # Compiled CSS
│   │   ├── js/            # Compiled JavaScript
│   │   └── images/        # Optimized images
├── src/                    # PHP framework code
├── public/                 # Web root files
├── storage/                # Application storage
├── database/               # Database files
├── composer.json           # PHP dependencies
├── .env.example            # Environment template
└── install.php             # Web installer
```

### **Step 4: Upload to Gandi.net**

#### **Option A: File Manager (if available)**

1. Log into Gandi.net control panel
2. Navigate to File Manager
3. Upload all files to your web root directory
4. Ensure proper file permissions

#### **Option B: FTP/SFTP**

```bash
# Using FileZilla, WinSCP, or command line
scp -r islamwiki/* username@yourdomain.com:public_html/
```

#### **Option C: Git Deployment (if supported)**

```bash
# On Gandi.net server
cd public_html
git clone https://github.com/your-org/islamwiki.git .
git checkout production
```

### **Step 5: Set File Permissions**

```bash
# If you have SSH access on Gandi.net
chmod 755 public_html/
chmod 644 public_html/.htaccess
chmod 644 public_html/index.php
chmod 755 public_html/storage/
chmod 755 public_html/storage/uploads/
chmod 644 public_html/.env
```

### **Step 6: Install PHP Dependencies**

#### **If SSH access available:**

```bash
cd public_html
composer install --optimize-autoloader --no-dev
```

#### **If no SSH access:**

1. Run `composer install` locally
2. Upload the entire `vendor/` folder
3. Ensure `vendor/autoload.php` is accessible

### **Step 7: Database Setup**

#### **Using Gandi.net Database Manager:**

1. Create MySQL database in control panel
2. Note database name, username, password
3. Import `database/schema.sql` using phpMyAdmin

#### **Using phpMyAdmin:**

1. Access phpMyAdmin from Gandi.net control panel
2. Select your database
3. Import the `database/schema.sql` file

### **Step 8: Environment Configuration**

Create `.env` file on Gandi.net:

```env
# Database (Gandi.net specific)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_gandi_database
DB_USERNAME=your_gandi_username
DB_PASSWORD=your_gandi_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Application
APP_NAME=IslamWiki
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC

# Security
JWT_SECRET=your_random_32_character_secret
APP_KEY=your_random_32_character_key
CSRF_TOKEN_NAME=csrf_token

# Cache (file-based for shared hosting)
CACHE_DRIVER=file
CACHE_PREFIX=islamwiki_
CACHE_TTL=3600

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIES=true

# Mail (Gandi.net SMTP)
MAIL_DRIVER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="IslamWiki"
```

## 🌐 **Gandi.net Web Server Configuration**

### **Apache (.htaccess already configured):**

The framework includes a `.htaccess` file optimized for shared hosting that:

- Redirects all requests to `index.php`
- Sets security headers
- Enables caching and compression
- Blocks access to sensitive directories

### **If you need custom configuration:**

```apache
# In your .htaccess or Apache config
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

## 🚀 **Installation Process**

1. **Access your domain** - Should show the framework
2. **Run installer** - Visit `yourdomain.com/install.php`
3. **Follow setup wizard:**
   - Database connection test
   - Admin user creation
   - Initial configuration
4. **Delete install.php** - For security after installation

## 🔒 **Gandi.net Security Considerations**

### **Files to protect:**

- `.env` (environment variables)
- `src/` (PHP source code)
- `storage/` (application data)
- `vendor/` (PHP dependencies)

### **The .htaccess already blocks:**

- Access to `.env` files
- Access to `src/` directory
- Access to `storage/` directory
- Access to configuration files

## 📱 **Frontend Development Workflow**

### **Local Development:**

```bash
npm run dev          # Start development server
npm run build        # Build for production
npm run preview      # Preview production build
```

### **Deployment to Gandi.net:**

```bash
npm run build:shared-hosting  # Build optimized assets
# Upload dist/ contents to Gandi.net assets/ folder
```

## 🆘 **Gandi.net Specific Troubleshooting**

### **Common Issues:**

1. **500 Internal Server Error**
   - Check PHP version (need 8.2+)
   - Verify `.htaccess` is uploaded
   - Check file permissions
   - Enable error logging in `.env`

2. **Database Connection Failed**
   - Verify database credentials in `.env`
   - Check database exists in Gandi.net control panel
   - Ensure MySQL extension is enabled
   - Test connection with phpMyAdmin

3. **Assets Not Loading**
   - Ensure `assets/` folder is in web root
   - Check file paths in built HTML
   - Verify `.htaccess` rewrite rules
   - Check browser console for errors

4. **Permission Denied**
   - Set proper file permissions (755 for dirs, 644 for files)
   - Ensure web server can read files
   - Check storage directory is writable

### **Performance Tips for Gandi.net:**

- Enable Apache mod_deflate for compression
- Use browser caching via `.htaccess`
- Optimize images before uploading
- Minimize database queries
- Use file-based caching (already configured)

## 🎯 **Gandi.net Deployment Summary**

**What works on Gandi.net:**

- ✅ **PHP Framework**: Full functionality
- ✅ **MySQL Database**: Standard support
- ✅ **Static Assets**: Pre-built frontend files
- ✅ **Apache Server**: `.htaccess` support
- ✅ **File Storage**: Local file system

**What you build locally:**

- ✅ **React Components**: Compiled to static HTML/CSS/JS
- ✅ **TypeScript**: Transpiled to JavaScript
- ✅ **Tailwind CSS**: Compiled to regular CSS
- ✅ **Optimized Assets**: Minified, compressed files

**The result:**
frontend that gets compiled to static assets and served alongside your PHP backend.
frontend** that gets compiled to static assets and served alongside your PHP backend.

---

**Need Help?** Check the main README.md or create an issue in the project repository.
