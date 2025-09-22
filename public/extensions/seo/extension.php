<?php
/**
 * SEO Extension for MuslimWiki
 * 
 * This extension provides comprehensive SEO functionality including:
 * - Meta tags generation
 * - Open Graph tags
 * - Twitter Card tags
 * - Structured data (JSON-LD)
 * - Template-based SEO configuration
 * 
 * @version 1.0.0
 * @author MuslimWiki Team
 */

// Allow direct access for testing and integration

class SEOExtension {
    private $config;
    private $seo_data;
    
    public function __construct() {
        $this->config = $this->loadConfig();
        $this->seo_data = [];
    }
    
    /**
     * Load SEO configuration
     */
    private function loadConfig() {
        return [
            'default_site_name' => 'MuslimWiki',
            'default_locale' => 'en_EN',
            'default_type' => 'website',
            'twitter_site' => '@MuslimWiki',
            'twitter_creator' => '@MuslimWiki',
            'facebook_app_id' => '',
            'google_analytics_id' => '',
            'schema_org_type' => 'Article',
            'enable_structured_data' => true,
            'enable_open_graph' => true,
            'enable_twitter_cards' => true
        ];
    }
    
    /**
     * Parse SEO template and extract data
     */
    public function parseSEOTemplate($content) {
        // Look for {{#seo:|...}} template
        $pattern = '/\{\{#seo:\|([^}]+)\}\}/';
        if (preg_match($pattern, $content, $matches)) {
            $params = $this->parseTemplateParams($matches[1]);
            $this->seo_data = array_merge($this->seo_data, $params);
            return true;
        }
        return false;
    }
    
    /**
     * Parse template parameters
     */
    private function parseTemplateParams($param_string) {
        $params = [];
        $pairs = explode('|', $param_string);
        
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                list($key, $value) = explode('=', $pair, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Handle special values
                if (strpos($value, '{{') !== false) {
                    $value = $this->processTemplateVariables($value);
                }
                
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
    
    /**
     * Process template variables like {{REVISIONYEAR}}
     */
    private function processTemplateVariables($value) {
        // Replace revision date variables
        $value = str_replace('{{REVISIONYEAR}}', date('Y'), $value);
        $value = str_replace('{{REVISIONMONTH}}', date('m'), $value);
        $value = str_replace('{{REVISIONDAY2}}', date('d'), $value);
        
        // Handle partial template variables that might be cut off
        $value = str_replace('{{REVISIONYEAR', date('Y'), $value);
        $value = str_replace('{{REVISIONMONTH', date('m'), $value);
        $value = str_replace('{{REVISIONDAY2', date('d'), $value);
        
        return $value;
    }
    
    /**
     * Generate meta tags
     */
    public function generateMetaTags() {
        $meta_tags = [];
        
        // Basic meta tags
        if (!empty($this->seo_data['title'])) {
            $title = $this->seo_data['title'];
            if (isset($this->seo_data['title_mode']) && $this->seo_data['title_mode'] === 'append') {
                $title .= ' - ' . $this->config['default_site_name'];
            }
            $meta_tags[] = '<title>' . htmlspecialchars($title) . '</title>';
        }
        
        if (!empty($this->seo_data['description'])) {
            $meta_tags[] = '<meta name="description" content="' . htmlspecialchars($this->seo_data['description']) . '">';
        }
        
        if (!empty($this->seo_data['keywords'])) {
            $meta_tags[] = '<meta name="keywords" content="' . htmlspecialchars($this->seo_data['keywords']) . '">';
        }
        
        // Open Graph tags
        if ($this->config['enable_open_graph']) {
            $meta_tags = array_merge($meta_tags, $this->generateOpenGraphTags());
        }
        
        // Twitter Card tags
        if ($this->config['enable_twitter_cards']) {
            $meta_tags = array_merge($meta_tags, $this->generateTwitterCardTags());
        }
        
        // Additional meta tags
        $meta_tags[] = '<meta name="robots" content="index, follow">';
        $meta_tags[] = '<meta name="author" content="' . htmlspecialchars($this->config['default_site_name']) . '">';
        $meta_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        
        if (!empty($this->seo_data['locale'])) {
            $meta_tags[] = '<meta property="og:locale" content="' . htmlspecialchars($this->seo_data['locale']) . '">';
        }
        
        return implode("\n    ", $meta_tags);
    }
    
    /**
     * Generate Open Graph tags
     */
    private function generateOpenGraphTags() {
        $og_tags = [];
        
        $og_tags[] = '<meta property="og:type" content="' . htmlspecialchars($this->seo_data['type'] ?? $this->config['default_type']) . '">';
        $og_tags[] = '<meta property="og:site_name" content="' . htmlspecialchars($this->seo_data['site_name'] ?? $this->config['default_site_name']) . '">';
        
        if (!empty($this->seo_data['title'])) {
            $og_tags[] = '<meta property="og:title" content="' . htmlspecialchars($this->seo_data['title']) . '">';
        }
        
        if (!empty($this->seo_data['description'])) {
            $og_tags[] = '<meta property="og:description" content="' . htmlspecialchars($this->seo_data['description']) . '">';
        }
        
        if (!empty($this->seo_data['url'])) {
            $og_tags[] = '<meta property="og:url" content="' . htmlspecialchars($this->seo_data['url']) . '">';
        }
        
        if (!empty($this->seo_data['image'])) {
            $og_tags[] = '<meta property="og:image" content="' . htmlspecialchars($this->seo_data['image']) . '">';
        }
        
        if (!empty($this->seo_data['published_time'])) {
            $og_tags[] = '<meta property="article:published_time" content="' . htmlspecialchars($this->seo_data['published_time']) . '">';
        }
        
        if (!empty($this->seo_data['modified_time'])) {
            $og_tags[] = '<meta property="article:modified_time" content="' . htmlspecialchars($this->seo_data['modified_time']) . '">';
        }
        
        return $og_tags;
    }
    
    /**
     * Generate Twitter Card tags
     */
    private function generateTwitterCardTags() {
        $twitter_tags = [];
        
        $twitter_tags[] = '<meta name="twitter:card" content="summary_large_image">';
        $twitter_tags[] = '<meta name="twitter:site" content="' . htmlspecialchars($this->config['twitter_site']) . '">';
        $twitter_tags[] = '<meta name="twitter:creator" content="' . htmlspecialchars($this->config['twitter_creator']) . '">';
        
        if (!empty($this->seo_data['title'])) {
            $twitter_tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($this->seo_data['title']) . '">';
        }
        
        if (!empty($this->seo_data['description'])) {
            $twitter_tags[] = '<meta name="twitter:description" content="' . htmlspecialchars($this->seo_data['description']) . '">';
        }
        
        if (!empty($this->seo_data['image'])) {
            $twitter_tags[] = '<meta name="twitter:image" content="' . htmlspecialchars($this->seo_data['image']) . '">';
        }
        
        return $twitter_tags;
    }
    
    /**
     * Generate structured data (JSON-LD)
     */
    public function generateStructuredData() {
        if (!$this->config['enable_structured_data']) {
            return '';
        }
        
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => $this->config['schema_org_type'],
            'name' => $this->seo_data['title'] ?? '',
            'description' => $this->seo_data['description'] ?? '',
            'url' => $this->seo_data['url'] ?? '',
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->seo_data['site_name'] ?? $this->config['default_site_name'],
                'url' => $this->seo_data['url'] ?? ''
            ]
        ];
        
        if (!empty($this->seo_data['published_time'])) {
            $structured_data['datePublished'] = $this->seo_data['published_time'];
        }
        
        if (!empty($this->seo_data['modified_time'])) {
            $structured_data['dateModified'] = $this->seo_data['modified_time'];
        }
        
        if (!empty($this->seo_data['image'])) {
            $structured_data['image'] = $this->seo_data['image'];
        }
        
        if (!empty($this->seo_data['keywords'])) {
            $keywords = explode(',', $this->seo_data['keywords']);
            $keywords = array_map('trim', $keywords);
            $structured_data['keywords'] = implode(', ', $keywords);
        }
        
        return '<script type="application/ld+json">' . json_encode($structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }
    
    /**
     * Set SEO data from external source
     */
    public function setSEOData($data) {
        $this->seo_data = array_merge($this->seo_data, $data);
    }
    
    /**
     * Get current SEO data
     */
    public function getSEOData() {
        return $this->seo_data;
    }
    
    /**
     * Generate canonical URL
     */
    public function generateCanonicalURL($url) {
        return '<link rel="canonical" href="' . htmlspecialchars($url) . '">';
    }
    
    /**
     * Generate sitemap entry
     */
    public function generateSitemapEntry($url, $lastmod = null, $changefreq = 'weekly', $priority = '0.8') {
        if ($lastmod === null) {
            $lastmod = date('Y-m-d');
        }
        
        return [
            'url' => $url,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority
        ];
    }
}

// Initialize the extension
$seo_extension = new SEOExtension();

// Hook into the wiki system
if (function_exists('add_wiki_extension')) {
    add_wiki_extension('seo', $seo_extension);
}
