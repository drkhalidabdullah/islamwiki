<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Get search query
$query = $_GET['q'] ?? '';
$query = trim($query);

if (empty($query) || strlen($query) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Query too short'
    ]);
    exit;
}

try {
    // Get top suggestions (exact matches)
    $topSuggestions = getTopSuggestions($query);
    
    // Get categorized results
    $topArticles = getTopArticles($query);
    $newestArticles = getNewestArticles($query);
    $didYouKnow = getDidYouKnowFacts($query);
    
    echo json_encode([
        'success' => true,
        'topSuggestions' => $topSuggestions,
        'topArticles' => $topArticles,
        'newestArticles' => $newestArticles,
        'didYouKnow' => $didYouKnow
    ]);
    
} catch (Exception $e) {
    error_log("Search suggestions error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Search failed'
    ]);
}

function getTopSuggestions($query) {
    global $pdo;
    
    $suggestions = [];
    
    // Search for exact title matches
    $stmt = $pdo->prepare("
        SELECT 'article' as type, title, slug, 'iw iw-file-alt' as icon, 
               CONCAT('/wiki/', slug) as url, 'View Article' as action,
               CONCAT('/wiki/', slug, '/edit') as editUrl, 1 as editable
        FROM wiki_articles 
        WHERE status = 'published' 
        AND (title LIKE ? OR slug LIKE ?)
        ORDER BY 
            CASE 
                WHEN title = ? THEN 1
                WHEN title LIKE ? THEN 2
                ELSE 3
            END,
            view_count DESC
        LIMIT 5
    ");
    
    $exactQuery = $query;
    $likeQuery = "%$query%";
    $stmt->execute([$exactQuery, $exactQuery, $exactQuery, $likeQuery]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'title' => $row['title'],
            'meta' => 'Article',
            'icon' => $row['icon'],
            'url' => $row['url'],
            'action' => $row['action'],
            'editUrl' => $row['editUrl'],
            'editable' => $row['editable']
        ];
    }
    
    return $suggestions;
}

function getTopArticles($query) {
    global $pdo;
    
    $articles = [];
    
    $stmt = $pdo->prepare("
        SELECT wa.title, wa.slug, wa.excerpt, wa.view_count, wa.created_at,
               cc.name as category_name
        FROM wiki_articles wa
        LEFT JOIN content_categories cc ON wa.category_id = cc.id
        WHERE wa.status = 'published' 
        AND (wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)
        ORDER BY wa.view_count DESC, wa.created_at DESC
        LIMIT 10
    ");
    
    $likeQuery = "%$query%";
    $stmt->execute([$likeQuery, $likeQuery, $likeQuery]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $articles[] = [
            'title' => $row['title'],
            'url' => "/wiki/" . $row['slug'],
            'category' => $row['category_name'] ?: 'General',
            'date' => date('M j, Y', strtotime($row['created_at'])),
            'excerpt' => $row['excerpt']
        ];
    }
    
    return $articles;
}

function getNewestArticles($query) {
    global $pdo;
    
    $articles = [];
    
    $stmt = $pdo->prepare("
        SELECT wa.title, wa.slug, wa.excerpt, wa.created_at,
               cc.name as category_name
        FROM wiki_articles wa
        LEFT JOIN content_categories cc ON wa.category_id = cc.id
        WHERE wa.status = 'published' 
        AND (wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)
        ORDER BY wa.created_at DESC
        LIMIT 10
    ");
    
    $likeQuery = "%$query%";
    $stmt->execute([$likeQuery, $likeQuery, $likeQuery]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $articles[] = [
            'title' => $row['title'],
            'url' => "/wiki/" . $row['slug'],
            'category' => $row['category_name'] ?: 'General',
            'date' => date('M j, Y', strtotime($row['created_at'])),
            'excerpt' => $row['excerpt']
        ];
    }
    
    return $articles;
}

function getDidYouKnowFacts($query) {
    // This could be expanded to pull from a facts database
    // For now, return some sample Islamic facts
    $facts = [
        [
            'content' => 'Islam means "submission to the will of Allah" in Arabic.',
            'source' => 'Quran 3:19'
        ],
        [
            'content' => 'Muslims pray five times a day facing the Kaaba in Mecca.',
            'source' => 'Quran 2:144'
        ],
        [
            'content' => 'The Quran was revealed over a period of 23 years.',
            'source' => 'Islamic History'
        ],
        [
            'content' => 'Islamic civilization made significant contributions to science, mathematics, and medicine during the Islamic Golden Age.',
            'source' => 'Historical Records'
        ],
        [
            'content' => 'The Five Pillars of Islam are: Shahada, Salah, Zakat, Sawm, and Hajj.',
            'source' => 'Hadith'
        ]
    ];
    
    // Filter facts based on query
    $filteredFacts = array_filter($facts, function($fact) use ($query) {
        return stripos($fact['content'], $query) !== false || 
               stripos($fact['source'], $query) !== false;
    });
    
    // If no matches, return all facts
    if (empty($filteredFacts)) {
        return array_slice($facts, 0, 5);
    }
    
    return array_slice($filteredFacts, 0, 5);
}
?>
