# IslamWiki Framework - Database Schema

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🗄️ **Database Schema Overview**

The IslamWiki Framework uses a comprehensive database schema designed to support Islamic content management, social networking, learning management, and real-time communication features.

## 🎯 **Database Design Principles**

### **1. Normalization**
- **Third Normal Form**: Eliminate data redundancy
- **Referential Integrity**: Foreign key constraints
- **Atomic Values**: Single values in each field
- **Functional Dependencies**: Proper key relationships

### **2. Scalability Considerations**
- **Partitioning Strategy**: Support for large datasets
- **Sharding Support**: Horizontal scaling capability
- **Read Replicas**: Separate read/write operations
- **Connection Pooling**: Efficient connection management

### **3. Performance Optimization**
- **Strategic Indexing**: Optimize query performance
- **Query Optimization**: Efficient SQL queries
- **Caching Strategy**: Multi-level caching support
- **Connection Management**: Optimize database connections

## 🏗️ **Database Architecture**

### **Database Structure**
```
islamwiki (Database)
├── Core Tables
│   ├── users                    # User accounts
│   ├── roles                    # User roles
│   ├── user_roles              # User-role relationships
│   ├── user_profiles            # Extended user info
│   └── settings                # System settings
├── Content Tables
│   ├── content_categories       # Content categories
│   ├── articles                 # Wiki articles
│   ├── article_versions         # Content versioning
│   ├── comments                 # User comments
│   └── tags                     # Content tags
├── Social Tables
│   ├── posts                    # Social posts
│   ├── likes                    # Post/comment likes
│   ├── follows                  # User relationships
│   ├── messages                 # Private messages
│   └── notifications            # User notifications
├── Learning Tables
│   ├── courses                  # Learning courses
│   ├── lessons                  # Course lessons
│   ├── enrollments              # Course enrollments
│   ├── progress                 # Learning progress
│   └── assessments              # Learning assessments
├── System Tables
│   ├── activity_logs            # System activity
│   ├── sessions                 # User sessions
│   ├── cache                    # System cache
│   └── migrations               # Database migrations
└── Islamic Content Tables
    ├── scholars                 # Islamic scholars
    ├── fatwas                   # Islamic rulings
    ├── hadiths                  # Prophetic traditions
    └── quran_verses             # Quranic verses
```

## 📊 **Core Tables**

### **1. Users Table (`users`)**

**Purpose**: Store user account information and authentication data

```sql
CREATE TABLE `users` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `email` varchar(255) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `display_name` varchar(100) NOT NULL,
    `bio` text,
    `avatar` varchar(255),
    `email_verified_at` timestamp NULL,
    `email_verification_token` varchar(100),
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `is_banned` tinyint(1) NOT NULL DEFAULT 0,
    `last_login_at` timestamp NULL,
    `last_seen_at` timestamp NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_username` (`username`),
    KEY `idx_email` (`email`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique user identifier
- **`username`**: Unique username for login
- **`email`**: Unique email address
- **`password_hash`**: Hashed password (bcrypt)
- **`first_name`**: User's first name
- **`last_name`**: User's last name
- **`display_name`**: Public display name
- **`bio`**: User biography
- **`avatar`**: Profile picture path
- **`email_verified_at`**: Email verification timestamp
- **`email_verification_token`**: Email verification token
- **`is_active`**: Account active status
- **`is_banned`**: Account ban status
- **`last_login_at`**: Last login timestamp
- **`last_seen_at`**: Last activity timestamp
- **`created_at`**: Account creation timestamp
- **`updated_at`**: Last update timestamp

### **2. Roles Table (`roles`)**

**Purpose**: Define user roles and permissions

```sql
CREATE TABLE `roles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL UNIQUE,
    `display_name` varchar(100) NOT NULL,
    `description` text,
    `permissions` json,
    `is_system` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique role identifier
- **`name`**: Unique role name (e.g., 'admin', 'moderator')
- **`display_name`**: Human-readable role name
- **`description`**: Role description
- **`permissions`**: JSON array of permissions
- **`is_system`**: System role flag (cannot be deleted)
- **`created_at`**: Role creation timestamp
- **`updated_at`**: Last update timestamp

**Default Roles**:
```sql
INSERT INTO `roles` (`name`, `display_name`, `description`, `permissions`, `is_system`) VALUES
('admin', 'Administrator', 'Full system access', '["*"]', 1),
('moderator', 'Moderator', 'Content moderation access', '["content.moderate", "users.view", "comments.moderate"]', 1),
('editor', 'Editor', 'Content creation and editing', '["content.create", "content.edit", "content.publish"]', 1),
('user', 'User', 'Standard user access', '["content.view", "comments.create", "profile.edit"]', 1);
```

### **3. User Roles Table (`user_roles`)**

**Purpose**: Many-to-many relationship between users and roles

```sql
CREATE TABLE `user_roles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `role_id` bigint(20) unsigned NOT NULL,
    `granted_by` bigint(20) unsigned,
    `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_role` (`user_id`, `role_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_role_id` (`role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique relationship identifier
- **`user_id`**: Reference to users table
- **`role_id`**: Reference to roles table
- **`granted_by`**: User who granted the role
- **`granted_at`**: Role assignment timestamp

### **4. User Profiles Table (`user_profiles`)**

**Purpose**: Extended user information and preferences

```sql
CREATE TABLE `user_profiles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL UNIQUE,
    `date_of_birth` date,
    `gender` enum('male', 'female', 'other') NULL,
    `location` varchar(255),
    `website` varchar(255),
    `social_links` json,
    `preferences` json,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique profile identifier
- **`user_id`**: Reference to users table
- **`date_of_birth`**: User's birth date
- **`gender`**: User's gender
- **`location`**: User's location
- **`website`**: User's website
- **`social_links`**: JSON object of social media links
- **`preferences`**: JSON object of user preferences
- **`created_at`**: Profile creation timestamp
- **`updated_at`**: Last update timestamp

## 📝 **Content Tables**

### **1. Content Categories Table (`content_categories`)**

**Purpose**: Organize content into hierarchical categories

```sql
CREATE TABLE `content_categories` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `parent_id` bigint(20) unsigned NULL,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL UNIQUE,
    `description` text,
    `image` varchar(255),
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`),
    FOREIGN KEY (`parent_id`) REFERENCES `content_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique category identifier
- **`parent_id`**: Parent category (self-referencing)
- **`name`**: Category name
- **`slug`**: URL-friendly category name
- **`description`**: Category description
- **`image`**: Category image path
- **`sort_order`**: Display order
- **`is_active`**: Category active status
- **`created_at`**: Category creation timestamp
- **`updated_at`**: Last update timestamp

**Default Categories**:
```sql
INSERT INTO `content_categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Islamic Beliefs', 'islamic-beliefs', 'Core Islamic beliefs and theology', 1),
('Islamic Law', 'islamic-law', 'Islamic jurisprudence and legal rulings', 2),
('Islamic History', 'islamic-history', 'Islamic history and civilization', 3),
('Islamic Ethics', 'islamic-ethics', 'Islamic moral and ethical teachings', 4),
('Islamic Practices', 'islamic-practices', 'Daily Islamic practices and rituals', 5);
```

### **2. Articles Table (`articles`)**

**Purpose**: Store wiki articles and content

```sql
CREATE TABLE `articles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `content` longtext NOT NULL,
    `excerpt` text,
    `author_id` bigint(20) unsigned NOT NULL,
    `category_id` bigint(20) unsigned,
    `status` enum('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `featured` tinyint(1) NOT NULL DEFAULT 0,
    `view_count` bigint(20) unsigned NOT NULL DEFAULT 0,
    `meta_title` varchar(255),
    `meta_description` text,
    `meta_keywords` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_author_id` (`author_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_status` (`status`),
    KEY `idx_featured` (`featured`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `content_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique article identifier
- **`title`**: Article title
- **`slug`**: URL-friendly article title
- **`content`**: Article content (Markdown)
- **`excerpt`**: Article summary
- **`author_id`**: Article author reference
- **`category_id`**: Article category reference
- **`status`**: Article publication status
- **`featured`**: Featured article flag
- **`view_count`**: Article view count
- **`meta_title`**: SEO meta title
- **`meta_description`**: SEO meta description
- **`meta_keywords`**: SEO meta keywords
- **`created_at`**: Article creation timestamp
- **`updated_at`**: Last update timestamp

### **3. Article Versions Table (`article_versions`)**

**Purpose**: Track content changes and version history

```sql
CREATE TABLE `article_versions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `version_number` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` longtext NOT NULL,
    `excerpt` text,
    `changes_summary` text,
    `created_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_version_number` (`version_number`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique version identifier
- **`article_id`**: Reference to articles table
- **`version_number`**: Version number
- **`title`**: Article title at this version
- **`content`**: Article content at this version
- **`excerpt`**: Article excerpt at this version
- **`changes_summary`**: Summary of changes
- **`created_by`**: User who created this version
- **`created_at`**: Version creation timestamp

### **4. Comments Table (`comments`)**

**Purpose**: Store user comments and discussions

```sql
CREATE TABLE `comments` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `content` text NOT NULL,
    `author_id` bigint(20) unsigned NOT NULL,
    `parent_id` bigint(20) unsigned NULL,
    `article_id` bigint(20) unsigned,
    `is_approved` tinyint(1) NOT NULL DEFAULT 0,
    `is_spam` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_author_id` (`author_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique comment identifier
- **`content`**: Comment content
- **`author_id`**: Comment author reference
- **`parent_id`**: Parent comment (for replies)
- **`article_id`**: Associated article
- **`is_approved`**: Comment approval status
- **`is_spam`**: Spam detection flag
- **`created_at`**: Comment creation timestamp
- **`updated_at`**: Last update timestamp

## 👥 **Social Tables**

### **1. Posts Table (`posts`)**

**Purpose**: Store social media posts

```sql
CREATE TABLE `posts` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `content` text NOT NULL,
    `author_id` bigint(20) unsigned NOT NULL,
    `type` enum('text', 'image', 'video', 'link') NOT NULL DEFAULT 'text',
    `media_url` varchar(255),
    `link_url` varchar(255),
    `link_title` varchar(255),
    `link_description` text,
    `link_image` varchar(255),
    `is_public` tinyint(1) NOT NULL DEFAULT 1,
    `like_count` bigint(20) unsigned NOT NULL DEFAULT 0,
    `comment_count` bigint(20) unsigned NOT NULL DEFAULT 0,
    `share_count` bigint(20) unsigned NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_author_id` (`author_id`),
    KEY `idx_type` (`type`),
    KEY `idx_is_public` (`is_public`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique post identifier
- **`content`**: Post content
- **`author_id`**: Post author reference
- **`type`**: Post type (text, image, video, link)
- **`media_url`**: Media file path
- **`link_url`**: Link URL
- **`link_title`**: Link title
- **`link_description`**: Link description
- **`link_image`**: Link preview image
- **`is_public`**: Public visibility flag
- **`like_count`**: Number of likes
- **`comment_count`**: Number of comments
- **`share_count`**: Number of shares
- **`created_at`**: Post creation timestamp
- **`updated_at`**: Last update timestamp

### **2. Likes Table (`likes`)**

**Purpose**: Track user likes on posts and comments

```sql
CREATE TABLE `likes` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `likeable_type` varchar(50) NOT NULL,
    `likeable_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_like` (`user_id`, `likeable_type`, `likeable_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_likeable` (`likeable_type`, `likeable_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique like identifier
- **`user_id`**: User who liked
- **`likeable_type`**: Type of liked item (Post, Comment, Article)
- **`likeable_id`**: ID of liked item
- **`created_at`**: Like creation timestamp

### **3. Follows Table (`follows`)**

**Purpose**: Track user following relationships

```sql
CREATE TABLE `follows` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `follower_id` bigint(20) unsigned NOT NULL,
    `following_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_follow` (`follower_id`, `following_id`),
    KEY `idx_follower_id` (`follower_id`),
    KEY `idx_following_id` (`following_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique follow relationship identifier
- **`follower_id`**: User who is following
- **`following_id`**: User being followed
- **`created_at`**: Follow relationship creation timestamp

## 📚 **Learning Tables**

### **1. Courses Table (`courses`)**

**Purpose**: Store learning courses

```sql
CREATE TABLE `courses` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `instructor_id` bigint(20) unsigned NOT NULL,
    `difficulty_level` enum('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
    `duration` int(11) NOT NULL DEFAULT 0,
    `price` decimal(10,2) NOT NULL DEFAULT 0.00,
    `is_published` tinyint(1) NOT NULL DEFAULT 0,
    `enrollment_count` bigint(20) unsigned NOT NULL DEFAULT 0,
    `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_instructor_id` (`instructor_id`),
    KEY `idx_difficulty_level` (`difficulty_level`),
    KEY `idx_is_published` (`is_published`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique course identifier
- **`title`**: Course title
- **`slug`**: URL-friendly course title
- **`description`**: Course description
- **`instructor_id`**: Course instructor reference
- **`difficulty_level`**: Course difficulty level
- **`duration`**: Course duration in minutes
- **`price`**: Course price
- **`is_published`**: Course publication status
- **`enrollment_count`**: Number of enrolled students
- **`rating`**: Course rating (0.00 - 5.00)
- **`created_at`**: Course creation timestamp
- **`updated_at`**: Last update timestamp

### **2. Lessons Table (`lessons`)**

**Purpose**: Store course lessons

```sql
CREATE TABLE `lessons` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `course_id` bigint(20) unsigned NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` longtext NOT NULL,
    `video_url` varchar(255),
    `duration` int(11) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `is_free` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_course_id` (`course_id`),
    KEY `idx_sort_order` (`sort_order`),
    KEY `idx_is_free` (`is_free`),
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique lesson identifier
- **`course_id`**: Reference to courses table
- **`title`**: Lesson title
- **`content`**: Lesson content
- **`video_url`**: Lesson video URL
- **`duration`**: Lesson duration in minutes
- **`sort_order`**: Lesson display order
- **`is_free`**: Free lesson flag
- **`created_at`**: Lesson creation timestamp
- **`updated_at`**: Last update timestamp

## 🔒 **System Tables**

### **1. Sessions Table (`sessions`)**

**Purpose**: Store user session information

```sql
CREATE TABLE `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint(20) unsigned NULL,
    `ip_address` varchar(45),
    `user_agent` text,
    `payload` longtext NOT NULL,
    `last_activity` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_last_activity` (`last_activity`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Session identifier
- **`user_id`**: User reference (NULL for guest sessions)
- **`ip_address`**: User's IP address
- **`user_agent`**: User's browser/device information
- **`payload`**: Session data
- **`last_activity`**: Last activity timestamp

### **2. Activity Logs Table (`activity_logs`)**

**Purpose**: Track system and user activities

```sql
CREATE TABLE `activity_logs` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NULL,
    `action` varchar(100) NOT NULL,
    `description` text,
    `ip_address` varchar(45),
    `user_agent` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique log entry identifier
- **`user_id`**: User who performed the action
- **`action`**: Action performed
- **`description`**: Action description
- **`ip_address`**: User's IP address
- **`user_agent`**: User's browser/device information
- **`created_at`**: Action timestamp

## 🕌 **Islamic Content Tables**

### **1. Scholars Table (`scholars`)**

**Purpose**: Store information about Islamic scholars

```sql
CREATE TABLE `scholars` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `biography` text,
    `birth_date` date,
    `death_date` date,
    `school_of_thought` varchar(100),
    `specialization` varchar(255),
    `image` varchar(255),
    `is_verified` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_school_of_thought` (`school_of_thought`),
    KEY `idx_is_verified` (`is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields Description**:
- **`id`**: Unique scholar identifier
- **`name`**: Scholar's name
- **`biography`**: Scholar's biography
- **`birth_date`**: Scholar's birth date
- **`death_date`**: Scholar's death date
- **`school_of_thought`**: Scholar's school of thought
- **`specialization`**: Scholar's area of specialization
- **`image`**: Scholar's image path
- **`is_verified`**: Scholar verification status
- **`created_at`**: Record creation timestamp
- **`updated_at`**: Last update timestamp

## 📊 **Database Indexes**

### **Performance Indexes**

#### **1. Primary Indexes**
- All tables have primary key indexes on `id` fields
- Unique constraints on username, email, and slug fields

#### **2. Foreign Key Indexes**
- All foreign key fields are indexed for join performance
- Composite indexes for complex queries

#### **3. Search Indexes**
- Full-text search indexes on content fields
- Partial indexes for status and date fields

#### **4. Composite Indexes**
```sql
-- User activity index
CREATE INDEX idx_user_activity ON users(is_active, last_seen_at);

-- Content search index
CREATE INDEX idx_content_search ON articles(status, category_id, created_at);

-- Social feed index
CREATE INDEX idx_social_feed ON posts(author_id, is_public, created_at);
```

## 🔄 **Database Migrations**

### **Migration System**

#### **1. Migration Files**
- Version-controlled database changes
- Rollback capability
- Environment-specific migrations

#### **2. Migration Commands**
```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Reset database
php artisan migrate:reset

# Refresh database
php artisan migrate:refresh
```

## 📈 **Database Optimization**

### **Performance Strategies**

#### **1. Query Optimization**
- Use prepared statements
- Optimize JOIN operations
- Limit result sets
- Use appropriate indexes

#### **2. Caching Strategy**
- Query result caching
- Object caching
- Page caching
- CDN integration

#### **3. Connection Management**
- Connection pooling
- Read/write splitting
- Connection timeouts
- Error handling

---

## 📚 **Related Documentation**

- **[Architecture Overview](ARCHITECTURE_OVERVIEW.md)** - High-level architecture
- **[Components Overview](COMPONENTS_OVERVIEW.md)** - Framework components
- **[API Reference](API_REFERENCE.md)** - API documentation
- **[Security Guide](SECURITY_GUIDE.md)** - Security implementation
- **[Performance Guide](PERFORMANCE_GUIDE.md)** - Performance optimization

---

**Last Updated:** August 30, 2025  
**Next Update:** With v0.1.0 release  
**Maintainer:** Khalid Abdullah  
**License:** AGPL-3.0  
**Status:** Active Development 