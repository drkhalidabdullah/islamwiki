<?php
// Utility functions
require_once __DIR__ . '/../config/database.php';

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function show_message($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message'], $_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function get_user_roles($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.name, r.display_name 
        FROM user_roles ur 
        JOIN roles r ON ur.role_id = r.id 
        WHERE ur.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function has_role($user_id, $role_name) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM user_roles ur 
        JOIN roles r ON ur.role_id = r.id 
        WHERE ur.user_id = ? AND r.name = ?
    ");
    $stmt->execute([$user_id, $role_name]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

function is_admin($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    return $user_id && has_role($user_id, 'admin');
}

function is_moderator($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    return $user_id && (has_role($user_id, 'admin') || has_role($user_id, 'moderator'));
}

function is_editor($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    return $user_id && (has_role($user_id, 'admin') || has_role($user_id, 'moderator') || has_role($user_id, 'editor'));
}

function get_user($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_user_profile($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function log_activity($action, $description = '', $user_id = null, $metadata = null) {
    global $pdo;
    
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, metadata) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $action,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $metadata ? json_encode($metadata) : null
    ]);
}

function get_system_setting($key, $default = null) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value, type FROM system_settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    
    if (!$result) {
        return $default;
    }
    
    switch ($result['type']) {
        case 'boolean':
            return (bool) $result['value'];
        case 'integer':
            return (int) $result['value'];
        case 'json':
            return json_decode($result['value'], true);
        default:
            return $result['value'];
    }
}

function set_system_setting($key, $value, $type = 'string', $description = '') {
    global $pdo;
    
    if ($type === 'json') {
        $value = json_encode($value);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO system_settings (`key`, `value`, `type`, `description`) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        `value` = VALUES(`value`), 
        `type` = VALUES(`type`), 
        `description` = VALUES(`description`)
    ");
    
    return $stmt->execute([$key, $value, $type, $description]);
}

function generate_slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function format_date($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function truncate_text($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function createSlug($text) {
    // Capitalize first letter to ensure consistent naming
    $text = ucfirst(trim($text));
    // Use existing generate_slug function for the actual slug creation
    return generate_slug($text);
}

// Updated createSlug function to handle capitalization properly

/**
 * Check if user can view draft article
 */
function can_view_draft($article, $user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    if (!$user_id) return false;
    
    // Admin can view all drafts
    if (is_admin($user_id)) return true;
    
    // Author can view their own drafts
    if ($article['author_id'] == $user_id) return true;
    
    // Editors can view shared/public drafts
    if (is_editor($user_id) && $article['collaboration_mode'] !== 'private') return true;
    
    // Check collaboration permissions
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM article_collaborations WHERE article_id = ? AND user_id = ? AND is_active = 1");
    $stmt->execute([$article['id'], $user_id]);
    if ($stmt->fetchColumn() > 0) return true;
    
    return false;
}

/**
 * Build article query with proper permissions
 */
function build_article_query($base_query, $where_conditions = [], $params = []) {
    $user_id = $_SESSION["user_id"] ?? null;
    $is_logged_in = is_logged_in();
    $is_editor = is_editor();
    
    if (!$is_logged_in) {
        // Guest users can only see published articles
        $where_conditions[] = "wa.status = 'published'";
    } elseif (!$is_editor) {
        // Regular users can see published articles and their own drafts
        $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
        $params[] = $user_id;
    } else {
        // Editors can see published articles and drafts they have access to
        $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
    }
    
    $where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";
    
    return [
        "query" => $base_query . $where_clause,
        "params" => $params
    ];
}

/**
 * Check if user has scholar role
 */
function is_scholar($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    return $user_id && has_role($user_id, 'scholar');
}

/**
 * Check if user has reviewer role
 */
function is_reviewer($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    return $user_id && has_role($user_id, 'reviewer');
}

// User Profile System Functions

function get_user_by_username($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function get_user_followers($user_id, $limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, uf.created_at as followed_at 
        FROM users u 
        JOIN user_follows uf ON u.id = uf.follower_id 
        WHERE uf.following_id = ? 
        ORDER BY uf.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function get_user_following($user_id, $limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, uf.created_at as followed_at 
        FROM users u 
        JOIN user_follows uf ON u.id = uf.following_id 
        WHERE uf.follower_id = ? 
        ORDER BY uf.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function follow_user($follower_id, $following_id) {
    global $pdo;
    if ($follower_id == $following_id) return false;
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO user_follows (follower_id, following_id) 
        VALUES (?, ?)
    ");
    return $stmt->execute([$follower_id, $following_id]);
}

function unfollow_user($follower_id, $following_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        DELETE FROM user_follows 
        WHERE follower_id = ? AND following_id = ?
    ");
    return $stmt->execute([$follower_id, $following_id]);
}

function is_following($follower_id, $following_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM user_follows 
        WHERE follower_id = ? AND following_id = ?
    ");
    $stmt->execute([$follower_id, $following_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

function get_followers_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_follows WHERE following_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

function get_following_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_follows WHERE follower_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

function create_user_post($user_id, $content, $type = 'text', $media_url = null, $article_id = null) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO user_posts (user_id, content, post_type, media_url, article_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$user_id, $content, $type, $media_url, $article_id]);
}

function get_user_posts($user_id, $limit = 20, $offset = 0, $public_only = true) {
    global $pdo;
    $where_clause = $public_only ? "AND is_public = 1" : "";
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar 
        FROM user_posts up 
        JOIN users u ON up.user_id = u.id 
        WHERE up.user_id = ? $where_clause
        ORDER BY up.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function get_feed_posts($user_id, $limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar 
        FROM user_posts up 
        JOIN users u ON up.user_id = u.id 
        WHERE (up.user_id = ? OR up.user_id IN (
            SELECT following_id FROM user_follows WHERE follower_id = ?
        )) AND up.is_public = 1
        ORDER BY up.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function like_post($user_id, $post_id) {
    global $pdo;
    $pdo->beginTransaction();
    
    try {
        // Add like interaction
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO post_interactions (post_id, user_id, interaction_type) 
            VALUES (?, ?, 'like')
        ");
        $stmt->execute([$post_id, $user_id]);
        
        // Update likes count
        $stmt = $pdo->prepare("
            UPDATE user_posts 
            SET likes_count = (
                SELECT COUNT(*) FROM post_interactions 
                WHERE post_id = ? AND interaction_type = 'like'
            ) 
            WHERE id = ?
        ");
        $stmt->execute([$post_id, $post_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function unlike_post($user_id, $post_id) {
    global $pdo;
    $pdo->beginTransaction();
    
    try {
        // Remove like interaction
        $stmt = $pdo->prepare("
            DELETE FROM post_interactions 
            WHERE post_id = ? AND user_id = ? AND interaction_type = 'like'
        ");
        $stmt->execute([$post_id, $user_id]);
        
        // Update likes count
        $stmt = $pdo->prepare("
            UPDATE user_posts 
            SET likes_count = (
                SELECT COUNT(*) FROM post_interactions 
                WHERE post_id = ? AND interaction_type = 'like'
            ) 
            WHERE id = ?
        ");
        $stmt->execute([$post_id, $post_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function is_post_liked($user_id, $post_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM post_interactions 
        WHERE post_id = ? AND user_id = ? AND interaction_type = 'like'
    ");
    $stmt->execute([$post_id, $user_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

function get_user_photos($user_id, $limit = 20, $offset = 0, $public_only = true) {
    global $pdo;
    $where_clause = $public_only ? "AND is_public = 1" : "";
    $stmt = $pdo->prepare("
        SELECT * FROM user_photos 
        WHERE user_id = ? $where_clause
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function get_user_events($user_id, $limit = 20, $offset = 0, $public_only = true) {
    global $pdo;
    $where_clause = $public_only ? "AND is_public = 1" : "";
    $stmt = $pdo->prepare("
        SELECT * FROM user_events 
        WHERE user_id = ? $where_clause
        ORDER BY start_date DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function get_user_achievements($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM user_achievements 
        WHERE user_id = ? 
        ORDER BY earned_at DESC
    ");
    $stmt->execute([$user_id]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post["parsed_content"] = parse_post_markdown($post["content"]);
    }
    
    return $posts;
}

function get_user_stats($user_id) {
    global $pdo;
    
    // Get article stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_articles,
            COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
            COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
            SUM(view_count) as total_views
        FROM wiki_articles 
        WHERE author_id = ?
    ");
    $stmt->execute([$user_id]);
    $article_stats = $stmt->fetch();
    
    // Get social stats
    $followers_count = get_followers_count($user_id);
    $following_count = get_following_count($user_id);
    
    // Get post stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_posts,
            SUM(likes_count) as total_likes,
            SUM(comments_count) as total_comments
        FROM user_posts 
        WHERE user_id = ? AND is_public = 1
    ");
    $stmt->execute([$user_id]);
    $post_stats = $stmt->fetch();
    
    return [
        'articles' => $article_stats,
        'followers' => $followers_count,
        'following' => $following_count,
        'posts' => $post_stats
    ];
}

function can_view_profile($viewer_id, $profile_user_id) {
    global $pdo;
    
    // User can always view their own profile
    if ($viewer_id == $profile_user_id) return true;
    
    // Get profile privacy level
    $stmt = $pdo->prepare("
        SELECT privacy_level FROM user_profiles 
        WHERE user_id = ?
    ");
    $stmt->execute([$profile_user_id]);
    $profile = $stmt->fetch();
    
    if (!$profile) return true; // Default to public if no profile settings
    
    switch ($profile['privacy_level']) {
        case 'public':
            return true;
        case 'community':
            return is_logged_in();
        case 'followers':
            return is_following($viewer_id, $profile_user_id);
        case 'private':
            return false;
        default:
            return true;
    }
}

function get_user_profile_complete($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, up.* 
        FROM users u 
        LEFT JOIN user_profiles up ON u.id = up.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Parse markdown content for user posts
 */
function parse_post_markdown($content) {
    // Simple markdown parser for posts
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    
    // Headers
    $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
    $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
    $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
    
    // Bold
    $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
    
    // Italic
    $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
    
    // Strikethrough
    $content = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $content);
    
    // Code blocks
    $content = preg_replace('/```([\s\S]*?)```/', '<pre><code>$1</code></pre>', $content);
    
    // Inline code
    $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
    
    // Links
    $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $content);
    
    // Images
    $content = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" style="max-width: 100%; height: auto;">', $content);
    
    // Blockquotes
    $content = preg_replace('/^> (.*$)/m', '<blockquote>$1</blockquote>', $content);
    
    // Lists
    $content = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $content);
    $content = preg_replace('/^- (.*$)/m', '<li>$1</li>', $content);
    $content = preg_replace('/^(\d+)\. (.*$)/m', '<li>$2</li>', $content);
    
    // Wrap consecutive list items in ul/ol
    $content = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $content);
    
    // Line breaks
    $content = nl2br($content);
    
    return $content;
}

/**
 * Get user posts with markdown parsing
 */
function get_user_posts_with_markdown($user_id, $limit = 10, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name 
        FROM user_posts up 
        JOIN users u ON up.user_id = u.id 
        WHERE up.user_id = ? AND up.is_public = 1 
        ORDER BY up.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    $posts = $stmt->fetchAll();
    
    // Parse markdown for each post
    foreach ($posts as &$post) {
        $post['parsed_content'] = parse_post_markdown($post['content']);
    }
    
    return $posts;
}

