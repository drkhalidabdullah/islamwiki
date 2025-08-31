-- IslamWiki Database Schema
-- Author: Khalid Abdullah
-- Version: 0.0.1
-- Date: 2025-08-30
-- License: AGPL-3.0
-- 
-- This file contains the complete database structure for the IslamWiki platform

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `islamwiki` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `islamwiki`;

-- Users table
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

-- User roles table
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

-- Roles table
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

-- User profiles table
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

-- Content categories table
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

-- Wiki articles table
CREATE TABLE `wiki_articles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `content` longtext NOT NULL,
    `excerpt` text,
    `category_id` bigint(20) unsigned,
    `author_id` bigint(20) unsigned NOT NULL,
    `status` enum('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `view_count` bigint(20) unsigned NOT NULL DEFAULT 0,
    `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
    `rating_count` int(11) NOT NULL DEFAULT 0,
    `meta_title` varchar(255),
    `meta_description` text,
    `meta_keywords` text,
    `published_at` timestamp NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_author_id` (`author_id`),
    KEY `idx_status` (`status`),
    KEY `idx_published_at` (`published_at`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`category_id`) REFERENCES `content_categories` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Article versions table
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
    UNIQUE KEY `unique_article_version` (`article_id`, `version_number`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_version_number` (`version_number`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table
CREATE TABLE `comments` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `content_type` enum('article', 'question', 'course') NOT NULL,
    `content_id` bigint(20) unsigned NOT NULL,
    `parent_id` bigint(20) unsigned NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `content` text NOT NULL,
    `is_approved` tinyint(1) NOT NULL DEFAULT 1,
    `is_spam` tinyint(1) NOT NULL DEFAULT 0,
    `upvotes` int(11) NOT NULL DEFAULT 0,
    `downvotes` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_content` (`content_type`, `content_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table
CREATE TABLE `user_sessions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `token` varchar(255) NOT NULL UNIQUE,
    `ip_address` varchar(45),
    `user_agent` text,
    `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` timestamp NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_token` (`token`),
    KEY `idx_expires_at` (`expires_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE `system_settings` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `key` varchar(100) NOT NULL UNIQUE,
    `value` text,
    `type` enum('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `description` text,
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_key` (`key`),
    KEY `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE `activity_logs` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NULL,
    `action` varchar(100) NOT NULL,
    `description` text,
    `ip_address` varchar(45),
    `user_agent` text,
    `metadata` json,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`name`, `display_name`, `description`, `is_system`) VALUES
('admin', 'Administrator', 'Full system access and control', 1),
('moderator', 'Moderator', 'Content moderation and user management', 1),
('editor', 'Editor', 'Content creation and editing', 1),
('user', 'User', 'Standard user with basic permissions', 1),
('guest', 'Guest', 'Limited read-only access', 1);

-- Insert default system settings
INSERT INTO `system_settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('site_name', 'IslamWiki', 'string', 'Website name', 1),
('site_description', 'Comprehensive Islamic knowledge platform', 'string', 'Website description', 1),
('site_url', 'https://islamwiki.org', 'string', 'Website URL', 1),
('default_language', 'en', 'string', 'Default language', 1),
('registration_enabled', '1', 'boolean', 'Allow user registration', 1),
('email_verification_required', '1', 'boolean', 'Require email verification', 1),
('max_upload_size', '8388608', 'integer', 'Maximum file upload size in bytes', 1),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode', 0),
('maintenance_message', 'Site is under maintenance. Please check back later.', 'string', 'Maintenance mode message', 1);

-- Insert default content categories
INSERT INTO `content_categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Islamic Beliefs', 'islamic-beliefs', 'Core Islamic beliefs and theology', 1),
('Islamic Law', 'islamic-law', 'Islamic jurisprudence and legal rulings', 2),
('Islamic History', 'islamic-history', 'Islamic history and civilization', 3),
('Islamic Ethics', 'islamic-ethics', 'Islamic moral and ethical teachings', 4),
('Islamic Practices', 'islamic-practices', 'Daily Islamic practices and rituals', 5),
('Islamic Sciences', 'islamic-sciences', 'Islamic contributions to various sciences', 6),
('Contemporary Issues', 'contemporary-issues', 'Modern Islamic issues and challenges', 7); 