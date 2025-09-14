<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Get search parameters
$query = sanitize_input($_GET['q'] ?? '');
$content_type = sanitize_input($_GET['type'] ?? 'all');
$category = sanitize_input($_GET['category'] ?? '');
$sort = sanitize_input($_GET['sort'] ?? 'relevance');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$user_id = $_SESSION['user_id'] ?? null;

// Initialize results
$results = [
    'articles' => [],
    'users' => [],
    'posts' => [],
    'groups' => [],
    'events' => [],
    'ummah' => []
];

$total_results = 0;

try {
    // Log search analytics
    if (!empty($query)) {
        logSearchQuery($query, $content_type, $user_id);
    }

    // Perform search if query is provided
    if (!empty($query)) {
        $search_results = performComprehensiveSearch($query, $content_type, $category, $sort, $limit, $offset, $user_id);
        $results = $search_results['results'];
        $total_results = $search_results['total'];
    }

    // Get search suggestions if no results or empty query
    $suggestions = [];
    if (empty($query) || $total_results === 0) {
        $suggestions = getSearchSuggestions($content_type);
    }

    // Get trending topics
    $trending = getTrendingTopics();

    echo json_encode([
        'success' => true,
        'query' => $query,
        'content_type' => $content_type,
        'results' => $results,
        'total_results' => $total_results,
        'page' => $page,
        'limit' => $limit,
        'suggestions' => $suggestions,
        'trending' => $trending,
        'has_more' => $total_results > ($page * $limit)
    ]);

} catch (Exception $e) {
    error_log("Enhanced search API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Search temporarily unavailable',
        'message' => $e->getMessage()
    ]);
}

function performComprehensiveSearch($query, $content_type, $category, $sort, $limit, $offset, $user_id) {
    global $pdo;
    
    $results = [
        'articles' => [],
        'users' => [],
        'posts' => [],
        'groups' => [],
        'events' => [],
        'ummah' => []
    ];
    
    $total = 0;
    
    // Search Wiki Articles
    if ($content_type === 'all' || $content_type === 'articles') {
        $articles = searchArticles($query, $category, $sort, $limit, $offset);
        $results['articles'] = $articles['results'];
        $total += $articles['count'];
    }
    
    // Search Users
    if ($content_type === 'all' || $content_type === 'people') {
        $users = searchUsers($query, $sort, $limit, $offset);
        $results['users'] = $users['results'];
        $total += $users['count'];
    }
    
    // Search Posts (User Posts only for now)
    if ($content_type === 'all' || $content_type === 'posts') {
        $posts = searchPosts($query, $sort, $limit, $offset, $user_id);
        $results['posts'] = $posts['results'];
        $total += $posts['count'];
    }
    
    // Search Groups
    if ($content_type === 'all' || $content_type === 'groups') {
        $groups = searchGroups($query, $sort, $limit, $offset);
        $results['groups'] = $groups['results'];
        $total += $groups['count'];
    }
    
    // Search Events
    if ($content_type === 'all' || $content_type === 'events') {
        $events = searchEvents($query, $sort, $limit, $offset);
        $results['events'] = $events['results'];
        $total += $events['count'];
    }
    
    // Search Ummah (Community content)
    if ($content_type === 'all' || $content_type === 'ummah') {
        $ummah = searchUmmahContent($query, $sort, $limit, $offset, $user_id);
        $results['ummah'] = $ummah['results'];
        $total += $ummah['count'];
    }
    
    return [
        'results' => $results,
        'total' => $total
    ];
}

function searchArticles($query, $category, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT a.*, c.name as category_name, u.username as author_username, u.display_name as author_name
            FROM wiki_articles a
            LEFT JOIN content_categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published'";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }
    
    if (!empty($category)) {
        $sql .= " AND a.category_id = ?";
        $params[] = $category;
    }
    
    // Add sorting
    switch ($sort) {
        case 'title':
            $sql .= " ORDER BY a.title ASC";
            break;
        case 'date':
            $sql .= " ORDER BY a.published_at DESC";
            break;
        case 'relevance':
        default:
            $sql .= " ORDER BY a.view_count DESC, a.published_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'results' => $results,
        'count' => count($results)
    ];
}

function searchUsers($query, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT u.*, up.interests
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.is_active = 1 AND u.is_banned = 0";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (u.username LIKE ? OR u.display_name LIKE ? OR u.first_name LIKE ? 
                  OR u.last_name LIKE ? OR up.interests LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 5, $search_term);
    }
    
    // Add sorting
    switch ($sort) {
        case 'name':
            $sql .= " ORDER BY u.display_name ASC";
            break;
        case 'date':
            $sql .= " ORDER BY u.created_at DESC";
            break;
        case 'relevance':
        default:
            $sql .= " ORDER BY u.last_seen_at DESC, u.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'results' => $results,
        'count' => count($results)
    ];
}

function searchPosts($query, $sort, $limit, $offset, $user_id) {
    global $pdo;
    
    $sql = "SELECT p.*, u.username, u.display_name, u.avatar
            FROM user_posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.is_public = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND p.content LIKE ?";
        $params[] = "%$query%";
    }
    
    // Add sorting
    switch ($sort) {
        case 'date':
            $sql .= " ORDER BY p.created_at DESC";
            break;
        case 'popularity':
            $sql .= " ORDER BY p.likes_count DESC, p.created_at DESC";
            break;
        case 'relevance':
        default:
            $sql .= " ORDER BY p.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'results' => $results,
        'count' => count($results)
    ];
}

function searchGroups($query, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT g.*, u.username as creator_username, u.display_name as creator_name
            FROM groups g
            JOIN users u ON g.created_by = u.id
            WHERE g.is_active = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (g.name LIKE ? OR g.description LIKE ? OR g.tags LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }
    
    // Add sorting
    switch ($sort) {
        case 'name':
            $sql .= " ORDER BY g.name ASC";
            break;
        case 'members':
            $sql .= " ORDER BY g.members_count DESC";
            break;
        case 'date':
            $sql .= " ORDER BY g.created_at DESC";
            break;
        case 'relevance':
        default:
            $sql .= " ORDER BY g.members_count DESC, g.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'results' => $results,
        'count' => count($results)
    ];
}

function searchEvents($query, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT e.*, u.username as creator_username, u.display_name as creator_name
            FROM community_events e
            JOIN users u ON e.created_by = u.id
            WHERE e.is_public = 1 AND e.start_date >= NOW()";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.tags LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }
    
    // Add sorting
    switch ($sort) {
        case 'title':
            $sql .= " ORDER BY e.title ASC";
            break;
        case 'date':
            $sql .= " ORDER BY e.start_date ASC";
            break;
        case 'popularity':
            $sql .= " ORDER BY e.current_attendees DESC";
            break;
        case 'relevance':
        default:
            $sql .= " ORDER BY e.start_date ASC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'results' => $results,
        'count' => count($results)
    ];
}

function searchUmmahContent($query, $sort, $limit, $offset, $user_id) {
    global $pdo;
    
    // Ummah content includes community discussions, announcements, and featured content
    $results = [];
    
    // Get featured articles
    $featured_articles = searchFeaturedArticles($query, $limit / 3);
    $results = array_merge($results, $featured_articles);
    
    // Get community discussions (popular posts)
    $discussions = searchCommunityDiscussions($query, $limit / 3);
    $results = array_merge($results, $discussions);
    
    // Get community announcements
    $announcements = searchCommunityAnnouncements($query, $limit / 3);
    $results = array_merge($results, $announcements);
    
    return [
        'results' => array_slice($results, 0, $limit),
        'count' => count($results)
    ];
}

function searchFeaturedArticles($query, $limit) {
    global $pdo;
    
    $sql = "SELECT 'featured_article' as content_type, a.*, c.name as category_name, u.username as author_username, u.display_name as author_name
            FROM wiki_articles a
            LEFT JOIN content_categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published' AND a.is_featured = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 3, $search_term);
    }
    
    $sql .= " ORDER BY a.view_count DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchCommunityDiscussions($query, $limit) {
    global $pdo;
    
    $sql = "SELECT 'discussion' as content_type, p.*, u.username, u.display_name, u.avatar
            FROM user_posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.is_public = 1 AND p.likes_count > 5";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND p.content LIKE ?";
        $params[] = "%$query%";
    }
    
    $sql .= " ORDER BY p.likes_count DESC, p.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchCommunityAnnouncements($query, $limit) {
    global $pdo;
    
    $sql = "SELECT 'announcement' as content_type, e.*, u.username as creator_username, u.display_name as creator_name
            FROM community_events e
            JOIN users u ON e.created_by = u.id
            WHERE e.is_public = 1 AND e.is_featured = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 2, $search_term);
    }
    
    $sql .= " ORDER BY e.start_date ASC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSearchSuggestions($content_type = 'all') {
    global $pdo;
    
    $sql = "SELECT suggestion, suggestion_type, content_type, search_count
            FROM search_suggestions 
            WHERE is_active = 1";
    
    $params = [];
    
    if ($content_type !== 'all') {
        $sql .= " AND (content_type = ? OR content_type IS NULL)";
        $params[] = $content_type;
    }
    
    $sql .= " ORDER BY search_count DESC, click_count DESC LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrendingTopics() {
    global $pdo;
    
    $sql = "SELECT suggestion, search_count, content_type
            FROM search_suggestions 
            WHERE is_active = 1 AND suggestion_type = 'trending'
            ORDER BY search_count DESC 
            LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function logSearchQuery($query, $content_type, $user_id) {
    global $pdo;
    
    try {
        $sql = "INSERT INTO search_analytics (user_id, search_query, content_type, session_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $query,
            $content_type,
            session_id(),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        // Update search count for suggestions
        $update_sql = "INSERT INTO search_suggestions (suggestion, suggestion_type, content_type, search_count)
                       VALUES (?, 'popular', ?, 1)
                       ON DUPLICATE KEY UPDATE search_count = search_count + 1";
        
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$query, $content_type]);
        
    } catch (Exception $e) {
        error_log("Error logging search query: " . $e->getMessage());
    }
}
?>
<script src="/skins/bismillah/assets/js/search_index.js"></script>
