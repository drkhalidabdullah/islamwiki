<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(['success' => false, 'message' => 'Search query required']);
    exit();
}

$query = trim($_GET['q']);
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

try {
    // Search for users by username or display name
    $stmt = $pdo->prepare("
        SELECT id, username, display_name, avatar, first_name, last_name
        FROM users 
        WHERE (username LIKE ? OR display_name LIKE ? OR first_name LIKE ? OR last_name LIKE ?)
        AND id != ?
        ORDER BY 
            CASE 
                WHEN username LIKE ? THEN 1
                WHEN display_name LIKE ? THEN 2
                WHEN first_name LIKE ? THEN 3
                ELSE 4
            END,
            username ASC
        LIMIT ?
    ");
    
    $search_term = "%{$query}%";
    $user_id = $_SESSION['user_id'];
    
    $stmt->execute([
        $search_term, $search_term, $search_term, $search_term, $user_id,
        $search_term, $search_term, $search_term, $limit
    ]);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'display_name' => $user['display_name'] ?: $user['username'],
            'avatar' => $user['avatar'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $formatted_users,
        'total' => count($formatted_users)
    ]);
    
} catch (PDOException $e) {
    error_log("User search error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>