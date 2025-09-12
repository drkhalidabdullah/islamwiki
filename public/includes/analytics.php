<?php
/**
 * Analytics and Monitoring System
 * Tracks user behavior, performance metrics, and system health
 */

/**
 * Track page view
 */
function track_page_view($page, $user_id = null, $additional_data = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO page_views (user_id, page, ip_address, user_agent, referrer, additional_data, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $page,
            get_client_ip(),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_REFERER'] ?? '',
            json_encode($additional_data)
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Analytics tracking error: " . $e->getMessage());
        return false;
    }
}

/**
 * Track user action
 */
function track_user_action($action, $user_id = null, $details = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_actions (user_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $action,
            json_encode($details),
            get_client_ip()
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("User action tracking error: " . $e->getMessage());
        return false;
    }
}

/**
 * Track performance metrics
 */
function track_performance($metric, $value, $page = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO performance_metrics (metric, value, page, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([$metric, $value, $page]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Performance tracking error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get analytics data
 */
function get_analytics_data($days = 7, $metric = 'page_views') {
    global $pdo;
    
    $tables = [
        'page_views' => 'page_views',
        'user_actions' => 'user_actions',
        'performance' => 'performance_metrics'
    ];
    
    if (!isset($tables[$metric])) {
        return [];
    }
    
    $table = $tables[$metric];
    
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM {$table}
            WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Analytics data retrieval error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get popular pages
 */
function get_popular_pages($days = 7, $limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT page, COUNT(*) as views
            FROM page_views
            WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY page
            ORDER BY views DESC
            LIMIT ?
        ");
        
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Popular pages retrieval error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user engagement metrics
 */
function get_user_engagement($days = 7) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT user_id) as active_users,
                COUNT(*) as total_actions,
                AVG(actions_per_user) as avg_actions_per_user
            FROM (
                SELECT user_id, COUNT(*) as actions_per_user
                FROM user_actions
                WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
                AND user_id IS NOT NULL
                GROUP BY user_id
            ) as user_stats
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("User engagement retrieval error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get system health metrics
 */
function get_system_health() {
    global $pdo;
    
    try {
        // Database connection test
        $db_status = $pdo->query("SELECT 1") ? 'healthy' : 'unhealthy';
        
        // Recent error count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as error_count
            FROM error_logs
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute();
        $error_count = $stmt->fetchColumn();
        
        // Average response time (if tracking)
        $stmt = $pdo->prepare("
            SELECT AVG(value) as avg_response_time
            FROM performance_metrics
            WHERE metric = 'response_time'
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute();
        $avg_response_time = $stmt->fetchColumn();
        
        return [
            'database' => $db_status,
            'errors_last_hour' => $error_count,
            'avg_response_time' => $avg_response_time ?: 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } catch (PDOException $e) {
        error_log("System health check error: " . $e->getMessage());
        return [
            'database' => 'unhealthy',
            'errors_last_hour' => 0,
            'avg_response_time' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

/**
 * Track search queries
 */
function track_search($query, $results_count, $user_id = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO search_analytics (user_id, query, results_count, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $query,
            $results_count,
            get_client_ip()
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Search tracking error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get search analytics
 */
function get_search_analytics($days = 7, $limit = 20) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT query, COUNT(*) as search_count, AVG(results_count) as avg_results
            FROM search_analytics
            WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY query
            ORDER BY search_count DESC
            LIMIT ?
        ");
        
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Search analytics retrieval error: " . $e->getMessage());
        return [];
    }
}

/**
 * Track content interaction
 */
function track_content_interaction($content_type, $content_id, $action, $user_id = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO content_interactions (user_id, content_type, content_id, action, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $content_type,
            $content_id,
            $action,
            get_client_ip()
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Content interaction tracking error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get content popularity
 */
function get_content_popularity($content_type, $days = 7, $limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT content_id, COUNT(*) as interactions
            FROM content_interactions
            WHERE content_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY content_id
            ORDER BY interactions DESC
            LIMIT ?
        ");
        
        $stmt->execute([$content_type, $days, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Content popularity retrieval error: " . $e->getMessage());
        return [];
    }
}

/**
 * Auto-track page views
 */
function auto_track_page_view() {
    $page = $_SERVER['REQUEST_URI'] ?? '/';
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    
    // Track the page view
    track_page_view($page, $user_id, [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Track performance automatically
 */
function auto_track_performance() {
    $start_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
    $end_time = microtime(true);
    $response_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
    
    track_performance('response_time', $response_time, $_SERVER['REQUEST_URI'] ?? '/');
}

// Auto-track on page load
if (!defined('DISABLE_ANALYTICS')) {
    // Check if analytics is enabled in system settings
    $enable_analytics = get_system_setting('enable_analytics', true);
    if ($enable_analytics) {
        register_shutdown_function('auto_track_performance');
        auto_track_page_view();
    }
}
?>
