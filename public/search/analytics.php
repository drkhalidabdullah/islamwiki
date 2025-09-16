<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: /login');
    exit;
}

$period = $_GET['period'] ?? 'week';
$limit = min((int)($_GET['limit'] ?? 50), 100);

// Get analytics data
$analytics_data = getSearchAnalytics($period, $limit);
$trends_data = getSearchTrends($period);
$popular_searches = getPopularSearches($period, $limit);
$search_insights = generateSearchInsights($analytics_data, $trends_data);

include "../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/search_analytics.js"></script>
<?php

?>
<script src="/skins/bismillah/assets/js/search_analytics.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/search_analytics.css">
<?php
?>

<div class="analytics-page">
    <div class="analytics-header">
        <h1>Search Analytics Dashboard</h1>
        <div class="analytics-controls">
            <select id="periodSelect" onchange="updateAnalytics()">
                <option value="day" <?php echo $period === 'day' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>This Week</option>
                <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>This Month</option>
                <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>This Year</option>
            </select>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <i class="iw iw-search"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format($analytics_data['total_searches']); ?></h3>
                <p>Total Searches</p>
                <span class="metric-change <?php echo $analytics_data['search_change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics_data['search_change'] >= 0 ? '+' : ''; ?><?php echo number_format($analytics_data['search_change'], 1); ?>%
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="iw iw-users"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format($analytics_data['unique_searchers']); ?></h3>
                <p>Unique Searchers</p>
                <span class="metric-change <?php echo $analytics_data['searcher_change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics_data['searcher_change'] >= 0 ? '+' : ''; ?><?php echo number_format($analytics_data['searcher_change'], 1); ?>%
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="iw iw-chart-line"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format($analytics_data['avg_results_per_search'], 1); ?></h3>
                <p>Avg Results/Search</p>
                <span class="metric-change <?php echo $analytics_data['results_change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics_data['results_change'] >= 0 ? '+' : ''; ?><?php echo number_format($analytics_data['results_change'], 1); ?>%
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="iw iw-clock"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format($analytics_data['avg_search_time'], 2); ?>s</h3>
                <p>Avg Search Time</p>
                <span class="metric-change <?php echo $analytics_data['time_change'] <= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics_data['time_change'] <= 0 ? '' : '+'; ?><?php echo number_format($analytics_data['time_change'], 1); ?>%
                </span>
            </div>
        </div>
    </div>

    <!-- Search Trends Chart -->
    <div class="analytics-section">
        <h2>Search Trends</h2>
        <div class="chart-container">
            <canvas id="trendsChart"></canvas>
        </div>
    </div>

    <!-- Popular Searches -->
    <div class="analytics-section">
        <h2>Popular Searches</h2>
        <div class="popular-searches">
            <?php foreach ($popular_searches as $index => $search): ?>
                <div class="search-item">
                    <div class="search-rank"><?php echo $index + 1; ?></div>
                    <div class="search-content">
                        <h4><?php echo htmlspecialchars($search['query']); ?></h4>
                        <p><?php echo number_format($search['search_count']); ?> searches</p>
                        <div class="search-trend">
                            <span class="trend-indicator <?php echo $search['trend'] === 'up' ? 'up' : ($search['trend'] === 'down' ? 'down' : 'stable'); ?>">
                                <i class="iw iw-arrow-<?php echo $search['trend'] === 'up' ? 'up' : ($search['trend'] === 'down' ? 'down' : 'right'); ?>"></i>
                            </span>
                            <span><?php echo ucfirst($search['trend']); ?></span>
                        </div>
                    </div>
                    <div class="search-actions">
                        <a href="/search?q=<?php echo urlencode($search['query']); ?>" class="btn btn-sm">View Results</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Search Insights -->
    <div class="analytics-section">
        <h2>Search Insights</h2>
        <div class="insights-grid">
            <?php foreach ($search_insights as $insight): ?>
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="<?php echo $insight['icon']; ?>"></i>
                    </div>
                    <div class="insight-content">
                        <h4><?php echo $insight['title']; ?></h4>
                        <p><?php echo $insight['description']; ?></p>
                        <?php if (isset($insight['recommendation'])): ?>
                            <div class="insight-recommendation">
                                <strong>Recommendation:</strong> <?php echo $insight['recommendation']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Search Performance -->
    <div class="analytics-section">
        <h2>Search Performance</h2>
        <div class="performance-grid">
            <div class="performance-card">
                <h3>Search Types</h3>
                <div class="performance-chart">
                    <canvas id="searchTypesChart"></canvas>
                </div>
            </div>
            <div class="performance-card">
                <h3>Search Success Rate</h3>
                <div class="success-rate">
                    <div class="success-circle">
                        <span class="success-percentage"><?php echo number_format($analytics_data['success_rate'], 1); ?>%</span>
                    </div>
                    <p>Successful searches (results found)</p>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
function getSearchAnalytics($period, $limit) {
    global $pdo;
    
    $date_condition = getDateCondition($period);
    
    // Get total searches
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_searches
        FROM search_analytics 
        WHERE search_time >= $date_condition
    ");
    $stmt->execute();
    $total_searches = $stmt->fetch()['total_searches'];
    
    // Get unique searchers
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT user_id) as unique_searchers
        FROM search_analytics 
        WHERE search_time >= $date_condition AND user_id IS NOT NULL
    ");
    $stmt->execute();
    $unique_searchers = $stmt->fetch()['unique_searchers'];
    
    // Get average results per search
    $stmt = $pdo->prepare("
        SELECT AVG(results_count) as avg_results
        FROM search_analytics 
        WHERE search_time >= $date_condition
    ");
    $stmt->execute();
    $avg_results = $stmt->fetch()['avg_results'] ?? 0;
    
    // Get search types distribution
    $stmt = $pdo->prepare("
        SELECT content_type, COUNT(*) as count
        FROM search_analytics 
        WHERE search_time >= $date_condition
        GROUP BY content_type
    ");
    $stmt->execute();
    $search_types = [];
    while ($row = $stmt->fetch()) {
        $search_types[$row['content_type']] = $row['count'];
    }
    
    // Calculate success rate (searches with results > 0)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN results_count > 0 THEN 1 ELSE 0 END) as successful
        FROM search_analytics 
        WHERE search_time >= $date_condition
    ");
    $stmt->execute();
    $success_data = $stmt->fetch();
    $success_rate = $success_data['total'] > 0 ? ($success_data['successful'] / $success_data['total']) * 100 : 0;
    
    // Get previous period data for comparison
    $prev_period_data = getPreviousPeriodData($period);
    
    return [
        'total_searches' => $total_searches,
        'unique_searchers' => $unique_searchers,
        'avg_results_per_search' => $avg_results,
        'avg_search_time' => 0.5, // Placeholder
        'search_types' => $search_types,
        'success_rate' => $success_rate,
        'search_change' => calculateChange($total_searches, $prev_period_data['total_searches']),
        'searcher_change' => calculateChange($unique_searchers, $prev_period_data['unique_searchers']),
        'results_change' => calculateChange($avg_results, $prev_period_data['avg_results']),
        'time_change' => 0 // Placeholder
    ];
}

function getDateCondition($period) {
    switch ($period) {
        case 'day':
            return 'DATE_SUB(NOW(), INTERVAL 1 DAY)';
        case 'week':
            return 'DATE_SUB(NOW(), INTERVAL 1 WEEK)';
        case 'month':
            return 'DATE_SUB(NOW(), INTERVAL 1 MONTH)';
        case 'year':
            return 'DATE_SUB(NOW(), INTERVAL 1 YEAR)';
        default:
            return 'DATE_SUB(NOW(), INTERVAL 1 WEEK)';
    }
}

function getPreviousPeriodData($period) {
    global $pdo;
    
    $prev_date_condition = getPreviousDateCondition($period);
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_searches,
            COUNT(DISTINCT user_id) as unique_searchers,
            AVG(results_count) as avg_results
        FROM search_analytics 
        WHERE search_time >= $prev_date_condition
    ");
    $stmt->execute();
    return $stmt->fetch();
}

function getPreviousDateCondition($period) {
    switch ($period) {
        case 'day':
            return 'DATE_SUB(NOW(), INTERVAL 2 DAY)';
        case 'week':
            return 'DATE_SUB(NOW(), INTERVAL 2 WEEK)';
        case 'month':
            return 'DATE_SUB(NOW(), INTERVAL 2 MONTH)';
        case 'year':
            return 'DATE_SUB(NOW(), INTERVAL 2 YEAR)';
        default:
            return 'DATE_SUB(NOW(), INTERVAL 2 WEEK)';
    }
}

function calculateChange($current, $previous) {
    if ($previous == 0) return 0;
    return (($current - $previous) / $previous) * 100;
}

function getSearchTrends($period) {
    global $pdo;
    
    $date_condition = getDateCondition($period);
    $group_by = getGroupBy($period);
    
    $stmt = $pdo->prepare("
        SELECT 
            $group_by as period,
            COUNT(*) as searches
        FROM search_analytics 
        WHERE search_time >= $date_condition
        GROUP BY $group_by
        ORDER BY period ASC
    ");
    $stmt->execute();
    
    $trends = $stmt->fetchAll();
    
    return [
        'labels' => array_column($trends, 'period'),
        'searches' => array_column($trends, 'searches')
    ];
}

function getGroupBy($period) {
    switch ($period) {
        case 'day':
            return 'HOUR(search_time)';
        case 'week':
            return 'DATE(search_time)';
        case 'month':
            return 'DATE(search_time)';
        case 'year':
            return 'MONTH(search_time)';
        default:
            return 'DATE(search_time)';
    }
}

function getPopularSearches($period, $limit) {
    global $pdo;
    
    $date_condition = getDateCondition($period);
    
    $stmt = $pdo->prepare("
        SELECT 
            query,
            COUNT(*) as search_count,
            AVG(results_count) as avg_results
        FROM search_analytics 
        WHERE search_time >= $date_condition
        GROUP BY query
        ORDER BY search_count DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    
    $searches = $stmt->fetchAll();
    
    // Add trend information
    foreach ($searches as &$search) {
        $search['trend'] = getSearchTrend($search['query'], $period);
    }
    
    return $searches;
}

function getSearchTrend($query, $period) {
    global $pdo;
    
    $current_condition = getDateCondition($period);
    $prev_condition = getPreviousDateCondition($period);
    
    // Get current period count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM search_analytics 
        WHERE query = ? AND search_time >= $current_condition
    ");
    $stmt->execute([$query]);
    $current_count = $stmt->fetch()['count'];
    
    // Get previous period count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM search_analytics 
        WHERE query = ? AND search_time >= $prev_condition AND search_time < $current_condition
    ");
    $stmt->execute([$query]);
    $prev_count = $stmt->fetch()['count'];
    
    if ($prev_count == 0) return 'stable';
    if ($current_count > $prev_count * 1.1) return 'up';
    if ($current_count < $prev_count * 0.9) return 'down';
    return 'stable';
}

function generateSearchInsights($analytics_data, $trends_data) {
    $insights = [];
    
    // Search volume insight
    if ($analytics_data['total_searches'] > 1000) {
        $insights[] = [
            'icon' => 'iw iw-chart-line',
            'title' => 'High Search Volume',
            'description' => 'Your search system is experiencing high usage with ' . number_format($analytics_data['total_searches']) . ' searches this period.',
            'recommendation' => 'Consider optimizing search performance and adding more content to meet user demand.'
        ];
    }
    
    // Success rate insight
    if ($analytics_data['success_rate'] < 70) {
        $insights[] = [
            'icon' => 'iw iw-exclamation-triangle',
            'title' => 'Low Success Rate',
            'description' => 'Only ' . number_format($analytics_data['success_rate'], 1) . '% of searches are returning results.',
            'recommendation' => 'Improve search algorithms and add more content to increase success rate.'
        ];
    }
    
    // User engagement insight
    if ($analytics_data['unique_searchers'] > 0) {
        $searches_per_user = $analytics_data['total_searches'] / $analytics_data['unique_searchers'];
        if ($searches_per_user > 5) {
            $insights[] = [
                'icon' => 'iw iw-users',
                'title' => 'High User Engagement',
                'description' => 'Users are highly engaged with an average of ' . number_format($searches_per_user, 1) . ' searches per user.',
                'recommendation' => 'This indicates strong user satisfaction with the search functionality.'
            ];
        }
    }
    
    return $insights;
}
?>
