<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'System Maintenance';
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
    
    set_system_setting('maintenance_mode', $new_mode);
    log_activity('maintenance_toggle', 'Maintenance mode ' . ($new_mode ? 'enabled' : 'disabled'), $_SESSION['user_id']);
    
    header('Location: /admin/maintenance');
    exit;
}

// Handle cache clearing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cache'])) {
    $cache_type = $_POST['cache_type'] ?? 'all';
    
    switch ($cache_type) {
        case 'system':
            $pdo->query("DELETE FROM system_settings WHERE `key` LIKE 'cache_%'");
            show_message('System cache cleared successfully.', 'success');
            break;
        case 'sessions':
            $pdo->query("DELETE FROM user_sessions WHERE expires_at < NOW()");
            show_message('Session cache cleared successfully.', 'success');
            break;
        case 'logs':
            $pdo->query("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            show_message('Log cache cleared successfully.', 'success');
            break;
        case 'all':
            $pdo->query("DELETE FROM system_settings WHERE `key` LIKE 'cache_%'");
            $pdo->query("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $pdo->query("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            show_message('All caches cleared successfully.', 'success');
            break;
    }
    
    log_activity('cache_cleared', "Cleared $cache_type cache", $_SESSION['user_id']);
    header('Location: /admin/maintenance');
    exit;
}

// Handle database optimization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['optimize_database'])) {
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->query("OPTIMIZE TABLE `$table`");
        }
        show_message('Database optimized successfully.', 'success');
        log_activity('database_optimized', 'Database tables optimized', $_SESSION['user_id']);
    } catch (Exception $e) {
        show_message('Failed to optimize database: ' . $e->getMessage(), 'error');
    }
    header('Location: /admin/maintenance');
    exit;
}

// Handle file cleanup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cleanup_files'])) {
    $cleanup_type = $_POST['cleanup_type'] ?? 'temp';
    $cleaned = 0;
    
    switch ($cleanup_type) {
        case 'temp':
            $temp_dir = '/tmp/';
            if (is_dir($temp_dir)) {
                $files = glob($temp_dir . 'islamwiki_*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < (time() - 3600)) {
                        unlink($file);
                        $cleaned++;
                    }
                }
            }
            break;
        case 'uploads':
            $upload_dir = '/var/www/html/uploads/posts/';
            if (is_dir($upload_dir)) {
                $db_files = $pdo->query("SELECT file_path FROM wiki_files")->fetchAll(PDO::FETCH_COLUMN);
                $db_files = array_map(function($path) {
                    return basename($path);
                }, $db_files);
                
                $files = glob($upload_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file) && !in_array(basename($file), $db_files)) {
                        unlink($file);
                        $cleaned++;
                    }
                }
            }
            break;
    }
    
    show_message("Cleaned up $cleaned files successfully.", 'success');
    log_activity('file_cleanup', "Cleaned up $cleaned $cleanup_type files", $_SESSION['user_id']);
    header('Location: /admin/maintenance');
    exit;
}

// Get system information
$system_info = [
    'php_version' => PHP_VERSION,
    'memory_limit' => ini_get('memory_limit'),
    'disk_free_space' => disk_free_space('/'),
    'memory_usage' => memory_get_usage(true)
];

// Get database information
$db_info = [];
try {
    $db_info['version'] = $pdo->query("SELECT VERSION()")->fetchColumn();
    $db_info['size'] = $pdo->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ")->fetchColumn();
} catch (Exception $e) {
    $db_info['error'] = $e->getMessage();
}

// Get maintenance statistics
$maintenance_stats = [];
try {
    $maintenance_stats['total_sessions'] = $pdo->query("SELECT COUNT(*) FROM user_sessions")->fetchColumn();
    $maintenance_stats['expired_sessions'] = $pdo->query("SELECT COUNT(*) FROM user_sessions WHERE expires_at < NOW()")->fetchColumn();
    $maintenance_stats['total_logs'] = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
    $maintenance_stats['old_logs'] = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
} catch (Exception $e) {
    $maintenance_stats['error'] = $e->getMessage();
}

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/maintenance.css">
<?php
?>

<div class="maintenance-page">
    <!-- Header -->
    <div class="maintenance-header">
        <div class="header-content">
            <h1><i class="fas fa-tools"></i> System Maintenance</h1>
            <p>Primary maintenance control center and system diagnostics</p>
        </div>
        <div class="header-actions">
            <a href="/admin" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Admin
            </a>
        </div>
    </div>

    <!-- Primary Maintenance Mode Control -->
    <div class="maintenance-section primary-control">
        <div class="section-header">
            <h2><i class="fas fa-power-off"></i> Maintenance Mode Control</h2>
            <div class="header-actions">
                <a href="/admin/system_settings" class="btn btn-outline btn-sm">
                    <i class="fas fa-cog"></i> Configure Settings
                </a>
            </div>
        </div>
        
        <div class="maintenance-mode-card">
            <?php if (is_maintenance_mode()): ?>
                <div class="maintenance-active">
                    <div class="status-indicator">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Maintenance Mode Active</span>
                        <div class="status-badge">ONLINE</div>
                    </div>
                    <div class="maintenance-details">
                        <div class="detail-item">
                            <strong>Message:</strong> 
                            <span><?php echo htmlspecialchars(get_system_setting('maintenance_message', 'Site is under maintenance')); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Estimated Time:</strong> 
                            <span><?php echo htmlspecialchars(get_system_setting('estimated_downtime', 'Unknown')); ?></span>
                        </div>
                        <div class="detail-item">
                            <strong>Status:</strong> 
                            <span class="status-text">Site is inaccessible to regular users</span>
                        </div>
                    </div>
                    <div class="maintenance-actions">
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="toggle_maintenance" class="btn btn-success btn-lg">
                                <i class="fas fa-power-off"></i> Disable Maintenance Mode
                            </button>
                        </form>
                        <a href="/admin/system_settings" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Message & Settings
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="maintenance-inactive">
                    <div class="status-indicator">
                        <i class="fas fa-check-circle"></i>
                        <span>Site is Online</span>
                        <div class="status-badge online">LIVE</div>
                    </div>
                    <div class="maintenance-details">
                        <div class="detail-item">
                            <strong>Status:</strong> 
                            <span class="status-text">All systems operational and accessible to users</span>
                        </div>
                        <div class="detail-item">
                            <strong>Last Maintenance:</strong> 
                            <span><?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                    </div>
                    <div class="maintenance-actions">
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="toggle_maintenance" class="btn btn-warning btn-lg" 
                                    onclick="return confirm('⚠️ WARNING: This will make the site inaccessible to regular users.\n\nAre you sure you want to enable maintenance mode?')">
                                <i class="fas fa-tools"></i> Enable Maintenance Mode
                            </button>
                        </form>
                        <a href="/admin/system_settings" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configure Settings
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Maintenance Actions -->
    <div class="maintenance-section">
        <h2><i class="fas fa-bolt"></i> Quick Maintenance Actions</h2>
        <div class="quick-actions-grid">
            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-broom"></i>
                </div>
                <div class="action-content">
                    <h3>Clear All Caches</h3>
                    <p>Remove all cached data to improve performance</p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="all">
                        <button type="submit" name="clear_cache" class="btn btn-primary" 
                                onclick="return confirm('Clear all caches? This will improve performance.')">
                            <i class="fas fa-trash"></i> Clear All
                        </button>
                    </form>
                </div>
            </div>

            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="action-content">
                    <h3>Optimize Database</h3>
                    <p>Optimize all database tables for better performance</p>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="optimize_database" class="btn btn-success" 
                                onclick="return confirm('Optimize all database tables? This may take a moment.')">
                            <i class="fas fa-magic"></i> Optimize
                        </button>
                    </form>
                </div>
            </div>

            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="action-content">
                    <h3>Clean Sessions</h3>
                    <p>Remove expired user sessions</p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="sessions">
                        <button type="submit" name="clear_cache" class="btn btn-info">
                            <i class="fas fa-user-times"></i> Clean
                        </button>
                    </form>
                </div>
            </div>

            <div class="quick-action-card">
                <div class="action-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="action-content">
                    <h3>Clean Old Logs</h3>
                    <p>Remove activity logs older than 30 days</p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="logs">
                        <button type="submit" name="clear_cache" class="btn btn-warning">
                            <i class="fas fa-history"></i> Clean
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="maintenance-section">
        <h2><i class="fas fa-heartbeat"></i> System Health</h2>
        <div class="health-grid">
            <div class="health-card">
                <h3><i class="fas fa-server"></i> Server Status</h3>
                <div class="health-metrics">
                    <div class="metric">
                        <span class="label">PHP Version:</span>
                        <span class="value"><?php echo $system_info['php_version']; ?></span>
                    </div>
                    <div class="metric">
                        <span class="label">Memory Usage:</span>
                        <span class="value"><?php echo format_file_size($system_info['memory_usage']); ?> / <?php echo $system_info['memory_limit']; ?></span>
                    </div>
                    <div class="metric">
                        <span class="label">Disk Space:</span>
                        <span class="value"><?php echo format_file_size($system_info['disk_free_space']); ?> free</span>
                    </div>
                    <div class="metric">
                        <span class="label">Uptime:</span>
                        <span class="value status-healthy">Operational</span>
                    </div>
                </div>
            </div>

            <div class="health-card">
                <h3><i class="fas fa-database"></i> Database Status</h3>
                <div class="health-metrics">
                    <?php if (isset($db_info['error'])): ?>
                        <div class="metric error">
                            <span class="label">Error:</span>
                            <span class="value"><?php echo htmlspecialchars($db_info['error']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="metric">
                            <span class="label">Version:</span>
                            <span class="value"><?php echo $db_info['version']; ?></span>
                        </div>
                        <div class="metric">
                            <span class="label">Size:</span>
                            <span class="value"><?php echo $db_info['size']; ?> MB</span>
                        </div>
                        <div class="metric">
                            <span class="label">Status:</span>
                            <span class="value status-healthy">Connected</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Maintenance Tools -->
    <div class="maintenance-section">
        <h2><i class="fas fa-wrench"></i> Advanced Maintenance Tools</h2>
        <div class="tools-grid">
            <!-- Cache Management -->
            <div class="tool-card">
                <h3><i class="fas fa-broom"></i> Cache Management</h3>
                <p>Clear specific types of cached data</p>
                <div class="tool-actions">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="system">
                        <button type="submit" name="clear_cache" class="btn btn-primary btn-sm">
                            <i class="fas fa-database"></i> System Cache
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="sessions">
                        <button type="submit" name="clear_cache" class="btn btn-info btn-sm">
                            <i class="fas fa-users"></i> Sessions
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="logs">
                        <button type="submit" name="clear_cache" class="btn btn-warning btn-sm">
                            <i class="fas fa-file-alt"></i> Old Logs
                        </button>
                    </form>
                </div>
            </div>

            <!-- File Cleanup -->
            <div class="tool-card">
                <h3><i class="fas fa-folder-open"></i> File Cleanup</h3>
                <p>Clean up temporary and orphaned files</p>
                <div class="tool-actions">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cleanup_type" value="temp">
                        <button type="submit" name="cleanup_files" class="btn btn-info">
                            <i class="fas fa-clock"></i> Temp Files
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cleanup_type" value="uploads">
                        <button type="submit" name="cleanup_files" class="btn btn-warning" 
                                onclick="return confirm('This will delete orphaned upload files. Continue?')">
                            <i class="fas fa-upload"></i> Orphaned Files
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- System Statistics -->
    <div class="maintenance-section">
        <h2><i class="fas fa-chart-bar"></i> System Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Sessions</h3>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($maintenance_stats['total_sessions'] ?? 0); ?></div>
                    <div class="stat-label">Total Sessions</div>
                    <div class="stat-detail"><?php echo number_format($maintenance_stats['expired_sessions'] ?? 0); ?> expired</div>
                </div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-file-alt"></i> Logs</h3>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($maintenance_stats['total_logs'] ?? 0); ?></div>
                    <div class="stat-label">Total Logs</div>
                    <div class="stat-detail"><?php echo number_format($maintenance_stats['old_logs'] ?? 0); ?> old (>30 days)</div>
                </div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-hdd"></i> Storage</h3>
                <div class="stat-content">
                    <div class="stat-number"><?php echo format_file_size($system_info['disk_free_space']); ?></div>
                    <div class="stat-label">Free Space</div>
                    <div class="stat-detail">Available</div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include "../../includes/footer.php"; ?>
