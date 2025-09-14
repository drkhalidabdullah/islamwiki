-- Create missing tables for enhanced page information
-- This migration adds tables needed for comprehensive page statistics

-- 1. Wiki Edit History Table
CREATE TABLE IF NOT EXISTS `wiki_edit_history` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `editor_id` bigint(20) unsigned NOT NULL,
    `edit_type` enum('create', 'edit', 'minor_edit', 'revert') NOT NULL DEFAULT 'edit',
    `old_content` longtext,
    `new_content` longtext,
    `edit_summary` text,
    `bytes_changed` int(11) NOT NULL DEFAULT 0,
    `lines_added` int(11) NOT NULL DEFAULT 0,
    `lines_removed` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_editor_id` (`editor_id`),
    KEY `idx_edit_type` (`edit_type`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. User Watchlist Table (corrected name)
CREATE TABLE IF NOT EXISTS `user_watchlist` (
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

-- 3. Wiki Article Views Table (for detailed view tracking)
CREATE TABLE IF NOT EXISTS `wiki_article_views` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    `referrer` text,
    `view_date` date NOT NULL,
    `view_count` int(11) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_view_date` (`view_date`),
    KEY `idx_ip_address` (`ip_address`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Wiki Redirects Table
CREATE TABLE IF NOT EXISTS `wiki_redirects` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `from_slug` varchar(255) NOT NULL,
    `to_article_id` bigint(20) unsigned NOT NULL,
    `created_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_from_slug` (`from_slug`),
    KEY `idx_to_article_id` (`to_article_id`),
    KEY `idx_created_by` (`created_by`),
    FOREIGN KEY (`to_article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Add missing columns to wiki_articles if they don't exist
ALTER TABLE `wiki_articles` 
ADD COLUMN IF NOT EXISTS `namespace_id` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `protection_level` enum('none', 'autoconfirmed', 'sysop') NOT NULL DEFAULT 'none',
ADD COLUMN IF NOT EXISTS `is_redirect` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `redirect_target_id` bigint(20) unsigned NULL,
ADD COLUMN IF NOT EXISTS `last_edit_by` bigint(20) unsigned NULL,
ADD COLUMN IF NOT EXISTS `last_edit_at` timestamp NULL,
ADD COLUMN IF NOT EXISTS `edit_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `word_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `char_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `byte_count` int(11) NOT NULL DEFAULT 0;

-- Add indexes for new columns
ALTER TABLE `wiki_articles`
ADD KEY IF NOT EXISTS `idx_namespace_id` (`namespace_id`),
ADD KEY IF NOT EXISTS `idx_protection_level` (`protection_level`),
ADD KEY IF NOT EXISTS `idx_is_redirect` (`is_redirect`),
ADD KEY IF NOT EXISTS `idx_redirect_target_id` (`redirect_target_id`),
ADD KEY IF NOT EXISTS `idx_last_edit_by` (`last_edit_by`),
ADD KEY IF NOT EXISTS `idx_last_edit_at` (`last_edit_at`),
ADD KEY IF NOT EXISTS `idx_edit_count` (`edit_count`);

-- 6. Create initial edit history entries for existing articles
INSERT IGNORE INTO `wiki_edit_history` (`article_id`, `editor_id`, `edit_type`, `new_content`, `edit_summary`, `bytes_changed`, `created_at`)
SELECT 
    wa.id,
    wa.author_id,
    'create',
    wa.content,
    'Initial creation',
    LENGTH(wa.content),
    wa.created_at
FROM `wiki_articles` wa
WHERE wa.status = 'published';

-- 7. Update article statistics
UPDATE `wiki_articles` wa
SET 
    `word_count` = (
        SELECT LENGTH(TRIM(REGEXP_REPLACE(wa2.content, '<[^>]+>', ' '))) - LENGTH(REPLACE(TRIM(REGEXP_REPLACE(wa2.content, '<[^>]+>', ' ')), ' ', '')) + 1
        FROM `wiki_articles` wa2 
        WHERE wa2.id = wa.id
    ),
    `char_count` = LENGTH(TRIM(REGEXP_REPLACE(wa.content, '<[^>]+>', ''))),
    `byte_count` = LENGTH(wa.content),
    `edit_count` = 1,
    `last_edit_by` = wa.author_id,
    `last_edit_at` = wa.updated_at
WHERE wa.status = 'published';
