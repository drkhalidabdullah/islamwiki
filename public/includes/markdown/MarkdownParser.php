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
    protected function parseWikiLinks($content) {
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
    protected function createSlug($text) {
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
    protected function pageExists($slug) {
        global $pdo;
        
        if (!$pdo) return false;
        
        try {
            // Check for both hyphen and underscore versions
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE (slug = ? OR slug = ?) AND status = 'published'");
            $underscore_slug = str_replace('-', '_', $slug);
            $stmt->execute([$slug, $underscore_slug]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Parse standard markdown syntax
     */
    private function parseMarkdown($content) {
        // Headers (skip H1 since it's already in the page header) - allow characters immediately after #
        $content = preg_replace('/^###(\s*)(.*$)/m', '<h3>$2</h3>', $content);
        $content = preg_replace('/^##(\s*)(.*$)/m', '<h2>$2</h2>', $content);
        $content = preg_replace('/^#(\s*)(.*$)/m', '<h2>$2</h2>', $content); // Convert H1 to H2
        
        // Lists - handle nested unordered and ordered lists (must be before bold/italic)
        $content = $this->parseLists($content);
        
        // Bold and italic
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Code blocks
        $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
        // Removed backtick parsing as it conflicts with Arabic transliteration
        
        // Images
        $content = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="post-image" >', $content);
        
        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);
        
        // Horizontal rules
        $content = preg_replace('/^---$/m', '<hr>', $content);
        
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
     * Parse nested lists with support for multiple levels
     */
    private function parseLists($content) {
        $lines = explode("\n", $content);
        $result = [];
        $list_stack = []; // Stack to track nested lists
        $current_level = 0;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Check for unordered list item (allow characters immediately after *)
            if (preg_match('/^(\*+)(\s*)(.+)$/', $trimmed, $matches) && !preg_match('/^-\s*\[/', $trimmed)) {
                $level = strlen($matches[1]); // Count the number of *
                $content_text = $matches[3];
                
                // Adjust list stack to match current level
                $this->adjustListStack($list_stack, $level, 'ul', $result);
                
                // Add the list item
                $result[] = str_repeat('  ', $level - 1) . '<li>' . $content_text . '</li>';
                $current_level = $level;
            }
            // Check for checkbox list item: - [ ] or - [x] (with optional indentation)
            elseif (preg_match('/^(\s*)-\s*\[\s*([xX]?)\s*\]\s*(.+)$/', $line, $matches)) {
                $indent = $matches[1];
                $is_checked = !empty($matches[2]);
                $content_text = $matches[3];
                
                // Calculate level based on indentation (4 spaces = 1 level)
                $level = strlen($indent) / 4 + 1;
                
                // Close any open lists before adding checkbox
                while (!empty($list_stack)) {
                    $list_info = array_pop($list_stack);
                    $result[] = str_repeat('  ', $list_info['level'] - 1) . '</' . $list_info['type'] . '>';
                }
                
                // Add the checkbox as a standalone element with indentation
                $checkbox = $is_checked ? '<input type="checkbox" checked disabled>' : '<input type="checkbox" disabled>';
                $indent_spaces = str_repeat('  ', $level - 1);
                $result[] = $indent_spaces . '<div class="checkbox-item checkbox-level-' . $level . '">' . $checkbox . ' ' . $content_text . '</div>';
                $current_level = 0;
            }
            // Check for ordered list item (with optional indentation)
            elseif (preg_match('/^(\s*)(\d+)\. (.+)$/', $line, $matches)) {
                $indent = $matches[1];
                $number = $matches[2];
                $content_text = $matches[3];
                
                // Calculate level based on indentation (4 spaces = 1 level)
                $level = strlen($indent) / 4 + 1;
                
                // Adjust list stack to match current level
                $this->adjustListStack($list_stack, $level, 'ol', $result);
                
                // Add the list item
                $result[] = str_repeat('  ', $level - 1) . '<li>' . $content_text . '</li>';
                $current_level = $level;
            }
            // Check for letter list item (a., b., c., i., ii., etc.) (with optional indentation)
            elseif (preg_match('/^(\s*)([a-z]+)\. (.+)$/', $line, $matches)) {
                $indent = $matches[1];
                $letter = $matches[2];
                $content_text = $matches[3];
                
                // Calculate level based on indentation (4 spaces = 1 level)
                $level = strlen($indent) / 4 + 1;
                
                // Adjust list stack to match current level with letter list type
                $this->adjustListStack($list_stack, $level, 'ol', $result, 'letter-list');
                
                // Add the list item
                $result[] = str_repeat('  ', $level - 1) . '<li>' . $content_text . '</li>';
                $current_level = $level;
            }
            // Non-list line
            else {
                // Close all open lists
                while (!empty($list_stack)) {
                    $list_info = array_pop($list_stack);
                    $result[] = str_repeat('  ', $list_info['level'] - 1) . '</' . $list_info['type'] . '>';
                }
                $current_level = 0;
                $result[] = $line;
            }
        }
        
        // Close any remaining open lists
        while (!empty($list_stack)) {
            $list_info = array_pop($list_stack);
            $result[] = str_repeat('  ', $list_info['level'] - 1) . '</' . $list_info['type'] . '>';
        }
        
        return implode("\n", $result);
    }
    
    /**
     * Adjust the list stack to match the required level and type
     */
    private function adjustListStack(&$list_stack, $level, $type, &$result, $class = '') {
        // Close lists that are deeper than current level
        while (!empty($list_stack) && $list_stack[count($list_stack) - 1]['level'] > $level) {
            $list_info = array_pop($list_stack);
            $result[] = str_repeat('  ', $list_info['level'] - 1) . '</' . $list_info['type'] . '>';
        }
        
        // If we need to go deeper, open new lists
        if (empty($list_stack) || $list_stack[count($list_stack) - 1]['level'] < $level) {
            $list_stack[] = ['level' => $level, 'type' => $type, 'class' => $class];
            $class_attr = $class ? ' class="' . $class . '"' : '';
            $result[] = str_repeat('  ', $level - 1) . '<' . $type . $class_attr . '>';
        }
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
