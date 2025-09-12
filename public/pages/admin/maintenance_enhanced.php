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

<style>
/* Enhanced Maintenance Page Styles */
.maintenance-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

.maintenance-header {
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

.header-content h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
    font-weight: 700;
}

.header-content p {
    margin: 0;
    font-size: 1.1rem;
    opacity: 0.9;
}

.maintenance-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.maintenance-section.primary-control {
    border: 2px solid #3498db;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Primary Maintenance Mode Control */
.maintenance-mode-card {
    border-radius: 8px;
    overflow: hidden;
}

.maintenance-active {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #ffc107;
    padding: 2rem;
}

.maintenance-inactive {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid #28a745;
    padding: 2rem;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 600;
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.status-indicator i {
    font-size: 1.5rem;
}

.status-badge {
    position: absolute;
    right: 0;
    top: 0;
    background: #ffc107;
    color: #212529;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.online {
    background: #28a745;
    color: white;
}

.maintenance-details {
    margin-bottom: 2rem;
    display: grid;
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item strong {
    color: #495057;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item span {
    color: #2c3e50;
    font-size: 1rem;
}

.status-text {
    color: #28a745 !important;
    font-weight: 500;
}

.maintenance-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

/* Quick Actions Grid */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.quick-action-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #3498db;
}

.action-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.action-content h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.action-content p {
    margin: 0 0 1rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Health Grid */
.health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.health-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border-left: 4px solid #3498db;
}

.health-card h3 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.health-metrics {
    display: grid;
    gap: 0.75rem;
}

.metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.metric:last-child {
    border-bottom: none;
}

.metric .label {
    font-weight: 500;
    color: #495057;
}

.metric .value {
    font-family: 'Courier New', monospace;
    color: #2c3e50;
}

.metric.error .value {
    color: #e74c3c;
}

.status-healthy {
    color: #27ae60;
    font-weight: 600;
}

/* Tools Grid */
.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.tool-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.tool-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.tool-card h3 {
    margin: 0 0 0.75rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tool-card p {
    margin: 0 0 1rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.tool-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    border: 1px solid #e9ecef;
}

.stat-card h3 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #3498db;
}

.stat-label {
    font-weight: 500;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

.stat-detail {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1rem;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}

.btn-primary { background: #3498db; color: white; }
.btn-primary:hover { background: #2980b9; transform: translateY(-1px); }

.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; transform: translateY(-1px); }

.btn-success { background: #27ae60; color: white; }
.btn-success:hover { background: #229954; transform: translateY(-1px); }

.btn-warning { background: #f39c12; color: white; }
.btn-warning:hover { background: #e67e22; transform: translateY(-1px); }

.btn-danger { background: #e74c3c; color: white; }
.btn-danger:hover { background: #c0392b; transform: translateY(-1px); }

.btn-info { background: #17a2b8; color: white; }
.btn-info:hover { background: #138496; transform: translateY(-1px); }

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 1px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .maintenance-page {
        padding: 1rem;
    }
    
    .maintenance-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .health-grid,
    .tools-grid,
    .stats-grid,
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .maintenance-actions,
    .tool-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
    
    .status-indicator {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .status-badge {
        position: static;
        margin-top: 0.5rem;
    }
}
</style>

<?php include "../../includes/footer.php"; ?>
