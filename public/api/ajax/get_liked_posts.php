<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT post_id 
        FROM post_interactions 
        WHERE user_id = ? AND interaction_type = 'like'
    ");
    $stmt->execute([$current_user_id]);
    $liked_posts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'liked_posts' => $liked_posts
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
