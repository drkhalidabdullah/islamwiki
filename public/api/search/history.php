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

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'get';
$limit = min((int)($_GET['limit'] ?? 20), 50);

try {
    switch ($action) {
        case 'get':
            // Get user's search history
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    query,
                    content_type,
                    filters,
                    results_count,
                    searched_at
                FROM user_search_history 
                WHERE user_id = ? 
                ORDER BY searched_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$user_id, $limit]);
            $history = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'history' => $history,
                'total' => count($history)
            ]);
            break;
            
        case 'clear':
            // Clear user's search history
            $stmt = $pdo->prepare("DELETE FROM user_search_history WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Search history cleared'
            ]);
            break;
            
        case 'delete':
            // Delete specific search history item
            $history_id = (int)($_GET['id'] ?? 0);
            
            if ($history_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid history ID']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM user_search_history WHERE id = ? AND user_id = ?");
            $stmt->execute([$history_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Search history item deleted'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'History item not found']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Search history error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
