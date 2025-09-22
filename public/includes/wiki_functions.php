<?php
/**
 * Enhanced Wiki Functions for IslamWiki
 * Provides Wikipedia-style functionality including namespaces, templates, and more
 * 
 * @author Khalid Abdullah
 * @version 0.0.0.14
 * @license AGPL-3.0
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/markdown/MarkdownParser.php';

/**
 * Get all wiki namespaces
 */
function get_wiki_namespaces($active_only = true) {
    global $pdo;
    
    $sql = "SELECT * FROM wiki_namespaces";
    if ($active_only) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY sort_order, name";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Check if a slug has a redirect and return the target article
 */
function get_redirect_target($slug) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT r.to_article_id, a.slug as target_slug, a.title as target_title
        FROM wiki_redirects r
        JOIN wiki_articles a ON r.to_article_id = a.id
        WHERE r.from_slug = ?
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Create a redirect from one slug to another
 */
function create_redirect($from_slug, $to_article_id, $created_by) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO wiki_redirects (from_slug, to_article_id, created_by) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$from_slug, $to_article_id, $created_by]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Delete a redirect by ID
 */
function delete_redirect($redirect_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM wiki_redirects WHERE id = ?");
        return $stmt->execute([$redirect_id]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get all redirects with target article information
 */
function get_all_redirects() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT r.*, a.title as target_title, a.slug as target_slug, u.username as created_by_username
        FROM wiki_redirects r
        JOIN wiki_articles a ON r.to_article_id = a.id
        JOIN users u ON r.created_by = u.id
        ORDER BY r.created_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Get namespace by ID or name
 */
function get_wiki_namespace($identifier) {
    global $pdo;
    
    if (is_numeric($identifier)) {
        $stmt = $pdo->prepare("SELECT * FROM wiki_namespaces WHERE id = ?");
        $stmt->execute([$identifier]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM wiki_namespaces WHERE name = ?");
        $stmt->execute([$identifier]);
    }
    
    return $stmt->fetch();
}

/**
 * Parse page title to extract namespace and title
 */
function parse_wiki_title($full_title) {
    $parts = explode(':', $full_title, 2);
    
    if (count($parts) === 2) {
        $namespace_name = trim($parts[0]);
        $title = trim($parts[1]);
        
        // Check if namespace exists
        $namespace = get_wiki_namespace($namespace_name);
        if ($namespace) {
            return [
                'namespace' => $namespace,
                'title' => $title,
                'full_title' => $full_title
            ];
        }
    }
    
    // Return as main namespace if no namespace found
    $main_namespace = get_wiki_namespace('Main');
    return [
        'namespace' => $main_namespace,
        'title' => $full_title,
        'full_title' => $full_title
    ];
}

/**
 * Create or get category by name
 */
function get_or_create_category($category_name) {
    global $pdo;
    
    $slug = createSlug($category_name);
    
    // Try to get existing category
    $stmt = $pdo->prepare("SELECT * FROM wiki_categories WHERE name = ? OR slug = ?");
    $stmt->execute([$category_name, $slug]);
    $category = $stmt->fetch();
    
    if ($category) {
        return $category;
    }
    
    // Create new category
    $stmt = $pdo->prepare("
        INSERT INTO wiki_categories (name, slug, created_by) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$category_name, $slug, $_SESSION['user_id'] ?? 1]);
    
    return get_or_create_category($category_name);
}

/**
 * Add article to category
 */
function add_article_to_category($article_id, $category_name) {
    global $pdo;
    
    $category = get_or_create_category($category_name);
    
    // Check if relationship already exists
    $stmt = $pdo->prepare("
        SELECT id FROM wiki_article_categories 
        WHERE article_id = ? AND category_id = ?
    ");
    $stmt->execute([$article_id, $category['id']]);
    
    if (!$stmt->fetch()) {
        // Add relationship
        $stmt = $pdo->prepare("
            INSERT INTO wiki_article_categories (article_id, category_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$article_id, $category['id']]);
        
        // Update category article count
        $stmt = $pdo->prepare("
            UPDATE wiki_categories 
            SET article_count = article_count + 1 
            WHERE id = ?
        ");
        $stmt->execute([$category['id']]);
    }
}

/**
 * Remove article from category
 */
function remove_article_from_category($article_id, $category_name) {
    global $pdo;
    
    $category = get_or_create_category($category_name);
    
    // Remove relationship
    $stmt = $pdo->prepare("
        DELETE FROM wiki_article_categories 
        WHERE article_id = ? AND category_id = ?
    ");
    $stmt->execute([$article_id, $category['id']]);
    
    // Update category article count
    $stmt = $pdo->prepare("
        UPDATE wiki_categories 
        SET article_count = article_count - 1 
        WHERE id = ? AND article_count > 0
    ");
    $stmt->execute([$category['id']]);
}

/**
 * Get categories for an article
 */
function get_article_categories($article_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT wc.* 
        FROM wiki_categories wc
        JOIN wiki_article_categories wac ON wc.id = wac.category_id
        WHERE wac.article_id = ?
        ORDER BY wc.name
    ");
    $stmt->execute([$article_id]);
    return $stmt->fetchAll();
}

/**
 * Get articles in a category
 */
function get_category_articles($category_slug, $limit = 50, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name
        FROM wiki_articles wa
        JOIN users u ON wa.author_id = u.id
        JOIN wiki_article_categories wac ON wa.id = wac.article_id
        JOIN wiki_categories wc ON wac.category_id = wc.id
        WHERE wc.slug = ? AND wa.status = 'published'
        ORDER BY wa.title
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$category_slug, $limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Get subcategories of a category
 */
function get_category_subcategories($category_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM wiki_categories 
        WHERE parent_id = ? 
        ORDER BY name
    ");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll();
}

/**
 * Get parent categories of a category
 */
function get_category_parents($category_id) {
    global $pdo;
    
    $parents = [];
    $current_id = $category_id;
    
    while ($current_id) {
        $stmt = $pdo->prepare("
            SELECT parent_id, name, slug 
            FROM wiki_categories 
            WHERE id = ?
        ");
        $stmt->execute([$current_id]);
        $category = $stmt->fetch();
        
        if ($category && $category['parent_id']) {
            $parents[] = $category;
            $current_id = $category['parent_id'];
        } else {
            break;
        }
    }
    
    return array_reverse($parents);
}

/**
 * Update article categories from parsed content
 */
function update_article_categories($article_id, $categories) {
    global $pdo;
    
    // Remove all existing categories for this article
    $stmt = $pdo->prepare("
        DELETE FROM wiki_article_categories WHERE article_id = ?
    ");
    $stmt->execute([$article_id]);
    
    // Add new categories
    foreach ($categories as $category_name) {
        add_article_to_category($article_id, $category_name);
    }
    
    // Recalculate all category counts to ensure accuracy
    $stmt = $pdo->prepare("
        UPDATE wiki_categories 
        SET article_count = (
            SELECT COUNT(*) 
            FROM wiki_article_categories wac
            JOIN wiki_articles wa ON wac.article_id = wa.id
            WHERE wac.category_id = wiki_categories.id 
            AND wa.status = 'published'
        )
    ");
    $stmt->execute();
}

/**
 * Fix all category counts to match actual published articles
 */
function fix_all_category_counts() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE wiki_categories 
        SET article_count = (
            SELECT COUNT(*) 
            FROM wiki_article_categories wac
            JOIN wiki_articles wa ON wac.article_id = wa.id
            WHERE wac.category_id = wiki_categories.id 
            AND wa.status = 'published'
        )
    ");
    $stmt->execute();
    
    return $stmt->rowCount();
}

/**
 * Get article by title (with namespace support)
 */
function get_wiki_article_by_title($title, $namespace_id = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name, wn.name as namespace_name, wn.display_name as namespace_display
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE wa.title = ? AND wa.namespace_id = ? AND wa.status = 'published'
    ");
    $stmt->execute([$title, $namespace_id]);
    return $stmt->fetch();
}

/**
 * Get article by slug (with namespace support)
 */
function get_wiki_article_by_slug($slug, $namespace_id = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name, wn.name as namespace_name, wn.display_name as namespace_display
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE wa.slug = ? AND wa.namespace_id = ? AND wa.status = 'published'
    ");
    $stmt->execute([$slug, $namespace_id]);
    return $stmt->fetch();
}

/**
 * Create or update talk page
 */
function create_or_update_talk_page($article_id, $content, $user_id) {
    global $pdo;
    
    // Check if talk page exists
    $stmt = $pdo->prepare("SELECT id FROM wiki_talk_pages WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing talk page
        $stmt = $pdo->prepare("
            UPDATE wiki_talk_pages 
            SET content = ?, updated_by = ?, updated_at = NOW() 
            WHERE article_id = ?
        ");
        $stmt->execute([$content, $user_id, $article_id]);
        return $existing['id'];
    } else {
        // Create new talk page
        $stmt = $pdo->prepare("
            INSERT INTO wiki_talk_pages (article_id, content, created_by, updated_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$article_id, $content, $user_id, $user_id]);
        return $pdo->lastInsertId();
    }
}

/**
 * Get talk page for article
 */
function get_talk_page($article_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT wtp.*, u.username, u.display_name 
        FROM wiki_talk_pages wtp 
        JOIN users u ON wtp.updated_by = u.id 
        WHERE wtp.article_id = ?
    ");
    $stmt->execute([$article_id]);
    return $stmt->fetch();
}

/**
 * Get all templates
 */
function get_wiki_templates($search = null) {
    global $pdo;
    
    $sql = "SELECT * FROM wiki_templates";
    $params = [];
    
    if ($search) {
        $sql .= " WHERE name LIKE ? OR description LIKE ?";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY usage_count DESC, name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get template by name or slug
 */
function get_wiki_template($identifier) {
    global $pdo;
    
    if (is_numeric($identifier)) {
        $stmt = $pdo->prepare("SELECT * FROM wiki_templates WHERE id = ?");
        $stmt->execute([$identifier]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM wiki_templates WHERE name = ? OR slug = ?");
        $stmt->execute([$identifier, $identifier]);
    }
    
    return $stmt->fetch();
}

/**
 * Parse template content with parameters
 */
function parse_template($template_name, $parameters = []) {
    $template = get_wiki_template($template_name);
    if (!$template) {
        return "{{Template not found: $template_name}}";
    }
    
    $content = $template['content'];
    
    // Replace parameters
    foreach ($parameters as $key => $value) {
        $content = str_replace("{{$key}}", $value, $content);
        $content = str_replace("{{{$key}}}", $value, $content);
    }
    
    // Increment usage count
    $stmt = $pdo->prepare("UPDATE wiki_templates SET usage_count = usage_count + 1 WHERE id = ?");
    $stmt->execute([$template['id']]);
    
    return $content;
}

/**
 * Add article to user's watchlist
 */
function add_to_watchlist($user_id, $article_id, $notify_email = true) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_watchlists (user_id, article_id, notify_email) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE notify_email = ?
        ");
        $stmt->execute([$user_id, $article_id, $notify_email, $notify_email]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove article from user's watchlist
 */
function remove_from_watchlist($user_id, $article_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM user_watchlists WHERE user_id = ? AND article_id = ?");
    return $stmt->execute([$user_id, $article_id]);
}

/**
 * Check if article is in user's watchlist
 */
function is_in_watchlist($user_id, $article_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM user_watchlists WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$user_id, $article_id]);
    return $stmt->fetch() !== false;
}

/**
 * Get user's watchlist
 */
function get_user_watchlist($user_id, $limit = 50, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT uw.*, wa.title, wa.slug, wa.updated_at, wa.last_edit_at, 
               u.username, u.display_name, wn.name as namespace_name
        FROM user_watchlists uw
        JOIN wiki_articles wa ON uw.article_id = wa.id
        LEFT JOIN users u ON wa.last_edit_by = u.id
        LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE uw.user_id = ?
        ORDER BY wa.last_edit_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Get recent changes to watched articles
 */
function get_recent_watchlist_changes($user_id, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT av.*, wa.title, wa.slug, wa.id as article_id,
               u.username, u.display_name, wn.name as namespace_name
        FROM article_versions av
        JOIN wiki_articles wa ON av.article_id = wa.id
        JOIN user_watchlists uw ON wa.id = uw.article_id
        LEFT JOIN users u ON av.created_by = u.id
        LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE uw.user_id = ? AND av.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY av.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}



/**
 * Upload file to wiki
 */
function upload_wiki_file($file, $user_id, $description = '') {
    global $pdo;
    
    $upload_dir = '/var/www/html/public/uploads/wiki/files/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Get image dimensions if it's an image
        $width = $height = null;
        if (strpos($file['type'], 'image/') === 0) {
            $image_info = getimagesize($file_path);
            if ($image_info) {
                $width = $image_info[0];
                $height = $image_info[1];
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO wiki_files (filename, original_name, file_path, file_size, mime_type, width, height, description, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $filename,
            $file['name'],
            $file_path,
            $file['size'],
            $file['type'],
            $width,
            $height,
            $description,
            $user_id
        ]);
        
        return [
            'success' => true,
            'file_id' => $pdo->lastInsertId(),
            'filename' => $filename,
            'url' => '/uploads/wiki/files/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Upload failed'];
}

/**
 * Get file by ID or filename
 */
function get_wiki_file($identifier) {
    global $pdo;
    
    if (is_numeric($identifier)) {
        $stmt = $pdo->prepare("SELECT * FROM wiki_files WHERE id = ?");
        $stmt->execute([$identifier]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM wiki_files WHERE filename = ?");
        $stmt->execute([$identifier]);
    }
    
    return $stmt->fetch();
}

/**
 * Get recent changes
 */
function get_recent_changes($limit = 50, $namespace_id = null) {
    global $pdo;
    
    $sql = "
        SELECT wa.*, u.username, u.display_name, wn.name as namespace_name, wn.display_name as namespace_display
        FROM wiki_articles wa
        JOIN users u ON wa.last_edit_by = u.id
        JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE wa.status = 'published'
    ";
    $params = [];
    
    if ($namespace_id !== null) {
        $sql .= " AND wa.namespace_id = ?";
        $params[] = $namespace_id;
    }
    
    $sql .= " ORDER BY wa.last_edit_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get all pages (with namespace filtering)
 */
function get_all_pages($namespace_id = null, $limit = 100, $offset = 0) {
    global $pdo;
    
    $sql = "
        SELECT wa.*, u.username, u.display_name, wn.name as namespace_name, wn.display_name as namespace_display
        FROM wiki_articles wa
        JOIN users u ON wa.author_id = u.id
        JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE wa.status = 'published'
    ";
    $params = [];
    
    if ($namespace_id !== null) {
        $sql .= " AND wa.namespace_id = ?";
        $params[] = $namespace_id;
    }
    
    $sql .= " ORDER BY wa.title ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get user contributions
 */
function get_user_contributions($user_id, $limit = 50) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT wa.*, wn.name as namespace_name, wn.display_name as namespace_display
        FROM wiki_articles wa
        JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
        WHERE wa.author_id = ? OR wa.last_edit_by = ?
        ORDER BY wa.updated_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $user_id, $limit]);
    return $stmt->fetchAll();
}

/**
 * Log special page action
 */
function log_special_action($log_type, $page_title, $page_id, $user_id, $action, $details = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO wiki_special_logs (log_type, page_title, page_id, user_id, action, details) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$log_type, $page_title, $page_id, $user_id, $action, json_encode($details)]);
}

/**
 * Enhanced markdown parser with wiki features
 * @deprecated Use WikiParser instead
 */
class EnhancedMarkdownParser extends MarkdownParser {
    
    public function parse($text) {
        // First parse templates
        $text = $this->parseTemplates($text);
        
        // Parse file links
        $text = $this->parseFileLinks($text);
        
        // Parse categories
        $text = $this->parseCategories($text);
        
        // Call parent parse method (which handles wiki links and markdown)
        return parent::parse($text);
    }
    
    private function parseTemplates($text) {
        // Parse template syntax: {{Template Name|param1|param2}}
        $text = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) {
            $template_content = trim($matches[1]);
            $parts = explode('|', $template_content);
            $template_name = trim($parts[0]);
            
            $parameters = [];
            for ($i = 1; $i < count($parts); $i++) {
                $parameters[$i] = trim($parts[$i]);
            }
            
            return parse_template($template_name, $parameters);
        }, $text);
        
        return $text;
    }
    
    private function parseFileLinks($text) {
        // Parse file syntax: [[File:filename.jpg|thumb|Caption with [[nested]] markup]]
        $text = preg_replace_callback('/\[\[File:([^|\]]+)(?:\|([^|\]]+))?(?:\|(.*?))?\]\]/', function($matches) {
            $filename = trim($matches[1]);
            $options = isset($matches[2]) ? trim($matches[2]) : '';
            $caption = isset($matches[3]) ? trim($matches[3]) : '';
            
            // Check if caption contains nested wiki markup
            if (strpos($caption, '[[') !== false && strpos($caption, ']]') !== false) {
                // For captions with nested markup, we need to parse them separately
                $caption = parseWikiLinks($caption);
            } else {
                $caption = htmlspecialchars($caption);
            }
            
            $file = get_wiki_file($filename);
            if (!$file) {
                return '<span class="missing-file">[File not found: ' . htmlspecialchars($filename) . ']</span>';
            }
            
            $url = '/uploads/wiki/files/' . $file['filename'];
            $is_thumb = $options === 'thumb';
            
            if ($is_thumb) {
                return '<div class="wiki-thumbnail"><img src="' . $url . '" alt="' . htmlspecialchars($filename) . '" class="thumb-image"><div class="thumb-caption">' . $caption . '</div></div>';
            } else {
                return '<img src="' . $url . '" alt="' . htmlspecialchars($filename) . '">';
            }
        }, $text);
        
        return $text;
    }
    
    private function parseCategories($text) {
        // Parse category syntax: [[Category:Category Name]]
        $text = preg_replace_callback('/\[\[Category:([^\]]+)\]\]/', function($matches) {
            $category_name = trim($matches[1]);
            // Categories are handled separately, just remove from content
            return '';
        }, $text);
        
        return $text;
    }
}

/**
 * Get page statistics
 */
function get_wiki_statistics() {
    global $pdo;
    
    $stats = [];
    
    // Total articles
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'");
    $stats['total_articles'] = $stmt->fetch()['count'];
    
    // Total pages (including all namespaces)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'");
    $stats['total_pages'] = $stmt->fetch()['count'];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total edits
    $stmt = $pdo->query("SELECT SUM(edit_count) as count FROM wiki_articles");
    $stats['total_edits'] = $stmt->fetch()['count'] ?: 0;
    
    // Total files
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_files");
    $stats['total_files'] = $stmt->fetch()['count'];
    
    return $stats;
}

/**
 * Get total article count for templates
 */
function get_total_article_count() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'");
    return $stmt->fetch()['count'];
}

/**
 * Get Hijri date for templates
 */
function get_hijri_date() {
    // Simple Hijri date calculation (approximate)
    $gregorian_date = new DateTime();
    $year = $gregorian_date->format('Y');
    $month = $gregorian_date->format('n');
    $day = $gregorian_date->format('j');
    
    // Convert to Hijri (approximate calculation)
    $hijri_year = $year - 622;
    $hijri_month = $month;
    $hijri_day = $day;
    
    // Adjust for Hijri calendar differences
    if ($month > 7) {
        $hijri_year++;
    }
    
    $hijri_months = [
        1 => 'Muharram', 2 => 'Safar', 3 => 'Rabi\' al-awwal', 4 => 'Rabi\' al-thani',
        5 => 'Jumada al-awwal', 6 => 'Jumada al-thani', 7 => 'Rajab', 8 => 'Sha\'ban',
        9 => 'Ramadan', 10 => 'Shawwal', 11 => 'Dhu al-Qi\'dah', 12 => 'Dhu al-Hijjah'
    ];
    
    return $hijri_day . ' ' . $hijri_months[$hijri_month] . ' ' . $hijri_year;
}
