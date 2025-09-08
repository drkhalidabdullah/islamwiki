<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'Analytics & Reports';
require_login();
require_admin();

$current_user = get_user($_SESSION['user_id']);

// Get date range
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Validate dates
$date_from = date('Y-m-d', strtotime($date_from));
$date_to = date('Y-m-d', strtotime($date_to));

// Get analytics data
$analytics = [];

// Page views
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as views
    FROM wiki_articles 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
$analytics['page_views'] = $stmt->fetchAll();

// Article creation trends
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as articles
    FROM wiki_articles 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
$analytics['article_creation'] = $stmt->fetchAll();

// Most viewed articles
$stmt = $pdo->prepare("
    SELECT 
        wa.title,
        wa.slug,
        wa.view_count,
        wa.created_at,
        u.username,
        u.display_name
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    WHERE wa.status = 'published'
    ORDER BY wa.view_count DESC
    LIMIT 10
");
$stmt->execute();
$analytics['most_viewed'] = $stmt->fetchAll();

// Most active users
$stmt = $pdo->prepare("
    SELECT 
        u.username,
        u.display_name,
        COUNT(wa.id) as articles_created,
        SUM(wa.view_count) as total_views,
        MAX(wa.created_at) as last_activity
    FROM users u
    LEFT JOIN wiki_articles wa ON u.id = wa.author_id
    WHERE wa.created_at BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY articles_created DESC, total_views DESC
    LIMIT 10
");
$stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
$analytics['active_users'] = $stmt->fetchAll();

// Category statistics
$stmt = $pdo->prepare("
    SELECT 
        cc.name as category_name,
        COUNT(wa.id) as article_count,
        SUM(wa.view_count) as total_views,
        AVG(wa.view_count) as avg_views
    FROM content_categories cc
    LEFT JOIN wiki_articles wa ON cc.id = wa.category_id
    WHERE wa.status = 'published'
    GROUP BY cc.id
    ORDER BY article_count DESC
");
$stmt->execute();
$analytics['categories'] = $stmt->fetchAll();

// User registration trends
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as registrations
    FROM users 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
$analytics['user_registrations'] = $stmt->fetchAll();

// Search statistics
$stmt = $pdo->prepare("
    SELECT 
        suggestion,
        search_count,
        suggestion_type,
        content_type
    FROM search_suggestions 
    WHERE is_active = 1
    ORDER BY search_count DESC
    LIMIT 20
");
$stmt->execute();
$analytics['search_stats'] = $stmt->fetchAll();

// File upload statistics
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as uploads,
        SUM(file_size) as total_size
    FROM wiki_files 
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->execute([$date_from . ' 00:00:00', $date_to . ' 23:59:59']);
$analytics['file_uploads'] = $stmt->fetchAll();

// System health metrics
$stmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM wiki_articles WHERE status = 'published') as published_articles,
        (SELECT COUNT(*) FROM wiki_articles WHERE status = 'draft') as draft_articles,
        (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users,
        (SELECT COUNT(*) FROM wiki_files) as total_files,
        (SELECT COUNT(*) FROM wiki_redirects) as total_redirects,
        (SELECT COUNT(*) FROM article_versions) as total_versions
");
$analytics['system_health'] = $stmt->fetch();

// Calculate totals
$analytics['totals'] = [
    'total_views' => array_sum(array_column($analytics['page_views'], 'views')),
    'total_articles' => array_sum(array_column($analytics['article_creation'], 'articles')),
    'total_registrations' => array_sum(array_column($analytics['user_registrations'], 'registrations')),
    'total_uploads' => array_sum(array_column($analytics['file_uploads'], 'uploads')),
    'total_upload_size' => array_sum(array_column($analytics['file_uploads'], 'total_size'))
];

include '../../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-header">
        <h1>Analytics & Reports</h1>
        <div class="admin-actions">
            <a href="/admin" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card">
        <h2>Date Range</h2>
        <form method="GET" class="date-filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="date_from">From:</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="form-group">
                    <label for="date_to">To:</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="card">
        <h2>Summary Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?php echo number_format($analytics['totals']['total_views']); ?></h3>
                    <p>Total Page Views</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📝</div>
                <div class="stat-content">
                    <h3><?php echo number_format($analytics['totals']['total_articles']); ?></h3>
                    <p>Articles Created</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo number_format($analytics['totals']['total_registrations']); ?></h3>
                    <p>New Users</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📁</div>
                <div class="stat-content">
                    <h3><?php echo number_format($analytics['totals']['total_uploads']); ?></h3>
                    <p>Files Uploaded</p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">💾</div>
                <div class="stat-content">
                    <h3><?php echo format_file_size($analytics['totals']['total_upload_size']); ?></h3>
                    <p>Storage Used</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <div class="card">
                <h2>Page Views Over Time</h2>
                <canvas id="pageViewsChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="card">
                <h2>Article Creation Trends</h2>
                <canvas id="articleCreationChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="card">
                <h2>User Registrations</h2>
                <canvas id="userRegistrationsChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="card">
                <h2>File Uploads</h2>
                <canvas id="fileUploadsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Most Viewed Articles -->
    <div class="card">
        <h2>Most Viewed Articles</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Article</th>
                        <th>Author</th>
                        <th>Views</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['most_viewed'] as $index => $article): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <a href="/wiki/<?php echo $article['slug']; ?>">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></td>
                            <td><?php echo number_format($article['view_count']); ?></td>
                            <td><?php echo format_date($article['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Most Active Users -->
    <div class="card">
        <h2>Most Active Users</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Articles Created</th>
                        <th>Total Views</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['active_users'] as $user): ?>
                        <tr>
                            <td>
                                <a href="/user/<?php echo $user['username']; ?>">
                                    <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?>
                                </a>
                            </td>
                            <td><?php echo number_format($user['articles_created']); ?></td>
                            <td><?php echo number_format($user['total_views']); ?></td>
                            <td><?php echo format_date($user['last_activity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Statistics -->
    <div class="card">
        <h2>Category Statistics</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Articles</th>
                        <th>Total Views</th>
                        <th>Avg Views</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['categories'] as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo number_format($category['article_count']); ?></td>
                            <td><?php echo number_format($category['total_views']); ?></td>
                            <td><?php echo number_format($category['avg_views'], 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Search Statistics -->
    <div class="card">
        <h2>Popular Search Terms</h2>
        <div class="search-stats">
            <?php foreach ($analytics['search_stats'] as $search): ?>
                <div class="search-item">
                    <span class="search-term"><?php echo htmlspecialchars($search['suggestion']); ?></span>
                    <span class="search-count"><?php echo number_format($search['search_count']); ?> searches</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- System Health -->
    <div class="card">
        <h2>System Health</h2>
        <div class="health-metrics">
            <div class="health-item">
                <div class="health-label">Published Articles</div>
                <div class="health-value"><?php echo number_format($analytics['system_health']['published_articles']); ?></div>
            </div>
            <div class="health-item">
                <div class="health-label">Draft Articles</div>
                <div class="health-value"><?php echo number_format($analytics['system_health']['draft_articles']); ?></div>
            </div>
            <div class="health-item">
                <div class="health-label">Active Users</div>
                <div class="health-value"><?php echo number_format($analytics['system_health']['active_users']); ?></div>
            </div>
            <div class="health-item">
                <div class="health-label">Total Files</div>
                <div class="health-value"><?php echo number_format($analytics['system_health']['total_files']); ?></div>
            </div>
            <div class="health-item">
                <div class="health-label">Redirects</div>
                <div class="health-value"><?php echo number_format($analytics['system_health']['total_redirects']); ?></div>
            </div>
            <div class="health-item">
                <div class="health-label">Article Versions</div>
                <div class="health-value"><?php echo number_format($analytics['system_health']['total_versions']); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart data
const pageViewsData = <?php echo json_encode($analytics['page_views']); ?>;
const articleCreationData = <?php echo json_encode($analytics['article_creation']); ?>;
const userRegistrationsData = <?php echo json_encode($analytics['user_registrations']); ?>;
const fileUploadsData = <?php echo json_encode($analytics['file_uploads']); ?>;

// Helper function to format chart data
function formatChartData(data, labelKey, valueKey) {
    return {
        labels: data.map(item => item[labelKey]),
        datasets: [{
            label: valueKey,
            data: data.map(item => item[valueKey]),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    };
}

// Page Views Chart
const pageViewsCtx = document.getElementById('pageViewsChart').getContext('2d');
new Chart(pageViewsCtx, {
    type: 'line',
    data: formatChartData(pageViewsData, 'date', 'views'),
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Article Creation Chart
const articleCreationCtx = document.getElementById('articleCreationChart').getContext('2d');
new Chart(articleCreationCtx, {
    type: 'line',
    data: formatChartData(articleCreationData, 'date', 'articles'),
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// User Registrations Chart
const userRegistrationsCtx = document.getElementById('userRegistrationsChart').getContext('2d');
new Chart(userRegistrationsCtx, {
    type: 'bar',
    data: formatChartData(userRegistrationsData, 'date', 'registrations'),
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// File Uploads Chart
const fileUploadsCtx = document.getElementById('fileUploadsChart').getContext('2d');
new Chart(fileUploadsCtx, {
    type: 'bar',
    data: formatChartData(fileUploadsData, 'date', 'uploads'),
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<style>
.admin-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.admin-header h1 {
    margin: 0;
    color: #2c3e50;
}

.admin-actions {
    display: flex;
    gap: 1rem;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.date-filter-form {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.form-row {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.form-group input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-item {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
}

.stat-content h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.chart-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
}

.chart-container h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table tr:hover {
    background: #f8f9fa;
}

.search-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.search-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.search-term {
    font-weight: 500;
    color: #2c3e50;
}

.search-count {
    color: #6c757d;
    font-size: 0.9rem;
}

.health-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.health-item {
    text-align: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.health-label {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.health-value {
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

@media (max-width: 768px) {
    .admin-page {
        padding: 1rem;
    }
    
    .admin-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .form-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .search-stats {
        grid-template-columns: 1fr;
    }
    
    .health-metrics {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
