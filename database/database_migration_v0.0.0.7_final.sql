-- IslamWiki Database Migration v0.0.0.7 (Final)
-- Comprehensive Search System Optimization
-- Author: Khalid Abdullah
-- Date: 2025-01-27

USE `islamwiki`;

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

-- Add some sample data for testing
INSERT IGNORE INTO `search_analytics` (`search_query`, `content_type`, `results_count`, `created_at`) VALUES
('islam', 'all', 15, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('quran', 'articles', 8, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('prayer', 'articles', 12, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('ramadan', 'all', 20, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('hajj', 'articles', 6, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Update version info (using correct column names)
INSERT INTO `system_settings` (`key`, `value`, `type`, `description`, `is_public`, `updated_at`) 
VALUES ('search_version', '0.0.0.7', 'string', 'Comprehensive Search System Version', 1, NOW())
ON DUPLICATE KEY UPDATE 
`value` = '0.0.0.7',
`description` = 'Comprehensive Search System Version',
`updated_at` = NOW();
