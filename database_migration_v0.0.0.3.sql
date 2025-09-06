-- IslamWiki v0.0.0.3 Database Migration
-- Enhanced Draft Management, Collaboration, and Scholar Verification

-- Add new roles for scholar verification
INSERT INTO `roles` (`name`, `display_name`, `description`, `is_system`) VALUES
('scholar', 'Scholar', 'Verified Islamic scholar with content verification authority', 1),
('reviewer', 'Content Reviewer', 'Can review and approve content for publication', 1);

-- Add new columns to wiki_articles table
ALTER TABLE `wiki_articles` 
ADD COLUMN `is_scholar_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_featured`,
ADD COLUMN `verified_by` bigint(20) unsigned NULL AFTER `is_scholar_verified`,
ADD COLUMN `verified_at` timestamp NULL AFTER `verified_by`,
ADD COLUMN `collaboration_mode` enum('private', 'shared', 'public') NOT NULL DEFAULT 'private' AFTER `verified_at`,
ADD COLUMN `draft_visibility` enum('author_only', 'editors', 'all_logged_in') NOT NULL DEFAULT 'author_only' AFTER `collaboration_mode`,
ADD COLUMN `last_edited_by` bigint(20) unsigned NULL AFTER `draft_visibility`,
ADD COLUMN `last_edited_at` timestamp NULL AFTER `last_edited_by`,
ADD KEY `idx_verified_by` (`verified_by`),
ADD KEY `idx_last_edited_by` (`last_edited_by`),
ADD KEY `idx_collaboration_mode` (`collaboration_mode`),
ADD KEY `idx_draft_visibility` (`draft_visibility`),
ADD FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
ADD FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Create article collaborations table for shared editing
CREATE TABLE `article_collaborations` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `role` enum('viewer', 'editor', 'reviewer') NOT NULL DEFAULT 'viewer',
    `invited_by` bigint(20) unsigned NULL,
    `invited_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `accepted_at` timestamp NULL,
    `permissions` json,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_article_user` (`article_id`, `user_id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_role` (`role`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create scholar verifications table
CREATE TABLE `scholar_verifications` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `scholar_id` bigint(20) unsigned NOT NULL,
    `status` enum('pending', 'approved', 'rejected', 'needs_revision') NOT NULL DEFAULT 'pending',
    `verification_notes` text,
    `scholar_credentials` text,
    `verified_sources` json,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_scholar_id` (`scholar_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`scholar_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create draft notifications table
CREATE TABLE `draft_notifications` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `notification_type` enum('draft_created', 'draft_updated', 'collaboration_invite', 'verification_request') NOT NULL,
    `message` text NOT NULL,
    `is_read` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_read` (`is_read`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
