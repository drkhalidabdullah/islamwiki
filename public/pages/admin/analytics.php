<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/analytics.php';

$page_title = 'Analytics Dashboard';

// Check admin permissions
if (!is_admin()) {
    redirect('/dashboard');
}

// Get analytics data
$days = (int)($_GET['days'] ?? 7);
$metric = $_GET['metric'] ?? 'page_views';

// Get various analytics data
$page_views = get_analytics_data($days, 'page_views');
$user_actions = get_analytics_data($days, 'user_actions');
$popular_pages = get_popular_pages($days, 10);
$user_engagement = get_user_engagement($days);
$search_analytics = get_search_analytics($days, 10);
$system_health = get_system_health();

// Get content popularity
$wiki_popularity = get_content_popularity('wiki_article', $days, 5);
$post_popularity = get_content_popularity('user_post', $days, 5);

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/admin_analytics.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/analytics.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-actions">
            <a href="/admin" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Admin Panel
            </a>
        </div>
        <h1><i class="fas fa-chart-line"></i> Analytics Dashboard</h1>
        <p>Monitor site performance, user behavior, and content engagement</p>
    </div>

    <!-- Time Period Selector -->
    <div class="analytics-controls">
        <div class="time-period-selector">
            <label for="days">Time Period:</label>
            <select id="days" onchange="updateAnalytics()">
                <option value="1" <?php echo $days === 1 ? 'selected' : ''; ?>>Last 24 Hours</option>
                <option value="7" <?php echo $days === 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30" <?php echo $days === 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                <option value="90" <?php echo $days === 90 ? 'selected' : ''; ?>>Last 90 Days</option>
            </select>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="health-overview">
        <h2>System Health</h2>
        <div class="health-cards">
            <div class="health-card <?php echo $system_health['database'] === 'healthy' ? 'healthy' : 'unhealthy'; ?>">
                <div class="health-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="health-content">
                    <h3>Database</h3>
                    <p><?php echo ucfirst($system_health['database']); ?></p>
                </div>
            </div>
            <div class="health-card <?php echo $system_health['errors_last_hour'] < 10 ? 'healthy' : 'warning'; ?>">
                <div class="health-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="health-content">
                    <h3>Errors (Last Hour)</h3>
                    <p><?php echo number_format($system_health['errors_last_hour']); ?></p>
                </div>
            </div>
            <div class="health-card <?php echo $system_health['avg_response_time'] < 1000 ? 'healthy' : 'warning'; ?>">
                <div class="health-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="health-content">
                    <h3>Avg Response Time</h3>
                    <p><?php echo number_format($system_health['avg_response_time'], 0); ?>ms</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format(array_sum(array_column($page_views, 'count'))); ?></h3>
                <p>Page Views</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format($user_engagement['active_users'] ?? 0); ?></h3>
                <p>Active Users</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format($user_engagement['total_actions'] ?? 0); ?></h3>
                <p>User Actions</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">
                <i class="fas fa-search"></i>
            </div>
            <div class="metric-content">
                <h3><?php echo number_format(array_sum(array_column($search_analytics, 'search_count'))); ?></h3>
                <p>Search Queries</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <h3>Page Views Over Time</h3>
            <canvas id="pageViewsChart" width="400" height="200"></canvas>
        </div>
        <div class="chart-container">
            <h3>User Actions Over Time</h3>
            <canvas id="userActionsChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Popular Content -->
    <div class="content-analytics">
        <div class="analytics-section">
            <h3>Popular Pages</h3>
            <div class="content-list">
                <?php foreach ($popular_pages as $page): ?>
                <div class="content-item">
                    <div class="content-info">
                        <h4><?php echo htmlspecialchars($page['page']); ?></h4>
                        <p><?php echo number_format($page['views']); ?> views</p>
                    </div>
                    <div class="content-stats">
                        <span class="stat"><?php echo number_format($page['unique_users']); ?> users</span>
                        <span class="stat"><?php echo number_format($page['unique_ips']); ?> IPs</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="analytics-section">
            <h3>Popular Search Queries</h3>
            <div class="content-list">
                <?php foreach ($search_analytics as $search): ?>
                <div class="content-item">
                    <div class="content-info">
                        <h4><?php echo htmlspecialchars($search['query']); ?></h4>
                        <p><?php echo number_format($search['search_count']); ?> searches</p>
                    </div>
                    <div class="content-stats">
                        <span class="stat"><?php echo number_format($search['avg_results'], 1); ?> avg results</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Content Popularity -->
    <div class="content-popularity">
        <div class="analytics-section">
            <h3>Popular Wiki Articles</h3>
            <div class="content-list">
                <?php foreach ($wiki_popularity as $content): ?>
                <div class="content-item">
                    <div class="content-info">
                        <h4>Article #<?php echo $content['content_id']; ?></h4>
                        <p><?php echo number_format($content['interactions']); ?> interactions</p>
                    </div>
                    <div class="content-stats">
                        <span class="stat"><?php echo number_format($content['unique_users']); ?> users</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="analytics-section">
            <h3>Popular User Posts</h3>
            <div class="content-list">
                <?php foreach ($post_popularity as $content): ?>
                <div class="content-item">
                    <div class="content-info">
                        <h4>Post #<?php echo $content['content_id']; ?></h4>
                        <p><?php echo number_format($content['interactions']); ?> interactions</p>
                    </div>
                    <div class="content-stats">
                        <span class="stat"><?php echo number_format($content['unique_users']); ?> users</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



<?php include "../../includes/footer.php"; ?>