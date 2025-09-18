<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$query = trim($_GET['q'] ?? '');
$limit = min((int)($_GET['limit'] ?? 10), 20); // Max 20 results

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['users' => []]);
    exit;
}

try {
    // Search for users by username or display name
    $search_term = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, username, display_name, avatar
        FROM users 
        WHERE (username LIKE ? OR display_name LIKE ?) 
        AND id != ? 
        ORDER BY 
            CASE 
                WHEN username LIKE ? THEN 1
                WHEN display_name LIKE ? THEN 2
                ELSE 3
            END,
            username ASC
        LIMIT ?
    ");
    
    $exact_match = $query . '%';
    $stmt->execute([
        $search_term, 
        $search_term, 
        $_SESSION['user_id'],
        $exact_match,
        $exact_match,
        $limit
    ]);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the results for autocomplete
    $formatted_users = [];
    foreach ($users as $user) {
        $formatted_users[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'display_name' => $user['display_name'],
            'avatar' => $user['avatar'],
            'label' => $user['display_name'] ? $user['display_name'] . ' (@' . $user['username'] . ')' : '@' . $user['username'],
            'value' => '@' . $user['username']
        ];
    }
    
    echo json_encode(['users' => $formatted_users]);
    
} catch (Exception $e) {
    error_log("User search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}
?>
