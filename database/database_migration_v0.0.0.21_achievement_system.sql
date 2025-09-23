-- Achievement System Database Migration
-- Version: 0.0.0.21
-- Description: Comprehensive award/badge/achievement/goals system with Islamic learning focus

-- Achievement Categories Table
CREATE TABLE IF NOT EXISTS `achievement_categories` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL UNIQUE,
    `description` text,
    `icon` varchar(255),
    `color` varchar(7) DEFAULT '#3498db',
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Achievement Types Table
CREATE TABLE IF NOT EXISTS `achievement_types` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL UNIQUE,
    `description` text,
    `icon` varchar(255),
    `color` varchar(7) DEFAULT '#e74c3c',
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Achievements Table
CREATE TABLE IF NOT EXISTS `achievements` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL UNIQUE,
    `description` text,
    `long_description` text,
    `category_id` bigint(20) unsigned NOT NULL,
    `type_id` bigint(20) unsigned NOT NULL,
    `icon` varchar(255),
    `color` varchar(7) DEFAULT '#f39c12',
    `rarity` enum('common','uncommon','rare','epic','legendary') DEFAULT 'common',
    `points` int(11) NOT NULL DEFAULT 0,
    `xp_reward` int(11) NOT NULL DEFAULT 0,
    `level_requirement` int(11) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_category` (`category_id`),
    KEY `idx_type` (`type_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_rarity` (`rarity`),
    FOREIGN KEY (`category_id`) REFERENCES `achievement_categories`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`type_id`) REFERENCES `achievement_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Achievement Requirements Table
CREATE TABLE IF NOT EXISTS `achievement_requirements` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `achievement_id` bigint(20) unsigned NOT NULL,
    `requirement_type` varchar(50) NOT NULL,
    `requirement_value` text NOT NULL,
    `requirement_operator` varchar(10) DEFAULT '>=',
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_achievement` (`achievement_id`),
    KEY `idx_type` (`requirement_type`),
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Achievements Table
CREATE TABLE IF NOT EXISTS `user_achievements` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `achievement_id` bigint(20) unsigned NOT NULL,
    `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
    `is_completed` tinyint(1) NOT NULL DEFAULT 0,
    `completed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_achievement` (`user_id`, `achievement_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_achievement` (`achievement_id`),
    KEY `idx_completed` (`is_completed`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Levels Table
CREATE TABLE IF NOT EXISTS `user_levels` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `level` int(11) NOT NULL DEFAULT 1,
    `total_xp` int(11) NOT NULL DEFAULT 0,
    `current_level_xp` int(11) NOT NULL DEFAULT 0,
    `xp_to_next_level` int(11) NOT NULL DEFAULT 100,
    `total_achievements` int(11) NOT NULL DEFAULT 0,
    `total_points` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_level` (`user_id`),
    KEY `idx_level` (`level`),
    KEY `idx_xp` (`total_xp`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Activity Log Table
CREATE TABLE IF NOT EXISTS `user_activity_log` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `activity_type` varchar(50) NOT NULL,
    `activity_data` json,
    `xp_earned` int(11) NOT NULL DEFAULT 0,
    `points_earned` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_activity` (`activity_type`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Achievement Notifications Table
CREATE TABLE IF NOT EXISTS `achievement_notifications` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `achievement_id` bigint(20) unsigned NOT NULL,
    `notification_type` enum('achievement_unlocked','level_up','milestone_reached') NOT NULL,
    `title` varchar(200) NOT NULL,
    `message` text NOT NULL,
    `is_read` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_achievement` (`achievement_id`),
    KEY `idx_read` (`is_read`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `achievement_categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
('Islamic Learning', 'islamic-learning', 'Achievements related to Islamic knowledge and learning', 'fas fa-mosque', '#2ecc71', 1),
('Community', 'community', 'Achievements for community participation and social activities', 'fas fa-users', '#3498db', 2),
('Content Creation', 'content-creation', 'Achievements for creating and contributing content', 'fas fa-edit', '#e74c3c', 3),
('Wiki Mastery', 'wiki-mastery', 'Achievements for wiki editing and maintenance', 'fas fa-book', '#f39c12', 4),
('Social Engagement', 'social-engagement', 'Achievements for social features and interactions', 'fas fa-heart', '#e91e63', 5),
('Special Events', 'special-events', 'Achievements for special occasions and events', 'fas fa-star', '#9b59b6', 6);

-- Insert default types
INSERT INTO `achievement_types` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
('Badge', 'badge', 'Visual badges earned for completing specific tasks', 'fas fa-medal', '#f39c12', 1),
('Achievement', 'achievement', 'Milestone achievements for reaching goals', 'fas fa-trophy', '#e74c3c', 2),
('Award', 'award', 'Special recognition awards for outstanding contributions', 'fas fa-award', '#9b59b6', 3),
('Goal', 'goal', 'Personal goals and objectives to work towards', 'fas fa-target', '#2ecc71', 4),
('Milestone', 'milestone', 'Significant milestones in user journey', 'fas fa-flag-checkered', '#3498db', 5);

-- Insert default achievements
INSERT INTO `achievements` (`name`, `slug`, `description`, `long_description`, `category_id`, `type_id`, `icon`, `color`, `rarity`, `points`, `xp_reward`, `level_requirement`) VALUES
-- Islamic Learning Achievements
('First Steps', 'first-steps', 'Complete your first Islamic learning activity', 'Welcome to your journey of Islamic learning! You\'ve taken the first step towards gaining knowledge.', 1, 1, 'fas fa-baby', '#2ecc71', 'common', 10, 50, 0),
('Quran Reader', 'quran-reader', 'Read 10 Quranic verses', 'You\'ve begun your journey with the Quran, the most important book in Islam.', 1, 2, 'fas fa-book-open', '#27ae60', 'uncommon', 25, 100, 1),
('Hadith Scholar', 'hadith-scholar', 'Study 50 hadiths', 'You\'ve shown dedication to learning the sayings of Prophet Muhammad (PBUH).', 1, 2, 'fas fa-quote-left', '#8e44ad', 'rare', 50, 200, 3),
('Islamic History Buff', 'islamic-history-buff', 'Read 20 articles about Islamic history', 'Your knowledge of Islamic history is growing, helping you understand the rich heritage of Islam.', 1, 2, 'fas fa-landmark', '#e67e22', 'uncommon', 30, 150, 2),
('Tajweed Master', 'tajweed-master', 'Complete 5 Tajweed lessons', 'You\'ve learned the proper way to recite the Quran with correct pronunciation.', 1, 3, 'fas fa-microphone', '#c0392b', 'rare', 75, 300, 4),
('Fiqh Student', 'fiqh-student', 'Study 30 Fiqh articles', 'You\'ve gained knowledge in Islamic jurisprudence, understanding the practical aspects of Islam.', 1, 2, 'fas fa-balance-scale', '#16a085', 'uncommon', 40, 180, 3),
('Sunnah Follower', 'sunnah-follower', 'Practice 20 Sunnah actions', 'You\'re following the beautiful example of Prophet Muhammad (PBUH) in your daily life.', 1, 3, 'fas fa-hands-praying', '#27ae60', 'rare', 60, 250, 3),
('Islamic Scholar', 'islamic-scholar', 'Complete 100 Islamic learning activities', 'You\'ve become a dedicated student of Islamic knowledge, showing great commitment to learning.', 1, 4, 'fas fa-graduation-cap', '#8e44ad', 'epic', 150, 500, 8),
('Quran Hafiz', 'quran-hafiz', 'Memorize 10 Surahs', 'You\'ve memorized portions of the Quran, a great achievement in Islamic learning.', 1, 4, 'fas fa-brain', '#e74c3c', 'legendary', 300, 1000, 10),

-- Community Achievements
('First Friend', 'first-friend', 'Make your first friend on the platform', 'Welcome to the community! You\'ve made your first connection.', 2, 1, 'fas fa-user-plus', '#3498db', 'common', 15, 75, 0),
('Social Butterfly', 'social-butterfly', 'Make 10 friends', 'You\'re becoming a social member of our community!', 2, 2, 'fas fa-users', '#2980b9', 'uncommon', 35, 150, 2),
('Community Helper', 'community-helper', 'Help 5 other users', 'You\'re making a positive impact in our community by helping others.', 2, 3, 'fas fa-hands-helping', '#16a085', 'uncommon', 45, 200, 3),
('Discussion Leader', 'discussion-leader', 'Start 10 meaningful discussions', 'You\'re leading conversations and fostering community engagement.', 2, 3, 'fas fa-comments', '#8e44ad', 'rare', 70, 300, 5),
('Community Champion', 'community-champion', 'Be active in the community for 30 days', 'Your consistent participation has made you a valued community member.', 2, 4, 'fas fa-crown', '#f39c12', 'epic', 120, 400, 7),

-- Content Creation Achievements
('First Article', 'first-article', 'Create your first article', 'You\'ve contributed your first piece of content to our knowledge base.', 3, 1, 'fas fa-file-alt', '#e74c3c', 'common', 20, 100, 0),
('Content Creator', 'content-creator', 'Create 10 articles', 'You\'re becoming a valuable content creator in our community.', 3, 2, 'fas fa-edit', '#c0392b', 'uncommon', 50, 250, 2),
('Wiki Editor', 'wiki-editor', 'Edit 50 wiki pages', 'You\'re actively contributing to our wiki, helping build our knowledge base.', 3, 2, 'fas fa-wikipedia-w', '#f39c12', 'uncommon', 60, 300, 3),
('Quality Contributor', 'quality-contributor', 'Receive 20 positive ratings on your content', 'Your content is highly valued by the community!', 3, 3, 'fas fa-star', '#e67e22', 'rare', 80, 350, 4),
('Content Master', 'content-master', 'Create 100 pieces of content', 'You\'re a master content creator, consistently contributing valuable content.', 3, 4, 'fas fa-trophy', '#8e44ad', 'epic', 200, 600, 8),

-- Wiki Mastery Achievements
('Wiki Explorer', 'wiki-explorer', 'Visit 50 wiki pages', 'You\'re exploring our rich knowledge base!', 4, 1, 'fas fa-search', '#3498db', 'common', 25, 125, 1),
('Wiki Contributor', 'wiki-contributor', 'Make 25 wiki contributions', 'You\'re actively contributing to our wiki knowledge base.', 4, 2, 'fas fa-plus-circle', '#2ecc71', 'uncommon', 40, 200, 2),
('Wiki Guardian', 'wiki-guardian', 'Moderate 10 wiki pages', 'You\'re helping maintain the quality of our wiki content.', 4, 3, 'fas fa-shield-alt', '#e74c3c', 'rare', 70, 300, 4),
('Wiki Master', 'wiki-master', 'Become an expert in 5 wiki categories', 'You\'ve mastered multiple areas of our wiki knowledge base.', 4, 4, 'fas fa-graduation-cap', '#9b59b6', 'epic', 150, 500, 7),

-- Social Engagement Achievements
('First Like', 'first-like', 'Give your first like', 'You\'ve started engaging with content in our community.', 5, 1, 'fas fa-heart', '#e91e63', 'common', 5, 25, 0),
('Social Engager', 'social-engager', 'Give 100 likes', 'You\'re actively engaging with content and showing appreciation.', 5, 2, 'fas fa-heart', '#c0392b', 'uncommon', 30, 150, 2),
('Commentator', 'commentator', 'Write 50 meaningful comments', 'You\'re contributing to discussions and sharing your thoughts.', 5, 2, 'fas fa-comment', '#3498db', 'uncommon', 40, 200, 2),
('Social Influencer', 'social-influencer', 'Get 50 likes on your content', 'Your content is resonating with the community!', 5, 3, 'fas fa-fire', '#e67e22', 'rare', 80, 350, 5),
('Community Star', 'community-star', 'Be mentioned 20 times in comments', 'You\'re a recognized and valued member of our community.', 5, 4, 'fas fa-star', '#f39c12', 'epic', 120, 400, 6);

-- Insert default requirements for some achievements
INSERT INTO `achievement_requirements` (`achievement_id`, `requirement_type`, `requirement_value`, `requirement_operator`) VALUES
(1, 'activity_count', '{"activity_type": "islamic_learning", "count": 1}', '>='),
(2, 'activity_count', '{"activity_type": "quran_reading", "count": 10}', '>='),
(3, 'activity_count', '{"activity_type": "hadith_study", "count": 50}', '>='),
(4, 'activity_count', '{"activity_type": "islamic_history", "count": 20}', '>='),
(5, 'activity_count', '{"activity_type": "tajweed_lesson", "count": 5}', '>='),
(6, 'activity_count', '{"activity_type": "fiqh_study", "count": 30}', '>='),
(7, 'activity_count', '{"activity_type": "sunnah_practice", "count": 20}', '>='),
(8, 'activity_count', '{"activity_type": "islamic_learning", "count": 100}', '>='),
(9, 'activity_count', '{"activity_type": "surah_memorization", "count": 10}', '>='),
(10, 'activity_count', '{"activity_type": "friend_add", "count": 1}', '>='),
(11, 'activity_count', '{"activity_type": "friend_add", "count": 10}', '>='),
(12, 'activity_count', '{"activity_type": "help_other", "count": 5}', '>='),
(13, 'activity_count', '{"activity_type": "discussion_start", "count": 10}', '>='),
(14, 'activity_count', '{"activity_type": "daily_active", "count": 30}', '>='),
(15, 'activity_count', '{"activity_type": "article_create", "count": 1}', '>='),
(16, 'activity_count', '{"activity_type": "article_create", "count": 10}', '>='),
(17, 'activity_count', '{"activity_type": "wiki_edit", "count": 50}', '>='),
(18, 'activity_count', '{"activity_type": "content_rating", "count": 20}', '>='),
(19, 'activity_count', '{"activity_type": "content_create", "count": 100}', '>='),
(20, 'activity_count', '{"activity_type": "wiki_visit", "count": 50}', '>='),
(21, 'activity_count', '{"activity_type": "wiki_contribute", "count": 25}', '>='),
(22, 'activity_count', '{"activity_type": "wiki_moderate", "count": 10}', '>='),
(23, 'activity_count', '{"activity_type": "wiki_expert", "count": 5}', '>='),
(24, 'activity_count', '{"activity_type": "like_given", "count": 1}', '>='),
(25, 'activity_count', '{"activity_type": "like_given", "count": 100}', '>='),
(26, 'activity_count', '{"activity_type": "comment_write", "count": 50}', '>='),
(27, 'activity_count', '{"activity_type": "like_received", "count": 50}', '>='),
(28, 'activity_count', '{"activity_type": "mention_received", "count": 20}', '>=');

-- Create indexes for better performance (after tables are created)
-- These will be created automatically with the table definitions above
