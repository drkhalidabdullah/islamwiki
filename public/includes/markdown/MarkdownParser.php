<?php

class MarkdownParser {
    private $wiki_base_url;
    
    public function __construct($wiki_base_url = 'wiki/') {
        $this->wiki_base_url = $wiki_base_url;
    }
    
    /**
     * Parse markdown content with wiki-specific features
     */
    public function parse($content) {
        // First, handle wiki links [[Page Name]] or [[Page Name|Display Text]]
        $content = $this->parseWikiLinks($content);
        
        // Then parse standard markdown
        $content = $this->parseMarkdown($content);
        
        return $content;
    }
    
    /**
     * Parse wiki links in the format [[Page Name]] or [[Page Name|Display Text]]
     */
    private function parseWikiLinks($content) {
        // Pattern to match [[Page Name]] or [[Page Name|Display Text]]
        $pattern = '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        
        return preg_replace_callback($pattern, function($matches) {
            $page_name = trim($matches[1]);
            $display_text = isset($matches[2]) ? trim($matches[2]) : $page_name;
            
            // Create slug from page name
            $slug = $this->createSlug($page_name);
            
            // Check if page exists (you can enhance this with database lookup)
            $exists = $this->pageExists($slug);
            $class = $exists ? 'wiki-link' : 'wiki-link missing';
            
            return sprintf(
                '<a href="/wiki/%s" class="%s" title="%s">%s</a>',
                $slug,
                $class,
                htmlspecialchars($page_name),
                htmlspecialchars($display_text)
            );
        }, $content);
    }
    
    /**
     * Create URL-friendly slug from page name
     */
    private function createSlug($text) {
        // Capitalize first letter for consistency
        $text = strtolower($text);
        $text = ucfirst(trim($text));
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remove leading/trailing hyphens
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Check if a wiki page exists (simplified version)
     */
    private function pageExists($slug) {
        global $pdo;
        
        if (!$pdo) return false;
        
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Parse standard markdown syntax
     */
    private function parseMarkdown($content) {
        // Headers (skip H1 since it's already in the page header)
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h2>$1</h2>', $content); // Convert H1 to H2
        
        // Bold and italic
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Code blocks
        $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
        
        // Images
        $content = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="post-image" >', $content);
        
        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);
        
        // Horizontal rules
        $content = preg_replace('/^---$/m', '<hr>', $content);
        
        // Lists - handle both unordered and ordered lists
        $lines = explode("\n", $content);
        $in_list = false;
        $list_type = '';
        $result = [];
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Check for unordered list item
            if (preg_match('/^\* (.+)$/', $trimmed, $matches)) {
                if (!$in_list || $list_type !== 'ul') {
                    if ($in_list) {
                        $result[] = '</' . $list_type . '>';
                    }
                    $result[] = '<ul>';
                    $in_list = true;
                    $list_type = 'ul';
                }
                $result[] = '<li>' . $matches[1] . '</li>';
            }
            // Check for ordered list item
            elseif (preg_match('/^(\d+)\. (.+)$/', $trimmed, $matches)) {
                if (!$in_list || $list_type !== 'ol') {
                    if ($in_list) {
                        $result[] = '</' . $list_type . '>';
                    }
                    $result[] = '<ol>';
                    $in_list = true;
                    $list_type = 'ol';
                }
                $result[] = '<li>' . $matches[2] . '</li>';
            }
            // Non-list line
            else {
                if ($in_list) {
                    $result[] = '</' . $list_type . '>';
                    $in_list = false;
                    $list_type = '';
                }
                $result[] = $line;
            }
        }
        
        // Close any open list
        if ($in_list) {
            $result[] = '</' . $list_type . '>';
        }
        
        $content = implode("\n", $result);
        
        // Line breaks - only convert double line breaks to paragraph breaks
        $content = preg_replace('/\n\n/', '</p><p>', $content);
        
        // Clean up any empty paragraphs
        $content = preg_replace('/<p><\/p>/', '', $content);
        
        // Wrap in paragraph tags only if content doesn't start with a block element
        if (!preg_match('/^<(h[1-6]|ul|ol|pre|blockquote)/', trim($content))) {
            $content = '<p>' . $content . '</p>';
        }
        
        // Clean up any malformed paragraph tags around block elements
        $content = preg_replace('/<p>(<(h[1-6]|ul|ol|pre|blockquote)[^>]*>.*?<\/(h[1-6]|ul|ol|pre|blockquote)>)<\/p>/s', '$1', $content);
        
        // Clean up empty paragraphs
        $content = preg_replace('/<p><\/p>/', '', $content);
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        $content = preg_replace('/<p>\s*<(h[1-6])/', '<$1', $content);
        $content = preg_replace('/<\/(h[1-6])>\s*<\/p>/', '</$1>', $content);
        
        return $content;
    }
    
    /**
     * Convert HTML back to markdown (for editing)
     */
    public function htmlToMarkdown($html) {
        // Convert wiki links back to [[Page Name]] format
        $html = preg_replace_callback(
            '/<a[^>]*href="[^"]*article\.php\?slug=([^"]*)"[^>]*class="[^"]*wiki-link[^"]*"[^>]*>([^<]*)<\/a>/',
            function($matches) {
                $slug = urldecode($matches[1]);
                $display_text = $matches[2];
                $page_name = $this->slugToPageName($slug);
                
                if ($display_text === $page_name) {
                    return "[[" . $page_name . "]]";
                } else {
                    return "[[" . $page_name . "|" . $display_text . "]]";
                }
            },
            $html
        );
        
        // Convert other HTML back to markdown
        $html = preg_replace('/<h1>(.*?)<\/h1>/', '# $1', $html);
        $html = preg_replace('/<h2>(.*?)<\/h2>/', '## $1', $html);
        $html = preg_replace('/<h3>(.*?)<\/h3>/', '### $1', $html);
        $html = preg_replace('/<strong>(.*?)<\/strong>/', '**$1**', $html);
        $html = preg_replace('/<em>(.*?)<\/em>/', '*$1*', $html);
        $html = preg_replace('/<code>(.*?)<\/code>/', '`$1`', $html);
        $html = preg_replace('/<pre><code>(.*?)<\/code><\/pre>/s', '```$1```', $html);
        $html = preg_replace('/<a href="([^"]*)">([^<]*)<\/a>/', '[$2]($1)', $html);
        
        // Remove paragraph tags
        $html = preg_replace('/<p>(.*?)<\/p>/s', '$1', $html);
        
        return $html;
    }
    
    /**
     * Convert slug back to page name
     */
    private function slugToPageName($slug) {
        // Convert hyphens back to spaces and capitalize
        $name = str_replace('-', ' ', $slug);
        $name = ucwords($name);
        return $name;
    }
}
