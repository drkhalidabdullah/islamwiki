<?php

require_once __DIR__ . '/../wiki_functions.php';

/**
 * Template Parser with MediaWiki-style features
 * Supports named parameters, conditional logic, and template inheritance
 */
class TemplateParser {
    
    private $pdo;
    private $template_cache = [];
    private $recursion_depth = 0;
    private $max_recursion = 10;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Parse template with advanced features
     */
    public function parseTemplate($template_name, $parameters = []) {
        // Prevent infinite recursion
        if ($this->recursion_depth >= $this->max_recursion) {
            return "{{Template recursion limit exceeded: $template_name}}";
        }
        
        $this->recursion_depth++;
        
        try {
            // Handle special TOC limit template
            if (strtolower($template_name) === 'toc limit') {
                $limit = isset($parameters['limit']) ? (int)$parameters['limit'] : 0;
                return '<!-- TOC_LIMIT:' . $limit . ' -->';
            }
            
            $template = $this->getTemplate($template_name);
            if (!$template) {
                return "{{Template not found: $template_name}}";
            }
            
            $content = $template['content'];
            
            // Use improved parsing approach
            $content = $this->parseTemplateContent($content, $parameters);
            
            // Parse wiki links in template content
            $content = $this->parseWikiLinks($content);
            
            $this->recursion_depth--;
            return $content;
            
        } catch (Exception $e) {
            $this->recursion_depth--;
            return "{{Template error: " . $e->getMessage() . "}}";
        }
    }
    
    /**
     * Parse text containing templates
     */
    public function parse($text, $parameters = []) {
        $this->recursion_depth = 0;
        // First parse template content (parameters, conditionals, etc.)
        $text = $this->parseTemplateContent($text, $parameters);
        // Then parse any actual templates
        $text = $this->parseTemplatesRecursive($text, $parameters);
        // Finally parse wiki links
        return $this->parseWikiLinks($text);
    }
    
    /**
     * Recursively parse templates in text
     */
    private function parseTemplatesRecursive($text, $parameters = []) {
        $this->recursion_depth++;
        if ($this->recursion_depth > $this->max_recursion) {
            return '{{ERROR: Template recursion depth exceeded}}';
        }

        $pos = 0;
        $result = '';
        
        while ($pos < strlen($text)) {
            $open_pos = strpos($text, '{{', $pos);
            if ($open_pos === false) {
                $result .= substr($text, $pos);
                break;
            }
            
            // Add content before the template
            $result .= substr($text, $pos, $open_pos - $pos);
            
            // Find the matching closing braces
            $brace_count = 0;
            $template_start = $open_pos + 2;
            $template_end = $template_start;
            
            for ($i = $template_start; $i < strlen($text); $i++) {
                if ($text[$i] === '{') {
                    $brace_count++;
                } elseif ($text[$i] === '}') {
                    if ($brace_count === 0) {
                        // Found first closing brace, now look for the second one
                        if ($i + 1 < strlen($text) && $text[$i + 1] === '}') {
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
                $template_content = substr($text, $template_start, $template_end - $template_start);
                $parsed_template = $this->parseSingleTemplate($template_content, $parameters);
                $result .= $parsed_template;
                $pos = $template_end + 2; // Skip both closing braces }}
            } else {
                // No matching closing brace found, treat as regular text
                $result .= substr($text, $open_pos, 2);
                $pos = $open_pos + 2;
            }
        }
        
        $new_text = $result;

        $this->recursion_depth--;
        return $new_text;
    }
    
    /**
     * Parse a single template
     */
    private function parseSingleTemplate($template_content, $parameters = []) {
        $template_content = trim($template_content);
        $parts = explode('|', $template_content);
        $template_name = trim($parts[0]);

        $template_params = [];
        for ($i = 1; $i < count($parts); $i++) {
            $param = trim($parts[$i]);
            if (strpos($param, '=') !== false) {
                list($key, $value) = explode('=', $param, 2);
                $template_params[trim($key)] = trim($value);
            } else {
                $template_params[$i] = $param; // Positional parameters
            }
        }

        // Merge provided parameters with template-specific parameters
        $current_parameters = array_merge($parameters, $template_params);

        // Handle parser functions like #if, #ifeq
        if (strpos($template_name, '#if:') === 0) {
            return $this->parseConditionalFunction($template_content, $current_parameters);
        }
        if (strpos($template_name, '#ifeq:') === 0) {
            return $this->parseIfeqFunction($template_content, $current_parameters);
        }

        $template = $this->getTemplate($template_name);
        if (!$template) {
            return "{{Template not found: $template_name}}";
        }
        
        $content = $template['content'];
        
        // Use improved parsing approach
        $content = $this->parseTemplateContent($content, $current_parameters);
        
        return $content;
    }
    
    /**
     * Get template from database with caching
     */
    private function getTemplate($template_name) {
        if (isset($this->template_cache[$template_name])) {
            return $this->template_cache[$template_name];
        }
        
        // Only normalize spaces to underscores for template names (not for other content)
        $normalized_name = str_replace(' ', '_', $template_name);
        $slug = $this->createSlug($template_name);
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM wiki_templates 
            WHERE name = ? OR name = ? OR slug = ? OR slug = ?
        ");
        $stmt->execute([$template_name, $normalized_name, $template_name, $slug]);
        $template = $stmt->fetch();
        
        if ($template) {
            $this->template_cache[$template_name] = $template;
        }
        
        return $template;
    }
    
    /**
     * Parse named parameters: {{param_name|value}} and {{{param_name|value}}}
     */
    private function parseNamedParameters($content, $parameters) {
        // Parse triple brace parameters: {{{param_name|value}}}
        $content = preg_replace_callback('/\{\{\{([^|{}]+)\|([^}]+)\}\}\}/', function($matches) use ($parameters) {
            $param_name = trim($matches[1]);
            $default_value = trim($matches[2]);
            
            if (isset($parameters[$param_name])) {
                return $parameters[$param_name];
            } elseif (isset($parameters[$param_name . '_default'])) {
                return $parameters[$param_name . '_default'];
            } else {
                return $default_value;
            }
        }, $content);
        
        // Parse named parameters: {{param_name|value}}
        $content = preg_replace_callback('/\{\{([^|{}]+)\|([^}]+)\}\}/', function($matches) use ($parameters) {
            $param_name = trim($matches[1]);
            $default_value = trim($matches[2]);
            
            if (isset($parameters[$param_name])) {
                return $parameters[$param_name];
            } elseif (isset($parameters[$param_name . '_default'])) {
                return $parameters[$param_name . '_default'];
            } else {
                return $default_value;
            }
        }, $content);
        
        // Parse numbered parameters: {{1|default}}
        $content = preg_replace_callback('/\{\{(\d+)\|([^}]*)\}\}/', function($matches) use ($parameters) {
            $param_num = (int)$matches[1];
            $default_value = trim($matches[2]);
            
            if (isset($parameters[$param_num])) {
                return $parameters[$param_num];
            } else {
                return $default_value;
            }
        }, $content);
        
        return $content;
    }
    
    /**
     * Parse conditional logic: {{#if:condition|true_value|false_value}}
     */
    private function parseConditionals($content, $parameters) {
        // Parse #if conditionals with a more flexible approach
        $content = preg_replace_callback('/\{\{#if:([^|{}]+)\|([^|{}]+)\|([^}]+)\}\}/', function($matches) use ($parameters) {
            $condition = trim($matches[1]);
            $true_value = trim($matches[2]);
            $false_value = trim($matches[3]);
            
            // Handle nested parameter syntax in condition
            $condition = $this->parseNestedParameters($condition, $parameters);
            
            if ($this->evaluateCondition($condition, $parameters)) {
                return $true_value;
            } else {
                return $false_value;
            }
        }, $content);
        
        // Parse #ifeq conditionals
        $content = preg_replace_callback('/\{\{#ifeq:([^|{}]+)\|([^|{}]+)\|([^|{}]+)\|([^}]+)\}\}/', function($matches) use ($parameters) {
            $value1 = trim($matches[1]);
            $value2 = trim($matches[2]);
            $true_value = trim($matches[3]);
            $false_value = trim($matches[4]);
            
            // Handle nested parameter syntax
            $value1 = $this->parseNestedParameters($value1, $parameters);
            $value2 = $this->parseNestedParameters($value2, $parameters);
            
            if ($value1 === $value2) {
                return $true_value;
            } else {
                return $false_value;
            }
        }, $content);
        
        return $content;
    }
    
    /**
     * Improved template content parsing
     */
    private function parseTemplateContent($content, $parameters) {
        // Step 1: Parse triple brace parameters recursively
        $content = $this->parseTripleBraces($content, $parameters);
        
        // Step 2: Parse conditionals
        $content = $this->parseConditionalsImproved($content, $parameters);
        
        // Step 3: Parse remaining double brace parameters
        $content = $this->parseDoubleBraces($content, $parameters);
        
        // Step 4: Parse magic words
        $content = $this->parseTemplateMagicWords($content, $parameters);
        
        return $content;
    }
    
    /**
     * Parse triple brace parameters: {{{param|default}}}
     */
    private function parseTripleBraces($content, $parameters) {
        // Use a more robust approach to handle nested content
        $pos = 0;
        $result = '';
        
        while ($pos < strlen($content)) {
            $open_pos = strpos($content, '{{{', $pos);
            if ($open_pos === false) {
                $result .= substr($content, $pos);
                break;
            }
            
            // Add content before the triple braces
            $result .= substr($content, $pos, $open_pos - $pos);
            
            // Find the closing triple braces first
            $close_pos = strpos($content, '}}}', $open_pos + 3);
            if ($close_pos === false) {
                // No closing braces found, treat as regular text
                $result .= substr($content, $open_pos, 3);
                $pos = $open_pos + 3;
                continue;
            }
            
            // Check if there's a pipe separator before the closing braces
            $pipe_pos = strpos($content, '|', $open_pos + 3);
            if ($pipe_pos !== false && $pipe_pos < $close_pos) {
                // Has pipe separator, extract parameter name and default value
                $param_name = trim(substr($content, $open_pos + 3, $pipe_pos - $open_pos - 3));
                $default_value = trim(substr($content, $pipe_pos + 1, $close_pos - $pipe_pos - 1));
            } else {
                // No pipe separator, just parameter name
                $param_name = trim(substr($content, $open_pos + 3, $close_pos - $open_pos - 3));
                $default_value = '';
            }
            
            if (isset($parameters[$param_name])) {
                $result .= $parameters[$param_name];
            } else {
                // Recursively parse the default value for nested parameters
                $result .= $this->parseTripleBraces($default_value, $parameters);
            }
            
            $pos = $close_pos + 3;
        }
        
        return $result;
    }
    
    /**
     * Parse conditionals with improved regex
     */
    private function parseConditionalsImproved($content, $parameters) {
        // Parse {{#if:condition|true|false}} syntax with more flexible matching
        return preg_replace_callback('/\{\{#if:([^|]+)\|([^|]+)\|([^}]+)\}\}/', function($matches) use ($parameters) {
            $condition = trim($matches[1]);
            $true_value = trim($matches[2]);
            $false_value = trim($matches[3]);
            
            // Check if condition is a parameter
            if (isset($parameters[$condition])) {
                $condition_value = $parameters[$condition];
            } else {
                $condition_value = $condition;
            }
            
            return !empty($condition_value) ? $true_value : $false_value;
        }, $content);
    }
    
    /**
     * Parse double brace parameters: {{param|default}} and {{param}}
     */
    private function parseDoubleBraces($content, $parameters) {
        // Parse {{param|default}} syntax - only match if it's not part of a wiki link
        $content = preg_replace_callback('/\{\{([^|{}]+)\|([^}]+)\}\}/', function($matches) use ($parameters) {
            $param_name = trim($matches[1]);
            $default_value = trim($matches[2]);
            
            // Skip if this looks like it might be part of a wiki link
            if (strpos($matches[0], '[') !== false || strpos($matches[0], ']') !== false) {
                return $matches[0];
            }
            
            if (isset($parameters[$param_name])) {
                return $parameters[$param_name];
            } else {
                return $default_value;
            }
        }, $content);
        
        // Parse {{param}} syntax - only match if it's not part of a wiki link
        $content = preg_replace_callback('/\{\{([^|{}]+)\}\}/', function($matches) use ($parameters) {
            $param_name = trim($matches[1]);
            
            // Skip if this looks like it might be part of a wiki link
            if (strpos($matches[0], '[') !== false || strpos($matches[0], ']') !== false) {
                return $matches[0];
            }
            
            if (isset($parameters[$param_name])) {
                return $parameters[$param_name];
            } else {
                return $matches[0]; // Return unchanged if not found
            }
        }, $content);
        
        // Clean up any remaining single braces that might have been created
        $content = preg_replace('/\{([^}]+)\}/', '$1', $content);
        
        return $content;
    }
    
    /**
     * Parse nested parameters like {{{param1|{{{param2|default}}}}}}
     */
    private function parseNestedParameters($content, $parameters) {
        // Parse triple brace parameters first
        $content = preg_replace_callback('/\{\{\{([^|{}]+)\|([^}]+)\}\}\}/', function($matches) use ($parameters) {
            $param_name = trim($matches[1]);
            $default_value = trim($matches[2]);
            
            // Recursively parse the default value for nested parameters
            $default_value = $this->parseNestedParameters($default_value, $parameters);
            
            if (isset($parameters[$param_name])) {
                return $parameters[$param_name];
            } else {
                return $default_value;
            }
        }, $content);
        
        return $content;
    }
    
    /**
     * Parse loops: {{#foreach:list|item}}
     */
    private function parseLoops($content, $parameters) {
        $content = preg_replace_callback('/\{\{#foreach:([^|]*)\|([^}]*)\}\}/', function($matches) use ($parameters) {
            $list_param = trim($matches[1]);
            $item_template = trim($matches[2]);
            
            if (isset($parameters[$list_param]) && is_array($parameters[$list_param])) {
                $result = '';
                foreach ($parameters[$list_param] as $item) {
                    $item_params = array_merge($parameters, ['item' => $item]);
                    $result .= $this->parseTemplate($item_template, $item_params);
                }
                return $result;
            }
            
            return '';
        }, $content);
        
        return $content;
    }
    
    /**
     * Parse magic words in templates
     */
    private function parseTemplateMagicWords($content, $parameters) {
        $magic_words = [
            'PAGENAME' => $parameters['PAGENAME'] ?? 'Unknown Page',
            'CURRENTYEAR' => date('Y'),
            'CURRENTMONTH' => date('F'),
            'CURRENTDAY' => date('j'),
            'CURRENTTIME' => date('H:i'),
            'CURRENTTIMESTAMP' => date('Y-m-d H:i:s'),
            'SITENAME' => $parameters['SITENAME'] ?? 'IslamWiki',
            'SERVER' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'PAGELANGUAGE' => 'en'
        ];
        
        foreach ($magic_words as $word => $value) {
            $content = str_replace('{{' . $word . '}}', $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Parse nested templates
     */
    private function parseNestedTemplates($content) {
        $pattern = '/\{\{([^}]+)\}\}/';
        
        return preg_replace_callback($pattern, function($matches) {
            $template_content = trim($matches[1]);
            $parts = explode('|', $template_content);
            $template_name = trim($parts[0]);
            
            // Skip magic words
            if (in_array($template_name, ['PAGENAME', 'CURRENTYEAR', 'CURRENTMONTH', 'CURRENTDAY', 'CURRENTTIME', 'CURRENTTIMESTAMP', 'SITENAME', 'SERVER', 'PAGELANGUAGE'])) {
                return $matches[0];
            }
            
            $parameters = [];
            for ($i = 1; $i < count($parts); $i++) {
                $param = trim($parts[$i]);
                if (strpos($param, '=') !== false) {
                    list($key, $value) = explode('=', $param, 2);
                    $parameters[trim($key)] = trim($value);
                } else {
                    $parameters[$i] = $param;
                }
            }
            
            return $this->parseTemplate($template_name, $parameters);
        }, $content);
    }
    
    /**
     * Evaluate condition for #if statements
     */
    private function evaluateCondition($condition, $parameters) {
        $condition = trim($condition);
        
        // Check if parameter exists and is not empty
        if (isset($parameters[$condition])) {
            return !empty($parameters[$condition]);
        }
        
        // Check for specific values
        if (preg_match('/^(.+)\s*==\s*(.+)$/', $condition, $matches)) {
            $left = trim($matches[1]);
            $right = trim($matches[2]);
            
            if (isset($parameters[$left])) {
                return $parameters[$left] == $right;
            }
        }
        
        // Check for numeric comparisons
        if (preg_match('/^(.+)\s*>\s*(\d+)$/', $condition, $matches)) {
            $param = trim($matches[1]);
            $value = (int)$matches[2];
            
            if (isset($parameters[$param]) && is_numeric($parameters[$param])) {
                return (int)$parameters[$param] > $value;
            }
        }
        
        return !empty($condition);
    }
    
    /**
     * Create template with validation
     */
    public function createTemplate($name, $content, $description = '', $parameters = []) {
        $slug = $this->createSlug($name);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO wiki_templates (name, slug, namespace, template_type, content, description, parameters, created_by, updated_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $name, $slug, 'Template', 'other', $content, $description, 
            json_encode($parameters), 
            $_SESSION['user_id'] ?? 1, 
            $_SESSION['user_id'] ?? 1
        ]);
    }
    
    /**
     * Update template
     */
    public function updateTemplate($id, $name, $content, $description = '', $parameters = []) {
        $slug = $this->createSlug($name);
        
        $stmt = $this->pdo->prepare("
            UPDATE wiki_templates 
            SET name = ?, slug = ?, content = ?, description = ?, parameters = ?, updated_by = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $name, $slug, $content, $description, 
            json_encode($parameters), 
            $_SESSION['user_id'] ?? 1, 
            $id
        ]);
    }
    
    /**
     * Parse wiki links in template content
     */
    private function parseWikiLinks($content) {
        // Pattern to match [[Page Name]] or [[Page Name|Display Text]]
        $pattern = '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        
        return preg_replace_callback($pattern, function($matches) {
            $page_name = trim($matches[1]);
            $display_text = isset($matches[2]) ? trim($matches[2]) : $page_name;
            
            // Create slug from page name
            $slug = $this->createSlug($page_name);
            
            // Check if page exists (simplified check - you can enhance this)
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
     * Check if a page exists in the database
     */
    private function pageExists($slug) {
        try {
            $pdo = $this->pdo;
            // Check for both hyphen and underscore versions
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE (slug = ? OR slug = ?) AND status = 'published'");
            $underscore_slug = str_replace('-', '_', $slug);
            $stmt->execute([$slug, $underscore_slug]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            // If there's an error, assume page doesn't exist
            return false;
        }
    }
    
    /**
     * Create URL-friendly slug
     */
    private function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
