-- IslamWiki Database Migration v0.0.0.4
-- User Profile System Enhancement
-- Author: Khalid Abdullah
-- Date: 2025-01-27

USE `islamwiki`;

-- User social features
CREATE TABLE `user_follows` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `follower_id` bigint(20) unsigned NOT NULL,
    `following_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_follow` (`follower_id`, `following_id`),
    KEY `idx_follower_id` (`follower_id`),
    KEY `idx_following_id` (`following_id`),
    FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User posts/status updates
CREATE TABLE `user_posts` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `content` text NOT NULL,
    `post_type` enum('text', 'image', 'link', 'article_share') NOT NULL DEFAULT 'text',
    `media_url` varchar(500),
    `link_url` varchar(500),
    `link_title` varchar(255),
    `link_description` text,
    `link_image` varchar(500),
    `article_id` bigint(20) unsigned NULL,
    `is_public` tinyint(1) NOT NULL DEFAULT 1,
    `likes_count` int(11) NOT NULL DEFAULT 0,
    `comments_count` int(11) NOT NULL DEFAULT 0,
    `shares_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_post_type` (`post_type`),
    KEY `idx_is_public` (`is_public`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post interactions (likes, shares, bookmarks)
CREATE TABLE `post_interactions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `post_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `interaction_type` enum('like', 'share', 'bookmark') NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_interaction` (`post_id`, `user_id`, `interaction_type`),
    KEY `idx_post_id` (`post_id`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`post_id`) REFERENCES `user_posts` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User photos/gallery
CREATE TABLE `user_photos` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `filename` varchar(255) NOT NULL,
    `original_filename` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_size` int(11) NOT NULL,
    `mime_type` varchar(100) NOT NULL,
    `width` int(11),
    `height` int(11),
    `caption` text,
    `is_profile_photo` tinyint(1) NOT NULL DEFAULT 0,
    `is_cover_photo` tinyint(1) NOT NULL DEFAULT 0,
    `is_public` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_profile_photo` (`is_profile_photo`),
    KEY `idx_is_cover_photo` (`is_cover_photo`),
    KEY `idx_is_public` (`is_public`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User events
CREATE TABLE `user_events` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text,
    `event_type` enum('personal', 'academic', 'professional', 'religious') NOT NULL DEFAULT 'personal',
    `start_date` datetime NOT NULL,
    `end_date` datetime,
    `location` varchar(255),
    `is_public` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_start_date` (`start_date`),
    KEY `idx_is_public` (`is_public`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User achievements/badges
CREATE TABLE `user_achievements` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `achievement_type` varchar(100) NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text,
    `icon` varchar(255),
    `earned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_achievement_type` (`achievement_type`),
    KEY `idx_earned_at` (`earned_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post comments
CREATE TABLE `post_comments` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `post_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `parent_id` bigint(20) unsigned NULL,
    `content` text NOT NULL,
    `likes_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_post_id` (`post_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`post_id`) REFERENCES `user_posts` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `post_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update user_profiles table to add more fields
ALTER TABLE `user_profiles` 
ADD COLUMN `cover_photo` varchar(255) AFTER `website`,
ADD COLUMN `interests` text AFTER `social_links`,
ADD COLUMN `education` text AFTER `interests`,
ADD COLUMN `profession` varchar(255) AFTER `education`,
ADD COLUMN `expertise_areas` text AFTER `profession`,
ADD COLUMN `privacy_level` enum('public', 'community', 'followers', 'private') NOT NULL DEFAULT 'community' AFTER `expertise_areas`;

-- Add indexes for better performance
CREATE INDEX `idx_user_posts_user_created` ON `user_posts` (`user_id`, `created_at` DESC);
CREATE INDEX `idx_user_follows_follower_created` ON `user_follows` (`follower_id`, `created_at` DESC);
CREATE INDEX `idx_user_follows_following_created` ON `user_follows` (`following_id`, `created_at` DESC);
CREATE INDEX `idx_user_photos_user_created` ON `user_photos` (`user_id`, `created_at` DESC);
CREATE INDEX `idx_user_events_user_start` ON `user_events` (`user_id`, `start_date` DESC);
