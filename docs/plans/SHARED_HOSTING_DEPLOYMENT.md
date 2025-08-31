# 🚀 IslamWiki Framework - Shared Hosting Deployment Guide

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 📋 **Overview**

This guide explains shared hosting deployment using "Build Locally, Deploy Assets".
The approach is **"Build Locally, Deploy Built Assets"**.

## 🔧 **Prerequisites (Local Development Machine)**

### **Required Software:**

- **Node.js 18+** (for building frontend assets)
- **npm 8+** (for package management)
- **Git** (for version control)
- **FileZilla/WinSCP** (for uploading files)

### **Install Node.js:**

```bash
# Windows: Download from https://nodejs.org/
# macOS: brew install node
# Linux: sudo apt install nodejs npm
```

## 🏗️ **Step-by-Step Deployment Process**

### **Step 1: Build Assets Locally**

```bash
# Clone your project
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install dependencies
npm install

# Build production assets
npm run build:shared-hosting
```

This creates a `dist/` folder with optimized assets.

### **Step 2: Prepare Files for Upload**

```bash
# Your project structure should look like:
islamwiki/
├── dist/                   # Built frontend assets
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
├── src/                    # PHP source code
├── public/                 # Web root
├── database/               # Database files
├── composer.json           # PHP dependencies
└── .env                    # Environment config
```

### **Step 3: Upload to Shared Hosting**

#### **Option A: Using File Manager (cPanel)**

1. Log into your cPanel
2. Navigate to File Manager
3. Upload all files to your `public_html/` directory
4. Ensure `public/` folder becomes your web root

#### **Option B: Using FTP/SFTP**

```bash
# Upload using FileZilla, WinSCP, or command line
scp -r islamwiki/* user@yourdomain.com:public_html/
```

### **Step 4: Set Permissions**

```bash
# On shared hosting, set these permissions:
chmod 755 public/
chmod 644 public/.htaccess
chmod 644 public/index.php
chmod 755 storage/
chmod 755 storage/uploads/
chmod 644 .env
```

### **Step 5: Install PHP Dependencies**

```bash
# On shared hosting (if SSH access is available)
composer install --optimize-autoloader --no-dev

# Or upload vendor/ folder from local machine
```

## 🌐 **Web Server Configuration**

### **Apache (.htaccess already configured):**

The framework includes a `.htaccess` file that:

- Redirects all requests to `index.php`
- Sets security headers
- Enables caching and compression
- Blocks access to sensitive files

### **Nginx (if available):**

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 📁 **Final Directory Structure on Shared Hosting**

```bash
public_html/                 # Your web root
├── assets/                  # Built frontend assets
│   ├── css/
│   ├── js/
│   └── images/
├── src/                     # PHP source code
├── storage/                 # Application storage
├── database/                # Database files
├── composer.json            # PHP dependencies
├── .env                     # Environment config
├── .htaccess                # Apache configuration
└── index.php                # Front controller
```

## ⚙️ **Environment Configuration**

### **Create .env file:**

```env
# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application
APP_NAME=IslamWiki
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
JWT_SECRET=your_random_secret_key
APP_KEY=your_random_app_key
```

## 🗄️ **Database Setup**

### **Option 1: phpMyAdmin (cPanel)**

1. Create database in cPanel
2. Import `database/schema.sql`
3. Update `.env` with database credentials

### **Option 2: Command Line (if SSH available)**

```bash
mysql -u username -p database_name < database/schema.sql
```

## 🚀 **Installation**

1. **Access your domain** - Should show the framework
2. **Run installer** - Visit `yourdomain.com/install.php`
3. **Follow setup wizard** - Database, admin user, etc.
4. **Delete install.php** - For security after installation

## 🔒 **Security Considerations**

### **Files to Protect:**

- `.env` (environment variables)
- `src/` (source code)
- `storage/` (application data)
- `composer.json` (dependencies)

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

### **Deployment:**

```bash
npm run build:shared-hosting  # Build optimized assets
# Upload dist/ contents to shared hosting assets/ folder
```

## 🆘 **Troubleshooting**

### **Common Issues:**

1. **500 Internal Server Error**
   - Check `.htaccess` is uploaded
   - Verify PHP version (8.2+ required)
   - Check file permissions

2. **Assets Not Loading**
   - Ensure `assets/` folder is in web root
   - Check file paths in built HTML
   - Verify `.htaccess` rewrite rules

3. **Database Connection Failed**
   - Verify database credentials in `.env`
   - Check database exists and is accessible
   - Ensure MySQL extension is enabled

### **Performance Tips:**

- Enable Apache mod_deflate for compression
- Use CDN for static assets if available
- Enable browser caching via `.htaccess`
- Optimize images before uploading

## 🎯 **Summary**

**For Shared Hosting:**

- ✅ **PHP Framework**: Works perfectly
- ✅ **Database**: Standard MySQL support
- ✅ **Static Assets**: Upload pre-built files
- ❌ **Node.js**: Not available (build locally)
- ❌ **Real-time**: Use polling instead of WebSockets

your shared hosting environment.
your shared hosting environment.**

---

**Need Help?** Check the main README.md or create an issue in the project repository.
