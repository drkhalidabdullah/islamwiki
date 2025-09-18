<?php
// Utility functions

// Include rate limiting and moderation functions
require_once __DIR__ . '/rate_limiter.php';
require_once __DIR__ . '/moderation_functions.php';

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
        // Store current URL as return URL
        $current_url = $_SERVER['REQUEST_URI'] ?? '/';
        $_SESSION['return_url'] = $current_url;
        
        header("Location: /login");
        exit();
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function redirect_with_return_url($default_url = '/dashboard') {
    // Use return URL if available, otherwise use default
    $return_url = $_SESSION['return_url'] ?? $default_url;
    unset($_SESSION['return_url']);
    redirect($return_url);
}

function show_message($message, $type = 'info') {
    // Check if notifications are enabled
    $enable_notifications = get_system_setting('enable_notifications', true);
    if (!$enable_notifications) {
        return;
    }
    
    // Store message for toast notification
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
}

function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function has_permission($user_id, $permission) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT r.permissions 
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $roles = $stmt->fetchAll();
    
    foreach ($roles as $role) {
        $permissions = json_decode($role['permissions'], true) ?: [];
        if (in_array($permission, $permissions)) {
            return true;
        }
    }
    
    return false;
}

function require_permission($permission) {
    if (!is_logged_in()) {
        // Store current URL as return URL
        $current_url = $_SERVER['REQUEST_URI'];
        $_SESSION['return_url'] = $current_url;
        
        header("Location: /login");
        exit();
    }
    
    if (!has_permission($_SESSION['user_id'], $permission)) {
        show_message('Access denied. You do not have permission to perform this action.', 'error');
        redirect_with_return_url();
    }
}

function get_user_roles($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT r.* 
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function assign_role($user_id, $role_id, $granted_by = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO user_roles (user_id, role_id, granted_by) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$user_id, $role_id, $granted_by]);
    } catch (Exception $e) {
        return false;
    }
}

function remove_role($user_id, $role_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
        return $stmt->execute([$user_id, $role_id]);
    } catch (Exception $e) {
        return false;
    }
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

function require_admin() {
    if (!is_admin()) {
        show_message('Access denied. Admin privileges required.', 'error');
        redirect('/dashboard');
    }
}

/**
 * Check if site is in maintenance mode
 * @return bool True if maintenance mode is enabled
 */
function is_maintenance_mode() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT value, type FROM system_settings WHERE `key` = 'maintenance_mode' AND (type = 'boolean' OR type = 'integer' OR type = 'string')");
        $stmt->execute();
        $result = $stmt->fetch();
        if (!$result) {
            return false;
        }
        
        // Handle different types
        if ($result['type'] === 'boolean') {
            return (bool) $result['value'];
        } elseif ($result['type'] === 'integer') {
            return (int) $result['value'] === 1;
        } else {
            // string type
            return $result['value'] === '1';
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if current user can bypass maintenance mode
 * @return bool True if user can bypass maintenance mode
 */
function can_bypass_maintenance() {
    return is_admin();
}

/**
 * Handle maintenance mode - redirect to maintenance page if enabled and user can't bypass
 */
function check_maintenance_mode() {
    if (is_maintenance_mode() && !can_bypass_maintenance()) {
        // Don't redirect if already on maintenance page to avoid infinite redirects
        $current_page = $_SERVER['REQUEST_URI'] ?? '';
        
        // Allow access to maintenance page during maintenance
        $allowed_pages = ['/maintenance'];
        $is_allowed_page = false;
        
        foreach ($allowed_pages as $allowed_page) {
            if (strpos($current_page, $allowed_page) !== false) {
                $is_allowed_page = true;
                break;
            }
        }
        
        if (!$is_allowed_page) {
            // Redirect to maintenance page
            header('Location: /maintenance');
            exit;
        }
    }
}

/**
 * Check if maintenance mode banner should be shown (for admins)
 */
function should_show_maintenance_banner() {
    return is_maintenance_mode() && can_bypass_maintenance();
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
            return empty($result['value']) && $default !== null ? $default : $result['value'];
    }
}

/**
 * Get the dynamic site name from settings or fall back to SITE_NAME constant
 * @return string The site name
 */
function get_site_name() {
    return get_system_setting('site_name', SITE_NAME);
}

/**
 * Get the copyright text from settings or generate default with site name
 * @return string The copyright text
 */
function get_copyright_text() {
    $custom_copyright = get_system_setting('copyright_text', '');
    if (!empty($custom_copyright)) {
        return $custom_copyright;
    }
    
    // Default copyright with dynamic site name
    return '© ' . date('Y') . ' ' . get_site_name() . '. All rights reserved.';
}

/**
 * Get the first user's email (the person who set up the site)
 * @return string The first user's email or default admin email
 */
function get_first_user_email() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT email FROM users ORDER BY created_at ASC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result && !empty($result['email'])) {
            return $result['email'];
        }
    } catch (Exception $e) {
        // If there's an error, fall back to default
        error_log("Error getting first user email: " . $e->getMessage());
    }
    
    // Fallback to default admin email
    return 'admin@islamwiki.org';
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

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

function createSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace spaces and special characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    
    return $text;
}

function truncate_text($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
    

// Friend/Follow functions
function follow_user($follower_id, $following_id) {
    global $pdo;
    if ($follower_id == $following_id) return false;
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO user_follows (follower_id, following_id, status) 
        VALUES (?, ?, 'pending')
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
        WHERE follower_id = ? AND following_id = ? AND status = 'accepted'
    ");
    $stmt->execute([$follower_id, $following_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

function accept_friend_request($follower_id, $following_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE user_follows 
        SET status = 'accepted' 
        WHERE follower_id = ? AND following_id = ? AND status = 'pending'
    ");
    return $stmt->execute([$follower_id, $following_id]);
}

function decline_friend_request($follower_id, $following_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE user_follows 
        SET status = 'declined' 
        WHERE follower_id = ? AND following_id = ? AND status = 'pending'
    ");
    return $stmt->execute([$follower_id, $following_id]);
}

// Notification functions
function create_notification($user_id, $type, $title, $message, $data = null) {
    global $pdo;
    
    // Check if notifications are enabled
    $enable_notifications = get_system_setting('enable_notifications', true);
    if (!$enable_notifications) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, data) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$user_id, $type, $title, $message, $data ? json_encode($data) : null]);
}

function get_user_notifications($user_id, $limit = 10, $unread_only = false) {
    global $pdo;
    $sql = "
        SELECT * FROM notifications 
        WHERE user_id = ?";
    
    if ($unread_only) {
        $sql .= " AND is_read = FALSE";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

function mark_notification_read($notification_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([$notification_id, $user_id]);
}

function get_unread_notification_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

function get_recent_messages($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, 
               sender.username as sender_username,
               sender.display_name as sender_display_name,
               recipient.username as recipient_username,
               recipient.display_name as recipient_display_name
        FROM messages m
        JOIN users sender ON m.sender_id = sender.id
        JOIN users recipient ON m.recipient_id = recipient.id
        WHERE (m.sender_id = ? OR m.recipient_id = ?)
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $user_id, $limit]);
    return $stmt->fetchAll();
}

function get_unread_message_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM messages 
        WHERE recipient_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['count'];
}

// User lookup functions
function get_user_by_username($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function can_view_profile($current_user_id, $profile_user_id) {
    // For now, allow all users to view profiles
    // This can be enhanced later with privacy settings
    return true;
}

function get_user_profile_complete($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_user_stats($user_id) {
    global $pdo;
    
    // Get basic stats
    $stats = [
        'posts_count' => 0,
        'followers_count' => 0,
        'following_count' => 0,
        'articles_count' => 0
    ];
    
    // Count posts (if posts table exists)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $stats['posts_count'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        // Posts table might not exist yet
    }
    
    // Count followers
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_follows WHERE following_id = ? AND status = 'accepted'");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $stats['followers_count'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        // user_follows table might not exist yet
    }
    
    // Count following
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_follows WHERE follower_id = ? AND status = 'accepted'");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $stats['following_count'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        // user_follows table might not exist yet
    }
    
    // Count articles (if articles table exists)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM articles WHERE author_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $stats['articles_count'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        // Articles table might not exist yet
    }
    
    return $stats;
}

// User content functions
function create_user_post($user_id, $content, $post_type = 'text', $media_url = null, $link_url = null, $link_title = null, $link_description = null, $link_image = null, $article_id = null, $is_public = 1) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_posts 
            (user_id, content, post_type, media_url, link_url, link_title, link_description, link_image, article_id, is_public, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $user_id,
            $content,
            $post_type,
            $media_url,
            $link_url,
            $link_title,
            $link_description,
            $link_image,
            $article_id,
            $is_public
        ]);
        
        return $result ? $pdo->lastInsertId() : false;
    } catch (Exception $e) {
        error_log("Error creating user post: " . $e->getMessage());
        return false;
    }
}

function get_user_posts_with_markdown($user_id, $limit = 20, $offset = 0, $include_markdown = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT up.*, u.username, u.display_name, u.avatar
            FROM user_posts up
            JOIN users u ON up.user_id = u.id
            WHERE up.user_id = ? AND up.is_public = 1
            ORDER BY up.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        $posts = $stmt->fetchAll();
        
        // Parse markdown content for each post
        if ($include_markdown && !empty($posts)) {
            try {
                require_once __DIR__ . '/markdown/MarkdownParser.php';
                $parser = new MarkdownParser();
                
                foreach ($posts as &$post) {
                    if (!empty($post['content'])) {
                        $post['parsed_content'] = $parser->parse($post['content']);
                        error_log("Parsed content for post {$post['id']}: " . $post['parsed_content']);
                    }
                }
            } catch (Exception $e) {
                error_log("Error parsing markdown: " . $e->getMessage());
            }
        }
        
        return $posts;
    } catch (Exception $e) {
        error_log("Error getting user posts: " . $e->getMessage());
        return [];
    }
}

function get_user_photos($user_id, $limit = 20, $offset = 0, $include_metadata = false) {
    global $pdo;
    
    // For now, return empty array since photos table might not exist
    // This can be implemented when photos functionality is added
    return [];
}

function get_user_events($user_id, $limit = 20, $offset = 0, $include_details = false) {
    global $pdo;
    
    // For now, return empty array since events table might not exist
    // This can be implemented when events functionality is added
    return [];
}

function get_user_achievements($user_id) {
    global $pdo;
    
    // For now, return empty array since achievements table might not exist
    // This can be implemented when achievements functionality is added
    return [];
}

function is_post_liked($user_id, $post_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM post_interactions 
            WHERE post_id = ? AND user_id = ? AND interaction_type = 'like'
        ");
        $stmt->execute([$post_id, $user_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

function like_post($user_id, $post_id) {
    global $pdo;
    
    try {
        // Check if already liked
        if (is_post_liked($user_id, $post_id)) {
            return true; // Already liked
        }
        
        // Add like
        $stmt = $pdo->prepare("
            INSERT INTO post_interactions (post_id, user_id, interaction_type) 
            VALUES (?, ?, 'like')
        ");
        return $stmt->execute([$post_id, $user_id]);
    } catch (Exception $e) {
        return false;
    }
}

function unlike_post($user_id, $post_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            DELETE FROM post_interactions 
            WHERE post_id = ? AND user_id = ? AND interaction_type = 'like'
        ");
        return $stmt->execute([$post_id, $user_id]);
    } catch (Exception $e) {
        return false;
    }
}

function get_post_likes_count($post_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM post_interactions 
            WHERE post_id = ? AND interaction_type = 'like'
        ");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch();
        return $result['count'];
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get user avatar with fallback to default
 * @param string|null $avatar The user's avatar path
 * @return string The avatar path or default avatar
 */
function get_user_avatar($avatar = null) {
    if (!empty($avatar) && file_exists($_SERVER['DOCUMENT_ROOT'] . $avatar)) {
        return $avatar;
    }
    return '/assets/images/default-avatar.svg';
}

function add_comment($post_id, $user_id, $content, $parent_id = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO post_comments (post_id, user_id, content, parent_id) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$post_id, $user_id, $content, $parent_id]);
    } catch (Exception $e) {
        return false;
    }
}

function get_post_comments($post_id, $limit = 20, $offset = 0) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT pc.*, u.username, u.display_name, u.avatar
            FROM post_comments pc
            JOIN users u ON pc.user_id = u.id
            WHERE pc.post_id = ? AND pc.parent_id IS NULL
            ORDER BY pc.created_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$post_id, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function get_comment_replies($comment_id, $limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT pc.*, u.username, u.display_name, u.avatar
            FROM post_comments pc
            JOIN users u ON pc.user_id = u.id
            WHERE pc.parent_id = ?
            ORDER BY pc.created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$comment_id, $limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function get_comment_count($post_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM post_comments 
            WHERE post_id = ?
        ");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch();
        return $result['count'];
    } catch (Exception $e) {
        return 0;
    }
}
