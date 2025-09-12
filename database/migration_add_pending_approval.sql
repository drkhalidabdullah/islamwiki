-- Migration to add pending_approval status for article approval system
-- This allows all users to create articles but requires approval from moderators/editors/admins/scholars

-- Add pending_approval status to wiki_articles table
ALTER TABLE `wiki_articles` 
MODIFY COLUMN `status` enum('draft', 'pending_approval', 'published', 'archived') NOT NULL DEFAULT 'draft';

-- Add approval tracking columns
ALTER TABLE `wiki_articles` 
ADD COLUMN `approved_by` bigint(20) unsigned NULL AFTER `status`,
ADD COLUMN `approved_at` timestamp NULL AFTER `approved_by`,
ADD COLUMN `rejection_reason` text NULL AFTER `approved_at`,
ADD KEY `idx_approved_by` (`approved_by`),
ADD KEY `idx_approved_at` (`approved_at`),
ADD FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Create article approval queue table for moderators
CREATE TABLE IF NOT EXISTS `article_approval_queue` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `submitted_by` bigint(20) unsigned NOT NULL,
    `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `priority` enum('low', 'normal', 'high') NOT NULL DEFAULT 'normal',
    `notes` text NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_article_queue` (`article_id`),
    KEY `idx_submitted_by` (`submitted_by`),
    KEY `idx_submitted_at` (`submitted_at`),
    KEY `idx_priority` (`priority`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
