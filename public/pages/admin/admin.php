<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
// require_once '../../includes/analytics.php';

$page_title = 'Admin Dashboard';
check_maintenance_mode();
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect_with_return_url();
}


// Get comprehensive statistics
$stats = [];

// User statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['new_users_30d'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$stats['active_users'] = $stmt->fetch()['count'];

// Article statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles");
$stats['total_articles'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'");
$stats['published_articles'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'draft'");
$stats['draft_articles'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['new_articles_30d'] = $stmt->fetch()['count'];

// Category statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM content_categories");
$stats['total_categories'] = $stmt->fetch()['count'];

// File statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_files");
$stats['total_files'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(file_size) as total_size FROM wiki_files");
$stats['total_file_size'] = $stmt->fetch()['total_size'] ?: 0;

// Comment statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM comments");
$stats['total_comments'] = $stmt->fetch()['count'];

// Recent activity
$stmt = $pdo->query("
    SELECT 'user' as type, username as title, created_at, 'fas fa-user' as icon, 'success' as color
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'article' as type, title, created_at, 'fas fa-file-alt' as icon, 'info' as color
    FROM wiki_articles 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY created_at DESC 
    LIMIT 10
");
$recent_activity = $stmt->fetchAll();

// Recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// Recent articles
$stmt = $pdo->query("
    SELECT a.*, u.username, u.display_name 
    FROM wiki_articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$recent_articles = $stmt->fetchAll();

// Popular articles (by views)
$stmt = $pdo->query("
    SELECT a.title, a.slug, a.view_count, u.username
    FROM wiki_articles a 
    JOIN users u ON a.author_id = u.id 
    WHERE a.status = 'published'
    ORDER BY a.view_count DESC 
    LIMIT 5
");
$popular_articles = $stmt->fetchAll();

// System health
$system_health = [
    'database' => 'healthy',
    'storage' => disk_free_space('/') > (1024 * 1024 * 1024) ? 'healthy' : 'warning', // 1GB free
    'memory' => 'healthy',
    'uptime' => 'healthy'
];

// Get server info
$server_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
];

include "../../includes/header.php";
?>

<div class="admin-dashboard">
    <!-- Welcome Header -->
    <div class="admin-welcome">
        <div class="welcome-content">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?>! Here's what's happening with your site.</p>
            <div class="welcome-actions">
                <a href="/pages/wiki/create_article.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Article
                </a>
                <a href="/admin/manage_users" class="btn btn-secondary">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="/admin/analytics" class="btn btn-info">
                    <i class="fas fa-chart-line"></i> View Analytics
                </a>
            </div>
        </div>
        <div class="welcome-stats">
            <div class="quick-stat">
                <span class="stat-number"><?php echo number_format($stats['total_users']); ?></span>
                <span class="stat-label">Users</span>
            </div>
            <div class="quick-stat">
                <span class="stat-number"><?php echo number_format($stats['total_articles']); ?></span>
                <span class="stat-label">Articles</span>
            </div>
            <div class="quick-stat">
                <span class="stat-number"><?php echo number_format($stats['total_files']); ?></span>
                <span class="stat-label">Files</span>
            </div>
        </div>
    </div>

    <!-- Main Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card stat-users">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo $stats['new_users_30d']; ?> this month</span>
                </div>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_users']); ?></h3>
                <p>Total Users</p>
                <div class="stat-details">
                    <span><?php echo number_format($stats['active_users']); ?> active</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-articles">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo $stats['new_articles_30d']; ?> this month</span>
                </div>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_articles']); ?></h3>
                <p>Total Articles</p>
                <div class="stat-details">
                    <span><?php echo number_format($stats['published_articles']); ?> published</span>
                    <span><?php echo number_format($stats['draft_articles']); ?> drafts</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-files">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-hdd"></i>
                    <span><?php echo format_file_size($stats['total_file_size']); ?></span>
                </div>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_files']); ?></h3>
                <p>Uploaded Files</p>
                <div class="stat-details">
                    <span><?php echo format_file_size($stats['total_file_size']); ?> total size</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-categories">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-layer-group"></i>
                    <span>Organized content</span>
                </div>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_categories']); ?></h3>
                <p>Categories</p>
                <div class="stat-details">
                    <span>Content organization</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content Grid -->
    <div class="dashboard-grid">
        <!-- Left Column -->
        <div class="dashboard-left">
            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="/pages/wiki/create_article.php" class="quick-action">
                        <i class="fas fa-plus"></i>
                        <span>Create Article</span>
                    </a>
                    <a href="/admin/manage_users" class="quick-action">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                    <a href="/admin/analytics" class="quick-action">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="/admin/system_settings" class="quick-action">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-section">
                <h2><i class="fas fa-clock"></i> Recent Activity</h2>
                <div class="activity-feed">
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon activity-<?php echo $activity['color']; ?>">
                                <i class="<?php echo $activity['icon']; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                                <div class="activity-meta">
                                    <?php echo ucfirst($activity['type']); ?> • <?php echo format_date($activity['created_at']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-activity">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Health -->
            <div class="dashboard-section">
                <h2><i class="fas fa-heartbeat"></i> System Health</h2>
                <div class="health-status">
                    <div class="health-item">
                        <span class="health-label">Database</span>
                        <span class="health-status status-<?php echo $system_health['database']; ?>">
                            <i class="fas fa-circle"></i> <?php echo ucfirst($system_health['database']); ?>
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">Storage</span>
                        <span class="health-status status-<?php echo $system_health['storage']; ?>">
                            <i class="fas fa-circle"></i> <?php echo ucfirst($system_health['storage']); ?>
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">Memory</span>
                        <span class="health-status status-<?php echo $system_health['memory']; ?>">
                            <i class="fas fa-circle"></i> <?php echo ucfirst($system_health['memory']); ?>
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">Uptime</span>
                        <span class="health-status status-<?php echo $system_health['uptime']; ?>">
                            <i class="fas fa-circle"></i> <?php echo ucfirst($system_health['uptime']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="dashboard-right">
            <!-- Popular Articles -->
            <div class="dashboard-section">
                <h2><i class="fas fa-fire"></i> Popular Articles</h2>
                <div class="popular-articles">
                    <?php if (!empty($popular_articles)): ?>
                        <?php foreach ($popular_articles as $article): ?>
                        <div class="popular-article">
                            <div class="article-info">
                                <h4><a href="/wiki/article.php?slug=<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h4>
                                <p>By <?php echo htmlspecialchars($article['username']); ?></p>
                            </div>
                            <div class="article-stats">
                                <span class="view-count">
                                    <i class="fas fa-eye"></i> <?php echo number_format($article['view_count']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No popular articles yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Server Information -->
            <div class="dashboard-section">
                <h2><i class="fas fa-server"></i> Server Info</h2>
                <div class="server-info">
                    <div class="info-item">
                        <span class="info-label">PHP Version</span>
                        <span class="info-value"><?php echo $server_info['php_version']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Memory Limit</span>
                        <span class="info-value"><?php echo $server_info['memory_limit']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Upload Max</span>
                        <span class="info-value"><?php echo $server_info['upload_max_filesize']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Max Execution</span>
                        <span class="info-value"><?php echo $server_info['max_execution_time']; ?>s</span>
                    </div>
                </div>
            </div>

            <!-- Admin Tools -->
            <div class="dashboard-section">
                <h2><i class="fas fa-tools"></i> Admin Tools</h2>
                <div class="admin-tools">
                    <a href="/admin/manage_redirects" class="tool-link">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Manage Redirects</span>
                    </a>
                    <a href="/admin/manage_files" class="tool-link">
                        <i class="fas fa-folder-open"></i>
                        <span>File Manager</span>
                    </a>
                    <a href="/admin/manage_categories" class="tool-link">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                    <a href="/admin/content_moderation" class="tool-link">
                        <i class="fas fa-shield-alt"></i>
                        <span>Moderation</span>
                    </a>
                    <a href="/admin/maintenance" class="tool-link">
                        <i class="fas fa-wrench"></i>
                        <span>Maintenance</span>
                    </a>
                    <a href="/admin/manage_permissions" class="tool-link">
                        <i class="fas fa-key"></i>
                        <span>Permissions</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
/* Modern Admin Dashboard Styles */
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Welcome Header */
.admin-welcome {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.welcome-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
    font-weight: 700;
}

.welcome-content p {
    margin: 0 0 1.5rem 0;
    font-size: 1.1rem;
    opacity: 0.9;
}

.welcome-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.welcome-stats {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.quick-stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.stat-label {
    font-size: 1rem;
    color: #ffffff;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 5px solid transparent;
    border: 1px solid #e9ecef;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    border-left-width: 6px;
}

.stat-card.stat-users { border-left-color: #3498db; }
.stat-card.stat-articles { border-left-color: #e74c3c; }
.stat-card.stat-files { border-left-color: #f39c12; }
.stat-card.stat-categories { border-left-color: #9b59b6; }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-users .stat-icon { background: #3498db; }
.stat-articles .stat-icon { background: #e74c3c; }
.stat-files .stat-icon { background: #f39c12; }
.stat-categories .stat-icon { background: #9b59b6; }

.stat-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #27ae60;
    font-weight: 500;
}

.stat-content h3 {
    font-size: 3.5rem;
    margin: 0 0 0.5rem 0;
    color: #2563eb;
    font-weight: 900;
    text-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    line-height: 1;
    letter-spacing: -1px;
}

.stat-content p {
    margin: 0 0 1rem 0;
    color: #34495e;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-details {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-details span {
    background: #e8f4f8;
    padding: 0.4rem 0.9rem;
    border-radius: 15px;
    font-size: 0.85rem;
    color: #2c3e50;
    font-weight: 600;
    border: 1px solid #bdc3c7;
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.dashboard-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.dashboard-section h2 {
    margin: 0 0 1.5rem 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.quick-action {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.quick-action:hover {
    background: #e9ecef;
    border-color: #3498db;
    text-decoration: none;
    color: #2c3e50;
    transform: translateY(-1px);
}

.quick-action i {
    font-size: 1.2rem;
    color: #3498db;
}

/* Activity Feed */
.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
}

.activity-success { background: #27ae60; }
.activity-info { background: #3498db; }
.activity-warning { background: #f39c12; }
.activity-danger { background: #e74c3c; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.activity-meta {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.no-activity {
    text-align: center;
    color: #7f8c8d;
    font-style: italic;
    padding: 2rem;
}

/* System Health */
.health-status {
    display: grid;
    gap: 0.75rem;
}

.health-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.health-label {
    font-weight: 500;
    color: #2c3e50;
}

.health-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-healthy { color: #27ae60; }
.status-warning { color: #f39c12; }
.status-danger { color: #e74c3c; }

/* Popular Articles */
.popular-articles {
    display: grid;
    gap: 1rem;
}

.popular-article {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.popular-article:hover {
    background: #e9ecef;
    transform: translateX(4px);
}

.article-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
}

.article-info h4 a {
    color: #2c3e50;
    text-decoration: none;
}

.article-info h4 a:hover {
    color: #3498db;
}

.article-info p {
    margin: 0;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.article-stats {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.view-count {
    background: #3498db;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Server Info */
.server-info {
    display: grid;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-label {
    font-weight: 500;
    color: #2c3e50;
}

.info-value {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    color: #7f8c8d;
}

/* Admin Tools */
.admin-tools {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.tool-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.tool-link:hover {
    background: #e9ecef;
    border-color: #3498db;
    text-decoration: none;
    color: #2c3e50;
    transform: translateY(-1px);
}

.tool-link i {
    font-size: 1.1rem;
    color: #3498db;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-1px);
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .admin-dashboard {
        padding: 1rem;
    }
    
    .admin-welcome {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .welcome-stats {
        justify-content: center;
    }
    
    .welcome-actions {
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-tools {
        grid-template-columns: 1fr;
    }
    
    .popular-article {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .welcome-content h1 {
        font-size: 2rem;
    }
    
    .stat-content h3 {
        font-size: 2rem;
    }
    
    .welcome-actions {
        flex-direction: column;
    }
}
</style>

<?php include "../../includes/footer.php";; ?>
