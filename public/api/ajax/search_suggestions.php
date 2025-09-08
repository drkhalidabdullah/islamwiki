<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/config.php';
require_once '../../includes/functions.php';

$query = $_GET['q'] ?? '';
$limit = 10;

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $suggestions = [];
    
    // Get search suggestions from database
    $stmt = $pdo->prepare("
        SELECT suggestion, suggestion_type, content_type, search_count
        FROM search_suggestions 
        WHERE is_active = 1 
        AND suggestion LIKE ?
        ORDER BY search_count DESC, suggestion ASC
        LIMIT ?
    ");
    $stmt->execute(["%$query%", $limit]);
    $db_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add database suggestions
    foreach ($db_suggestions as $suggestion) {
        $suggestions[] = [
            'suggestion' => $suggestion['suggestion'],
            'suggestion_type' => $suggestion['suggestion_type'],
            'content_type' => $suggestion['content_type'],
            'search_count' => $suggestion['search_count']
        ];
    }
    
    // If we don't have enough suggestions, add article titles
    if (count($suggestions) < $limit) {
        $remaining = $limit - count($suggestions);
        
        $stmt = $pdo->prepare("
            SELECT title, 'article' as content_type, view_count as search_count
            FROM wiki_articles 
            WHERE status = 'published' 
            AND title LIKE ?
            ORDER BY view_count DESC, title ASC
            LIMIT ?
        ");
        $stmt->execute(["%$query%", $remaining]);
        $article_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($article_suggestions as $article) {
            // Check if this suggestion already exists
            $exists = false;
            foreach ($suggestions as $existing) {
                if (strtolower($existing['suggestion']) === strtolower($article['title'])) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $suggestions[] = [
                    'suggestion' => $article['title'],
                    'suggestion_type' => 'article',
                    'content_type' => 'article',
                    'search_count' => $article['search_count']
                ];
            }
        }
    }
    
    // If still not enough, add user suggestions
    if (count($suggestions) < $limit) {
        $remaining = $limit - count($suggestions);
        
        $stmt = $pdo->prepare("
            SELECT username, display_name, 'user' as content_type, 0 as search_count
            FROM users 
            WHERE is_active = 1 
            AND (username LIKE ? OR display_name LIKE ?)
            ORDER BY username ASC
            LIMIT ?
        ");
        $stmt->execute(["%$query%", "%$query%", $remaining]);
        $user_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($user_suggestions as $user) {
            $display_name = $user['display_name'] ?: $user['username'];
            
            // Check if this suggestion already exists
            $exists = false;
            foreach ($suggestions as $existing) {
                if (strtolower($existing['suggestion']) === strtolower($display_name)) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $suggestions[] = [
                    'suggestion' => $display_name,
                    'suggestion_type' => 'user',
                    'content_type' => 'user',
                    'search_count' => 0
                ];
            }
        }
    }
    
    // If still not enough, add category suggestions
    if (count($suggestions) < $limit) {
        $remaining = $limit - count($suggestions);
        
        $stmt = $pdo->prepare("
            SELECT name, 'category' as content_type, 0 as search_count
            FROM content_categories 
            WHERE is_active = 1 
            AND name LIKE ?
            ORDER BY name ASC
            LIMIT ?
        ");
        $stmt->execute(["%$query%", $remaining]);
        $category_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($category_suggestions as $category) {
            // Check if this suggestion already exists
            $exists = false;
            foreach ($suggestions as $existing) {
                if (strtolower($existing['suggestion']) === strtolower($category['name'])) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $suggestions[] = [
                    'suggestion' => $category['name'],
                    'suggestion_type' => 'category',
                    'content_type' => 'category',
                    'search_count' => 0
                ];
            }
        }
    }
    
    // Limit results and return
    $suggestions = array_slice($suggestions, 0, $limit);
    
    echo json_encode($suggestions);
    
} catch (Exception $e) {
    error_log("Search suggestions error: " . $e->getMessage());
    echo json_encode([]);
}
?>