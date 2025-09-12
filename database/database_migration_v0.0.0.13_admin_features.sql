-- Migration: Admin Feature Controls
-- Version: 0.0.0.13
-- Description: Ensure all admin feature toggles are set to enabled by default

-- Insert default feature settings if they don't exist
INSERT IGNORE INTO `system_settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('allow_registration', '1', 'boolean', 'Allow new user registration', 1),
('enable_comments', '1', 'boolean', 'Enable commenting system', 1),
('enable_wiki', '1', 'boolean', 'Enable wiki functionality', 1),
('enable_social', '1', 'boolean', 'Enable social networking features', 1),
('enable_analytics', '1', 'boolean', 'Enable analytics tracking', 1),
('enable_notifications', '1', 'boolean', 'Enable notification system', 1);

-- Update existing settings to ensure they are enabled by default
UPDATE `system_settings` SET `value` = '1' WHERE `key` = 'allow_registration' AND `value` = '0';
UPDATE `system_settings` SET `value` = '1' WHERE `key` = 'enable_comments' AND `value` = '0';
UPDATE `system_settings` SET `value` = '1' WHERE `key` = 'enable_wiki' AND `value` = '0';
UPDATE `system_settings` SET `value` = '1' WHERE `key` = 'enable_social' AND `value` = '0';
UPDATE `system_settings` SET `value` = '1' WHERE `key` = 'enable_analytics' AND `value` = '0';
UPDATE `system_settings` SET `value` = '1' WHERE `key` = 'enable_notifications' AND `value` = '0';
