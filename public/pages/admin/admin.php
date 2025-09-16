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

// Handle maintenance mode toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_maintenance'])) {
    $current_mode = is_maintenance_mode();
    $new_mode = $current_mode ? 0 : 1;
    
    // Update maintenance mode setting
    set_system_setting('maintenance_mode', $new_mode);
    
    // Log the action
    log_activity('maintenance_toggle', 'Maintenance mode ' . ($new_mode ? 'enabled' : 'disabled'), $_SESSION['user_id']);
    
    // Redirect to refresh the page
    header('Location: /admin');
    exit;
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
    SELECT 'user' as type, username as title, created_at, 'iw iw-user' as icon, 'success' as color
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'article' as type, title, created_at, 'iw iw-file-alt' as icon, 'info' as color
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

// Load admin CSS
$admin_css = true;
include "../../includes/header.php";

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<?php

?>
<script src="/skins/bismillah/assets/js/admin_dashboard.js"></script>
<?php
?>

<div class="admin-dashboard">
    <!-- Welcome Header -->
    <div class="admin-welcome">
        <div class="welcome-content">
            <h1><i class="iw iw-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?>! Here's what's happening with your site.</p>
            <div class="welcome-actions">
                <a href="/pages/wiki/create_article.php" class="btn btn-primary">
                    <i class="iw iw-plus"></i> Create Article
                </a>
                <a href="/admin/manage_users" class="btn btn-secondary">
                    <i class="iw iw-users"></i> Manage Users
                </a>
                <a href="/admin/analytics" class="btn btn-info">
                    <i class="iw iw-chart-line"></i> View Analytics
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

    <!-- System Health -->
    <div class="dashboard-section system-health-section">
        <h2><i class="iw iw-heartbeat"></i> System Health</h2>
        <div class="health-status">
            <div class="health-item">
                <span class="health-label">Database</span>
                <span class="health-status-indicator status-<?php echo $system_health['database']; ?>">
                    <i class="iw iw-circle"></i> <?php echo ucfirst($system_health['database']); ?>
                </span>
            </div>
            <div class="health-item">
                <span class="health-label">Storage</span>
                <span class="health-status-indicator status-<?php echo $system_health['storage']; ?>">
                    <i class="iw iw-circle"></i> <?php echo ucfirst($system_health['storage']); ?>
                </span>
            </div>
            <div class="health-item">
                <span class="health-label">Memory</span>
                <span class="health-status-indicator status-<?php echo $system_health['memory']; ?>">
                    <i class="iw iw-circle"></i> <?php echo ucfirst($system_health['memory']); ?>
                </span>
            </div>
            <div class="health-item">
                <span class="health-label">Uptime</span>
                <span class="health-status-indicator status-<?php echo $system_health['uptime']; ?>">
                    <i class="iw iw-circle"></i> <?php echo ucfirst($system_health['uptime']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card stat-users">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="iw iw-users"></i>
                </div>
                <div class="stat-trend">
                    <i class="iw iw-arrow-up"></i>
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
                    <i class="iw iw-file-alt"></i>
                </div>
                <div class="stat-trend">
                    <i class="iw iw-arrow-up"></i>
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
                    <i class="iw iw-folder"></i>
                </div>
                <div class="stat-trend">
                    <i class="iw iw-hdd"></i>
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
                    <i class="iw iw-tags"></i>
                </div>
                <div class="stat-trend">
                    <i class="iw iw-layer-group"></i>
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
            <!-- Maintenance Mode Status -->
            <div class="dashboard-section maintenance-status-section">
                <h2><i class="iw iw-tools"></i> Maintenance Mode</h2>
                <div class="maintenance-status">
                    <?php if (is_maintenance_mode()): ?>
                        <div class="maintenance-active">
                            <div class="maintenance-info">
                                <div class="maintenance-status-indicator">
                                    <i class="iw iw-circle status-warning"></i>
                                    <span>Maintenance Mode Active</span>
                                </div>
                                <div class="maintenance-details">
                                    <p><strong>Message:</strong> <?php echo htmlspecialchars(get_system_setting('maintenance_message', 'Site is under maintenance')); ?></p>
                                    <p><strong>Estimated Time:</strong> <?php echo htmlspecialchars(get_system_setting('estimated_downtime', 'Unknown')); ?></p>
                                </div>
                            </div>
                            <div class="maintenance-actions">
                                <a href="/admin/system_settings" class="btn btn-warning btn-sm">
                                    <i class="iw iw-cog"></i> Manage
                                </a>
                                <button onclick="toggleMaintenanceMode()" class="btn btn-success btn-sm">
                                    <i class="iw iw-power-off"></i> Disable
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="maintenance-inactive">
                            <div class="maintenance-info">
                                <div class="maintenance-status-indicator">
                                    <i class="iw iw-circle status-healthy"></i>
                                    <span>Site is Online</span>
                                </div>
                                <div class="maintenance-details">
                                    <p>All systems are operational and accessible to users.</p>
                                </div>
                            </div>
                            <div class="maintenance-actions">
                                <a href="/admin/system_settings" class="btn btn-primary btn-sm">
                                    <i class="iw iw-cog"></i> Settings
                                </a>
                                <button onclick="toggleMaintenanceMode()" class="btn btn-warning btn-sm">
                                    <i class="iw iw-tools"></i> Enable
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-section">
                <h2><i class="iw iw-clock"></i> Recent Activity</h2>
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

        </div>

        <!-- Right Column -->
        <div class="dashboard-right">
            <!-- Admin Actions -->
            <div class="dashboard-section">
                <h2><i class="iw iw-cogs"></i> Admin Actions</h2>
                
                <!-- Most Common Actions -->
                <div class="action-category">
                    <h3><i class="iw iw-star"></i> Most Used</h3>
                    <div class="action-grid">
                        <a href="/pages/wiki/create_article.php" class="action-item primary">
                            <i class="iw iw-plus"></i>
                            <span>Create Article</span>
                            <small>New wiki content</small>
                        </a>
                        <a href="/admin/manage_users" class="action-item primary">
                            <i class="iw iw-users"></i>
                            <span>Manage Users</span>
                            <small>User accounts</small>
                        </a>
                        <a href="/admin/analytics" class="action-item primary">
                            <i class="iw iw-chart-line"></i>
                            <span>Analytics</span>
                            <small>Site statistics</small>
                        </a>
                        <a href="/admin/system_settings" class="action-item primary">
                            <i class="iw iw-cog"></i>
                            <span>Settings</span>
                            <small>System config</small>
                        </a>
                    </div>
                </div>

                <!-- System Management -->
                <div class="action-category">
                    <h3><i class="iw iw-tools"></i> System Management</h3>
                    <div class="action-grid">
                        <a href="/admin/maintenance" class="action-item secondary">
                            <i class="iw iw-wrench"></i>
                            <span>Maintenance</span>
                            <small>System maintenance</small>
                        </a>
                        <a href="/admin/manage_files" class="action-item secondary">
                            <i class="iw iw-folder-open"></i>
                            <span>File Manager</span>
                            <small>Uploaded files</small>
                        </a>
                        <a href="/admin/manage_categories" class="action-item secondary">
                            <i class="iw iw-tags"></i>
                            <span>Categories</span>
                            <small>Content organization</small>
                        </a>
                        <a href="/admin/content_moderation" class="action-item secondary">
                            <i class="iw iw-shield-alt"></i>
                            <span>Moderation</span>
                            <small>Content review</small>
                        </a>
                    </div>
                </div>

                <!-- Advanced Tools -->
                <div class="action-category">
                    <h3><i class="iw iw-cog"></i> Advanced Tools</h3>
                    <div class="action-grid">
                        <a href="/admin/manage_redirects" class="action-item tertiary">
                            <i class="iw iw-exchange-alt"></i>
                            <span>Redirects</span>
                            <small>URL redirects</small>
                        </a>
                        <a href="/admin/manage_permissions" class="action-item tertiary">
                            <i class="iw iw-key"></i>
                            <span>Permissions</span>
                            <small>Access control</small>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Popular Articles -->
            <div class="dashboard-section">
                <h2><i class="iw iw-fire"></i> Popular Articles</h2>
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
                                    <i class="iw iw-eye"></i> <?php echo number_format($article['view_count']); ?>
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
                <h2><i class="iw iw-server"></i> Server Info</h2>
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

        </div>
    </div>
</div>




<?php include "../../includes/footer.php";; ?>
