-- IslamWiki Database Migration v0.0.0.8
-- Comprehensive Search Engine with Groups and Community Features
-- Author: Khalid Abdullah
-- Date: 2025-01-27

USE `islamwiki`;

-- Groups table for community groups
CREATE TABLE `groups` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `cover_image` varchar(500),
    `group_type` enum('public', 'private', 'restricted') NOT NULL DEFAULT 'public',
    `category` varchar(100),
    `tags` text,
    `created_by` bigint(20) unsigned NOT NULL,
    `members_count` int(11) NOT NULL DEFAULT 0,
    `posts_count` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_group_type` (`group_type`),
    KEY `idx_category` (`category`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group members table
CREATE TABLE `group_members` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `group_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `role` enum('admin', 'moderator', 'member') NOT NULL DEFAULT 'member',
    `status` enum('active', 'pending', 'banned') NOT NULL DEFAULT 'active',
    `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_activity_at` timestamp NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_group_member` (`group_id`, `user_id`),
    KEY `idx_group_id` (`group_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_role` (`role`),
    KEY `idx_status` (`status`),
    KEY `idx_joined_at` (`joined_at`),
    FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group posts table
CREATE TABLE `group_posts` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `group_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `content` text NOT NULL,
    `post_type` enum('text', 'image', 'link', 'poll', 'event') NOT NULL DEFAULT 'text',
    `media_url` varchar(500),
    `link_url` varchar(500),
    `link_title` varchar(255),
    `link_description` text,
    `link_image` varchar(500),
    `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
    `is_approved` tinyint(1) NOT NULL DEFAULT 1,
    `likes_count` int(11) NOT NULL DEFAULT 0,
    `comments_count` int(11) NOT NULL DEFAULT 0,
    `shares_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_group_id` (`group_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_post_type` (`post_type`),
    KEY `idx_is_pinned` (`is_pinned`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Community events table
CREATE TABLE `community_events` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `event_type` enum('online', 'offline', 'hybrid') NOT NULL DEFAULT 'offline',
    `location` varchar(500),
    `online_link` varchar(500),
    `start_date` datetime NOT NULL,
    `end_date` datetime,
    `timezone` varchar(50) DEFAULT 'UTC',
    `max_attendees` int(11),
    `current_attendees` int(11) NOT NULL DEFAULT 0,
    `cover_image` varchar(500),
    `tags` text,
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `is_public` tinyint(1) NOT NULL DEFAULT 1,
    `created_by` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_start_date` (`start_date`),
    KEY `idx_is_featured` (`is_featured`),
    KEY `idx_is_public` (`is_public`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event attendees table
CREATE TABLE `event_attendees` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `event_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `status` enum('attending', 'maybe', 'not_attending') NOT NULL DEFAULT 'attending',
    `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_event_attendee` (`event_id`, `user_id`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_registered_at` (`registered_at`),
    FOREIGN KEY (`event_id`) REFERENCES `community_events` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search analytics table
CREATE TABLE `search_analytics` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NULL,
    `search_query` varchar(500) NOT NULL,
    `content_type` varchar(50),
    `results_count` int(11) NOT NULL DEFAULT 0,
    `clicked_result_id` bigint(20) unsigned NULL,
    `clicked_result_type` varchar(50),
    `session_id` varchar(100),
    `ip_address` varchar(45),
    `user_agent` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_search_query` (`search_query`),
    KEY `idx_content_type` (`content_type`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search suggestions table
CREATE TABLE `search_suggestions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `suggestion` varchar(255) NOT NULL,
    `suggestion_type` enum('popular', 'trending', 'recommended') NOT NULL DEFAULT 'popular',
    `content_type` varchar(50),
    `click_count` int(11) NOT NULL DEFAULT 0,
    `search_count` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_suggestion` (`suggestion`, `suggestion_type`),
    KEY `idx_suggestion_type` (`suggestion_type`),
    KEY `idx_content_type` (`content_type`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_click_count` (`click_count`),
    KEY `idx_search_count` (`search_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add full-text search indexes for better search performance
ALTER TABLE `wiki_articles` ADD FULLTEXT(`title`, `content`, `excerpt`);
ALTER TABLE `user_posts` ADD FULLTEXT(`content`);
ALTER TABLE `groups` ADD FULLTEXT(`name`, `description`);
ALTER TABLE `group_posts` ADD FULLTEXT(`content`);
ALTER TABLE `community_events` ADD FULLTEXT(`title`, `description`);

-- Add additional indexes for search optimization
CREATE INDEX `idx_wiki_articles_search` ON `wiki_articles` (`status`, `published_at` DESC);
CREATE INDEX `idx_user_posts_search` ON `user_posts` (`is_public`, `created_at` DESC);
CREATE INDEX `idx_groups_search` ON `groups` (`is_active`, `created_at` DESC);
CREATE INDEX `idx_group_posts_search` ON `group_posts` (`is_approved`, `created_at` DESC);
CREATE INDEX `idx_community_events_search` ON `community_events` (`is_public`, `start_date` DESC);

-- Insert some default search suggestions
INSERT INTO `search_suggestions` (`suggestion`, `suggestion_type`, `content_type`, `search_count`) VALUES
('Islam', 'popular', 'articles', 100),
('Quran', 'popular', 'articles', 95),
('Hadith', 'popular', 'articles', 90),
('Prayer', 'popular', 'articles', 85),
('Ramadan', 'popular', 'articles', 80),
('Community', 'trending', 'groups', 50),
('Events', 'trending', 'events', 45),
('Discussion', 'trending', 'posts', 40);
