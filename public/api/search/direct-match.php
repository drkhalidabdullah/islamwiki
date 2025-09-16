<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Get the search query
$query = $_GET['q'] ?? '';
$query = trim($query);

if (empty($query)) {
    echo json_encode(['url' => null]);
    exit;
}

try {
    // First, try to find an exact match by slug
    $stmt = $pdo->prepare("
        SELECT wa.slug, wa.title, 'article' as type
        FROM wiki_articles wa 
        WHERE wa.slug = ? AND wa.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$query]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode(['url' => '/wiki/' . $result['slug']]);
        exit;
    }
    
    // Try to find a close match by title (case insensitive)
    $stmt = $pdo->prepare("
        SELECT wa.slug, wa.title, 'article' as type
        FROM wiki_articles wa 
        WHERE LOWER(wa.title) = LOWER(?) AND wa.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$query]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode(['url' => '/wiki/' . $result['slug']]);
        exit;
    }
    
    // Try to find a partial match by title
    $stmt = $pdo->prepare("
        SELECT wa.slug, wa.title, 'article' as type
        FROM wiki_articles wa 
        WHERE LOWER(wa.title) LIKE LOWER(?) AND wa.status = 'published'
        ORDER BY 
            CASE 
                WHEN LOWER(wa.title) = LOWER(?) THEN 1
                WHEN LOWER(wa.title) LIKE LOWER(?) THEN 2
                ELSE 3
            END,
            wa.view_count DESC
        LIMIT 1
    ");
    $searchTerm = '%' . $query . '%';
    $stmt->execute([$searchTerm, $query, $searchTerm]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode(['url' => '/wiki/' . $result['slug']]);
        exit;
    }
    
    // No direct match found
    echo json_encode(['url' => null]);
    
} catch (PDOException $e) {
    error_log("Direct match search error: " . $e->getMessage());
    echo json_encode(['url' => null]);
}
?>

