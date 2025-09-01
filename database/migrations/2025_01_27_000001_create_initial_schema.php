<?php

use IslamWiki\Core\Database\DatabaseManager;

/**
 * Create Initial Schema Migration
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class CreateInitialSchema
{
    private DatabaseManager $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->createUsersTable();
        $this->createRolesTable();
        $this->createUserRolesTable();
        $this->createUserProfilesTable();
        $this->createContentCategoriesTable();
        $this->createArticlesTable();
        $this->createArticleVersionsTable();
        $this->createCommentsTable();
        $this->createPostsTable();
        $this->createLikesTable();
        $this->createFollowsTable();
        $this->createCoursesTable();
        $this->createLessonsTable();
        $this->createSessionsTable();
        $this->createActivityLogsTable();
        $this->createScholarsTable();
        $this->insertDefaultData();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropTables();
    }

    /**
     * Create users table
     */
    private function createUsersTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create roles table
     */
    private function createRolesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `roles` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create user_roles table
     */
    private function createUserRolesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `user_roles` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create user_profiles table
     */
    private function createUserProfilesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `user_profiles` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create content_categories table
     */
    private function createContentCategoriesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `content_categories` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create articles table
     */
    private function createArticlesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `articles` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create article_versions table
     */
    private function createArticleVersionsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `article_versions` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create comments table
     */
    private function createCommentsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `comments` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create posts table
     */
    private function createPostsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `posts` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create likes table
     */
    private function createLikesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `likes` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create follows table
     */
    private function createFollowsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `follows` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create courses table
     */
    private function createCoursesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `courses` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create lessons table
     */
    private function createLessonsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `lessons` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create sessions table
     */
    private function createSessionsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `sessions` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create activity_logs table
     */
    private function createActivityLogsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `activity_logs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create scholars table
     */
    private function createScholarsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `scholars` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Insert default data
     */
    private function insertDefaultData(): void
    {
        // Insert default roles
        $this->insertDefaultRoles();
        
        // Insert default categories
        $this->insertDefaultCategories();
        
        // Insert default admin user
        $this->insertDefaultAdminUser();
    }

    /**
     * Insert default roles
     */
    private function insertDefaultRoles(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access', 'permissions' => '["*"]', 'is_system' => 1],
            ['name' => 'moderator', 'display_name' => 'Moderator', 'description' => 'Content moderation access', 'permissions' => '["content.moderate", "users.view", "comments.moderate"]', 'is_system' => 1],
            ['name' => 'editor', 'display_name' => 'Editor', 'description' => 'Content creation and editing', 'permissions' => '["content.create", "content.edit", "content.publish"]', 'is_system' => 1],
            ['name' => 'user', 'display_name' => 'User', 'description' => 'Standard user access', 'permissions' => '["content.view", "comments.create", "profile.edit"]', 'is_system' => 1]
        ];

        foreach ($roles as $role) {
            $sql = "INSERT IGNORE INTO `roles` (name, display_name, description, permissions, is_system) VALUES (?, ?, ?, ?, ?)";
            $this->database->execute($sql, [
                $role['name'],
                $role['display_name'],
                $role['description'],
                $role['permissions'],
                $role['is_system']
            ]);
        }
    }

    /**
     * Insert default categories
     */
    private function insertDefaultCategories(): void
    {
        $categories = [
            ['name' => 'Islamic Beliefs', 'slug' => 'islamic-beliefs', 'description' => 'Core Islamic beliefs and theology', 'sort_order' => 1],
            ['name' => 'Islamic Law', 'slug' => 'islamic-law', 'description' => 'Islamic jurisprudence and legal rulings', 'sort_order' => 2],
            ['name' => 'Islamic History', 'slug' => 'islamic-history', 'description' => 'Islamic history and civilization', 'sort_order' => 3],
            ['name' => 'Islamic Ethics', 'slug' => 'islamic-ethics', 'description' => 'Islamic moral and ethical teachings', 'sort_order' => 4],
            ['name' => 'Islamic Practices', 'slug' => 'islamic-practices', 'description' => 'Daily Islamic practices and rituals', 'sort_order' => 5]
        ];

        foreach ($categories as $category) {
            $sql = "INSERT IGNORE INTO `content_categories` (name, slug, description, sort_order) VALUES (?, ?, ?, ?)";
            $this->database->execute($sql, [
                $category['name'],
                $category['slug'],
                $category['description'],
                $category['sort_order']
            ]);
        }
    }

    /**
     * Insert default admin user
     */
    private function insertDefaultAdminUser(): void
    {
        // Check if admin user already exists
        $stmt = $this->database->execute("SELECT id FROM `users` WHERE username = 'admin' LIMIT 1");
        if ($stmt->fetch()) {
            return; // Admin user already exists
        }

        // Create admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO `users` (username, email, password_hash, first_name, last_name, display_name, bio, is_active, email_verified_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->database->execute($sql, [
            'admin',
            'admin@islamwiki.org',
            $adminPassword,
            'System',
            'Administrator',
            'System Administrator',
            'Default system administrator account',
            1
        ]);

        // Get admin user ID
        $adminId = $this->database->lastInsertId();

        // Get admin role ID
        $stmt = $this->database->execute("SELECT id FROM `roles` WHERE name = 'admin' LIMIT 1");
        $adminRole = $stmt->fetch();
        
        if ($adminRole) {
            // Assign admin role
            $sql = "INSERT INTO `user_roles` (user_id, role_id, granted_by) VALUES (?, ?, ?)";
            $this->database->execute($sql, [$adminId, $adminRole['id'], $adminId]);
        }
    }

    /**
     * Drop all tables
     */
    private function dropTables(): void
    {
        $tables = [
            'activity_logs', 'sessions', 'lessons', 'courses', 'follows', 'likes', 'posts',
            'comments', 'article_versions', 'articles', 'content_categories', 'user_profiles',
            'user_roles', 'roles', 'scholars', 'users'
        ];

        foreach ($tables as $table) {
            $this->database->execute("DROP TABLE IF EXISTS `{$table}`");
        }
    }
} 