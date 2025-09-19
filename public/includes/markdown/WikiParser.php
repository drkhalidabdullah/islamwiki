<?php

require_once __DIR__ . '/MarkdownParser.php';
require_once __DIR__ . '/TemplateParser.php';

/**
 * Comprehensive Wiki Parser with MediaWiki-style features
 * Supports tables, references, magic words, categories, templates, and security
 */
class WikiParser extends MarkdownParser {
    
    // Collections
    private $references = [];
    private $categories = [];
    private $headings = [];
    
    // TOC settings
    private $toc_enabled = true;
    private $toc_forced = false;
    private $toc_position = 'auto';
    private $toc_limit = 0;
    private $notitle = false;
    private $nocat = false;
    
    // Template processing flag
    private $is_processing_templates = false;
    private $template_recursion_depth = 0;
    private $max_template_recursion = 3;
    
    // Security settings
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
    
    // Template parser
    private $template_parser;
    
    public function __construct($wiki_base_url = 'wiki/') {
        parent::__construct($wiki_base_url);
        
        // Initialize template parser if PDO is available
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
            try {
                $this->template_parser = new TemplateParser($GLOBALS['pdo']);
            } catch (Exception $e) {
                error_log("Failed to initialize TemplateParser: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Parse content with all wiki features
     */
    public function parse($content) {
        // Reset collections
        $this->references = [];
        $this->categories = [];
        $this->headings = [];
        $this->toc_enabled = true;
        $this->toc_forced = false;
        $this->toc_position = 'auto';
        $this->toc_limit = 0;
        $this->notitle = false;
        $this->nocat = false;
        
        // Parse magic words first (including TOC magic words)
        $content = $this->parseMagicWords($content);
        
        // Handle TOC limit from templates
        $content = $this->parseTOCLimit($content);
        
        // Parse wiki markup headings (=heading=, ==heading==, etc.)
        $content = $this->parseWikiHeadings($content);
        
        // Parse other features
        $content = $this->parseReferences($content);
        $content = $this->parseTables($content);
        $content = $this->parseCategories($content);
        $content = $this->parseTemplates($content);
        
        // Handle inline templates before parent parsing
        $content = $this->handleInlineTemplates($content);
        
        // Call parent parse method (handles wiki links and markdown)
        $content = parent::parse($content);
        
        // Parse wiki formatting after wiki links to handle bold text in links
        $content = $this->parseWikiFormatting($content);
        
        // Extract headings for TOC (after content is parsed to HTML)
        $this->extractHeadings($content);
        
        // TOC is handled by JavaScript in the sidebar, not inline
        // $content = $this->addTableOfContents($content);
        
        // Add references section if any exist
        if (!empty($this->references)) {
            $content .= $this->generateReferencesSection();
        }
        
        // Sanitize HTML output for security
        $content = $this->sanitizeHtml($content);
        
        return $content;
    }
    
    /**
     * Parse MediaWiki-style magic words
     */
    private function parseMagicWords($content) {
        // Handle behavior switches (double underscores)
        $content = preg_replace_callback('/__([A-Z_]+)__/', function($matches) {
            $magic_word = $matches[1];
            return $this->handleBehaviorSwitch($magic_word);
        }, $content);
        
        // Handle variables (double braces)
        $content = preg_replace_callback('/\{\{([A-Z_]+)\}\}/', function($matches) {
            $magic_word = $matches[1];
            return $this->getMagicWordValue($magic_word);
        }, $content);
        
        // Handle parser functions
        $content = $this->parseParserFunctions($content);
        
        return $content;
    }
    
    /**
     * Handle behavior switches (__WORD__)
     */
    private function handleBehaviorSwitch($magic_word) {
        switch ($magic_word) {
            case 'NOTOC':
                $this->toc_enabled = false;
                return '';
            case 'FORCETOC':
                $this->toc_forced = true;
                return '';
            case 'TOC':
                $this->toc_position = 'manual';
                return '<!-- TOC_POSITION -->';
            case 'NOTITLE':
                $this->notitle = true;
                return '';
            case 'NOCAT':
                $this->nocat = true;
                return '';
            case 'NOEDITSECTION':
                // Not implemented yet
                return '';
            case 'NOCONTENTCONVERTER':
                // Not implemented yet
                return '';
            case 'NOGALLERY':
                // Not implemented yet
                return '';
            case 'FORCEUPPERCASE':
                // Not implemented yet
                return '';
            case 'DISAMBIG':
                // Not implemented yet
                return '';
            case 'NEWSECTIONLINK':
                // Not implemented yet
                return '';
            case 'NONEWSECTIONLINK':
                // Not implemented yet
                return '';
            case 'HIDDENCAT':
                // Not implemented yet
                return '';
            case 'EXPECTUNUSEDCAT':
                // Not implemented yet
                return '';
            case 'INDEX':
                // Not implemented yet
                return '';
            case 'NOINDEX':
                // Not implemented yet
                return '';
            case 'STATICREDIRECT':
                // Not implemented yet
                return '';
            case 'NOGLOBAL':
                // Not implemented yet
                return '';
            case 'DISPLAYTITLE':
                // Not implemented yet
                return '';
            case 'DEFAULTSORT':
                // Not implemented yet
                return '';
            case 'DEFAULTSORTKEY':
                // Not implemented yet
                return '';
            case 'NOCONTENTCONVERTER':
                // Not implemented yet
                return '';
            case 'NOCC':
                // Not implemented yet
                return '';
            case 'NOTITLECONVERTER':
                // Not implemented yet
                return '';
            case 'NOTC':
                // Not implemented yet
                return '';
            case 'NOGALLERY':
                // Not implemented yet
                return '';
            case 'FORCEUPPERCASE':
                // Not implemented yet
                return '';
            case 'FORCELOWERCASE':
                // Not implemented yet
                return '';
            default:
                return '__' . $magic_word . '__'; // Return unchanged if not recognized
        }
    }
    
    /**
     * Get magic word value for variables
     */
    private function getMagicWordValue($magic_word) {
        switch ($magic_word) {
            // Date and time
            case 'CURRENTYEAR':
                return date('Y');
            case 'CURRENTMONTH':
                return date('F');
            case 'CURRENTMONTHNAME':
                return date('F');
            case 'CURRENTMONTHABBREV':
                return date('M');
            case 'CURRENTDAY':
                return date('j');
            case 'CURRENTDAY2':
                return date('d');
            case 'CURRENTDAYNAME':
                return date('l');
            case 'CURRENTDOW':
                return date('w');
            case 'CURRENTTIME':
                return date('H:i');
            case 'CURRENTHOUR':
                return date('H');
            case 'CURRENTWEEK':
                return date('W');
            case 'CURRENTTIMESTAMP':
                return date('YmdHis');
            case 'LOCALYEAR':
                return date('Y');
            case 'LOCALMONTH':
                return date('F');
            case 'LOCALMONTHNAME':
                return date('F');
            case 'LOCALMONTHABBREV':
                return date('M');
            case 'LOCALDAY':
                return date('j');
            case 'LOCALDAY2':
                return date('d');
            case 'LOCALDAYNAME':
                return date('l');
            case 'LOCALDOW':
                return date('w');
            case 'LOCALTIME':
                return date('H:i');
            case 'LOCALHOUR':
                return date('H');
            case 'LOCALWEEK':
                return date('W');
            case 'LOCALTIMESTAMP':
                return date('YmdHis');
            
            // Technical metadata
            case 'PAGENAME':
                return $this->getCurrentPageName();
            case 'PAGENAMEE':
                return urlencode($this->getCurrentPageName());
            case 'FULLPAGENAME':
                return $this->getCurrentPageName();
            case 'FULLPAGENAMEE':
                return urlencode($this->getCurrentPageName());
            case 'BASEPAGENAME':
                return $this->getBasePageName();
            case 'BASEPAGENAMEE':
                return urlencode($this->getBasePageName());
            case 'SUBPAGENAME':
                return $this->getSubPageName();
            case 'SUBPAGENAMEE':
                return urlencode($this->getSubPageName());
            case 'ROOTPAGENAME':
                return $this->getRootPageName();
            case 'ROOTPAGENAMEE':
                return urlencode($this->getRootPageName());
            case 'TALKPAGENAME':
                return $this->getTalkPageName();
            case 'TALKPAGENAMEE':
                return urlencode($this->getTalkPageName());
            case 'SUBJECTPAGENAME':
                return $this->getSubjectPageName();
            case 'SUBJECTPAGENAMEE':
                return urlencode($this->getSubjectPageName());
            case 'ARTICLEPAGENAME':
                return $this->getArticlePageName();
            case 'ARTICLEPAGENAMEE':
                return urlencode($this->getArticlePageName());
            
            // Site information
            case 'SITENAME':
                return $this->getSiteName();
            case 'SERVER':
                return $_SERVER['SERVER_NAME'] ?? 'localhost';
            case 'SERVERNAME':
                return $_SERVER['SERVER_NAME'] ?? 'localhost';
            case 'SCRIPTPATH':
                return '';
            case 'STYLEPATH':
                return '/skins';
            case 'CURRENTVERSION':
                return '1.0.0';
            case 'REVISIONID':
                return '1';
            case 'REVISIONDAY':
                return date('j');
            case 'REVISIONDAY2':
                return date('d');
            case 'REVISIONMONTH':
                return date('F');
            case 'REVISIONMONTH1':
                return date('n');
            case 'REVISIONYEAR':
                return date('Y');
            case 'REVISIONTIMESTAMP':
                return date('YmdHis');
            case 'REVISIONUSER':
                return $this->getCurrentUser();
            
            // Localization
            case 'CONTENTLANG':
                return 'en';
            case 'PAGELANGUAGE':
                return 'en';
            case 'DIRLTR':
                return 'ltr';
            case 'DIRRTL':
                return 'rtl';
            
            // Statistics
            case 'NUMBEROFARTICLES':
                return $this->getNumberOfArticles();
            case 'NUMBEROFFILES':
                return $this->getNumberOfFiles();
            case 'NUMBEROFEDITS':
                return $this->getNumberOfEdits();
            case 'NUMBEROFVIEWS':
                return $this->getNumberOfViews();
            case 'NUMBEROFUSERS':
                return $this->getNumberOfUsers();
            case 'NUMBEROFADMINS':
                return $this->getNumberOfAdmins();
            case 'NUMBEROFPAGES':
                return $this->getNumberOfPages();
            
            // Namespaces
            case 'NS':
                return '0';
            case 'NSE':
                return '0';
            case 'SUBJECTSPACE':
                return '0';
            case 'SUBJECTSPACEE':
                return '0';
            case 'TALKSPACE':
                return '1';
            case 'TALKSPACEE':
                return '1';
            
            default:
                return '{{' . $magic_word . '}}'; // Return unchanged if not recognized
        }
    }
    
    /**
     * Parse parser functions
     */
    private function parseParserFunctions($content) {
        // Handle #if functions
        $content = preg_replace_callback('/\{\{#if:([^|]*)\|([^|]*)\|?([^}]*)\}\}/', function($matches) {
            $condition = trim($matches[1]);
            $true_value = $matches[2];
            $false_value = isset($matches[3]) ? $matches[3] : '';
            
            return !empty($condition) ? $true_value : $false_value;
        }, $content);
        
        // Handle #ifeq functions
        $content = preg_replace_callback('/\{\{#ifeq:([^|]*)\|([^|]*)\|([^|]*)\|?([^}]*)\}\}/', function($matches) {
            $value1 = trim($matches[1]);
            $value2 = trim($matches[2]);
            $true_value = $matches[3];
            $false_value = isset($matches[4]) ? $matches[4] : '';
            
            return ($value1 === $value2) ? $true_value : $false_value;
        }, $content);
        
        // Handle #switch functions
        $content = preg_replace_callback('/\{\{#switch:([^|]*)\|([^}]*)\}\}/', function($matches) {
            $value = trim($matches[1]);
            $cases = $matches[2];
            
            // Parse cases
            $case_pattern = '/([^=]*)=([^|]*)\|?/';
            preg_match_all($case_pattern, $cases, $case_matches, PREG_SET_ORDER);
            
            foreach ($case_matches as $case) {
                $case_value = trim($case[1]);
                $case_result = $case[2];
                
                if ($case_value === $value) {
                    return $case_result;
                }
            }
            
            // Check for default case
            if (preg_match('/#default=([^|]*)\|?/', $cases, $default_matches)) {
                return $default_matches[1];
            }
            
            return '';
        }, $content);
        
        // Handle #expr functions (basic math)
        $content = preg_replace_callback('/\{\{#expr:([^}]*)\}\}/', function($matches) {
            $expression = $matches[1];
            return $this->evaluateExpression($expression);
        }, $content);
        
        // Handle #len functions
        $content = preg_replace_callback('/\{\{#len:([^}]*)\}\}/', function($matches) {
            return strlen($matches[1]);
        }, $content);
        
        // Handle #pos functions
        $content = preg_replace_callback('/\{\{#pos:([^|]*)\|([^}]*)\}\}/', function($matches) {
            $haystack = $matches[1];
            $needle = $matches[2];
            $pos = strpos($haystack, $needle);
            return $pos !== false ? $pos + 1 : 0;
        }, $content);
        
        // Handle #rpos functions
        $content = preg_replace_callback('/\{\{#rpos:([^|]*)\|([^}]*)\}\}/', function($matches) {
            $haystack = $matches[1];
            $needle = $matches[2];
            $pos = strrpos($haystack, $needle);
            return $pos !== false ? $pos + 1 : 0;
        }, $content);
        
        // Handle #sub functions
        $content = preg_replace_callback('/\{\{#sub:([^|]*)\|([^|]*)\|?([^}]*)\}\}/', function($matches) {
            $string = $matches[1];
            $start = (int)$matches[2] - 1; // Convert to 0-based
            $length = isset($matches[3]) ? (int)$matches[3] : null;
            
            if ($length !== null) {
                return substr($string, $start, $length);
            } else {
                return substr($string, $start);
            }
        }, $content);
        
        // Handle #replace functions
        $content = preg_replace_callback('/\{\{#replace:([^|]*)\|([^|]*)\|([^}]*)\}\}/', function($matches) {
            $string = $matches[1];
            $search = $matches[2];
            $replace = $matches[3];
            return str_replace($search, $replace, $string);
        }, $content);
        
        return $content;
    }
    
    /**
     * Evaluate mathematical expressions
     */
    private function evaluateExpression($expression) {
        // Basic math operations
        $expression = preg_replace('/\s+/', '', $expression);
        
        // Handle basic arithmetic
        if (preg_match('/^[\d\+\-\*\/\(\)\.\s]+$/', $expression)) {
            try {
                // Simple evaluation (be careful with eval in production)
                $result = eval("return $expression;");
                return is_numeric($result) ? $result : '';
            } catch (Exception $e) {
                return '';
            }
        }
        
        return '';
    }
    
    /**
     * Get current page name
     */
    private function getCurrentPageName() {
        return $GLOBALS['current_page_name'] ?? 'Main Page';
    }
    
    /**
     * Get base page name (without subpage)
     */
    private function getBasePageName() {
        $page_name = $this->getCurrentPageName();
        $parts = explode('/', $page_name);
        return $parts[0];
    }
    
    /**
     * Get subpage name
     */
    private function getSubPageName() {
        $page_name = $this->getCurrentPageName();
        $parts = explode('/', $page_name);
        return count($parts) > 1 ? end($parts) : '';
    }
    
    /**
     * Get root page name
     */
    private function getRootPageName() {
        $page_name = $this->getCurrentPageName();
        $parts = explode('/', $page_name);
        return $parts[0];
    }
    
    /**
     * Get talk page name
     */
    private function getTalkPageName() {
        $page_name = $this->getCurrentPageName();
        return "Talk:$page_name";
    }
    
    /**
     * Get subject page name
     */
    private function getSubjectPageName() {
        $page_name = $this->getCurrentPageName();
        return $page_name;
    }
    
    /**
     * Get article page name
     */
    private function getArticlePageName() {
        $page_name = $this->getCurrentPageName();
        return $page_name;
    }
    
    /**
     * Get site name
     */
    private function getSiteName() {
        return $GLOBALS['site_name'] ?? 'Wiki';
    }
    
    /**
     * Get current user
     */
    private function getCurrentUser() {
        return $_SESSION['username'] ?? 'Anonymous';
    }
    
    /**
     * Get number of articles
     */
    private function getNumberOfArticles() {
        // This would query the database
        return '100';
    }
    
    /**
     * Get number of files
     */
    private function getNumberOfFiles() {
        return '50';
    }
    
    /**
     * Get number of edits
     */
    private function getNumberOfEdits() {
        return '1000';
    }
    
    /**
     * Get number of views
     */
    private function getNumberOfViews() {
        return '5000';
    }
    
    /**
     * Get number of users
     */
    private function getNumberOfUsers() {
        return '25';
    }
    
    /**
     * Get number of admins
     */
    private function getNumberOfAdmins() {
        return '2';
    }
    
    /**
     * Get number of pages
     */
    private function getNumberOfPages() {
        return '150';
    }
    
    /**
     * Parse MediaWiki-style tables: {| |}
     */
    private function parseTables($content) {
        $pattern = '/\{\|(.*?)\|\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $table_content = $matches[1];
            $rows = explode('|-', $table_content);
            
            $html = '<table class="wiki-table">';
            
            foreach ($rows as $row) {
                $row = trim($row);
                if (empty($row)) continue;
                
                if (strpos($row, '|') === 0) {
                    $row = substr($row, 1);
                }
                
                $cells = explode('|', $row);
                $is_header = strpos($row, '!') === 0;
                
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $cell = trim($cell);
                    if (empty($cell)) continue;
                    
                    $cell = preg_replace('/^[!|]/', '', $cell);
                    $cell_content = $this->parseInlineWiki($cell);
                    
                    $tag = $is_header ? 'th' : 'td';
                    $html .= "<$tag>$cell_content</$tag>";
                }
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            return $html;
        }, $content);
    }
    
    /**
     * Parse references: <ref>content</ref>
     */
    private function parseReferences($content) {
        $pattern = '/<ref>(.*?)<\/ref>/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $ref_content = $matches[1];
            $ref_id = count($this->references) + 1;
            
            $this->references[] = [
                'id' => $ref_id,
                'content' => $ref_content
            ];
            
            return '<sup><a href="#ref' . $ref_id . '" id="ref' . $ref_id . '">[' . $ref_id . ']</a></sup>';
        }, $content);
    }
    
    /**
     * Parse categories: [[Category:Name]]
     */
    private function parseCategories($content) {
        $pattern = '/\[\[Category:([^\]]+)\]\]/';
        
        return preg_replace_callback($pattern, function($matches) {
            $category = trim($matches[1]);
            $this->categories[] = $category;
            return ''; // Categories are not displayed in content
        }, $content);
    }
    
    /**
     * Get parsed categories
     */
    public function getCategories() {
        return $this->categories;
    }
    
    /**
     * Parse templates: {{Template Name|param1|param2}}
     */
    private function parseTemplates($content) {
        if (!$this->template_parser) {
            return $content;
        }
        
        // First handle includeonly/noinclude tags
        $content = $this->parseIncludeOnlyTags($content);
        
        $pattern = '/\{\{([^{}]*(?:\{[^}]*\}[^{}]*)*)\}\}/';
        
        $result = preg_replace_callback($pattern, function($matches) {
            $template_content = $matches[1];
            
            // Check if this is an inline template (good article, etc.)
            $inline_templates = ['good article'];
            $template_name = trim(explode('|', $template_content)[0]);
            
            // Handle MediaWiki-style parser functions like {{#time:format}}
            if (preg_match('/^#([^:]+):(.+)$/', $template_content, $func_matches)) {
                $function_name = trim($func_matches[1]);
                $function_params = trim($func_matches[2]);
                
                if ($function_name === 'time') {
                    return date($function_params);
                } elseif ($function_name === 'invoke') {
                    // Handle {{#invoke:Module|function|params}}
                    $invoke_parts = explode('|', $function_params);
                    $module = trim($invoke_parts[0]);
                    $function = isset($invoke_parts[1]) ? trim($invoke_parts[1]) : 'main';
                    
                    // Parse additional parameters
                    $params = [];
                    for ($i = 2; $i < count($invoke_parts); $i++) {
                        $param = trim($invoke_parts[$i]);
                        if (strpos($param, '=') !== false) {
                            list($key, $value) = explode('=', $param, 2);
                            $params[trim($key)] = trim($value);
                        } else {
                            $params[] = $param;
                        }
                    }
                    
                    // Execute the module
                    if ($this->template_parser) {
                        return $this->template_parser->parseTemplate($module, $params);
                    }
                    
                    return "{{#invoke:$module|$function}}";
                }
                
                return "{{#$function_name:$function_params}}"; // Return unchanged if not handled
            }
            
            // Parse template name and parameters, being careful not to split wiki links
            $parts = $this->parseTemplateParameters($template_content);
            $template_name = trim(array_shift($parts));
            $parameters = [];
            
            foreach ($parts as $index => $part) {
                $part = trim($part);
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                    $parameters[trim($key)] = trim($value);
                } else {
                    $parameters[(string)($index + 1)] = $part;
                }
            }
            
            $result = $this->template_parser->parseTemplate($template_name, $parameters);
            
            // Mark inline templates for special handling
            if (in_array($template_name, $inline_templates)) {
                $result = '<!--INLINE_TEMPLATE-->' . $result . '<!--/INLINE_TEMPLATE-->';
            }
            
            return $result;
        }, $content);
        
        // Recursively process any remaining templates in the result
        // Limit recursion depth to prevent infinite loops
        // Skip recursive processing completely for now to avoid issues
        // if ($result !== $content && $this->template_recursion_depth < $this->max_template_recursion) {
        //     $this->template_recursion_depth++;
        //     $result = $this->parseTemplates($result);
        //     $this->template_recursion_depth--;
        // }
        
        return $result;
    }
    
    /**
     * Parse includeonly/noinclude tags
     */
    private function parseIncludeOnlyTags($content) {
        // Handle <includeonly>content</includeonly> - only include the content
        $content = preg_replace_callback('/<includeonly>(.*?)<\/includeonly>/s', function($matches) {
            return $matches[1]; // Return just the content inside includeonly
        }, $content);
        
        // Handle <noinclude>content</noinclude> - remove the content
        $content = preg_replace_callback('/<noinclude>(.*?)<\/noinclude>/s', function($matches) {
            return ''; // Remove noinclude content
        }, $content);
        
        return $content;
    }
    
    /**
     * Override parseWikiLinks to handle formatting in display text
     */
    protected function parseWikiLinks($content) {
        // Pattern to match [[Page Name]] or [[Page Name|Display Text]]
        $pattern = '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        
        return preg_replace_callback($pattern, function($matches) {
            $page_name = trim($matches[1]);
            $display_text = isset($matches[2]) ? trim($matches[2]) : $page_name;
            
            // Parse formatting in display text
            $display_text = $this->parseWikiFormatting($display_text);
            
            // Create slug from page name
            $slug = $this->createSlug($page_name);
            
            // Check if page exists
            $exists = $this->pageExists($slug);
            $class = $exists ? 'wiki-link' : 'wiki-link missing';
            
            return sprintf(
                '<a href="/wiki/%s" class="%s" title="%s">%s</a>',
                $slug,
                $class,
                htmlspecialchars($page_name),
                $display_text // Don't HTML-encode display text since it may contain HTML
            );
        }, $content);
    }
    
    /**
     * Parse template parameters, being careful not to split wiki links
     */
    private function parseTemplateParameters($content) {
        $parts = [];
        $current_part = '';
        $bracket_depth = 0;
        $i = 0;
        
        while ($i < strlen($content)) {
            $char = $content[$i];
            
            if ($char === '[' && $i + 1 < strlen($content) && $content[$i + 1] === '[') {
                // Start of wiki link
                $bracket_depth++;
                $current_part .= $char;
                $i++;
                $current_part .= $content[$i]; // Add the second '['
            } elseif ($char === ']' && $i + 1 < strlen($content) && $content[$i + 1] === ']') {
                // End of wiki link
                $bracket_depth--;
                $current_part .= $char;
                $i++;
                $current_part .= $content[$i]; // Add the second ']'
            } elseif ($char === '|' && $bracket_depth === 0) {
                // Parameter separator (only when not inside wiki links)
                $parts[] = $current_part;
                $current_part = '';
            } else {
                $current_part .= $char;
            }
            
            $i++;
        }
        
        // Add the last part
        if ($current_part !== '') {
            $parts[] = $current_part;
        }
        
        return $parts;
    }
    
    /**
     * Parse wiki formatting: '''bold''', ''italic'', etc.
     */
    private function parseWikiFormatting($content) {
        // Bold: '''text''' - use non-greedy matching to handle apostrophes
        $content = preg_replace('/\'\'\'(.*?)\'\'\'/s', '<strong>$1</strong>', $content);
        
        // Italic: ''text'' - use non-greedy matching to handle apostrophes
        $content = preg_replace('/\'\'(.*?)\'\'/s', '<em>$1</em>', $content);
        
        // Underline: <u>text</u>
        $content = preg_replace('/<u>([^<]+)<\/u>/', '<u>$1</u>', $content);
        
        // Strikethrough: <s>text</s>
        $content = preg_replace('/<s>([^<]+)<\/s>/', '<s>$1</s>', $content);
        
        // Code: <code>text</code>
        $content = preg_replace('/<code>([^<]+)<\/code>/', '<code>$1</code>', $content);
        
        return $content;
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
     * Parse wiki markup headings (=heading=, ==heading==, etc.)
     */
    private function parseWikiHeadings($content) {
        // Match wiki headings: =heading=, ==heading==, ===heading===, etc.
        $content = preg_replace_callback('/^(={1,6})\s*(.+?)\s*\1\s*$/m', function($matches) {
            $level = strlen($matches[1]);
            $text = trim($matches[2]);
            return "<h$level>$text</h$level>";
        }, $content);
        
        return $content;
    }
    
    /**
     * Extract headings from content for TOC generation
     */
    private function extractHeadings($content) {
        $this->headings = [];
        
        // First, try to match markdown headings (# ## ### etc.) in raw content
        preg_match_all('/^(#{1,6})\s+(.+)$/m', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $level = strlen($match[1]);
            $text = trim($match[2]);
            $id = $this->createHeadingId($text);
            
            $this->headings[] = [
                'level' => $level,
                'text' => $text,
                'id' => $id
            ];
        }
        
        // If no markdown headings found, look for HTML headings in parsed content
        if (empty($this->headings)) {
            preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $content, $html_matches, PREG_SET_ORDER);
            
            foreach ($html_matches as $match) {
                $level = (int)$match[1];
                $text = trim(strip_tags($match[2]));
                $id = $this->createHeadingId($text);
                
                $this->headings[] = [
                    'level' => $level,
                    'text' => $text,
                    'id' => $id
                ];
            }
        }
        
        // If still no headings found, look for bold text that might be treated as headings
        if (empty($this->headings)) {
            // Look for bold text patterns that could be section headers
            preg_match_all('/<strong[^>]*>(.*?)<\/strong>/i', $content, $bold_matches, PREG_SET_ORDER);
            $seen_headings = [];
            
            foreach ($bold_matches as $index => $match) {
                $text = trim(strip_tags($match[1]));
                // Only consider bold text that's reasonably long and looks like a heading
                if (strlen($text) > 3 && strlen($text) < 100 && !preg_match('/^[a-z\s]+$/', $text)) {
                    // Skip duplicates
                    if (in_array($text, $seen_headings)) {
                        continue;
                    }
                    
                    // Skip very short Arabic text, symbols, or single characters with hyphens
                    if (strlen($text) < 5 && (preg_match('/^[\p{Arabic}\s\-]+$/u', $text) || preg_match('/^[a-z\-]+$/i', $text))) {
                        continue;
                    }
                    
                    // Skip text that's mostly Arabic characters with hyphens (like س-ل-م)
                    if (preg_match('/^[\p{Arabic}\-]+$/u', $text) && strlen($text) < 10) {
                        continue;
                    }
                    
                    // Skip text that's mostly symbols or very short
                    if (preg_match('/^[\-\s]+$/', $text) || strlen(trim($text)) < 3) {
                        continue;
                    }
                    
                    $id = $this->createHeadingId($text);
                    
                    // Only add if we got a valid ID
                    if (!empty($id) && $id !== '') {
                        $this->headings[] = [
                            'level' => 2, // Default to h2 for bold text
                            'text' => $text,
                            'id' => $id
                        ];
                        $seen_headings[] = $text;
                    }
                }
            }
        }
        
        // Debug: Log extracted headings for troubleshooting
        if (empty($this->headings)) {
            error_log("WikiParser: No headings found in content. Content length: " . strlen($content));
        } else {
            error_log("WikiParser: Found " . count($this->headings) . " headings");
        }
    }
    
    /**
     * Create heading ID from text
     */
    private function createHeadingId($text) {
        // Remove HTML tags and create URL-friendly ID
        $text = strip_tags($text);
        
        // For Arabic text, transliterate to English
        if (preg_match('/[\p{Arabic}]/u', $text)) {
            // Simple transliteration for common Arabic words
            $transliterations = [
                'إسلام' => 'islam',
                'الله' => 'allah',
                'القرآن' => 'quran',
                'محمد' => 'muhammad',
                'صلاة' => 'salah',
                'زكاة' => 'zakat',
                'صوم' => 'sawm',
                'حج' => 'hajj',
                'توحيد' => 'tawhid',
                'ملائكة' => 'malaika',
                'أنبياء' => 'anbiya',
                'قيامة' => 'qiyama'
            ];
            
            foreach ($transliterations as $arabic => $english) {
                $text = str_replace($arabic, $english, $text);
            }
        }
        
        // Remove special characters but keep letters, numbers, spaces, hyphens, underscores
        $text = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $text);
        $text = preg_replace('/\s+/', '_', trim($text));
        
        // If empty after processing, create a fallback ID
        if (empty($text)) {
            $text = 'heading_' . uniqid();
        }
        
        return strtolower($text);
    }
    
    /**
     * Add table of contents to content
     */
    private function addTableOfContents($content) {
        // Check if TOC should be shown
        if (!$this->toc_enabled || (count($this->headings) < 3 && !$this->toc_forced)) {
            return $content;
        }
        
        // Generate TOC HTML
        $toc_html = $this->generateTableOfContents();
        
        if ($this->toc_position === 'manual') {
            // Replace TOC placeholder
            $content = str_replace('<!-- TOC_POSITION -->', $toc_html, $content);
        } else {
            // Auto-position: after lead, before first heading
            $content = $this->insertTOCAfterLead($content, $toc_html);
        }
        
        return $content;
    }
    
    /**
     * Generate table of contents HTML
     */
    private function generateTableOfContents() {
        if (empty($this->headings)) {
            return '';
        }
        
        $toc_html = '<div class="wiki-toc">';
        $toc_html .= '<div class="toc-header">';
        $toc_html .= '<h2>Contents</h2>';
        $toc_html .= '</div>';
        $toc_html .= '<div class="toc-content">';
        $toc_html .= '<ul class="toc-list">';
        
        foreach ($this->headings as $heading) {
            $level = $heading['level'];
            
            // Apply TOC limit if set
            if ($this->toc_limit > 0 && $level > $this->toc_limit) {
                continue;
            }
            
            // Add the heading with proper indentation via CSS classes
            $toc_html .= '<li class="toc-level-' . $level . '">';
            $toc_html .= '<a href="#' . $heading['id'] . '">' . htmlspecialchars($heading['text']) . '</a>';
            $toc_html .= '</li>';
        }
        
        $toc_html .= '</ul>';
        $toc_html .= '</div>';
        $toc_html .= '</div>';
        
        return $toc_html;
    }
    
    /**
     * Insert TOC after lead section, before first heading
     */
    private function insertTOCAfterLead($content, $toc_html) {
        // Find the first heading
        $first_heading_pos = strpos($content, '<h');
        if ($first_heading_pos === false) {
            return $content . $toc_html;
        }
        
        // Insert TOC before first heading
        return substr($content, 0, $first_heading_pos) . $toc_html . substr($content, $first_heading_pos);
    }
    
    /**
     * Generate references section
     */
    private function generateReferencesSection() {
        if (empty($this->references)) {
            return '';
        }
        
        $html = '<div class="wiki-references">';
        $html .= '<h2>References</h2>';
        $html .= '<ol>';
        
        foreach ($this->references as $ref) {
            $html .= '<li id="ref' . $ref['id'] . '">' . $ref['content'] . '</li>';
        }
        
        $html .= '</ol>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Parse TOC limit from template comments
     */
    private function parseTOCLimit($content) {
        // Look for TOC limit comments
        $pattern = '/<!-- TOC_LIMIT:(\d+) -->/';
        
        return preg_replace_callback($pattern, function($matches) {
            $this->toc_limit = (int)$matches[1];
            return ''; // Remove the comment
        }, $content);
    }
    
    /**
     * Sanitize HTML content for security
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
            'script', 'style', 'iframe', 'object', 'embed', 'form',
            'textarea', 'select', 'button', 'link', 'meta', 'base'
        ];
        
        foreach ($dangerous_tags as $tag) {
            $html = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $html);
            $html = preg_replace('/<' . $tag . '[^>]*\/?>/i', '', $html);
        }
        
        // Remove dangerous input types but allow checkboxes
        $html = preg_replace('/<input(?![^>]*type=["\']checkbox["\'])[^>]*\/?>/i', '', $html);
        
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
    
    // Getters for debugging and external access
    
    public function getReferences() {
        return $this->references;
    }
    
    public function getHeadings() {
        return $this->headings;
    }
    
    public function isTocEnabled() {
        return $this->toc_enabled;
    }
    
    public function isTocForced() {
        return $this->toc_forced;
    }
    
    public function getTocPosition() {
        return $this->toc_position;
    }
    
    public function getTocLimit() {
        return $this->toc_limit;
    }
    
    public function isNotitleEnabled() {
        return $this->notitle;
    }
    
    public function isNocatEnabled() {
        return $this->nocat;
    }
    
    public function getAllowedTags() {
        return $this->allowed_tags;
    }
    
    public function getAllowedAttributes() {
        return $this->allowed_attributes;
    }
    
    public function addAllowedTag($tag) {
        if (!in_array($tag, $this->allowed_tags)) {
            $this->allowed_tags[] = $tag;
        }
    }
    
    public function addAllowedAttribute($attribute) {
        if (!in_array($attribute, $this->allowed_attributes)) {
            $this->allowed_attributes[] = $attribute;
        }
    }
    
    /**
     * Handle inline templates to prevent them from being wrapped in <p> tags
     */
    private function handleInlineTemplates($content) {
        // Find inline templates and move them to the title area
        $pattern = '/<!--INLINE_TEMPLATE-->(.*?)<!--\/INLINE_TEMPLATE-->/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $template_content = $matches[1];
            
            // For now, just return the template content without wrapping
            // The parent parse method will handle the rest
            return $template_content;
        }, $content);
    }
}
