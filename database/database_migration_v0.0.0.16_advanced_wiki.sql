-- Database Migration: Advanced Wiki Features
-- Version: 0.0.0.16
-- Description: Enhanced wiki system with advanced templates, categories, and security

-- 1. Update wiki_templates table with enhanced features
ALTER TABLE wiki_templates 
ADD COLUMN IF NOT EXISTS namespace VARCHAR(50) DEFAULT 'Template' AFTER slug,
ADD COLUMN IF NOT EXISTS template_type ENUM('infobox', 'citation', 'navbox', 'stub', 'disambiguation', 'main', 'other') DEFAULT 'other' AFTER namespace,
ADD COLUMN IF NOT EXISTS parameters JSON AFTER description,
ADD COLUMN IF NOT EXISTS documentation TEXT AFTER parameters,
ADD COLUMN IF NOT EXISTS is_system_template BOOLEAN DEFAULT FALSE AFTER documentation,
ADD COLUMN IF NOT EXISTS version VARCHAR(20) DEFAULT '1.0' AFTER is_system_template,
ADD COLUMN IF NOT EXISTS usage_count INT UNSIGNED DEFAULT 0 AFTER version,
ADD COLUMN IF NOT EXISTS last_used_at TIMESTAMP NULL AFTER usage_count;

-- 2. Create wiki_categories table
CREATE TABLE IF NOT EXISTS `wiki_categories` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `parent_id` bigint(20) unsigned NULL,
    `article_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`),
    KEY `idx_slug` (`slug`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_article_count` (`article_count`),
    FOREIGN KEY (`parent_id`) REFERENCES `wiki_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create wiki_article_categories table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS `wiki_article_categories` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `category_id` bigint(20) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_article_category` (`article_id`, `category_id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_category_id` (`category_id`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `wiki_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create wiki_template_usage table
CREATE TABLE IF NOT EXISTS `wiki_template_usage` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `template_id` bigint(20) unsigned NOT NULL,
    `article_id` bigint(20) unsigned NOT NULL,
    `usage_count` int(11) NOT NULL DEFAULT 1,
    `first_used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_template_article` (`template_id`, `article_id`),
    KEY `idx_template_id` (`template_id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_usage_count` (`usage_count`),
    FOREIGN KEY (`template_id`) REFERENCES `wiki_templates` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create wiki_references table
CREATE TABLE IF NOT EXISTS `wiki_references` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `article_id` bigint(20) unsigned NOT NULL,
    `reference_text` text NOT NULL,
    `reference_url` varchar(500) NULL,
    `reference_type` ENUM('book', 'journal', 'website', 'other') DEFAULT 'other',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article_id` (`article_id`),
    KEY `idx_reference_type` (`reference_type`),
    FOREIGN KEY (`article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create wiki_namespaces table
CREATE TABLE IF NOT EXISTS `wiki_namespaces` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `display_name` varchar(100) NOT NULL,
    `description` text,
    `is_main` boolean DEFAULT FALSE,
    `is_talk` boolean DEFAULT FALSE,
    `sort_order` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_name` (`name`),
    KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Insert default namespaces
INSERT INTO `wiki_namespaces` (`name`, `display_name`, `description`, `is_main`, `is_talk`, `sort_order`) VALUES
('Main', 'Main', 'Main article namespace', TRUE, FALSE, 0),
('Template', 'Template', 'Template namespace for reusable components', FALSE, FALSE, 10),
('Category', 'Category', 'Category namespace for organizing articles', FALSE, FALSE, 20),
('Help', 'Help', 'Help namespace for documentation', FALSE, FALSE, 30),
('User', 'User', 'User namespace for user pages', FALSE, FALSE, 40),
('Talk', 'Talk', 'Talk namespace for article discussions', FALSE, TRUE, 50),
('Template_talk', 'Template talk', 'Template talk namespace', FALSE, TRUE, 60),
('Category_talk', 'Category talk', 'Category talk namespace', FALSE, TRUE, 70),
('Help_talk', 'Help talk', 'Help talk namespace', FALSE, TRUE, 80),
('User_talk', 'User talk', 'User talk namespace', FALSE, TRUE, 90);

-- 8. Add namespace_id to wiki_articles table
ALTER TABLE wiki_articles 
ADD COLUMN IF NOT EXISTS namespace_id int(11) DEFAULT 1 AFTER id,
ADD COLUMN IF NOT EXISTS is_redirect BOOLEAN DEFAULT FALSE AFTER namespace_id,
ADD COLUMN IF NOT EXISTS redirect_target_id bigint(20) unsigned NULL AFTER is_redirect,
ADD COLUMN IF NOT EXISTS content_model ENUM('wikitext', 'markdown', 'html') DEFAULT 'wikitext' AFTER redirect_target_id,
ADD COLUMN IF NOT EXISTS parser_version VARCHAR(20) DEFAULT '1.0' AFTER content_model;

-- 9. Add foreign key for namespace
ALTER TABLE wiki_articles 
ADD CONSTRAINT `fk_wiki_articles_namespace` 
FOREIGN KEY (`namespace_id`) REFERENCES `wiki_namespaces` (`id`) ON DELETE RESTRICT;

-- 10. Add foreign key for redirect target
ALTER TABLE wiki_articles 
ADD CONSTRAINT `fk_wiki_articles_redirect_target` 
FOREIGN KEY (`redirect_target_id`) REFERENCES `wiki_articles` (`id`) ON DELETE SET NULL;

-- 11. Create wiki_parser_settings table
CREATE TABLE IF NOT EXISTS `wiki_parser_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_name` varchar(100) NOT NULL UNIQUE,
    `setting_value` text,
    `setting_type` ENUM('string', 'boolean', 'integer', 'json') DEFAULT 'string',
    `description` text,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Insert default parser settings
INSERT INTO `wiki_parser_settings` (`setting_name`, `setting_value`, `setting_type`, `description`) VALUES
('enable_wiki_syntax', 'true', 'boolean', 'Enable MediaWiki-style syntax'),
('enable_tables', 'true', 'boolean', 'Enable table parsing'),
('enable_references', 'true', 'boolean', 'Enable reference parsing'),
('enable_categories', 'true', 'boolean', 'Enable category parsing'),
('enable_magic_words', 'true', 'boolean', 'Enable magic words'),
('enable_templates', 'true', 'boolean', 'Enable template parsing'),
('html_sanitization', 'true', 'boolean', 'Enable HTML sanitization'),
('allowed_html_tags', '["p", "br", "strong", "em", "u", "s", "code", "pre", "blockquote", "h1", "h2", "h3", "h4", "h5", "h6", "ul", "ol", "li", "dl", "dt", "dd", "a", "img", "table", "thead", "tbody", "tfoot", "tr", "th", "td", "div", "span", "sup", "sub", "small", "mark", "del", "ins", "figure", "figcaption", "cite", "q", "abbr", "time", "address"]', 'json', 'Allowed HTML tags'),
('max_template_recursion', '10', 'integer', 'Maximum template recursion depth'),
('enable_external_links', 'true', 'boolean', 'Allow external links'),
('enable_file_uploads', 'true', 'boolean', 'Allow file uploads');

-- 13. Create indexes for performance
CREATE INDEX idx_wiki_articles_namespace ON wiki_articles(namespace_id);
CREATE INDEX idx_wiki_articles_content_model ON wiki_articles(content_model);
CREATE INDEX idx_wiki_articles_is_redirect ON wiki_articles(is_redirect);
CREATE INDEX idx_wiki_categories_parent ON wiki_categories(parent_id);
CREATE INDEX idx_wiki_template_usage_template ON wiki_template_usage(template_id);
CREATE INDEX idx_wiki_template_usage_article ON wiki_template_usage(article_id);
CREATE INDEX idx_wiki_references_article ON wiki_references(article_id);

-- 14. Update existing articles to use Main namespace
UPDATE wiki_articles SET namespace_id = 1 WHERE namespace_id IS NULL;

-- 15. Create trigger to update category article count
DELIMITER //
CREATE TRIGGER update_category_article_count_insert
AFTER INSERT ON wiki_article_categories
FOR EACH ROW
BEGIN
    UPDATE wiki_categories 
    SET article_count = article_count + 1 
    WHERE id = NEW.category_id;
END//

CREATE TRIGGER update_category_article_count_delete
AFTER DELETE ON wiki_article_categories
FOR EACH ROW
BEGIN
    UPDATE wiki_categories 
    SET article_count = article_count - 1 
    WHERE id = OLD.category_id;
END//
DELIMITER ;

-- 16. Create trigger to update template usage count
DELIMITER //
CREATE TRIGGER update_template_usage_count_insert
AFTER INSERT ON wiki_template_usage
FOR EACH ROW
BEGIN
    UPDATE wiki_templates 
    SET usage_count = usage_count + NEW.usage_count,
        last_used_at = NEW.last_used_at
    WHERE id = NEW.template_id;
END//

CREATE TRIGGER update_template_usage_count_update
AFTER UPDATE ON wiki_template_usage
FOR EACH ROW
BEGIN
    UPDATE wiki_templates 
    SET usage_count = usage_count - OLD.usage_count + NEW.usage_count,
        last_used_at = NEW.last_used_at
    WHERE id = NEW.template_id;
END//

CREATE TRIGGER update_template_usage_count_delete
AFTER DELETE ON wiki_template_usage
FOR EACH ROW
BEGIN
    UPDATE wiki_templates 
    SET usage_count = usage_count - OLD.usage_count
    WHERE id = OLD.template_id;
END//
DELIMITER ;
