<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get popular articles
    $popularArticles = getPopularArticles();
    
    // Get newest articles
    $newestArticles = getNewestArticles();
    
    // Get random facts
    $randomFacts = getRandomFacts();
    
    echo json_encode([
        'success' => true,
        'suggestions' => [
            'topArticles' => $popularArticles,
            'newestArticles' => $newestArticles,
            'didYouKnow' => $randomFacts
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Initial suggestions error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load suggestions'
    ]);
}

function getPopularArticles() {
    global $pdo;
    
    $articles = [];
    
    $stmt = $pdo->prepare("
        SELECT wa.title, wa.slug, wa.excerpt, wa.view_count, wa.created_at,
               cc.name as category_name
        FROM wiki_articles wa
        LEFT JOIN content_categories cc ON wa.category_id = cc.id
        WHERE wa.status = 'published'
        ORDER BY wa.view_count DESC, wa.created_at DESC
        LIMIT 8
    ");
    
    $stmt->execute();
    
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

function getNewestArticles() {
    global $pdo;
    
    $articles = [];
    
    $stmt = $pdo->prepare("
        SELECT wa.title, wa.slug, wa.excerpt, wa.created_at,
               cc.name as category_name
        FROM wiki_articles wa
        LEFT JOIN content_categories cc ON wa.category_id = cc.id
        WHERE wa.status = 'published'
        ORDER BY wa.created_at DESC
        LIMIT 8
    ");
    
    $stmt->execute();
    
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

function getRandomFacts() {
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
        ],
        [
            'content' => 'The word "Allah" is used by both Muslims and Arabic-speaking Christians to refer to God.',
            'source' => 'Linguistic Studies'
        ],
        [
            'content' => 'The Kaaba in Mecca is considered the most sacred site in Islam.',
            'source' => 'Quran 2:125'
        ],
        [
            'content' => 'Ramadan is the ninth month of the Islamic calendar and is observed by fasting.',
            'source' => 'Quran 2:183'
        ]
    ];
    
    // Return 5 random facts
    shuffle($facts);
    return array_slice($facts, 0, 5);
}
?>
