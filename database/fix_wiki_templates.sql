-- Fix wiki_templates table by adding missing columns
USE `islamwiki`;

-- Add missing columns to wiki_templates
ALTER TABLE `wiki_templates`
ADD COLUMN `namespace` VARCHAR(50) NOT NULL DEFAULT 'Template' AFTER `slug`,
ADD COLUMN `template_type` ENUM('infobox', 'citation', 'navbox', 'stub', 'disambiguation', 'main', 'other') NOT NULL DEFAULT 'other' AFTER `description`;

-- Add indexes for better performance
ALTER TABLE `wiki_templates`
ADD INDEX `idx_namespace` (`namespace`),
ADD INDEX `idx_template_type` (`template_type`);
