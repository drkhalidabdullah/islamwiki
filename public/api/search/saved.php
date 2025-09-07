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
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get user's saved searches
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    name,
                    query,
                    content_type,
                    filters,
                    created_at,
                    last_searched
                FROM saved_searches 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            $saved_searches = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'saved_searches' => $saved_searches,
                'total' => count($saved_searches)
            ]);
            break;
            
        case 'POST':
            // Save a new search
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['name']) || !isset($input['query'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Name and query are required']);
                exit;
            }
            
            $name = sanitize_input($input['name']);
            $query = sanitize_input($input['query']);
            $content_type = sanitize_input($input['content_type'] ?? 'all');
            $filters = json_encode($input['filters'] ?? []);
            
            $stmt = $pdo->prepare("
                INSERT INTO saved_searches (user_id, name, query, content_type, filters)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $name, $query, $content_type, $filters]);
            
            $saved_search_id = $pdo->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'saved_search' => [
                    'id' => $saved_search_id,
                    'name' => $name,
                    'query' => $query,
                    'content_type' => $content_type,
                    'filters' => $input['filters'] ?? []
                ]
            ]);
            break;
            
        case 'DELETE':
            // Delete a saved search
            $saved_search_id = (int)($_GET['id'] ?? 0);
            
            if ($saved_search_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid saved search ID']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM saved_searches WHERE id = ? AND user_id = ?");
            $stmt->execute([$saved_search_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Saved search deleted'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Saved search not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Saved searches error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
