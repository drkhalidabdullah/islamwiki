-- Migration: Extensions System
-- Version: 0.0.0.14
-- Description: Add extension settings to system_settings table

-- Insert extension settings
INSERT IGNORE INTO `system_settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('extension_newsbar_enabled', '0', 'boolean', 'Enable News Bar extension', 0),
('extension_newsbar_position', 'top', 'string', 'News Bar position (top/bottom)', 0),
('extension_newsbar_animation_speed', '20', 'integer', 'News Bar animation speed in seconds', 0),
('extension_newsbar_show_controls', '1', 'boolean', 'Show News Bar controls', 0),
('extension_newsbar_news_items', '[]', 'json', 'News Bar items', 0);

