-- SEO Extension Database Migration
-- Version: 0.0.0.20
-- Description: Adds SEO-related tables and functionality

-- Create SEO templates table
CREATE TABLE IF NOT EXISTS `seo_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `template_content` text NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create SEO metadata table
CREATE TABLE IF NOT EXISTS `seo_metadata` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL,
    `page_type` varchar(50) NOT NULL DEFAULT 'article',
    `title` varchar(255),
    `title_mode` varchar(20) DEFAULT 'append',
    `description` text,
    `keywords` text,
    `site_name` varchar(255),
    `locale` varchar(10) DEFAULT 'en_EN',
    `type` varchar(50) DEFAULT 'website',
    `url` varchar(500),
    `image` varchar(500),
    `published_time` datetime,
    `modified_time` datetime,
    `author` varchar(255),
    `section` varchar(255),
    `priority` decimal(3,1) DEFAULT 0.8,
    `changefreq` varchar(20) DEFAULT 'weekly',
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `page_id` (`page_id`),
    KEY `page_type` (`page_type`),
    KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create SEO analytics table
CREATE TABLE IF NOT EXISTS `seo_analytics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL,
    `page_type` varchar(50) NOT NULL DEFAULT 'article',
    `meta_title_length` int(11),
    `meta_description_length` int(11),
    `meta_keywords_count` int(11),
    `has_og_tags` tinyint(1) DEFAULT 0,
    `has_twitter_tags` tinyint(1) DEFAULT 0,
    `has_structured_data` tinyint(1) DEFAULT 0,
    `seo_score` int(11) DEFAULT 0,
    `last_checked` timestamp DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `page_id` (`page_id`),
    KEY `page_type` (`page_type`),
    KEY `seo_score` (`seo_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create SEO sitemap table
CREATE TABLE IF NOT EXISTS `seo_sitemap` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `url` varchar(500) NOT NULL,
    `page_id` int(11),
    `page_type` varchar(50) NOT NULL DEFAULT 'article',
    `lastmod` date,
    `changefreq` varchar(20) DEFAULT 'weekly',
    `priority` decimal(3,1) DEFAULT 0.8,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `url` (`url`),
    KEY `page_id` (`page_id`),
    KEY `page_type` (`page_type`),
    KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SEO templates
INSERT INTO `seo_templates` (`name`, `description`, `template_content`, `is_active`) VALUES
('Basic Article', 'Basic SEO template for articles', '{{#seo:|title={{PAGETITLE}}|description={{PAGEDESCRIPTION}}|keywords={{PAGEKEYWORDS}}|site_name=MuslimWiki|locale=en_EN|type=article}}', 1),
('Comprehensive Article', 'Comprehensive SEO template with all parameters', '{{#seo:|title={{PAGETITLE}}|title_mode=append|description={{PAGEDESCRIPTION}}|keywords={{PAGEKEYWORDS}}|site_name=MuslimWiki|locale=en_EN|type=article|published_time={{PUBLISHEDTIME}}|modified_time={{MODIFIEDTIME}}|author={{AUTHOR}}|section={{SECTION}}}}', 1),
('Category Page', 'SEO template for category pages', '{{#seo:|title={{PAGETITLE}}|description={{PAGEDESCRIPTION}}|keywords={{PAGEKEYWORDS}}|site_name=MuslimWiki|locale=en_EN|type=website|section=Category}}', 1),
('User Page', 'SEO template for user pages', '{{#seo:|title={{PAGETITLE}}|description={{PAGEDESCRIPTION}}|keywords={{PAGEKEYWORDS}}|site_name=MuslimWiki|locale=en_EN|type=profile|author={{AUTHOR}}}}', 1);

-- Insert default SEO settings
INSERT INTO `seo_metadata` (`page_id`, `page_type`, `title`, `title_mode`, `description`, `keywords`, `site_name`, `locale`, `type`, `priority`, `changefreq`, `is_active`) VALUES
(0, 'homepage', 'MuslimWiki - Islamic Knowledge Base', 'replace', 'MuslimWiki is a comprehensive Islamic knowledge base covering Quran, Hadith, Islamic history, and contemporary issues. Learn about Islam, its teachings, and the Muslim community.', 'Islam, Muslim, Quran, Hadith, Islamic history, Islamic knowledge, Islamic education, Islamic studies, Islamic culture, Islamic traditions', 'MuslimWiki', 'en_EN', 'website', 1.0, 'daily', 1),
(0, 'about', 'About MuslimWiki', 'append', 'Learn about MuslimWiki, our mission to provide accurate Islamic knowledge, and how we serve the global Muslim community.', 'About MuslimWiki, Islamic knowledge, Muslim community, Islamic education, Islamic resources', 'MuslimWiki', 'en_EN', 'website', 0.9, 'monthly', 1);

-- Create indexes for better performance
CREATE INDEX `idx_seo_metadata_page_lookup` ON `seo_metadata` (`page_id`, `page_type`, `is_active`);
CREATE INDEX `idx_seo_analytics_page_lookup` ON `seo_analytics` (`page_id`, `page_type`);
CREATE INDEX `idx_seo_sitemap_active` ON `seo_sitemap` (`is_active`, `priority` DESC);

-- Add foreign key constraints (if supported)
-- ALTER TABLE `seo_metadata` ADD CONSTRAINT `fk_seo_metadata_page` FOREIGN KEY (`page_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE;
-- ALTER TABLE `seo_analytics` ADD CONSTRAINT `fk_seo_analytics_page` FOREIGN KEY (`page_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE;
-- ALTER TABLE `seo_sitemap` ADD CONSTRAINT `fk_seo_sitemap_page` FOREIGN KEY (`page_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE;

