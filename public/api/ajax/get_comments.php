<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$post_id = $_GET['post_id'] ?? null;
$limit = (int)($_GET['limit'] ?? 20);
$offset = (int)($_GET['offset'] ?? 0);

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

try {
    $comments = get_post_comments($post_id, $limit, $offset);
    
    // Get replies for each comment
    foreach ($comments as &$comment) {
        $comment['replies'] = get_comment_replies($comment['id'], 5);
    }
    
    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'total' => get_comment_count($post_id)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
