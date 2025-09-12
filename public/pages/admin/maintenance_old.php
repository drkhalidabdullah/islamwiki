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
            <p>Comprehensive maintenance tools and system diagnostics</p>
        </div>
        <div class="header-actions">
            <a href="/admin" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Admin
            </a>
        </div>
    </div>

    <!-- Maintenance Mode Status -->
    <div class="maintenance-section">
        <h2><i class="fas fa-power-off"></i> Maintenance Mode</h2>
        <div class="maintenance-mode-card">
            <?php if (is_maintenance_mode()): ?>
                <div class="maintenance-active">
                    <div class="status-indicator">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Maintenance Mode Active</span>
                    </div>
                    <div class="maintenance-details">
                        <p><strong>Message:</strong> <?php echo htmlspecialchars(get_system_setting('maintenance_message', 'Site is under maintenance')); ?></p>
                        <p><strong>Estimated Time:</strong> <?php echo htmlspecialchars(get_system_setting('estimated_downtime', 'Unknown')); ?></p>
                    </div>
                    <div class="maintenance-actions">
                        <a href="/admin/system_settings" class="btn btn-warning">
                            <i class="fas fa-cog"></i> Configure
                        </a>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="toggle_maintenance" class="btn btn-success">
                                <i class="fas fa-power-off"></i> Disable
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="maintenance-inactive">
                    <div class="status-indicator">
                        <i class="fas fa-check-circle"></i>
                        <span>Site is Online</span>
                    </div>
                    <div class="maintenance-details">
                        <p>All systems are operational and accessible to users.</p>
                    </div>
                    <div class="maintenance-actions">
                        <a href="/admin/system_settings" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configure
                        </a>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="toggle_maintenance" class="btn btn-warning" 
                                    onclick="return confirm('Are you sure you want to enable maintenance mode? This will make the site inaccessible to regular users.')">
                                <i class="fas fa-tools"></i> Enable
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
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

    <!-- Maintenance Tools -->
    <div class="maintenance-section">
        <h2><i class="fas fa-wrench"></i> Maintenance Tools</h2>
        <div class="tools-grid">
            <!-- Cache Management -->
            <div class="tool-card">
                <h3><i class="fas fa-broom"></i> Cache Management</h3>
                <p>Clear various types of cached data to improve performance</p>
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
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cache_type" value="all">
                        <button type="submit" name="clear_cache" class="btn btn-danger btn-sm" 
                                onclick="return confirm('Are you sure you want to clear all caches?')">
                            <i class="fas fa-trash"></i> All Caches
                        </button>
                    </form>
                </div>
            </div>

            <!-- Database Optimization -->
            <div class="tool-card">
                <h3><i class="fas fa-database"></i> Database Optimization</h3>
                <p>Optimize database tables to improve performance and reduce fragmentation</p>
                <div class="tool-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="optimize_database" class="btn btn-success" 
                                onclick="return confirm('This will optimize all database tables. Continue?')">
                            <i class="fas fa-magic"></i> Optimize Database
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
/* Maintenance Page Styles */
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

.maintenance-section h2 {
    margin: 0 0 1.5rem 0;
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Maintenance Mode Card */
.maintenance-mode-card {
    border-radius: 8px;
    overflow: hidden;
}

.maintenance-active {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 1px solid #ffeaa7;
    padding: 1.5rem;
}

.maintenance-inactive {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 1px solid #c3e6cb;
    padding: 1.5rem;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.status-indicator i {
    font-size: 1.2rem;
}

.maintenance-details {
    margin-bottom: 1.5rem;
}

.maintenance-details p {
    margin: 0.5rem 0;
    color: #495057;
}

.maintenance-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
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
    
    .health-grid,
    .tools-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .maintenance-actions,
    .tool-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
}
</style>

<?php include "../../includes/footer.php"; ?>