# IslamWiki Framework - Deployment Guide

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🚀 **Deployment Guide Overview**

This document provides comprehensive deployment strategies for the IslamWiki Framework across different environments, from shared hosting to enterprise cloud deployments.

## 🎯 **Deployment Strategies**

### **1. Shared Hosting Deployment**
- **"Build Locally, Deploy Built Assets"** approach
- Frontend assets built and optimized locally
- Only built files and PHP source uploaded
- Web-based installation wizard included

### **2. VPS/Cloud Deployment**
- **Full Source Deployment** with advanced features
- Redis caching and WebSocket support
- Background job processing
- Advanced monitoring and scaling

### **3. Container Deployment**
- **Docker** containerization
- **Kubernetes** orchestration
- **Microservices** architecture
- **Auto-scaling** capabilities

## 🏠 **Shared Hosting Deployment**

### **1. Prerequisites (Local Development Machine)**

#### **Required Software**
```bash
# Node.js and NPM
node --version  # Should be 18.0+
npm --version   # Should be 8.0+

# PHP and Composer
php --version   # Should be 8.2+
composer --version  # Should be 2.0+

# Git for version control
git --version

# File transfer tools
# - FileZilla (Windows/macOS)
# - WinSCP (Windows)
# - Cyberduck (macOS)
# - Command line tools (Linux)
```

#### **Local Environment Setup**
```bash
# Clone repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build production assets
npm run build:shared-hosting
```

### **2. Build Process**

#### **Production Build Script**
```json
{
  "scripts": {
    "build:shared-hosting": "tsc && vite build --mode production && npm run optimize-assets",
    "optimize-assets": "echo 'Assets optimized for shared hosting deployment'"
  }
}
```

#### **Build Output Structure**
```
islamwiki/
├── dist/                   # Built frontend assets
│   ├── assets/
│   │   ├── css/           # Minified CSS files
│   │   ├── js/            # Minified JavaScript files
│   │   └── images/        # Optimized images
│   ├── index.html         # Main HTML file
│   └── favicon.ico        # Favicon
├── src/                    # PHP framework source
├── public/                 # Web root files
├── storage/                # Application storage
├── database/               # Database files
├── composer.json           # PHP dependencies
├── .env.example            # Environment template
└── install.php             # Web installer
```

### **3. File Upload Process**

#### **Using File Manager (cPanel)**
1. **Log into cPanel**
2. **Navigate to File Manager**
3. **Upload all files** to your `public_html/` directory
4. **Ensure proper file structure**:
   - `public/` folder becomes your web root
   - All framework files are accessible
   - Storage directories have proper permissions

#### **Using FTP/SFTP**
```bash
# Using command line SCP
scp -r islamwiki/* username@yourdomain.com:public_html/

# Using FileZilla or WinSCP
# - Host: yourdomain.com
# - Username: your_ftp_username
# - Password: your_ftp_password
# - Port: 21 (FTP) or 22 (SFTP)
```

### **4. Server Configuration**

#### **File Permissions**
```bash
# Set proper permissions on shared hosting
chmod 755 public_html/
chmod 644 public_html/.htaccess
chmod 644 public_html/index.php
chmod 755 public_html/storage/
chmod 755 public_html/storage/uploads/
chmod 644 public_html/.env
chmod 755 public_html/storage/logs/
chmod 755 public_html/storage/cache/
```

#### **Apache Configuration (.htaccess)**
```apache
# IslamWiki Framework - Apache Configuration
# Author: Khalid Abdullah
# Version: 0.0.1
# Date: 2025-08-30
# License: AGPL-3.0

# Enable URL rewriting
RewriteEngine On

# Redirect all requests to index.php (front controller pattern)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security Headers
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Prevent MIME type sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self';"
    
    # Remove server signature
    Header unset Server
    Header unset X-Powered-By
</IfModule>

# Caching and Compression
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Images
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    
    # CSS and JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    
    # Fonts
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    
    # HTML
    ExpiresByType text/html "access plus 1 hour"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>

# Security: Block access to sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.(env|log|sql|md|txt|yml|yaml|ini|conf|config)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security: Block access to vendor and storage directories
<IfModule mod_rewrite.c>
    RewriteRule ^(vendor|storage|config|database|src|tests)/ - [F,L]
</IfModule>

# PHP Settings
<IfModule mod_php.c>
    php_value upload_max_filesize 8M
    php_value post_max_size 8M
    php_value max_execution_time 30
    php_value memory_limit 128M
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>
```

### **5. Environment Configuration**

#### **Environment File Setup**
```bash
# Copy environment template
cp .env.example .env

# Edit .env file with your settings
nano .env
```

#### **Environment Configuration**
```env
# Application Settings
APP_NAME=IslamWiki
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC
APP_LOCALE=en

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=islamwiki
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache Configuration
CACHE_DRIVER=file
CACHE_PREFIX=islamwiki_
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIES=true

# Mail Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="IslamWiki"

# Security Configuration
JWT_SECRET=your_jwt_secret_key_here
JWT_TTL=60
JWT_REFRESH_TTL=20160
APP_KEY=your_app_key_here
CSRF_TOKEN_NAME=csrf_token

# File Upload Configuration
UPLOAD_MAX_SIZE=8388608
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
UPLOAD_PATH=uploads
```

### **6. Database Setup**

#### **Database Creation**
```sql
-- Create database
CREATE DATABASE `islamwiki` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant permissions
CREATE USER 'islamwiki_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON `islamwiki`.* TO 'islamwiki_user'@'localhost';
FLUSH PRIVILEGES;
```

#### **Schema Import**
```bash
# Import database schema
mysql -u your_db_user -p your_database < database/schema.sql

# Or use phpMyAdmin to import the schema file
```

### **7. Installation Process**

#### **Web-Based Installer**
1. **Navigate to your domain**: `https://yourdomain.com/install.php`
2. **Follow the installation wizard**:
   - System requirements check
   - Database configuration
   - Admin user creation
   - Initial setup completion

#### **Installation Verification**
```bash
# Check if installation was successful
curl -I https://yourdomain.com

# Should return HTTP 200 OK
# Check for any error logs
tail -f storage/logs/laravel.log
```

## ☁️ **VPS/Cloud Deployment**

### **1. Server Requirements**

#### **Minimum Specifications**
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 50GB SSD
- **OS**: Ubuntu 20.04 LTS or CentOS 8
- **PHP**: 8.2+
- **MySQL**: 8.0+ or MariaDB 10.5+
- **Nginx/Apache**: Latest stable version

#### **Recommended Specifications**
- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 100GB+ SSD
- **OS**: Ubuntu 22.04 LTS
- **PHP**: 8.2+
- **MySQL**: 8.0+
- **Nginx**: Latest stable version
- **Redis**: 6.0+

### **2. Server Setup**

#### **Initial Server Configuration**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common

# Set timezone
sudo timedatectl set-timezone UTC

# Create application user
sudo adduser islamwiki
sudo usermod -aG sudo islamwiki
```

#### **PHP Installation**
```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis \
    php8.2-gd php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
    php8.2-intl php8.2-opcache php8.2-bcmath

# Configure PHP-FPM
sudo nano /etc/php/8.2/fpm/php.ini
```

#### **PHP Configuration (php.ini)**
```ini
; Performance settings
memory_limit = 512M
max_execution_time = 60
max_input_time = 120
max_input_vars = 3000

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 0
opcache.revalidate_path = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

; File upload settings
upload_max_filesize = 16M
post_max_size = 16M
max_file_uploads = 20

; Session settings
session.gc_maxlifetime = 7200
session.gc_probability = 1
session.gc_divisor = 100
```

#### **MySQL Installation**
```bash
# Install MySQL
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- MySQL setup
CREATE DATABASE `islamwiki` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'islamwiki_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON `islamwiki`.* TO 'islamwiki_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### **Redis Installation**
```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
```

```conf
# Redis configuration
bind 127.0.0.1
port 6379
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

#### **Nginx Installation**
```bash
# Install Nginx
sudo apt install -y nginx

# Configure Nginx
sudo nano /etc/nginx/sites-available/islamwiki
```

#### **Nginx Configuration**
```nginx
# IslamWiki Nginx Configuration
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/islamwiki/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Handle static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Handle all other requests
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Security: Block access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ \.(env|log|sql|md|txt|yml|yaml|ini|conf|config)$ {
        deny all;
    }

    location ~ ^/(vendor|storage|config|database|src|tests)/ {
        deny all;
    }
}
```

### **3. Application Deployment**

#### **Deployment Process**
```bash
# Switch to application user
sudo su - islamwiki

# Clone repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build production assets
npm run build

# Set proper permissions
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data public/uploads/
sudo chmod -R 755 storage/
sudo chmod -R 755 public/uploads/

# Create environment file
cp .env.example .env
nano .env
```

#### **Environment Configuration (Production)**
```env
# Application Settings
APP_NAME=IslamWiki
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC
APP_LOCALE=en

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=islamwiki
DB_USERNAME=islamwiki_user
DB_PASSWORD=secure_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache Configuration
CACHE_DRIVER=redis
CACHE_PREFIX=islamwiki_
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIES=true

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3

# Queue Configuration
QUEUE_CONNECTION=redis
QUEUE_DRIVER=redis

# Real-time Configuration
REALTIME_DRIVER=websocket
REALTIME_WEBSOCKET_HOST=127.0.0.1
REALTIME_WEBSOCKET_PORT=6001

# Mail Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="IslamWiki"

# Security Configuration
JWT_SECRET=your_jwt_secret_key_here
JWT_TTL=60
JWT_REFRESH_TTL=20160
APP_KEY=your_app_key_here
CSRF_TOKEN_NAME=csrf_token

# File Upload Configuration
UPLOAD_MAX_SIZE=16777216
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,mp4,mp3
UPLOAD_PATH=uploads

# Monitoring Configuration
MONITORING_ENABLED=true
PERFORMANCE_MONITORING=true
LOG_LEVEL=info
```

### **4. SSL/HTTPS Setup**

#### **Let's Encrypt SSL**
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

#### **SSL Configuration**
```nginx
# HTTPS configuration
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # SSL security settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    
    # Other configuration same as HTTP
    root /var/www/islamwiki/public;
    index index.php index.html;
    
    # ... rest of configuration
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

## 🐳 **Docker Deployment**

### **1. Docker Configuration**

#### **Dockerfile**
```dockerfile
# IslamWiki Framework Dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev
RUN npm install
RUN npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/public/uploads

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
```

#### **Docker Compose**
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    container_name: islamwiki_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./storage:/var/www/storage
      - ./public/uploads:/var/www/public/uploads
    networks:
      - islamwiki_network

  nginx:
    image: nginx:alpine
    container_name: islamwiki_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./public:/var/www/public
      - ./docker/ssl:/etc/nginx/ssl
    networks:
      - islamwiki_network

  db:
    image: mysql:8.0
    container_name: islamwiki_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: islamwiki
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_USER: islamwiki_user
      MYSQL_PASSWORD: user_password
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - islamwiki_network

  redis:
    image: redis:alpine
    container_name: islamwiki_redis
    restart: unless-stopped
    networks:
      - islamwiki_network

  websocket:
    build: ./docker/websocket
    container_name: islamwiki_websocket
    restart: unless-stopped
    ports:
      - "6001:6001"
    networks:
      - islamwiki_network

volumes:
  db_data:

networks:
  islamwiki_network:
    driver: bridge
```

### **2. Docker Deployment Commands**
```bash
# Build and start services
docker-compose up -d --build

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Update application
git pull origin main
docker-compose up -d --build app

# Database backup
docker exec islamwiki_db mysqldump -u root -p islamwiki > backup.sql

# Restore database
docker exec -i islamwiki_db mysql -u root -p islamwiki < backup.sql
```

## ☸️ **Kubernetes Deployment**

### **1. Kubernetes Manifests**

#### **Namespace**
```yaml
# namespace.yaml
apiVersion: v1
kind: Namespace
metadata:
  name: islamwiki
```

#### **ConfigMap**
```yaml
# configmap.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: islamwiki-config
  namespace: islamwiki
data:
  APP_ENV: "production"
  APP_DEBUG: "false"
  DB_HOST: "islamwiki-mysql"
  REDIS_HOST: "islamwiki-redis"
```

#### **Secret**
```yaml
# secret.yaml
apiVersion: v1
kind: Secret
metadata:
  name: islamwiki-secrets
  namespace: islamwiki
type: Opaque
data:
  DB_PASSWORD: <base64-encoded-password>
  JWT_SECRET: <base64-encoded-secret>
  APP_KEY: <base64-encoded-key>
```

#### **Deployment**
```yaml
# deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: islamwiki-app
  namespace: islamwiki
spec:
  replicas: 3
  selector:
    matchLabels:
      app: islamwiki
  template:
    metadata:
      labels:
        app: islamwiki
    spec:
      containers:
      - name: islamwiki
        image: your-registry/islamwiki:latest
        ports:
        - containerPort: 9000
        envFrom:
        - configMapRef:
            name: islamwiki-config
        - secretRef:
            name: islamwiki-secrets
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /health
            port: 9000
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /health
            port: 9000
          initialDelaySeconds: 5
          periodSeconds: 5
```

#### **Service**
```yaml
# service.yaml
apiVersion: v1
kind: Service
metadata:
  name: islamwiki-service
  namespace: islamwiki
spec:
  selector:
    app: islamwiki
  ports:
  - protocol: TCP
    port: 80
    targetPort: 9000
  type: ClusterIP
```

#### **Ingress**
```yaml
# ingress.yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: islamwiki-ingress
  namespace: islamwiki
  annotations:
    kubernetes.io/ingress.class: "nginx"
    cert-manager.io/cluster-issuer: "letsencrypt-prod"
spec:
  tls:
  - hosts:
    - yourdomain.com
    secretName: islamwiki-tls
  rules:
  - host: yourdomain.com
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: islamwiki-service
            port:
              number: 80
```

### **2. Kubernetes Deployment Commands**
```bash
# Apply manifests
kubectl apply -f namespace.yaml
kubectl apply -f configmap.yaml
kubectl apply -f secret.yaml
kubectl apply -f deployment.yaml
kubectl apply -f service.yaml
kubectl apply -f ingress.yaml

# Check deployment status
kubectl get pods -n islamwiki
kubectl get services -n islamwiki
kubectl get ingress -n islamwiki

# Scale deployment
kubectl scale deployment islamwiki-app --replicas=5 -n islamwiki

# Update deployment
kubectl set image deployment/islamwiki-app islamwiki=your-registry/islamwiki:new-version -n islamwiki

# View logs
kubectl logs -f deployment/islamwiki-app -n islamwiki
```

## 📊 **Deployment Monitoring**

### **1. Health Checks**

#### **Health Check Endpoint**
```php
class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache()
        ];
        
        $healthy = !in_array(false, $checks);
        $statusCode = $healthy ? 200 : 503;
        
        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks
        ], $statusCode);
    }
    
    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkRedis(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkStorage(): bool
    {
        return is_writable(storage_path()) && is_writable(public_path('uploads'));
    }
    
    private function checkCache(): bool
    {
        try {
            Cache::put('health_check', 'ok', 1);
            return Cache::get('health_check') === 'ok';
        } catch (Exception $e) {
            return false;
        }
    }
}
```

### **2. Performance Monitoring**

#### **Prometheus Metrics**
```php
class MetricsController extends Controller
{
    public function metrics(): Response
    {
        $metrics = [
            'http_requests_total' => $this->getRequestCount(),
            'http_request_duration_seconds' => $this->getRequestDuration(),
            'database_connections_active' => $this->getActiveConnections(),
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'memory_usage_bytes' => memory_get_usage(true),
            'memory_peak_bytes' => memory_get_peak_usage(true)
        ];
        
        $prometheusFormat = '';
        foreach ($metrics as $name => $value) {
            $prometheusFormat .= "# HELP {$name} {$name}\n";
            $prometheusFormat .= "# TYPE {$name} gauge\n";
            $prometheusFormat .= "{$name} {$value}\n";
        }
        
        return response($prometheusFormat, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8'
        ]);
    }
}
```

---

## 📚 **Related Documentation**

- **[Architecture Overview](ARCHITECTURE_OVERVIEW.md)** - High-level architecture
- **[Components Overview](COMPONENTS_OVERVIEW.md)** - Framework components
- **[Database Schema](DATABASE_SCHEMA.md)** - Database documentation
- **[API Reference](API_REFERENCE.md)** - API documentation
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation
- **[Performance Guide](PERFORMANCE_GUIDE.md)** - Performance optimization

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development 