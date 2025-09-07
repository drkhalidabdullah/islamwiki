<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'System Settings';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => sanitize_input($_POST['site_name'] ?? ''),
        'site_description' => sanitize_input($_POST['site_description'] ?? ''),
        'site_keywords' => sanitize_input($_POST['site_keywords'] ?? ''),
        'admin_email' => sanitize_input($_POST['admin_email'] ?? ''),
        'posts_per_page' => (int)($_POST['posts_per_page'] ?? 10),
        'allow_registration' => isset($_POST['allow_registration']) ? 1 : 0,
        'require_email_verification' => isset($_POST['require_email_verification']) ? 1 : 0,
        'enable_comments' => isset($_POST['enable_comments']) ? 1 : 0,
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
    ];
    
    $updated = 0;
    foreach ($settings as $key => $value) {
        $type = is_int($value) ? 'integer' : (is_bool($value) ? 'boolean' : 'string');
        if (set_system_setting($key, $value, $type)) {
            $updated++;
        }
    }
    
    if ($updated > 0) {
        $success = 'System settings updated successfully.';
        log_activity('system_settings_updated', "Updated $updated system settings");
    } else {
        $error = 'Failed to update system settings.';
    }
}

// Get current settings
$current_settings = [
    'site_name' => get_system_setting('site_name', SITE_NAME),
    'site_description' => get_system_setting('site_description', 'A comprehensive Islamic knowledge platform'),
    'site_keywords' => get_system_setting('site_keywords', 'Islam, Islamic, knowledge, wiki'),
    'admin_email' => get_system_setting('admin_email', 'admin@islamwiki.org'),
    'posts_per_page' => get_system_setting('posts_per_page', 10),
    'allow_registration' => get_system_setting('allow_registration', true),
    'require_email_verification' => get_system_setting('require_email_verification', false),
    'enable_comments' => get_system_setting('enable_comments', true),
    'maintenance_mode' => get_system_setting('maintenance_mode', false),
];

// Get system information
$system_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database_version' => $pdo->query('SELECT VERSION()')->fetchColumn(),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
];

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_articles' => $pdo->query("SELECT COUNT(*) FROM wiki_articles")->fetchColumn(),
    'published_articles' => $pdo->query("SELECT COUNT(*) FROM wiki_articles WHERE status = 'published'")->fetchColumn(),
    'total_categories' => $pdo->query("SELECT COUNT(*) FROM content_categories")->fetchColumn(),
    'disk_usage' => function_exists('disk_total_space') ? disk_total_space('.') - disk_free_space('.') : 'N/A',
    'free_space' => function_exists('disk_free_space') ? disk_free_space('.') : 'N/A',
];

include "../../includes/header.php";;
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>System Settings</h1>
        <a href="admin.php" class="btn">Back to Admin Panel</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="settings-tabs">
        <button class="tab-button active" onclick="showTab('general')">General Settings</button>
        <button class="tab-button" onclick="showTab('system')">System Information</button>
        <button class="tab-button" onclick="showTab('statistics')">Statistics</button>
    </div>
    
    <!-- General Settings Tab -->
    <div id="general-tab" class="tab-content active">
        <div class="card">
            <h2>General Settings</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?php echo htmlspecialchars($current_settings['site_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" 
                               value="<?php echo htmlspecialchars($current_settings['admin_email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($current_settings['site_description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="site_keywords">Site Keywords (comma separated)</label>
                    <input type="text" id="site_keywords" name="site_keywords" 
                           value="<?php echo htmlspecialchars($current_settings['site_keywords']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="posts_per_page">Articles Per Page</label>
                    <input type="number" id="posts_per_page" name="posts_per_page" 
                           value="<?php echo $current_settings['posts_per_page']; ?>" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <h3>System Options</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_registration" 
                               <?php echo $current_settings['allow_registration'] ? 'checked' : ''; ?>>
                        Allow User Registration
                    </label>
                    
                    <label class="checkbox-label">
                        <input type="checkbox" name="require_email_verification" 
                               <?php echo $current_settings['require_email_verification'] ? 'checked' : ''; ?>>
                        Require Email Verification
                    </label>
                    
                    <label class="checkbox-label">
                        <input type="checkbox" name="enable_comments" 
                               <?php echo $current_settings['enable_comments'] ? 'checked' : ''; ?>>
                        Enable Comments
                    </label>
                    
                    <label class="checkbox-label">
                        <input type="checkbox" name="maintenance_mode" 
                               <?php echo $current_settings['maintenance_mode'] ? 'checked' : ''; ?>>
                        Maintenance Mode
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
    
    <!-- System Information Tab -->
    <div id="system-tab" class="tab-content">
        <div class="card">
            <h2>System Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>PHP Version:</strong>
                    <span><?php echo $system_info['php_version']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Server Software:</strong>
                    <span><?php echo $system_info['server_software']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Database Version:</strong>
                    <span><?php echo $system_info['database_version']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Upload Max Filesize:</strong>
                    <span><?php echo $system_info['upload_max_filesize']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Post Max Size:</strong>
                    <span><?php echo $system_info['post_max_size']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Memory Limit:</strong>
                    <span><?php echo $system_info['memory_limit']; ?></span>
                </div>
                <div class="info-item">
                    <strong>Max Execution Time:</strong>
                    <span><?php echo $system_info['max_execution_time']; ?>s</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Tab -->
    <div id="statistics-tab" class="tab-content">
        <div class="card">
            <h2>System Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_articles']); ?></div>
                    <div class="stat-label">Total Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['published_articles']); ?></div>
                    <div class="stat-label">Published Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_categories']); ?></div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
            
            <?php if ($stats['disk_usage'] !== 'N/A'): ?>
            <div class="disk-info">
                <h3>Disk Usage</h3>
                <div class="info-item">
                    <strong>Used Space:</strong>
                    <span><?php echo number_format($stats['disk_usage'] / (1024*1024*1024), 2); ?> GB</span>
                </div>
                <div class="info-item">
                    <strong>Free Space:</strong>
                    <span><?php echo number_format($stats['free_space'] / (1024*1024*1024), 2); ?> GB</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-container {
    max-width: 1200px;
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
    color: #2c3e50;
    margin: 0;
}

.settings-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.tab-button {
    padding: 0.75rem 1.5rem;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    border-radius: 4px;
}

.tab-button.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: normal !important;
}

.checkbox-label input {
    width: auto !important;
}

.info-grid,
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.stat-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #3498db;
}

.stat-label {
    color: #666;
    margin-top: 0.5rem;
}

.disk-info {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #e9ecef;
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab and activate button
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php include "../../includes/footer.php";; ?>
