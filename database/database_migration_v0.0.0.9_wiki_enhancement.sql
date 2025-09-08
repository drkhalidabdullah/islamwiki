-- IslamWiki Database Migration v0.0.0.9 - Wikipedia Features Enhancement
-- Author: Khalid Abdullah
-- Date: January 27, 2025
-- Description: Adds comprehensive Wikipedia-style features to the wiki system

USE `islamwiki`;

-- 1. Wiki Namespaces Table
CREATE TABLE IF NOT EXISTS `wiki_namespaces` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL UNIQUE,
    `display_name` varchar(100) NOT NULL,
    `is_talk` tinyint(1) NOT NULL DEFAULT 0,
    `parent_id` int(11) NULL,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_is_talk` (`is_talk`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_sort_order` (`sort_order`),
    FOREIGN KEY (`parent_id`) REFERENCES `wiki_namespaces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Wiki Talk Pages Table
CREATE TABLE IF NOT EXISTS `wiki_talk_pages` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `content` longtext NOT NULL,
    `created_by` bigint(20) unsigned NOT NULL,
    `updated_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_article_talk` (`article_id`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_updated_by` (`updated_by`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Wiki Templates Table
CREATE TABLE IF NOT EXISTS `wiki_templates` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `content` longtext NOT NULL,
    `description` text,
    `parameters` json,
    `usage_count` int(11) NOT NULL DEFAULT 0,
    `created_by` bigint(20) unsigned NOT NULL,
    `updated_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_slug` (`slug`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_updated_by` (`updated_by`),
    KEY `idx_usage_count` (`usage_count`),
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. User Watchlists Table
CREATE TABLE IF NOT EXISTS `user_watchlists` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `article_id` bigint(20) unsigned NOT NULL,
    `notify_email` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_watchlist` (`user_id`, `article_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Wiki Files Table
CREATE TABLE IF NOT EXISTS `wiki_files` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `filename` varchar(255) NOT NULL,
    `original_name` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_size` bigint(20) NOT NULL,
    `mime_type` varchar(100) NOT NULL,
    `width` int(11) NULL,
    `height` int(11) NULL,
    `description` text,
    `license` varchar(100),
    `uploaded_by` bigint(20) unsigned NOT NULL,
    `usage_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_filename` (`filename`),
    KEY `idx_uploaded_by` (`uploaded_by`),
    KEY `idx_mime_type` (`mime_type`),
    KEY `idx_usage_count` (`usage_count`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Wiki Redirects Table
CREATE TABLE IF NOT EXISTS `wiki_redirects` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `from_slug` varchar(255) NOT NULL UNIQUE,
    `to_article_id` bigint(20) unsigned NOT NULL,
    `created_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_from_slug` (`from_slug`),
    KEY `idx_to_article_id` (`to_article_id`),
    KEY `idx_created_by` (`created_by`),
    FOREIGN KEY (`to_article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Wiki Categories Table (Enhanced)
CREATE TABLE IF NOT EXISTS `wiki_categories` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `parent_id` bigint(20) unsigned NULL,
    `article_count` int(11) NOT NULL DEFAULT 0,
    `created_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_slug` (`slug`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_article_count` (`article_count`),
    FOREIGN KEY (`parent_id`) REFERENCES `wiki_categories` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Article Categories Junction Table
CREATE TABLE IF NOT EXISTS `wiki_article_categories` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `category_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_article_category` (`article_id`, `category_id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_category_id` (`category_id`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `wiki_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Wiki Special Pages Log
CREATE TABLE IF NOT EXISTS `wiki_special_logs` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `log_type` varchar(50) NOT NULL,
    `page_title` varchar(255) NOT NULL,
    `page_id` bigint(20) unsigned NULL,
    `user_id` bigint(20) unsigned NULL,
    `action` varchar(100) NOT NULL,
    `details` json,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_log_type` (`log_type`),
    KEY `idx_page_id` (`page_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`page_id`) REFERENCES `wiki_articles` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Enhanced Wiki Articles Table (Add missing columns)
ALTER TABLE `wiki_articles` 
ADD COLUMN IF NOT EXISTS `namespace_id` int(11) NOT NULL DEFAULT 1,
ADD COLUMN IF NOT EXISTS `protection_level` enum('none', 'autoconfirmed', 'sysop') NOT NULL DEFAULT 'none',
ADD COLUMN IF NOT EXISTS `is_redirect` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `redirect_target_id` bigint(20) unsigned NULL,
ADD COLUMN IF NOT EXISTS `last_edit_by` bigint(20) unsigned NULL,
ADD COLUMN IF NOT EXISTS `last_edit_at` timestamp NULL,
ADD COLUMN IF NOT EXISTS `edit_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `word_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `char_count` int(11) NOT NULL DEFAULT 0;

-- Add indexes for new columns
ALTER TABLE `wiki_articles`
ADD KEY IF NOT EXISTS `idx_namespace_id` (`namespace_id`),
ADD KEY IF NOT EXISTS `idx_protection_level` (`protection_level`),
ADD KEY IF NOT EXISTS `idx_is_redirect` (`is_redirect`),
ADD KEY IF NOT EXISTS `idx_redirect_target_id` (`redirect_target_id`),
ADD KEY IF NOT EXISTS `idx_last_edit_by` (`last_edit_by`),
ADD KEY IF NOT EXISTS `idx_last_edit_at` (`last_edit_at`),
ADD KEY IF NOT EXISTS `idx_edit_count` (`edit_count`);

-- Add foreign key for redirect target (if not exists)
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'wiki_articles' 
    AND CONSTRAINT_NAME = 'fk_redirect_target'
);

SET @sql = IF(@constraint_exists = 0, 
    'ALTER TABLE `wiki_articles` ADD CONSTRAINT `fk_redirect_target` FOREIGN KEY (`redirect_target_id`) REFERENCES `wiki_articles` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_redirect_target already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for last editor (if not exists)
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'wiki_articles' 
    AND CONSTRAINT_NAME = 'fk_last_edit_by'
);

SET @sql = IF(@constraint_exists = 0, 
    'ALTER TABLE `wiki_articles` ADD CONSTRAINT `fk_last_edit_by` FOREIGN KEY (`last_edit_by`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_last_edit_by already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert default namespaces
INSERT IGNORE INTO `wiki_namespaces` (`id`, `name`, `display_name`, `is_talk`, `parent_id`, `sort_order`) VALUES
(0, 'Main', 'Main', 0, NULL, 0),
(1, 'Talk', 'Talk', 1, 0, 1),
(2, 'User', 'User', 0, NULL, 2),
(3, 'User Talk', 'User talk', 1, 2, 3),
(4, 'Template', 'Template', 0, NULL, 4),
(5, 'Template Talk', 'Template talk', 1, 4, 5),
(6, 'File', 'File', 0, NULL, 6),
(7, 'File Talk', 'File talk', 1, 6, 7),
(8, 'Category', 'Category', 0, NULL, 8),
(9, 'Category Talk', 'Category talk', 1, 8, 9),
(10, 'Help', 'Help', 0, NULL, 10),
(11, 'Help Talk', 'Help talk', 1, 10, 11),
(12, 'Special', 'Special', 0, NULL, 12);

-- Insert default templates
INSERT IGNORE INTO `wiki_templates` (`name`, `slug`, `content`, `description`, `created_by`, `updated_by`) VALUES
('Stub', 'stub', '{{Stub|This article is a stub. You can help IslamWiki by expanding it.}}', 'Template for marking stub articles', 1, 1),
('Infobox', 'infobox', '{{Infobox\n| title = \n| image = \n| caption = \n| data1 = \n| label1 = \n| data2 = \n| label2 = \n}}', 'Generic infobox template', 1, 1),
('Citation Needed', 'citation-needed', '{{Citation needed|date={{CURRENTYEAR}}-{{CURRENTMONTH}}-{{CURRENTDAY}}}}', 'Template for marking content that needs citation', 1, 1),
('Cleanup', 'cleanup', '{{Cleanup|This article needs cleanup.}}', 'Template for marking articles that need cleanup', 1, 1),
('Featured Article', 'featured', '{{Featured|This is a featured article.}}', 'Template for marking featured articles', 1, 1);

-- Create uploads directory structure
-- Note: This will be handled by PHP code, not SQL

-- Update existing articles to use Main namespace (id = 0)
UPDATE `wiki_articles` SET `namespace_id` = 0 WHERE `namespace_id` IS NULL;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_articles_status_namespace` ON `wiki_articles` (`status`, `namespace_id`);
CREATE INDEX IF NOT EXISTS `idx_articles_featured_status` ON `wiki_articles` (`is_featured`, `status`);
CREATE INDEX IF NOT EXISTS `idx_articles_view_count_status` ON `wiki_articles` (`view_count`, `status`);

-- Create full-text search index for articles
ALTER TABLE `wiki_articles` ADD FULLTEXT(`title`, `content`, `excerpt`);

-- Create full-text search index for templates
ALTER TABLE `wiki_templates` ADD FULLTEXT(`name`, `content`, `description`);

-- Create full-text search index for categories
ALTER TABLE `wiki_categories` ADD FULLTEXT(`name`, `description`);

COMMIT;
