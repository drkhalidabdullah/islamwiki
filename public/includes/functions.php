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
    return $stmt->fetchAll();
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
?>
