-- Migration: Skins System
-- Version: 0.0.0.15
-- Description: Add skins system for theme management

-- Create skins table
CREATE TABLE IF NOT EXISTS `skins` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `version` VARCHAR(20) DEFAULT '1.0.0',
    `author` VARCHAR(100),
    `is_active` BOOLEAN DEFAULT FALSE,
    `is_default` BOOLEAN DEFAULT FALSE,
    `preview_image` VARCHAR(255),
    `css_file` VARCHAR(255),
    `js_file` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create user skin preferences table
CREATE TABLE IF NOT EXISTS `user_skin_preferences` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `skin_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`skin_id`) REFERENCES `skins`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_skin_unique` (`user_id`, `skin_id`)
);

-- Insert default Bismillah skin
INSERT INTO `skins` (`name`, `display_name`, `description`, `version`, `author`, `is_active`, `is_default`, `css_file`, `js_file`) VALUES
('bismillah', 'Bismillah', 'The default Islamic theme with clean design and traditional colors', '1.0.0', 'IslamWiki Team', TRUE, TRUE, 'bismillah.css', 'bismillah.js');

-- Add skin settings to system_settings
INSERT IGNORE INTO `system_settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('default_skin', 'bismillah', 'string', 'Default skin for the site', 1),
('allow_skin_selection', '1', 'boolean', 'Allow users to select their own skin', 1),
('admin_skin', 'bismillah', 'string', 'Skin for admin panel', 0);
