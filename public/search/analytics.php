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
                <i class="fas fa-search"></i>
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
                <i class="fas fa-users"></i>
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
                <i class="fas fa-chart-line"></i>
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
                <i class="fas fa-clock"></i>
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
                                <i class="fas fa-arrow-<?php echo $search['trend'] === 'up' ? 'up' : ($search['trend'] === 'down' ? 'down' : 'right'); ?>"></i>
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

<style>
.analytics-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e4e6ea;
}

.analytics-header h1 {
    color: #2c3e50;
    margin: 0;
}

.analytics-controls select {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    font-size: 14px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.metric-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.metric-icon {
    width: 60px;
    height: 60px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.metric-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
}

.metric-content p {
    margin: 0.25rem 0;
    color: #6c757d;
    font-size: 14px;
}

.metric-change {
    font-size: 12px;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.metric-change.positive {
    background: #d4edda;
    color: #155724;
}

.metric-change.negative {
    background: #f8d7da;
    color: #721c24;
}

.analytics-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.analytics-section h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e4e6ea;
}

.chart-container {
    height: 400px;
    position: relative;
}

.popular-searches {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.search-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #3498db;
}

.search-rank {
    width: 40px;
    height: 40px;
    background: #3498db;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.search-content {
    flex: 1;
}

.search-content h4 {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
}

.search-content p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.search-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
    font-size: 12px;
}

.trend-indicator.up {
    color: #28a745;
}

.trend-indicator.down {
    color: #dc3545;
}

.trend-indicator.stable {
    color: #6c757d;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.insight-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #17a2b8;
    display: flex;
    gap: 1rem;
}

.insight-icon {
    width: 50px;
    height: 50px;
    background: #17a2b8;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.insight-content h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.insight-content p {
    margin: 0 0 0.5rem 0;
    color: #6c757d;
    font-size: 14px;
}

.insight-recommendation {
    background: white;
    padding: 0.75rem;
    border-radius: 4px;
    font-size: 13px;
    color: #495057;
}

.performance-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.performance-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.performance-card h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.success-rate {
    text-align: center;
}

.success-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg <?php echo $analytics_data['success_rate'] * 3.6; ?>deg, #e9ecef <?php echo $analytics_data['success_rate'] * 3.6; ?>deg 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    position: relative;
}

.success-circle::before {
    content: '';
    position: absolute;
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
}

.success-percentage {
    position: relative;
    z-index: 1;
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .analytics-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .performance-grid {
        grid-template-columns: 1fr;
    }
    
    .search-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Analytics data
const analyticsData = <?php echo json_encode($analytics_data); ?>;
const trendsData = <?php echo json_encode($trends_data); ?>;
const searchTypesData = <?php echo json_encode($analytics_data['search_types']); ?>;

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initTrendsChart();
    initSearchTypesChart();
});

function initTrendsChart() {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.labels,
            datasets: [{
                label: 'Searches',
                data: trendsData.searches,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initSearchTypesChart() {
    const ctx = document.getElementById('searchTypesChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(searchTypesData),
            datasets: [{
                data: Object.values(searchTypesData),
                backgroundColor: [
                    '#3498db',
                    '#e74c3c',
                    '#2ecc71',
                    '#f39c12',
                    '#9b59b6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateAnalytics() {
    const period = document.getElementById('periodSelect').value;
    window.location.href = `/search/analytics?period=${period}`;
}
</script>

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
            'icon' => 'fas fa-chart-line',
            'title' => 'High Search Volume',
            'description' => 'Your search system is experiencing high usage with ' . number_format($analytics_data['total_searches']) . ' searches this period.',
            'recommendation' => 'Consider optimizing search performance and adding more content to meet user demand.'
        ];
    }
    
    // Success rate insight
    if ($analytics_data['success_rate'] < 70) {
        $insights[] = [
            'icon' => 'fas fa-exclamation-triangle',
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
                'icon' => 'fas fa-users',
                'title' => 'High User Engagement',
                'description' => 'Users are highly engaged with an average of ' . number_format($searches_per_user, 1) . ' searches per user.',
                'recommendation' => 'This indicates strong user satisfaction with the search functionality.'
            ];
        }
    }
    
    return $insights;
}
?>
