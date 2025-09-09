<?php
// Utility functions

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
        $current_url = $_SERVER['REQUEST_URI'];
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
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
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
function get_user_posts_with_markdown($user_id, $limit = 20, $offset = 0, $include_markdown = false) {
    global $pdo;
    
    // For now, return empty array since posts table might not exist
    // This can be implemented when posts functionality is added
    return [];
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
    
    // For now, return false since likes table might not exist
    // This can be implemented when likes functionality is added
    return false;
}
