-- IslamWiki Database Migration v0.0.0.7 (Fixed)
-- Comprehensive Search System Optimization
-- Author: Khalid Abdullah
-- Date: 2025-01-27

USE `islamwiki`;

-- Add full-text search indexes for better search performance
ALTER TABLE `wiki_articles` 
ADD FULLTEXT INDEX `ft_search` (`title`, `content`, `excerpt`);

-- Add search optimization indexes
ALTER TABLE `wiki_articles` 
ADD INDEX `idx_search_status_published` (`status`, `published_at`),
ADD INDEX `idx_search_category_status` (`category_id`, `status`),
ADD INDEX `idx_search_author_status` (`author_id`, `status`);

-- Add user search indexes
ALTER TABLE `users` 
ADD INDEX `idx_search_username` (`username`),
ADD INDEX `idx_search_display_name` (`display_name`),
ADD INDEX `idx_search_active` (`is_active`);

-- Add user profile search indexes (using existing fields)
ALTER TABLE `user_profiles` 
ADD INDEX `idx_search_interests` (`interests`(255)),
ADD INDEX `idx_search_profession` (`profession`),
ADD INDEX `idx_search_expertise` (`expertise_areas`(255));

-- Add message search indexes (if messages table exists)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
                     WHERE table_schema = 'islamwiki' AND table_name = 'messages');

SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE `messages` ADD INDEX `idx_search_message` (`message`(255)), ADD INDEX `idx_search_sender_recipient` (`sender_id`, `recipient_id`)',
    'SELECT "Messages table does not exist, skipping message indexes" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add content categories search indexes
ALTER TABLE `content_categories` 
ADD INDEX `idx_search_name` (`name`),
ADD INDEX `idx_search_active_sort` (`is_active`, `sort_order`);

-- Create search history table for logged-in users
CREATE TABLE IF NOT EXISTS `search_history` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `search_query` varchar(255) NOT NULL,
    `content_type` varchar(50) DEFAULT 'all',
    `results_count` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_search_query` (`search_query`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create search analytics table for admin insights
CREATE TABLE IF NOT EXISTS `search_analytics` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `search_query` varchar(255) NOT NULL,
    `content_type` varchar(50) DEFAULT 'all',
    `results_count` int(11) DEFAULT 0,
    `user_id` bigint(20) unsigned NULL,
    `ip_address` varchar(45),
    `user_agent` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_search_query` (`search_query`),
    KEY `idx_content_type` (`content_type`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for testing (optional)
INSERT IGNORE INTO `search_analytics` (`search_query`, `content_type`, `results_count`, `created_at`) VALUES
('islam', 'all', 15, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('quran', 'articles', 8, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('prayer', 'articles', 12, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('ramadan', 'all', 20, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('hajj', 'articles', 6, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Update version info
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`, `updated_at`) 
VALUES ('search_version', '0.0.0.7', 'Comprehensive Search System Version', NOW())
ON DUPLICATE KEY UPDATE 
`setting_value` = '0.0.0.7',
`description` = 'Comprehensive Search System Version',
`updated_at` = NOW();
