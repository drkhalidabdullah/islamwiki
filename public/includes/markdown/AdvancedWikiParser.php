<?php

require_once __DIR__ . '/MarkdownParser.php';

/**
 * Advanced Wiki Parser with comprehensive MediaWiki-style features
 * Supports tables, references, magic words, categories, and more
 */
class AdvancedWikiParser extends MarkdownParser {
    
    private $references = [];
    private $categories = [];
    private $magic_words = [];
    
    public function __construct($wiki_base_url = 'wiki/') {
        parent::__construct($wiki_base_url);
        $this->initializeMagicWords();
    }
    
    /**
     * Parse content with all advanced wiki features
     */
    public function parse($content) {
        // Reset collections
        $this->references = [];
        $this->categories = [];
        
        // Parse in order of precedence
        $content = $this->parseMagicWords($content);
        $content = $this->parseReferences($content);
        $content = $this->parseTables($content);
        $content = $this->parseCategories($content);
        $content = $this->parseTemplates($content);
        $content = $this->parseWikiFormatting($content);
        
        // Call parent parse method (handles wiki links and markdown)
        $content = parent::parse($content);
        
        // Add references section if any exist
        if (!empty($this->references)) {
            $content .= $this->generateReferencesSection();
        }
        
        return $content;
    }
    
    /**
     * Parse MediaWiki-style tables: {| |}
     */
    private function parseTables($content) {
        $pattern = '/\{\|(.*?)\|\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $table_content = trim($matches[1]);
            $lines = explode("\n", $table_content);
            $html = '<table class="wiki-table">';
            $in_row = false;
            $in_cell = false;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Table attributes
                if (strpos($line, '|') === 0 && strpos($line, '||') === false) {
                    $attrs = $this->parseTableAttributes($line);
                    $html .= '<table' . $attrs . '>';
                }
                // Row start
                elseif (strpos($line, '|-') === 0) {
                    if ($in_row) $html .= '</tr>';
                    $html .= '<tr>';
                    $in_row = true;
                }
                // Header cell
                elseif (strpos($line, '!') === 0) {
                    if (!$in_row) $html .= '<tr>';
                    $cell_content = substr($line, 1);
                    $html .= '<th>' . $this->parseInlineWiki($cell_content) . '</th>';
                    $in_row = true;
                }
                // Data cell
                elseif (strpos($line, '|') === 0) {
                    if (!$in_row) $html .= '<tr>';
                    $cell_content = substr($line, 1);
                    $html .= '<td>' . $this->parseInlineWiki($cell_content) . '</td>';
                    $in_row = true;
                }
            }
            
            if ($in_row) $html .= '</tr>';
            $html .= '</table>';
            
            return $html;
        }, $content);
    }
    
    /**
     * Parse table attributes
     */
    private function parseTableAttributes($line) {
        $attrs = '';
        if (preg_match('/class="([^"]*)"/', $line, $matches)) {
            $attrs .= ' class="' . $matches[1] . '"';
        }
        if (preg_match('/style="([^"]*)"/', $line, $matches)) {
            $attrs .= ' style="' . $matches[1] . '"';
        }
        return $attrs;
    }
    
    /**
     * Parse references: <ref>content</ref>
     */
    private function parseReferences($content) {
        // Parse <ref>content</ref> tags
        $content = preg_replace_callback('/<ref>(.*?)<\/ref>/s', function($matches) {
            $ref_content = trim($matches[1]);
            $ref_id = count($this->references) + 1;
            $this->references[$ref_id] = $ref_content;
            return '<sup><a href="#ref' . $ref_id . '" id="ref' . $ref_id . '">[' . $ref_id . ']</a></sup>';
        }, $content);
        
        // Parse <ref name="id" /> self-closing tags
        $content = preg_replace_callback('/<ref\s+name="([^"]*)"\s*\/>/', function($matches) {
            $ref_name = $matches[1];
            $ref_id = count($this->references) + 1;
            $this->references[$ref_id] = $ref_name; // Will be resolved later
            return '<sup><a href="#ref' . $ref_id . '" id="ref' . $ref_id . '">[' . $ref_id . ']</a></sup>';
        }, $content);
        
        return $content;
    }
    
    /**
     * Generate references section
     */
    private function generateReferencesSection() {
        if (empty($this->references)) return '';
        
        $html = '<div class="wiki-references">';
        $html .= '<h3>References</h3>';
        $html .= '<ol>';
        
        foreach ($this->references as $id => $content) {
            $html .= '<li id="ref' . $id . '">' . htmlspecialchars($content) . '</li>';
        }
        
        $html .= '</ol></div>';
        return $html;
    }
    
    /**
     * Parse templates: {{Template Name|param1|param2}}
     */
    private function parseTemplates($content) {
        // Parse template syntax: {{Template Name|param1|param2}} with proper nesting support
        $content = $this->parseTemplatesRecursive($content);
        return $content;
    }
    
    /**
     * Recursively parse templates with proper brace matching
     */
    private function parseTemplatesRecursive($content) {
        $pos = 0;
        $result = '';
        
        while ($pos < strlen($content)) {
            $open_pos = strpos($content, '{{', $pos);
            if ($open_pos === false) {
                $result .= substr($content, $pos);
                break;
            }
            
            // Add content before the template
            $result .= substr($content, $pos, $open_pos - $pos);
            
            // Find the matching closing braces
            $brace_count = 0;
            $template_start = $open_pos + 2;
            $template_end = $template_start;
            
            for ($i = $template_start; $i < strlen($content); $i++) {
                if ($content[$i] === '{') {
                    $brace_count++;
                } elseif ($content[$i] === '}') {
                    if ($brace_count === 0) {
                        // Found first closing brace, now look for the second one
                        if ($i + 1 < strlen($content) && $content[$i + 1] === '}') {
                            $template_end = $i;
                            break;
                        } else {
                            // Only one closing brace, continue looking
                            continue;
                        }
                    }
                    $brace_count--;
                }
            }
            
            if ($template_end > $template_start) {
                $template_content = substr($content, $template_start, $template_end - $template_start);
                $parsed_template = $this->parseSingleTemplate($template_content);
                $result .= $parsed_template;
                $pos = $template_end + 2; // Skip both closing braces }}
            } else {
                // No matching closing brace found, treat as regular text
                $result .= substr($content, $open_pos, 2);
                $pos = $open_pos + 2;
            }
        }
        
        return $result;
    }
    
    /**
     * Parse a single template
     */
    private function parseSingleTemplate($template_content) {
        $template_content = trim($template_content);
        
        // Skip magic words (they start with #)
        if (strpos($template_content, '#') === 0) {
            return '{{' . $template_content . '}}';
        }
        
        // Parse template parameters more carefully to handle | in content
        $parts = $this->parseTemplateParameters($template_content);
        $template_name = trim($parts[0]);
        
        // Extract parameters
        $parameters = [];
        for ($i = 1; $i < count($parts); $i++) {
            $param = trim($parts[$i]);
            if (strpos($param, '=') !== false) {
                list($key, $value) = explode('=', $param, 2);
                $parameters[trim($key)] = trim($value);
            } else {
                $parameters[$i] = $param; // Positional parameters
            }
        }
        
        // Load and parse the actual template
        return $this->loadAndParseTemplate($template_name, $parameters);
    }
    
    /**
     * Parse template parameters while respecting wiki link boundaries
     */
    private function parseTemplateParameters($template_content) {
        $parts = [];
        $current_part = '';
        $pos = 0;
        $in_wiki_link = false;
        $bracket_count = 0;
        
        while ($pos < strlen($template_content)) {
            $char = $template_content[$pos];
            
            if ($char === '[' && $pos + 1 < strlen($template_content) && $template_content[$pos + 1] === '[') {
                // Start of wiki link
                $in_wiki_link = true;
                $bracket_count = 0;
                $current_part .= $char;
                $pos++;
            } elseif ($char === ']' && $in_wiki_link) {
                $bracket_count++;
                $current_part .= $char;
                if ($bracket_count >= 2) {
                    // End of wiki link
                    $in_wiki_link = false;
                    $bracket_count = 0;
                }
                $pos++;
            } elseif ($char === '|' && !$in_wiki_link) {
                // Template parameter separator
                $parts[] = $current_part;
                $current_part = '';
                $pos++;
            } else {
                $current_part .= $char;
                $pos++;
            }
        }
        
        // Add the last part
        if ($current_part !== '') {
            $parts[] = $current_part;
        }
        
        return $parts;
    }
    
    /**
     * Create slug from text
     */
    private function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
    
    /**
     * Load and parse a template from the database
     */
    private function loadAndParseTemplate($template_name, $parameters = []) {
        try {
            // Load the template from database - try multiple formats
            global $pdo;
            $normalized_name = str_replace(' ', '_', $template_name);
            $slug = $this->createSlug($template_name);
            
            $stmt = $pdo->prepare("SELECT content FROM wiki_templates WHERE name = ? OR name = ? OR slug = ? OR slug = ?");
            $stmt->execute([$template_name, $normalized_name, $template_name, $slug]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$template) {
                return "<div class=\"template-error\">Template not found: $template_name</div>";
            }
            
            // Parse the template content with parameters
            require_once __DIR__ . '/AdvancedTemplateParser.php';
            $parser = new AdvancedTemplateParser($pdo);
            $parsed_content = $parser->parse($template['content'], $parameters);
            
            return $parsed_content;
            
        } catch (Exception $e) {
            return "<div class=\"template-error\">Error loading template $template_name: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    /**
     * Parse categories: [[Category:Name]]
     */
    private function parseCategories($content) {
        $pattern = '/\[\[Category:([^\]]+)\]\]/';
        
        return preg_replace_callback($pattern, function($matches) {
            $category_name = trim($matches[1]);
            $this->categories[] = $category_name;
            
            // Don't display category links in content, just collect them
            return '';
        }, $content);
    }
    
    /**
     * Parse MediaWiki-style formatting
     */
    private function parseWikiFormatting($content) {
        // Bold: '''text'''
        $content = preg_replace('/\'\'\'(.*?)\'\'\'/s', '<strong>$1</strong>', $content);
        
        // Italic: ''text''
        $content = preg_replace('/\'\'(.*?)\'\'/s', '<em>$1</em>', $content);
        
        return $content;
    }
    
    /**
     * Parse magic words: {{PAGENAME}}, {{CURRENTYEAR}}, etc.
     */
    private function parseMagicWords($content) {
        $pattern = '/\{\{([A-Z_]+)\}\}/';
        
        return preg_replace_callback($pattern, function($matches) {
            $magic_word = $matches[1];
            return $this->getMagicWordValue($magic_word);
        }, $content);
    }
    
    /**
     * Get magic word value
     */
    private function getMagicWordValue($magic_word) {
        switch ($magic_word) {
            case 'PAGENAME':
                return $this->getCurrentPageName();
            case 'CURRENTYEAR':
                return date('Y');
            case 'CURRENTMONTH':
                return date('F');
            case 'CURRENTDAY':
                return date('j');
            case 'CURRENTTIME':
                return date('H:i');
            case 'CURRENTTIMESTAMP':
                return date('Y-m-d H:i:s');
            case 'SITENAME':
                return $this->getSiteName();
            case 'SERVER':
                return $_SERVER['SERVER_NAME'] ?? 'localhost';
            case 'PAGELANGUAGE':
                return 'en';
            default:
                return '{{' . $magic_word . '}}'; // Return unchanged if not recognized
        }
    }
    
    /**
     * Initialize magic words
     */
    private function initializeMagicWords() {
        $this->magic_words = [
            'PAGENAME', 'CURRENTYEAR', 'CURRENTMONTH', 'CURRENTDAY',
            'CURRENTTIME', 'CURRENTTIMESTAMP', 'SITENAME', 'SERVER', 'PAGELANGUAGE'
        ];
    }
    
    /**
     * Parse inline wiki syntax (for table cells)
     */
    private function parseInlineWiki($content) {
        // Parse wiki links
        $content = $this->parseWikiLinks($content);
        
        // Parse basic formatting
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
        
        return $content;
    }
    
    /**
     * Get current page name
     */
    private function getCurrentPageName() {
        // This would be set by the calling code
        return $GLOBALS['current_page_name'] ?? 'Unknown Page';
    }
    
    /**
     * Get site name
     */
    private function getSiteName() {
        return $GLOBALS['site_name'] ?? 'IslamWiki';
    }
    
    /**
     * Get parsed categories
     */
    public function getCategories() {
        return $this->categories;
    }
    
    /**
     * Get parsed references
     */
    public function getReferences() {
        return $this->references;
    }
}
