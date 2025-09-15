<?php

require_once __DIR__ . '/AdvancedWikiParser.php';

/**
 * Secure Wiki Parser with HTML sanitization
 * Extends AdvancedWikiParser with security features
 */
class SecureWikiParser extends AdvancedWikiParser {
    
    private $allowed_tags = [
        'p', 'br', 'strong', 'em', 'u', 's', 'code', 'pre', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'a', 'img', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'div', 'span', 'sup', 'sub', 'small', 'mark', 'del', 'ins',
        'figure', 'figcaption', 'cite', 'q', 'abbr', 'time', 'address'
    ];
    
    private $allowed_attributes = [
        'href', 'title', 'alt', 'src', 'width', 'height', 'class', 'id',
        'data-*', 'aria-*', 'role', 'tabindex', 'target', 'rel'
    ];
    
    public function __construct($wiki_base_url = 'wiki/') {
        parent::__construct($wiki_base_url);
    }
    
    /**
     * Parse content with security sanitization
     */
    public function parse($content) {
        // Parse with all advanced features
        $content = parent::parse($content);
        
        // Sanitize HTML output
        $content = $this->sanitizeHtml($content);
        
        return $content;
    }
    
    /**
     * Sanitize HTML content
     */
    private function sanitizeHtml($html) {
        // Remove potentially dangerous tags and attributes
        $html = $this->removeDangerousTags($html);
        $html = $this->removeDangerousAttributes($html);
        $html = $this->validateLinks($html);
        $html = $this->validateImages($html);
        
        return $html;
    }
    
    /**
     * Remove dangerous HTML tags
     */
    private function removeDangerousTags($html) {
        $dangerous_tags = [
            'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
            'textarea', 'select', 'button', 'link', 'meta', 'base'
        ];
        
        foreach ($dangerous_tags as $tag) {
            $html = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $html);
            $html = preg_replace('/<' . $tag . '[^>]*\/?>/i', '', $html);
        }
        
        return $html;
    }
    
    /**
     * Remove dangerous attributes
     */
    private function removeDangerousAttributes($html) {
        $dangerous_attributes = [
            'onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur',
            'onchange', 'onsubmit', 'onreset', 'onselect', 'onkeydown', 'onkeyup',
            'onkeypress', 'onmousedown', 'onmouseup', 'onmousemove', 'onmouseout',
            'onabort', 'onbeforeunload', 'onerror', 'onhashchange', 'onload',
            'onpageshow', 'onpagehide', 'onresize', 'onscroll', 'onunload'
        ];
        
        foreach ($dangerous_attributes as $attr) {
            $html = preg_replace('/\s+' . $attr . '\s*=\s*["\'][^"\']*["\']/i', '', $html);
        }
        
        return $html;
    }
    
    /**
     * Validate and sanitize links
     */
    private function validateLinks($html) {
        $html = preg_replace_callback('/<a\s+([^>]*)>(.*?)<\/a>/is', function($matches) {
            $attributes = $matches[1];
            $content = $matches[2];
            
            // Extract href attribute
            if (preg_match('/href\s*=\s*["\']([^"\']*)["\']/', $attributes, $href_matches)) {
                $href = $href_matches[1];
                
                // Validate URL
                if ($this->isValidUrl($href)) {
                    // Add rel="noopener" for external links
                    if ($this->isExternalUrl($href)) {
                        $attributes = preg_replace('/rel\s*=\s*["\'][^"\']*["\']/', '', $attributes);
                        $attributes .= ' rel="noopener"';
                    }
                    
                    return '<a ' . $attributes . '>' . $content . '</a>';
                } else {
                    // Remove invalid links
                    return $content;
                }
            }
            
            return $matches[0];
        }, $html);
        
        return $html;
    }
    
    /**
     * Validate and sanitize images
     */
    private function validateImages($html) {
        $html = preg_replace_callback('/<img\s+([^>]*)\/?>/i', function($matches) {
            $attributes = $matches[1];
            
            // Extract src attribute
            if (preg_match('/src\s*=\s*["\']([^"\']*)["\']/', $attributes, $src_matches)) {
                $src = $src_matches[1];
                
                // Validate image URL
                if ($this->isValidImageUrl($src)) {
                    // Add security attributes
                    $attributes = preg_replace('/loading\s*=\s*["\'][^"\']*["\']/', '', $attributes);
                    $attributes .= ' loading="lazy"';
                    
                    return '<img ' . $attributes . '>';
                } else {
                    // Remove invalid images
                    return '';
                }
            }
            
            return $matches[0];
        }, $html);
        
        return $html;
    }
    
    /**
     * Check if URL is valid
     */
    private function isValidUrl($url) {
        // Allow relative URLs
        if (strpos($url, '/') === 0) {
            return true;
        }
        
        // Allow wiki links
        if (strpos($url, '/wiki/') !== false) {
            return true;
        }
        
        // Validate external URLs
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }
        
        $allowed_schemes = ['http', 'https', 'mailto'];
        if (!in_array($parsed['scheme'], $allowed_schemes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if URL is external
     */
    private function isExternalUrl($url) {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }
        
        $current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $parsed['host'] !== $current_host;
    }
    
    /**
     * Check if image URL is valid
     */
    private function isValidImageUrl($url) {
        // Allow relative URLs
        if (strpos($url, '/') === 0) {
            return true;
        }
        
        // Validate external URLs
        if (!$this->isValidUrl($url)) {
            return false;
        }
        
        // Check file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        
        return in_array($extension, $allowed_extensions);
    }
    
    /**
     * Get allowed tags
     */
    public function getAllowedTags() {
        return $this->allowed_tags;
    }
    
    /**
     * Get allowed attributes
     */
    public function getAllowedAttributes() {
        return $this->allowed_attributes;
    }
    
    /**
     * Add allowed tag
     */
    public function addAllowedTag($tag) {
        if (!in_array($tag, $this->allowed_tags)) {
            $this->allowed_tags[] = $tag;
        }
    }
    
    /**
     * Add allowed attribute
     */
    public function addAllowedAttribute($attribute) {
        if (!in_array($attribute, $this->allowed_attributes)) {
            $this->allowed_attributes[] = $attribute;
        }
    }
}
