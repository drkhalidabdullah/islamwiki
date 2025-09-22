<?php
/**
 * SEO Template for MuslimWiki
 * 
 * This template provides a standardized way to add SEO metadata to wiki articles
 * using the {{#seo:|...}} template syntax.
 * 
 * Usage:
 * {{#seo:|title=Page Title|title_mode=append|keywords=keyword1, keyword2|description=Page description|site_name=MuslimWiki|locale=en_EN|type=website|modified_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}|published_time=2025-01-01}}
 */

// Allow direct access for testing and integration

class SEOTemplate {
    
    /**
     * Process the SEO template
     */
    public static function process($params) {
        global $seo_extension;
        
        if (!$seo_extension) {
            return '';
        }
        
        // Parse the template parameters
        $seo_data = [];
        foreach ($params as $key => $value) {
            $seo_data[$key] = $value;
        }
        
        // Set the SEO data
        $seo_extension->setSEOData($seo_data);
        
        // Return empty string as this template doesn't produce visible content
        return '';
    }
    
    /**
     * Get template documentation
     */
    public static function getDocumentation() {
        return [
            'name' => 'SEO Template',
            'description' => 'Adds SEO metadata to wiki articles',
            'syntax' => '{{#seo:|param1=value1|param2=value2}}',
            'parameters' => [
                'title' => 'Page title (required)',
                'title_mode' => 'Title mode: append, prepend, or replace (default: append)',
                'description' => 'Meta description (required)',
                'keywords' => 'Comma-separated keywords',
                'site_name' => 'Site name (default: MuslimWiki)',
                'locale' => 'Locale code (default: en_EN)',
                'type' => 'Content type (default: website)',
                'url' => 'Canonical URL',
                'image' => 'Social media image URL',
                'published_time' => 'Publication date (YYYY-MM-DD)',
                'modified_time' => 'Last modified date (YYYY-MM-DD)',
                'author' => 'Article author',
                'section' => 'Article section/category'
            ],
            'examples' => [
                'Basic SEO' => '{{#seo:|title=Islam|description=Learn about Islam|keywords=Islam, Muslim, Quran}}',
                'Full SEO' => '{{#seo:|title=Muslims|title_mode=append|keywords=Islam, Muhammad, Quran|description=Comprehensive guide to Muslims|site_name=MuslimWiki|locale=en_EN|type=website|modified_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}|published_time=2025-01-01}}'
            ]
        ];
    }
}

// Register the template
if (function_exists('register_wiki_template')) {
    register_wiki_template('seo', 'SEOTemplate::process');
}
