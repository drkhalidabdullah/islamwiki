<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$action = $input['action'] ?? '';
$article_id = (int)($input['article_id'] ?? 0);

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Article ID is required']);
    exit;
}

// Verify article exists
$stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE id = ? AND status = 'published'");
$stmt->execute([$article_id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Article not found']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'add':
            $result = add_to_watchlist($user_id, $article_id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Added to watchlist']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add to watchlist']);
            }
            break;
            
        case 'remove':
            $result = remove_from_watchlist($user_id, $article_id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Removed from watchlist']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove from watchlist']);
            }
            break;
            
        case 'toggle':
            $is_watched = is_in_watchlist($user_id, $article_id);
            if ($is_watched) {
                $result = remove_from_watchlist($user_id, $article_id);
                $message = 'Removed from watchlist';
            } else {
                $result = add_to_watchlist($user_id, $article_id);
                $message = 'Added to watchlist';
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => $message, 'watched' => !$is_watched]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update watchlist']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Watchlist error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
