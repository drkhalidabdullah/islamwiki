<?php
/**
 * SEO Extension Hooks
 * 
 * This file contains hooks to integrate the SEO extension
 * with the MuslimWiki system.
 */

// Allow direct access for testing and integration

/**
 * Hook into the header generation
 */
function seo_hook_header($content) {
    global $seo_extension;
    
    if (!$seo_extension) {
        return $content;
    }
    
    // Parse SEO template from content
    $seo_extension->parseSEOTemplate($content);
    
    // Generate meta tags
    $meta_tags = $seo_extension->generateMetaTags();
    $structured_data = $seo_extension->generateStructuredData();
    
    // Insert meta tags into head
    $head_end = '</head>';
    $seo_content = $meta_tags . "\n    " . $structured_data;
    
    if (strpos($content, $head_end) !== false) {
        $content = str_replace($head_end, $seo_content . "\n" . $head_end, $content);
    }
    
    return $content;
}

/**
 * Hook into the content parsing
 */
function seo_hook_content_parse($content) {
    global $seo_extension;
    
    if (!$seo_extension) {
        return $content;
    }
    
    // Parse SEO template and remove it from content
    $pattern = '/\{\{#seo:\|([^}]+)\}\}/';
    $content = preg_replace($pattern, '', $content);
    
    return $content;
}

/**
 * Hook into the article display
 */
function seo_hook_article_display($article) {
    global $seo_extension;
    
    if (!$seo_extension) {
        return;
    }
    
    // Set additional SEO data from article
    $seo_data = [
        'url' => get_current_url(),
        'published_time' => $article['published_at'] ?? null,
        'modified_time' => $article['updated_at'] ?? null,
        'author' => $article['author'] ?? null,
    ];
    
    $seo_extension->setSEOData($seo_data);
}

/**
 * Hook into the sitemap generation
 */
function seo_hook_sitemap_generate($sitemap_entries) {
    global $seo_extension;
    
    if (!$seo_extension) {
        return $sitemap_entries;
    }
    
    // Add SEO-enhanced entries to sitemap
    foreach ($sitemap_entries as &$entry) {
        $seo_data = $seo_extension->getSEOData();
        
        if (!empty($seo_data['priority'])) {
            $entry['priority'] = $seo_data['priority'];
        }
        
        if (!empty($seo_data['changefreq'])) {
            $entry['changefreq'] = $seo_data['changefreq'];
        }
    }
    
    return $sitemap_entries;
}

/**
 * Hook into the RSS feed generation
 */
function seo_hook_rss_generate($rss_items) {
    global $seo_extension;
    
    if (!$seo_extension) {
        return $rss_items;
    }
    
    // Enhance RSS items with SEO data
    foreach ($rss_items as &$item) {
        $seo_data = $seo_extension->getSEOData();
        
        if (!empty($seo_data['description'])) {
            $item['description'] = $seo_data['description'];
        }
        
        if (!empty($seo_data['keywords'])) {
            $item['keywords'] = $seo_data['keywords'];
        }
    }
    
    return $rss_items;
}

/**
 * Register all hooks
 */
function register_seo_hooks() {
    // Register header hook
    if (function_exists('add_filter')) {
        add_filter('wiki_header', 'seo_hook_header');
        add_filter('wiki_content_parse', 'seo_hook_content_parse');
        add_action('wiki_article_display', 'seo_hook_article_display');
        add_filter('wiki_sitemap_generate', 'seo_hook_sitemap_generate');
        add_filter('wiki_rss_generate', 'seo_hook_rss_generate');
    }
}

// Register hooks when extension is loaded
register_seo_hooks();
