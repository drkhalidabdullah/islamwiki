<?php
/**
 * SEO Extension Configuration
 * 
 * This file contains configuration options for the SEO extension.
 * Modify these settings to customize the SEO behavior for your site.
 */

// Allow direct access for testing and integration

return [
    // Basic SEO Settings
    'default_site_name' => 'MuslimWiki',
    'default_locale' => 'en_EN',
    'default_type' => 'website',
    
    // Social Media Settings
    'twitter_site' => '@MuslimWiki',
    'twitter_creator' => '@MuslimWiki',
    'facebook_app_id' => '',
    
    // Analytics
    'google_analytics_id' => '',
    'google_tag_manager_id' => '',
    
    // Schema.org Settings
    'schema_org_type' => 'Article',
    'enable_structured_data' => true,
    
    // Feature Toggles
    'enable_open_graph' => true,
    'enable_twitter_cards' => true,
    'enable_canonical_urls' => true,
    'enable_meta_tags' => true,
    
    // Default SEO Values
    'default_keywords' => 'Islam, Muslim, Quran, Allah, Muhammad, Islamic, Religion, Faith',
    'default_description' => 'Learn about Islam, the Quran, and Islamic teachings on MuslimWiki',
    
    // Title Settings
    'title_separator' => ' - ',
    'title_max_length' => 60,
    'description_max_length' => 160,
    
    // Image Settings
    'default_og_image' => '/assets/images/default-og-image.jpg',
    'default_twitter_image' => '/assets/images/default-twitter-image.jpg',
    
    // URL Settings
    'force_https' => true,
    'remove_trailing_slash' => true,
    
    // Debug Settings
    'enable_debug_mode' => false,
    'debug_parameter' => 'seo_debug',
    
    // Template Settings
    'auto_generate_meta' => true,
    'use_article_content_for_meta' => true,
    'extract_keywords_from_content' => true,
    
    // Cache Settings
    'enable_meta_cache' => true,
    'cache_duration' => 3600, // 1 hour
    
    // Sitemap Settings
    'enable_sitemap' => true,
    'sitemap_priority' => 0.8,
    'sitemap_changefreq' => 'weekly',
    
    // RSS Settings
    'enable_rss' => true,
    'rss_title' => 'MuslimWiki - Latest Articles',
    'rss_description' => 'Stay updated with the latest articles from MuslimWiki',
    
    // Advanced Settings
    'enable_amp' => false,
    'enable_pwa' => false,
    'enable_web_manifest' => false,
    
    // Custom Meta Tags
    'custom_meta_tags' => [
        // Add custom meta tags here
        // 'custom-tag' => 'custom-value',
    ],
    
    // Exclude Pages from SEO
    'exclude_pages' => [
        '/admin/',
        '/api/',
        '/debug/',
        '/test/',
    ],
    
    // SEO Rules
    'rules' => [
        'require_title' => true,
        'require_description' => true,
        'require_keywords' => false,
        'auto_generate_og_image' => false,
        'validate_meta_lengths' => true,
    ]
];
