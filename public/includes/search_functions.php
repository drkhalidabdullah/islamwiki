<?php
/**
 * Search Functions for IslamWiki
 * Provides comprehensive search functionality across all content types
 * 
 * @author Khalid Abdullah
 * @version 0.0.0.18
 * @license AGPL-3.0
 */

/**
 * Perform comprehensive search across all content types
 */
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
        $ummah = searchUmmah($query, $sort, $limit, $offset);
        $results['ummah'] = $ummah['results'];
        $total += $ummah['count'];
    }
    
    return [
        'results' => $results,
        'total' => $total
    ];
}

/**
 * Search wiki articles
 */
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
        $search_term = "%$query%";
        $params = array_fill(0, 3, $search_term);
    }
    
    if (!empty($category)) {
        $sql .= " AND a.category_id = ?";
        $params[] = $category;
    }
    
    // Add sorting
    switch ($sort) {
        case 'date':
            $sql .= " ORDER BY a.published_at DESC";
            break;
        case 'title':
            $sql .= " ORDER BY a.title ASC";
            break;
        case 'popularity':
            $sql .= " ORDER BY a.view_count DESC";
            break;
        default: // relevance
            $sql .= " ORDER BY a.view_count DESC, a.published_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM wiki_articles a WHERE a.status = 'published'";
    $count_params = [];
    
    if (!empty($query)) {
        $count_sql .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
        $search_term = "%$query%";
        $count_params = array_fill(0, 3, $search_term);
    }
    
    if (!empty($category)) {
        $count_sql .= " AND a.category_id = ?";
        $count_params[] = $category;
    }
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $count = $count_stmt->fetchColumn();
    
    return [
        'results' => $results,
        'count' => $count
    ];
}

/**
 * Search users
 */
function searchUsers($query, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT u.*, up.interests
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.status = 'active'";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (u.username LIKE ? OR u.display_name LIKE ? OR u.email LIKE ? OR up.bio LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 4, $search_term);
    }
    
    // Add sorting
    switch ($sort) {
        case 'date':
            $sql .= " ORDER BY u.created_at DESC";
            break;
        case 'title':
            $sql .= " ORDER BY u.display_name ASC";
            break;
        case 'popularity':
            $sql .= " ORDER BY u.reputation DESC";
            break;
        default: // relevance
            $sql .= " ORDER BY u.reputation DESC, u.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.status = 'active'";
    $count_params = [];
    
    if (!empty($query)) {
        $count_sql .= " AND (u.username LIKE ? OR u.display_name LIKE ? OR u.email LIKE ? OR up.bio LIKE ?)";
        $search_term = "%$query%";
        $count_params = array_fill(0, 4, $search_term);
    }
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $count = $count_stmt->fetchColumn();
    
    return [
        'results' => $results,
        'count' => $count
    ];
}

/**
 * Search user posts
 */
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
        case 'title':
            $sql .= " ORDER BY p.content ASC";
            break;
        case 'popularity':
            $sql .= " ORDER BY p.likes_count DESC";
            break;
        default: // relevance
            $sql .= " ORDER BY p.likes_count DESC, p.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM user_posts p WHERE p.is_public = 1";
    $count_params = [];
    
    if (!empty($query)) {
        $count_sql .= " AND p.content LIKE ?";
        $count_params[] = "%$query%";
    }
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $count = $count_stmt->fetchColumn();
    
    return [
        'results' => $results,
        'count' => $count
    ];
}

/**
 * Search groups
 */
function searchGroups($query, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT g.*, u.username as creator_username, u.display_name as creator_name,
                   (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) as members_count,
                   (SELECT COUNT(*) FROM group_posts gp WHERE gp.group_id = g.id) as posts_count
            FROM groups g
            LEFT JOIN users u ON g.created_by = u.id
            WHERE g.is_active = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (g.name LIKE ? OR g.description LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 2, $search_term);
    }
    
    // Add sorting
    switch ($sort) {
        case 'date':
            $sql .= " ORDER BY g.created_at DESC";
            break;
        case 'title':
            $sql .= " ORDER BY g.name ASC";
            break;
        case 'popularity':
            $sql .= " ORDER BY members_count DESC";
            break;
        default: // relevance
            $sql .= " ORDER BY members_count DESC, g.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM groups g WHERE g.is_active = 1";
    $count_params = [];
    
    if (!empty($query)) {
        $count_sql .= " AND (g.name LIKE ? OR g.description LIKE ?)";
        $search_term = "%$query%";
        $count_params = array_fill(0, 2, $search_term);
    }
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $count = $count_stmt->fetchColumn();
    
    return [
        'results' => $results,
        'count' => $count
    ];
}

/**
 * Search events
 */
function searchEvents($query, $sort, $limit, $offset) {
    global $pdo;
    
    $sql = "SELECT e.*, u.username as creator_username, u.display_name as creator_name,
                   (SELECT COUNT(*) FROM event_attendees ea WHERE ea.event_id = e.id) as current_attendees
            FROM community_events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.is_public = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 2, $search_term);
    }
    
    // Add sorting
    switch ($sort) {
        case 'date':
            $sql .= " ORDER BY e.start_date ASC";
            break;
        case 'title':
            $sql .= " ORDER BY e.title ASC";
            break;
        case 'popularity':
            $sql .= " ORDER BY current_attendees DESC";
            break;
        default: // relevance
            $sql .= " ORDER BY e.start_date ASC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM community_events e WHERE e.is_public = 1";
    $count_params = [];
    
    if (!empty($query)) {
        $count_sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
        $search_term = "%$query%";
        $count_params = array_fill(0, 2, $search_term);
    }
    
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $count = $count_stmt->fetchColumn();
    
    return [
        'results' => $results,
        'count' => $count
    ];
}

/**
 * Search Ummah (Community content)
 */
function searchUmmah($query, $sort, $limit, $offset) {
    global $pdo;
    
    $results = [];
    
    // Search featured articles
    $featured_articles = searchFeaturedArticles($query, $limit);
    $results = array_merge($results, $featured_articles);
    
    // Search community discussions
    $discussions = searchCommunityDiscussions($query, $limit);
    $results = array_merge($results, $discussions);
    
    // Search community announcements
    $announcements = searchCommunityAnnouncements($query, $limit);
    $results = array_merge($results, $announcements);
    
    // Sort and limit results
    usort($results, function($a, $b) {
        return strtotime($b['created_at'] ?? $b['published_at']) - strtotime($a['created_at'] ?? $a['published_at']);
    });
    
    $results = array_slice($results, $offset, $limit);
    
    return [
        'results' => $results,
        'count' => count($results)
    ];
}

/**
 * Search featured articles
 */
function searchFeaturedArticles($query, $limit) {
    global $pdo;
    
    $sql = "SELECT 'featured_article' as content_type, a.*, u.username, u.display_name
            FROM wiki_articles a
            JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published' AND a.is_featured = 1";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (a.title LIKE ? OR a.content LIKE ?)";
        $search_term = "%$query%";
        $params = array_fill(0, 2, $search_term);
    }
    
    $sql .= " ORDER BY a.published_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Search community discussions
 */
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

/**
 * Search community announcements
 */
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

/**
 * Get search suggestions
 */
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

/**
 * Get trending topics
 */
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

/**
 * Log search query for analytics
 */
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

