-- Database Migration v0.0.0.15 - User Settings Enhancement
-- Add privacy_settings and notification_settings columns to user_profiles table

USE `islamwiki`;

-- Add new columns to user_profiles table
ALTER TABLE `user_profiles` 
ADD COLUMN `privacy_settings` json AFTER `preferences`,
ADD COLUMN `notification_settings` json AFTER `privacy_settings`;

-- Note: JSON columns don't support traditional indexes in MariaDB
-- We'll rely on the existing user_id index for performance

-- Insert default privacy settings for existing users
UPDATE `user_profiles` 
SET `privacy_settings` = JSON_OBJECT(
    'profile_visibility', 'public',
    'show_email', 0,
    'show_activity', 1,
    'allow_following', 1,
    'allow_messages', 'everyone',
    'search_visibility', 1
)
WHERE `privacy_settings` IS NULL;

-- Insert default notification settings for existing users
UPDATE `user_profiles` 
SET `notification_settings` = JSON_OBJECT(
    'email_notifications', 1,
    'push_notifications', 1,
    'post_notifications', 1,
    'comment_notifications', 1,
    'follow_notifications', 1,
    'message_notifications', 1,
    'article_notifications', 1,
    'weekly_digest', 0,
    'marketing_emails', 0
)
WHERE `notification_settings` IS NULL;
