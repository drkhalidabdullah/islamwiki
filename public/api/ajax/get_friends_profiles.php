<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's friends (people they follow) with their profile pictures
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.display_name,
            u.avatar,
            uf.created_at as friendship_date
        FROM user_follows uf
        JOIN users u ON uf.following_id = u.id
        WHERE uf.follower_id = ?
        ORDER BY uf.created_at DESC
        LIMIT 8
    ");
    $stmt->execute([$user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format friends data
    foreach ($friends as &$friend) {
        $friend['avatar'] = get_user_avatar($friend['id']);
        $friend['display_name'] = $friend['display_name'] ?: $friend['username'];
    }

    echo json_encode([
        'success' => true,
        'friends' => $friends,
        'count' => count($friends)
    ]);

} catch (PDOException $e) {
    error_log("Error fetching friends profiles: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load friends.']);
}
?>
