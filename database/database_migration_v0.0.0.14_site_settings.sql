-- Add default system settings for site configuration if they don't exist
INSERT IGNORE INTO system_settings (`key`, value, type, description, is_public) VALUES
('site_name', 'IslamWiki', 'string', 'Site name displayed in header and title', 1),
('site_description', 'A comprehensive Islamic knowledge platform', 'string', 'Site description for SEO and meta tags', 1),
('site_keywords', 'Islam, Islamic, knowledge, wiki, Islamic knowledge, Islamic education', 'string', 'Site keywords for SEO', 1),
('admin_email', '', 'string', 'Administrator email address', 0),
('contact_email', '', 'string', 'Contact email address for public inquiries', 1),
('posts_per_page', '10', 'integer', 'Number of posts per page in listings', 1),
('articles_per_page', '10', 'integer', 'Number of articles per page in listings', 1),
('copyright_text', '', 'string', 'Custom copyright text (leave empty for default)', 1);

-- Update existing empty values with defaults
UPDATE system_settings SET value = 'IslamWiki' WHERE `key` = 'site_name' AND (value = '' OR value IS NULL);
UPDATE system_settings SET value = 'A comprehensive Islamic knowledge platform' WHERE `key` = 'site_description' AND (value = '' OR value IS NULL);
UPDATE system_settings SET value = 'Islam, Islamic, knowledge, wiki, Islamic knowledge, Islamic education' WHERE `key` = 'site_keywords' AND (value = '' OR value IS NULL);
UPDATE system_settings SET value = '10' WHERE `key` = 'posts_per_page' AND (value = '' OR value IS NULL);
UPDATE system_settings SET value = '10' WHERE `key` = 'articles_per_page' AND (value = '' OR value IS NULL);
