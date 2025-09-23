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

class SeoExtension {
    public $name = 'SEO Extension';
    public $version = '1.0.0';
    public $description = 'Comprehensive SEO functionality with meta tags, Open Graph, Twitter Cards, and structured data';
    public $enabled = false;
    public $settings = [];
    
    private $config;
    private $seo_data;
    
    public function __construct() {
        $this->config = $this->loadConfig();
        $this->seo_data = [];
        $this->loadSettings();
    }
    
    /**
     * Load extension settings
     */
    private function loadSettings() {
        // Check if get_system_setting function exists (database connection available)
        if (function_exists('get_system_setting')) {
            $this->settings = [
                'enabled' => get_system_setting('seo_enabled', false),
                'default_site_name' => get_system_setting('seo_default_site_name', 'MuslimWiki'),
                'default_locale' => get_system_setting('seo_default_locale', 'en_EN'),
                'enable_open_graph' => get_system_setting('seo_enable_open_graph', true),
                'enable_twitter_cards' => get_system_setting('seo_enable_twitter_cards', true),
                'enable_structured_data' => get_system_setting('seo_enable_structured_data', true),
                'twitter_site' => get_system_setting('seo_twitter_site', '@MuslimWiki'),
                'facebook_app_id' => get_system_setting('seo_facebook_app_id', ''),
                'google_analytics_id' => get_system_setting('seo_google_analytics_id', '')
            ];
        } else {
            // Default settings when database is not available
            $this->settings = [
                'enabled' => false,
                'default_site_name' => 'MuslimWiki',
                'default_locale' => 'en_EN',
                'enable_open_graph' => true,
                'enable_twitter_cards' => true,
                'enable_structured_data' => true,
                'twitter_site' => '@MuslimWiki',
                'facebook_app_id' => '',
                'google_analytics_id' => ''
            ];
        }
        $this->enabled = $this->settings['enabled'];
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
    
    /**
     * Render extension (called by extension manager)
     */
    public function render() {
        // This method is called when the extension is enabled
        // We can add any frontend rendering logic here if needed
    }
    
    /**
     * Load extension assets
     */
    public function loadAssets() {
        if ($this->enabled) {
            echo '<link rel="stylesheet" href="/extensions/seo/assets/css/seo.css">';
        }
    }
    
    /**
     * Load extension scripts
     */
    public function loadScripts() {
        if ($this->enabled) {
            echo '<script src="/extensions/seo/assets/js/seo.js"></script>';
        }
    }
    
    /**
     * Get settings form for admin interface
     */
    public function getSettingsForm() {
        if (!$this->enabled) {
            return '<p>Enable the extension to configure settings.</p>';
        }
        
        $settings = $this->getAdminSettings();
        $html = '<div class="extension-settings-form">';
        
        foreach ($settings as $key => $setting) {
            $html .= '<div class="form-group">';
            $html .= '<label for="' . $key . '">' . htmlspecialchars($setting['label']) . '</label>';
            
            if ($setting['type'] === 'boolean') {
                $checked = $setting['value'] ? 'checked' : '';
                $html .= '<label class="toggle-switch">';
                $html .= '<input type="checkbox" name="' . $key . '" value="1" ' . $checked . '>';
                $html .= '<span class="toggle-slider"></span>';
                $html .= '</label>';
            } elseif ($setting['type'] === 'text') {
                $html .= '<input type="text" name="' . $key . '" value="' . htmlspecialchars($setting['value']) . '">';
            }
            
            if (isset($setting['description'])) {
                $html .= '<small class="form-help">' . htmlspecialchars($setting['description']) . '</small>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Save extension settings
     */
    public function saveSettings($data) {
        $settings_to_save = [
            'seo_enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : false,
            'seo_default_site_name' => isset($data['default_site_name']) ? $data['default_site_name'] : 'MuslimWiki',
            'seo_default_locale' => isset($data['default_locale']) ? $data['default_locale'] : 'en_EN',
            'seo_enable_open_graph' => isset($data['enable_open_graph']) ? (bool)$data['enable_open_graph'] : true,
            'seo_enable_twitter_cards' => isset($data['enable_twitter_cards']) ? (bool)$data['enable_twitter_cards'] : true,
            'seo_enable_structured_data' => isset($data['enable_structured_data']) ? (bool)$data['enable_structured_data'] : true,
            'seo_twitter_site' => isset($data['twitter_site']) ? $data['twitter_site'] : '@MuslimWiki',
            'seo_facebook_app_id' => isset($data['facebook_app_id']) ? $data['facebook_app_id'] : '',
            'seo_google_analytics_id' => isset($data['google_analytics_id']) ? $data['google_analytics_id'] : ''
        ];
        
        $saved = 0;
        foreach ($settings_to_save as $key => $value) {
            if (set_system_setting($key, $value)) {
                $saved++;
            }
        }
        
        $this->loadSettings(); // Reload settings
        return $saved > 0;
    }
    
    /**
     * Get admin settings configuration
     */
    public function getAdminSettings() {
        return [
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enable SEO Extension',
                'description' => 'Enable the SEO extension for better search engine optimization',
                'value' => $this->settings['enabled']
            ],
            'default_site_name' => [
                'type' => 'text',
                'label' => 'Default Site Name',
                'description' => 'Default site name for meta tags',
                'value' => $this->settings['default_site_name']
            ],
            'default_locale' => [
                'type' => 'text',
                'label' => 'Default Locale',
                'description' => 'Default locale code (e.g., en_EN)',
                'value' => $this->settings['default_locale']
            ],
            'enable_open_graph' => [
                'type' => 'boolean',
                'label' => 'Enable Open Graph',
                'description' => 'Enable Open Graph tags for social media sharing',
                'value' => $this->settings['enable_open_graph']
            ],
            'enable_twitter_cards' => [
                'type' => 'boolean',
                'label' => 'Enable Twitter Cards',
                'description' => 'Enable Twitter Card tags for enhanced Twitter sharing',
                'value' => $this->settings['enable_twitter_cards']
            ],
            'enable_structured_data' => [
                'type' => 'boolean',
                'label' => 'Enable Structured Data',
                'description' => 'Enable JSON-LD structured data for search engines',
                'value' => $this->settings['enable_structured_data']
            ],
            'twitter_site' => [
                'type' => 'text',
                'label' => 'Twitter Site Handle',
                'description' => 'Twitter handle for the site (e.g., @MuslimWiki)',
                'value' => $this->settings['twitter_site']
            ],
            'facebook_app_id' => [
                'type' => 'text',
                'label' => 'Facebook App ID',
                'description' => 'Facebook App ID for Open Graph (optional)',
                'value' => $this->settings['facebook_app_id']
            ],
            'google_analytics_id' => [
                'type' => 'text',
                'label' => 'Google Analytics ID',
                'description' => 'Google Analytics tracking ID (optional)',
                'value' => $this->settings['google_analytics_id']
            ]
        ];
    }
}

// Initialize the extension
$seo_extension = new SeoExtension();

// Hook into the wiki system
if (function_exists('add_wiki_extension')) {
    add_wiki_extension('seo', $seo_extension);
}
