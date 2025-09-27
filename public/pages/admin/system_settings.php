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

// Initialize managers
require_once '../../includes/extension_manager.php';
require_once '../../skins/skins_manager.php';

$extension_manager = new ExtensionManager();
$skins_manager = new SkinsManager();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_general') {
        $form_section = $_POST['form_section'] ?? 'unknown';
        $settings = [];
        
        // Process based on which form section was submitted
        if ($form_section === 'site_info') {
            // Site information settings
            if (isset($_POST['site_name'])) {
                $settings['site_name'] = sanitize_input($_POST['site_name']);
            }
            if (isset($_POST['site_description'])) {
                $settings['site_description'] = sanitize_input($_POST['site_description']);
            }
            if (isset($_POST['site_keywords'])) {
                $settings['site_keywords'] = sanitize_input($_POST['site_keywords']);
            }
            if (isset($_POST['admin_email'])) {
                $settings['admin_email'] = sanitize_input($_POST['admin_email']);
            }
            if (isset($_POST['contact_email'])) {
                $settings['contact_email'] = sanitize_input($_POST['contact_email']);
            }
            if (isset($_POST['posts_per_page'])) {
                $settings['posts_per_page'] = (int)$_POST['posts_per_page'];
            }
            if (isset($_POST['articles_per_page'])) {
                $settings['articles_per_page'] = (int)$_POST['articles_per_page'];
            }
            if (isset($_POST['copyright_text'])) {
                $settings['copyright_text'] = sanitize_input($_POST['copyright_text']);
            }
        } elseif ($form_section === 'features') {
            // Feature toggles - process all feature settings
            $settings['allow_registration'] = isset($_POST['allow_registration']) ? 1 : 0;
            $settings['require_email_verification'] = isset($_POST['require_email_verification']) ? 1 : 0;
            // Comments are now part of the social module
            // $settings['enable_comments'] = isset($_POST['enable_comments']) ? 1 : 0;
            $settings['enable_wiki'] = isset($_POST['enable_wiki']) ? 1 : 0;
            $settings['enable_social'] = isset($_POST['enable_social']) ? 1 : 0;
            $settings['enable_analytics'] = isset($_POST['enable_analytics']) ? 1 : 0;
            $settings['enable_notifications'] = isset($_POST['enable_notifications']) ? 1 : 0;
        } elseif ($form_section === 'maintenance') {
            // Maintenance settings
            $settings['maintenance_mode'] = isset($_POST['maintenance_mode']) ? true : false;
            if (isset($_POST['maintenance_message'])) {
                $settings['maintenance_message'] = sanitize_input($_POST['maintenance_message']);
            }
            if (isset($_POST['estimated_downtime'])) {
                $settings['estimated_downtime'] = sanitize_input($_POST['estimated_downtime']);
            }
        }
        
        $updated = 0;
        foreach ($settings as $key => $value) {
            // Determine type based on the value
            if (is_int($value)) {
                $type = 'integer';
            } elseif (is_bool($value) || $value === 0 || $value === 1) {
                $type = 'boolean';
                $value = (bool) $value; // Ensure it's a proper boolean
            } else {
                $type = 'string';
            }
            
            if (set_system_setting($key, $value, $type)) {
                $updated++;
            }
        }
        
        if ($updated > 0) {
            show_message('General settings updated successfully.', 'success');
            log_activity('system_settings_updated', "Updated $updated general settings");
        } else {
            show_message('Failed to update general settings.', 'error');
        }
        
        // Store the current tab for after redirect
        $current_tab = $_POST['current_tab'] ?? $form_section;
        $_SESSION['active_settings_tab'] = $current_tab;
        redirect('/admin/system_settings?tab=' . $current_tab);
        } elseif ($action === 'toggle_extension') {
            // Toggle extension enabled/disabled
            $extension_name = $_POST['extension_name'] ?? '';
            if ($extension_name) {
                $extension = $extension_manager->getExtension($extension_name);
                if ($extension) {
                    // Get the current status from the database using the correct setting key
                    $setting_key = $extension_manager->getExtensionSettingKey($extension_name);
                    $current_status = get_system_setting($setting_key, $extension->enabled);
                    $new_status = !$current_status;
                    
                    if (set_system_setting($setting_key, $new_status, 'boolean')) {
                        $status_text = $new_status ? 'enabled' : 'disabled';
                        show_message("Extension '{$extension->name}' {$status_text} successfully.", 'success');
                        log_activity('extension_toggled', "Extension '{$extension->name}' {$status_text}");
                    } else {
                        show_message('Failed to toggle extension.', 'error');
                    }
                } else {
                    show_message('Extension not found.', 'error');
                }
            } else {
                show_message('Invalid extension name.', 'error');
            }
            
            // Store the current tab for after redirect
            $current_tab = $_POST['current_tab'] ?? 'extensions';
            $_SESSION['active_settings_tab'] = $current_tab;
            
            // Debug logging
            error_log("Extension toggle - current_tab from POST: " . ($_POST['current_tab'] ?? 'not set'));
            error_log("Extension toggle - setting session to: " . $current_tab);
            
            redirect('/admin/system_settings?tab=' . $current_tab);
        } elseif ($action === 'update_extension_settings') {
            // Update extension settings
            $extension_name = $_POST['extension_name'] ?? '';
            if ($extension_name) {
                $extension = $extension_manager->getExtension($extension_name);
                if ($extension && method_exists($extension, 'saveSettings')) {
                    if ($extension->saveSettings($_POST)) {
                        show_message("Extension '{$extension->name}' settings updated successfully.", 'success');
                        log_activity('extension_settings_updated', "Updated settings for extension '{$extension->name}'");
                    } else {
                        show_message('Failed to update extension settings.', 'error');
                    }
                } else {
                    show_message('Extension not found or does not support settings.', 'error');
                }
            } else {
                show_message('Invalid extension name.', 'error');
            }
            
            // Store the current tab for after redirect
            $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'extensions';
            redirect('/admin/system_settings?tab=' . $current_tab);
        } elseif ($action === 'update_skin_settings') {
            // Update skin settings
            $settings = [];
            
            if (isset($_POST['default_skin'])) {
                $settings['default_skin'] = sanitize_input($_POST['default_skin']);
            }
            $settings['allow_skin_selection'] = isset($_POST['allow_skin_selection']) ? 1 : 0;
            
            $updated = 0;
            foreach ($settings as $key => $value) {
                $type = is_bool($value) || $value === 0 || $value === 1 ? 'boolean' : 'string';
                if (set_system_setting($key, $value, $type)) {
                    $updated++;
                }
            }
            
            if ($updated > 0) {
                show_message('Skin settings updated successfully.', 'success');
                log_activity('skin_settings_updated', "Updated skin settings");
            } else {
                show_message('Failed to update skin settings.', 'error');
            }
            
            // Store the current tab for after redirect
            $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'skins';
            redirect('/admin/system_settings?tab=' . $current_tab);
        } elseif ($action === 'activate_skin') {
            // Activate a skin
            $skin_name = $_POST['skin_name'] ?? '';
            if ($skin_name) {
                $skin = $skins_manager->getSkin($skin_name);
                
                if ($skin) {
                    if (set_system_setting('default_skin', $skin_name, 'string')) {
                        show_message("Skin '{$skin['display_name']}' activated successfully.", 'success');
                        log_activity('skin_activated', "Activated skin '{$skin['display_name']}'");
                    } else {
                        show_message('Failed to activate skin.', 'error');
                    }
                } else {
                    show_message('Skin not found.', 'error');
                }
            } else {
                show_message('Invalid skin name.', 'error');
            }
            
            // Store the current tab for after redirect
            $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'skins';
            redirect('/admin/system_settings?tab=' . $current_tab);
    } elseif ($action === 'update_security') {
        $settings = [
            'password_min_length' => (int)($_POST['password_min_length'] ?? 8),
            'session_timeout' => (int)($_POST['session_timeout'] ?? 3600),
            'max_login_attempts' => (int)($_POST['max_login_attempts'] ?? 5),
            'lockout_duration' => (int)($_POST['lockout_duration'] ?? 900),
            'enable_2fa' => isset($_POST['enable_2fa']) ? 1 : 0,
            'require_strong_passwords' => isset($_POST['require_strong_passwords']) ? 1 : 0,
            'enable_captcha' => isset($_POST['enable_captcha']) ? 1 : 0,
            'enable_rate_limiting' => isset($_POST['enable_rate_limiting']) ? 1 : 0,
        ];
        
        $updated = 0;
        foreach ($settings as $key => $value) {
            // Determine type based on the value
            if (is_int($value)) {
                $type = 'integer';
            } elseif (is_bool($value) || $value === 0 || $value === 1) {
                $type = 'boolean';
                $value = (bool) $value; // Ensure it's a proper boolean
            } else {
                $type = 'string';
            }
            
            if (set_system_setting($key, $value, $type)) {
                $updated++;
            }
        }
        
        if ($updated > 0) {
            show_message('Security settings updated successfully.', 'success');
            log_activity('security_settings_updated', "Updated $updated security settings");
        } else {
            show_message('Failed to update security settings.', 'error');
        }
        
        // Store the current tab for after redirect
        $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'security';
            redirect('/admin/system_settings?tab=' . $current_tab);
    } elseif ($action === 'update_email') {
        $settings = [
            'smtp_host' => sanitize_input($_POST['smtp_host'] ?? ''),
            'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
            'smtp_username' => sanitize_input($_POST['smtp_username'] ?? ''),
            'smtp_password' => $_POST['smtp_password'] ?: get_system_setting('smtp_password', ''),
            'smtp_encryption' => sanitize_input($_POST['smtp_encryption'] ?? 'tls'),
            'email_from_name' => sanitize_input($_POST['email_from_name'] ?? ''),
            'email_from_address' => sanitize_input($_POST['email_from_address'] ?? ''),
            'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? 1 : 0,
        ];
        
        $updated = 0;
        foreach ($settings as $key => $value) {
            // Determine type based on the value
            if (is_int($value)) {
                $type = 'integer';
            } elseif (is_bool($value) || $value === 0 || $value === 1) {
                $type = 'boolean';
                $value = (bool) $value; // Ensure it's a proper boolean
            } else {
                $type = 'string';
            }
            
            if (set_system_setting($key, $value, $type)) {
                $updated++;
            }
        }
        
        if ($updated > 0) {
            show_message('Email settings updated successfully.', 'success');
            log_activity('email_settings_updated', "Updated $updated email settings");
        } else {
            show_message('Failed to update email settings.', 'error');
        }
        
        // Store the current tab for after redirect
        $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'email';
            redirect('/admin/system_settings?tab=' . $current_tab);
    } elseif ($action === 'test_email') {
        $test_email = sanitize_input($_POST['test_email'] ?? '');
        if (filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            // Send test email (placeholder - would need actual email implementation)
            show_message('Test email sent to ' . $test_email, 'success');
            log_activity('test_email_sent', "Test email sent to $test_email");
        } else {
            show_message('Invalid email address.', 'error');
        }
        
        // Store the current tab for after redirect
        $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'email';
            redirect('/admin/system_settings?tab=' . $current_tab);
    } elseif ($action === 'clear_cache') {
        // Clear various caches
        $cleared = 0;
        
        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $cleared++;
        }
        
        // Clear session cache
        session_regenerate_id(true);
        $cleared++;
        
        if ($cleared > 0) {
            show_message('Cache cleared successfully.', 'success');
            log_activity('cache_cleared', "Cleared system cache");
        } else {
            show_message('No cache to clear.', 'info');
        }
        
        // Store the current tab for after redirect (cache clear can be from any tab)
        $_SESSION['active_settings_tab'] = $_POST['current_tab'] ?? 'general';
        redirect('/admin/system_settings?tab=' . $current_tab);
    } elseif ($action === 'toggle_module') {
        // Toggle module enabled/disabled
        $module_name = $_POST['module_name'] ?? '';
        if ($module_name) {
            $setting_key = 'enable_' . $module_name;
            $current_status = get_system_setting($setting_key, true);
            $new_status = !$current_status;
            
            if (set_system_setting($setting_key, $new_status, 'boolean')) {
                $status_text = $new_status ? 'enabled' : 'disabled';
                
                // If toggling social module, also update comments setting
                if ($module_name === 'social') {
                    set_system_setting('enable_comments', $new_status, 'boolean');
                    show_message("Social module (including comments) {$status_text} successfully.", 'success');
                    log_activity('module_toggled', "Social module (including comments) {$status_text}");
                } else {
                    show_message("Module '{$module_name}' {$status_text} successfully.", 'success');
                    log_activity('module_toggled', "Module '{$module_name}' {$status_text}");
                }
            } else {
                show_message("Failed to {$status_text} module '{$module_name}'.", 'error');
            }
        }
        
        // Store the current tab for after redirect
        $current_tab = $_POST['current_tab'] ?? 'modules';
        $_SESSION['active_settings_tab'] = $current_tab;
        
        // Debug logging
        error_log("Module toggle - current_tab from POST: " . ($_POST['current_tab'] ?? 'not set'));
        error_log("Module toggle - setting session to: " . $current_tab);
        
        redirect('/admin/system_settings?tab=' . $current_tab);
    }
}

// Store active tab after all processing
// Check URL parameter first, then session
$active_tab = $_GET['tab'] ?? $_SESSION['active_settings_tab'] ?? 'general';

// Debug logging
error_log("Active tab detection - URL param: " . ($_GET['tab'] ?? 'not set'));
error_log("Active tab detection - Session: " . ($_SESSION['active_settings_tab'] ?? 'not set'));
error_log("Active tab detection - Final: " . $active_tab);

// Get current settings
$current_settings = [
    // General Settings
    'site_name' => get_system_setting('site_name', SITE_NAME),
    'site_description' => get_system_setting('site_description', 'A comprehensive Islamic knowledge platform'),
    'site_keywords' => get_system_setting('site_keywords', 'Islam, Islamic, knowledge, wiki'),
    'admin_email' => get_system_setting('admin_email', '') ?: get_first_user_email(),
    'contact_email' => get_system_setting('contact_email', '') ?: get_first_user_email(),
    'posts_per_page' => get_system_setting('posts_per_page', 10),
    'articles_per_page' => get_system_setting('articles_per_page', 10),
    'allow_registration' => get_system_setting('allow_registration', true),
    'require_email_verification' => get_system_setting('require_email_verification', false),
    'enable_comments' => get_system_setting('enable_social', true), // Comments are now part of social module
    'enable_wiki' => get_system_setting('enable_wiki', true),
    'enable_social' => get_system_setting('enable_social', true),
    'maintenance_mode' => get_system_setting('maintenance_mode', false),
    'maintenance_message' => get_system_setting('maintenance_message', 'We are currently performing scheduled maintenance. Please check back later.'),
    'estimated_downtime' => get_system_setting('estimated_downtime', '2-4 hours'),
    'enable_analytics' => get_system_setting('enable_analytics', true),
    'enable_notifications' => get_system_setting('enable_notifications', true),
    'copyright_text' => get_system_setting('copyright_text', ''),
    
    // Security Settings
    'password_min_length' => get_system_setting('password_min_length', 8),
    'session_timeout' => get_system_setting('session_timeout', 3600),
    'max_login_attempts' => get_system_setting('max_login_attempts', 5),
    'lockout_duration' => get_system_setting('lockout_duration', 900),
    'enable_2fa' => get_system_setting('enable_2fa', false),
    'require_strong_passwords' => get_system_setting('require_strong_passwords', true),
    'enable_captcha' => get_system_setting('enable_captcha', false),
    'enable_rate_limiting' => get_system_setting('enable_rate_limiting', true),
    
    // Email Settings
    'smtp_host' => get_system_setting('smtp_host', ''),
    'smtp_port' => get_system_setting('smtp_port', 587),
    'smtp_username' => get_system_setting('smtp_username', ''),
    'smtp_password' => get_system_setting('smtp_password', ''),
    'smtp_encryption' => get_system_setting('smtp_encryption', 'tls'),
    'email_from_name' => get_system_setting('email_from_name', get_site_name()),
    'email_from_address' => get_system_setting('email_from_address', 'noreply@islamwiki.org'),
    'enable_email_notifications' => get_system_setting('enable_email_notifications', true),
];

// Get comprehensive system information
$system_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database_version' => $pdo->query('SELECT VERSION()')->fetchColumn(),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_vars' => ini_get('max_input_vars'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'date_timezone' => date_default_timezone_get(),
    'server_time' => date('Y-m-d H:i:s'),
    'server_load' => function_exists('sys_getloadavg') ? implode(', ', sys_getloadavg()) : 'N/A',
    'opcache_enabled' => function_exists('opcache_get_status') ? (opcache_get_status()['opcache_enabled'] ?? false) : false,
];

// Get comprehensive statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
    'new_users_30d' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'total_articles' => $pdo->query("SELECT COUNT(*) FROM wiki_articles")->fetchColumn(),
    'published_articles' => $pdo->query("SELECT COUNT(*) FROM wiki_articles WHERE status = 'published'")->fetchColumn(),
    'draft_articles' => $pdo->query("SELECT COUNT(*) FROM wiki_articles WHERE status = 'draft'")->fetchColumn(),
    'new_articles_30d' => $pdo->query("SELECT COUNT(*) FROM wiki_articles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'total_categories' => $pdo->query("SELECT COUNT(*) FROM content_categories")->fetchColumn(),
    'total_comments' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
    'total_files' => $pdo->query("SELECT COUNT(*) FROM wiki_files")->fetchColumn(),
    'total_file_size' => $pdo->query("SELECT SUM(file_size) FROM wiki_files")->fetchColumn() ?: 0,
    'disk_usage' => function_exists('disk_total_space') ? disk_total_space('.') - disk_free_space('.') : 'N/A',
    'free_space' => function_exists('disk_free_space') ? disk_free_space('.') : 'N/A',
    'disk_total' => function_exists('disk_total_space') ? disk_total_space('.') : 'N/A',
];

// Get recent activity
$recent_activity = $pdo->query("
    SELECT 'user' as type, username as name, created_at as date, 'registered' as action
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'article' as type, title as name, created_at as date, 'created' as action
    FROM wiki_articles 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY date DESC 
    LIMIT 10
")->fetchAll();

// Get system health status
$system_health = [
    'database' => 'healthy',
    'storage' => $stats['free_space'] !== 'N/A' && $stats['free_space'] > (1024*1024*1024) ? 'healthy' : 'warning',
    'memory' => ini_get('memory_limit') !== '-1' && (int)ini_get('memory_limit') >= 128 ? 'healthy' : 'warning',
    'php' => version_compare(PHP_VERSION, '8.0.0', '>=') ? 'healthy' : 'warning',
];

// Load admin CSS
$admin_css = true;
include "../../includes/header.php";

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<?php

?>
<script src="/skins/bismillah/assets/js/admin_system_settings.js"></script>
<?php
?>


<div class="admin-container">
    <div class="admin-header">
        <div class="header-content">
            <h1><i class="iw iw-cog"></i> System Settings</h1>
            <p>Configure and manage your system settings, security, and preferences</p>
        </div>
        <div class="header-actions">
            <a href="/admin" class="btn btn-secondary">
                <i class="iw iw-arrow-left"></i> Back to Admin Panel
            </a>
        </div>
    </div>
    
    
    <div class="settings-tabs">
        <button class="tab-button <?php echo $active_tab === 'general' ? 'active' : ''; ?>" onclick="showTab('general', this)">
            <i class="iw iw-globe"></i> General
        </button>
        <button class="tab-button <?php echo $active_tab === 'security' ? 'active' : ''; ?>" onclick="showTab('security', this)">
            <i class="iw iw-shield-alt"></i> Security
        </button>
        <button class="tab-button <?php echo $active_tab === 'email' ? 'active' : ''; ?>" onclick="showTab('email', this)">
            <i class="iw iw-envelope"></i> Email
        </button>
        <button class="tab-button <?php echo $active_tab === 'system' ? 'active' : ''; ?>" onclick="showTab('system', this)">
            <i class="iw iw-server"></i> System Info
        </button>
        <button class="tab-button <?php echo $active_tab === 'statistics' ? 'active' : ''; ?>" onclick="showTab('statistics', this)">
            <i class="iw iw-chart-bar"></i> Statistics
        </button>
        <button class="tab-button <?php echo $active_tab === 'tools' ? 'active' : ''; ?>" onclick="showTab('tools', this)">
            <i class="iw iw-tools"></i> Tools
        </button>
        <button class="tab-button <?php echo $active_tab === 'extensions' ? 'active' : ''; ?>" onclick="showTab('extensions', this)">
            <i class="iw iw-puzzle-piece"></i> Extensions
        </button>
        <button class="tab-button <?php echo $active_tab === 'modules' ? 'active' : ''; ?>" onclick="showTab('modules', this)">
            <i class="iw iw-cubes"></i> Modules
        </button>
        <button class="tab-button <?php echo $active_tab === 'skins' ? 'active' : ''; ?>" onclick="showTab('skins', this)">
            <i class="iw iw-palette"></i> Skins
        </button>
    </div>
    
    <!-- General Settings Tab -->
    <div id="general-tab" class="tab-content <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <!-- Two Column Layout -->
        <div class="settings-two-column">
            <!-- Column 1: Site Information -->
            <div class="column-1">
                <div class="card">
                    <h2><i class="iw iw-globe"></i> Site Information</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_general">
                        <input type="hidden" name="form_section" value="site_info">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="site_name">Site Name *</label>
                                <input type="text" id="site_name" name="site_name" 
                                       value="<?php echo htmlspecialchars($current_settings['site_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="admin_email">Admin Email *</label>
                                <input type="email" id="admin_email" name="admin_email" 
                                       value="<?php echo htmlspecialchars($current_settings['admin_email']); ?>" required>
                                <small class="form-help">Defaults to the first user's email (site setup person)</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_email">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email" 
                                       value="<?php echo htmlspecialchars($current_settings['contact_email']); ?>">
                                <small class="form-help">Defaults to the first user's email (site setup person)</small>
                            </div>
                            <div class="form-group">
                                <label for="posts_per_page">Posts Per Page</label>
                                <input type="number" id="posts_per_page" name="posts_per_page" 
                                       value="<?php echo $current_settings['posts_per_page']; ?>" min="1" max="100">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Site Description</label>
                            <textarea id="site_description" name="site_description" rows="3" 
                                      placeholder="Brief description of your site"><?php echo htmlspecialchars($current_settings['site_description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_keywords">Site Keywords (comma separated)</label>
                            <input type="text" id="site_keywords" name="site_keywords" 
                                   value="<?php echo htmlspecialchars($current_settings['site_keywords']); ?>"
                                   placeholder="islam, quran, hadith, knowledge">
                        </div>
                        
                        <div class="form-group">
                            <label for="copyright_text">Copyright Text (optional)</label>
                            <textarea id="copyright_text" name="copyright_text" rows="2" 
                                      placeholder="Leave empty to use default: © <?php echo date('Y'); ?> <?php echo get_site_name(); ?>. All rights reserved."><?php echo htmlspecialchars($current_settings['copyright_text']); ?></textarea>
                            <small class="form-help">Custom copyright text. If left empty, will use default format with site name.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Site Logo</label>
                            <div class="logo-upload-container">
                                <div class="logo-preview" id="logo-preview">
                                    <?php 
                                    $logo_url = get_site_logo_url();
                                    $logo_data = get_site_logo_data();
                                    if ($logo_url): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Site Logo" id="current-logo">
                                        <div class="logo-info">
                                            <small>
                                                <?php echo htmlspecialchars($logo_data['original_name'] ?? 'logo'); ?>
                                                <?php if (isset($logo_data['dimensions'])): ?>
                                                    (<?php echo $logo_data['dimensions']['width']; ?>×<?php echo $logo_data['dimensions']['height']; ?>)
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-logo">
                                            <i class="iw iw-image"></i>
                                            <p>No logo uploaded</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="logo-upload-controls">
                                    <input type="file" id="site_logo_input" name="site_logo" accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('site_logo_input').click()">
                                        <i class="iw iw-upload"></i> <?php echo $logo_url ? 'Change Logo' : 'Upload Logo'; ?>
                                    </button>
                                    <?php if ($logo_url): ?>
                                        <button type="button" class="btn btn-danger" onclick="removeSiteLogo()">
                                            <i class="iw iw-trash"></i> Remove
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <small class="form-help">Upload a logo for your site. Supported formats: JPEG, PNG, GIF, SVG. Max size: 5MB.</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="iw iw-save"></i> Save General Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Column 2: Maintenance Mode + Feature Toggles -->
            <div class="column-2">
                <div class="card maintenance-mode-card">
                    <h2><i class="iw iw-tools"></i> Maintenance Mode</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_general">
                        <input type="hidden" name="form_section" value="maintenance">
                        
                        <div class="maintenance-toggle-section">
                            <div class="feature-toggle maintenance-toggle">
                                <div class="toggle-content">
                                    <i class="iw iw-tools"></i>
                                    <div>
                                        <strong>Maintenance Mode</strong>
                                        <small>Put site in maintenance mode</small>
                                    </div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="maintenance_mode" 
                                           <?php echo $current_settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="maintenance-settings" id="maintenance-settings" >
                                <h3 >
                                    <i class="iw iw-cog"></i>
                                    Maintenance Settings
                                </h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="maintenance_message">Maintenance Message</label>
                                        <textarea id="maintenance_message" name="maintenance_message" rows="3" 
                                                  placeholder="Enter the message to display during maintenance..."><?php echo htmlspecialchars($current_settings['maintenance_message']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="estimated_downtime">Estimated Downtime</label>
                                        <input type="text" id="estimated_downtime" name="estimated_downtime" 
                                               value="<?php echo htmlspecialchars($current_settings['estimated_downtime']); ?>"
                                               placeholder="e.g., 2-4 hours, 30 minutes, etc.">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="iw iw-save"></i> Save Maintenance Settings
                        </button>
                    </form>
                </div>
                
                <div class="card">
                    <h2><i class="iw iw-toggle-on"></i> Feature Toggles</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_general">
                    <input type="hidden" name="form_section" value="features">
                    
                    <div class="feature-grid">
                        <div class="feature-toggle">
                            <div class="toggle-content">
                                <i class="iw iw-user-plus"></i>
                                <div>
                                    <strong>Allow User Registration</strong>
                                    <small>Enable new user registration</small>
                                </div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="allow_registration" 
                                       <?php echo $current_settings['allow_registration'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="feature-toggle">
                            <div class="toggle-content">
                                <i class="iw iw-envelope-check"></i>
                                <div>
                                    <strong>Email Verification</strong>
                                    <small>Require email verification for new users</small>
                                </div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="require_email_verification" 
                                       <?php echo $current_settings['require_email_verification'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        
                        <div class="feature-toggle">
                            <div class="toggle-content">
                                <i class="iw iw-book"></i>
                                <div>
                                    <strong>Wiki System</strong>
                                    <small>Enable wiki functionality</small>
                                </div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_wiki" 
                                       <?php echo $current_settings['enable_wiki'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="feature-toggle">
                            <div class="toggle-content">
                                <i class="iw iw-users"></i>
                                <div>
                                    <strong>Social Features</strong>
                                    <small>Enable social networking features</small>
                                </div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_social" 
                                       <?php echo $current_settings['enable_social'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="feature-toggle">
                            <div class="toggle-content">
                                <i class="iw iw-chart-line"></i>
                                <div>
                                    <strong>Analytics</strong>
                                    <small>Enable analytics tracking</small>
                                </div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_analytics" 
                                       <?php echo $current_settings['enable_analytics'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="feature-toggle">
                            <div class="toggle-content">
                                <i class="iw iw-bell"></i>
                                <div>
                                    <strong>Notifications</strong>
                                    <small>Enable notification system</small>
                                </div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_notifications" 
                                       <?php echo $current_settings['enable_notifications'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="iw iw-save"></i> Save Feature Settings
                    </button>
                </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Settings Tab -->
    <div id="security-tab" class="tab-content <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
        <div class="card">
            <h2><i class="iw iw-shield-alt"></i> Security Settings</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_security">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password_min_length">Minimum Password Length</label>
                        <input type="number" id="password_min_length" name="password_min_length" 
                               value="<?php echo $current_settings['password_min_length']; ?>" min="6" max="32">
                    </div>
                    <div class="form-group">
                        <label for="session_timeout">Session Timeout (seconds)</label>
                        <input type="number" id="session_timeout" name="session_timeout" 
                               value="<?php echo $current_settings['session_timeout']; ?>" min="300" max="86400">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="max_login_attempts">Max Login Attempts</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts" 
                               value="<?php echo $current_settings['max_login_attempts']; ?>" min="3" max="20">
                    </div>
                    <div class="form-group">
                        <label for="lockout_duration">Lockout Duration (seconds)</label>
                        <input type="number" id="lockout_duration" name="lockout_duration" 
                               value="<?php echo $current_settings['lockout_duration']; ?>" min="60" max="3600">
                    </div>
                </div>
                
                <div class="security-features">
                    <div class="feature-toggle">
                        <div class="toggle-content">
                            <i class="iw iw-key"></i>
                            <div>
                                <strong>Require Strong Passwords</strong>
                                <small>Enforce complex password requirements</small>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="require_strong_passwords" 
                                   <?php echo $current_settings['require_strong_passwords'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="feature-toggle">
                        <div class="toggle-content">
                            <i class="iw iw-mobile-alt"></i>
                            <div>
                                <strong>Two-Factor Authentication</strong>
                                <small>Enable 2FA for enhanced security</small>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_2fa" 
                                   <?php echo $current_settings['enable_2fa'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="feature-toggle">
                        <div class="toggle-content">
                            <i class="iw iw-robot"></i>
                            <div>
                                <strong>CAPTCHA Protection</strong>
                                <small>Enable CAPTCHA for forms</small>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_captcha" 
                                   <?php echo $current_settings['enable_captcha'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="feature-toggle">
                        <div class="toggle-content">
                            <i class="iw iw-tachometer-alt"></i>
                            <div>
                                <strong>Rate Limiting</strong>
                                <small>Limit requests per IP address</small>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_rate_limiting" 
                                   <?php echo $current_settings['enable_rate_limiting'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="iw iw-save"></i> Save Security Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Email Settings Tab -->
    <div id="email-tab" class="tab-content <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-envelope"></i> SMTP Configuration</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_email">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" 
                                   value="<?php echo htmlspecialchars($current_settings['smtp_host']); ?>"
                                   placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" 
                                   value="<?php echo $current_settings['smtp_port']; ?>" min="1" max="65535">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_username">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username" 
                                   value="<?php echo htmlspecialchars($current_settings['smtp_username']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" 
                                   placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_encryption">Encryption</label>
                            <select id="smtp_encryption" name="smtp_encryption">
                                <option value="none" <?php echo $current_settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None</option>
                                <option value="tls" <?php echo $current_settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $current_settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email_from_name">From Name</label>
                            <input type="text" id="email_from_name" name="email_from_name" 
                                   value="<?php echo htmlspecialchars($current_settings['email_from_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email_from_address">From Email Address</label>
                        <input type="email" id="email_from_address" name="email_from_address" 
                               value="<?php echo htmlspecialchars($current_settings['email_from_address']); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="iw iw-save"></i> Save Email Settings
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2><i class="iw iw-paper-plane"></i> Test Email</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="test_email">
                    
                    <div class="form-group">
                        <label for="test_email">Test Email Address</label>
                        <input type="email" id="test_email" name="test_email" 
                               placeholder="test@example.com" required>
                    </div>
                    
                    <button type="submit" class="btn btn-info">
                        <i class="iw iw-paper-plane"></i> Send Test Email
                    </button>
                </form>
                
                <div class="email-features">
                    <div class="feature-toggle">
                        <div class="toggle-content">
                            <i class="iw iw-bell"></i>
                            <div>
                                <strong>Email Notifications</strong>
                                <small>Enable email notifications</small>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="enable_email_notifications" 
                                   <?php echo $current_settings['enable_email_notifications'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Information Tab -->
    <div id="system-tab" class="tab-content <?php echo $active_tab === 'system' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-server"></i> Server Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>PHP Version:</strong>
                        <span class="status-<?php echo $system_health['php']; ?>"><?php echo $system_info['php_version']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Server Software:</strong>
                        <span><?php echo $system_info['server_software']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Database Version:</strong>
                        <span class="status-<?php echo $system_health['database']; ?>"><?php echo $system_info['database_version']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Server Time:</strong>
                        <span><?php echo $system_info['server_time']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Timezone:</strong>
                        <span><?php echo $system_info['date_timezone']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Server Load:</strong>
                        <span><?php echo $system_info['server_load']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2><i class="iw iw-memory"></i> PHP Configuration</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Memory Limit:</strong>
                        <span class="status-<?php echo $system_health['memory']; ?>"><?php echo $system_info['memory_limit']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Max Execution Time:</strong>
                        <span><?php echo $system_info['max_execution_time']; ?>s</span>
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
                        <strong>Max Input Vars:</strong>
                        <span><?php echo $system_info['max_input_vars']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Max File Uploads:</strong>
                        <span><?php echo $system_info['max_file_uploads']; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>OPcache Enabled:</strong>
                        <span class="status-<?php echo $system_info['opcache_enabled'] ? 'healthy' : 'warning'; ?>">
                            <?php echo $system_info['opcache_enabled'] ? 'Yes' : 'No'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Tab -->
    <div id="statistics-tab" class="tab-content <?php echo $active_tab === 'statistics' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-chart-bar"></i> Content Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-detail"><?php echo number_format($stats['active_users']); ?> active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['total_articles']); ?></div>
                        <div class="stat-label">Total Articles</div>
                        <div class="stat-detail"><?php echo number_format($stats['published_articles']); ?> published</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['total_categories']); ?></div>
                        <div class="stat-label">Categories</div>
                        <div class="stat-detail">Content organization</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['total_comments']); ?></div>
                        <div class="stat-label">Comments</div>
                        <div class="stat-detail">User interactions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['total_files']); ?></div>
                        <div class="stat-label">Files</div>
                        <div class="stat-detail"><?php echo format_file_size($stats['total_file_size']); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['new_users_30d']); ?></div>
                        <div class="stat-label">New Users (30d)</div>
                        <div class="stat-detail">Recent growth</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2><i class="iw iw-hdd"></i> Storage Information</h2>
                <?php if ($stats['disk_usage'] !== 'N/A'): ?>
                <div class="storage-info">
                    <div class="storage-item">
                        <div class="storage-label">Total Space</div>
                        <div class="storage-value"><?php echo format_file_size($stats['disk_total']); ?></div>
                    </div>
                    <div class="storage-item">
                        <div class="storage-label">Used Space</div>
                        <div class="storage-value"><?php echo format_file_size($stats['disk_usage']); ?></div>
                    </div>
                    <div class="storage-item">
                        <div class="storage-label">Free Space</div>
                        <div class="storage-value status-<?php echo $system_health['storage']; ?>"><?php echo format_file_size($stats['free_space']); ?></div>
                    </div>
                    <div class="storage-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $stats['disk_total'] > 0 ? ($stats['disk_usage'] / $stats['disk_total'] * 100) : 0; ?>%"></div>
                        </div>
                        <div class="progress-text">
                            <?php echo $stats['disk_total'] > 0 ? number_format($stats['disk_usage'] / $stats['disk_total'] * 100, 1) : 0; ?>% used
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <p>Storage information not available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <h2><i class="iw iw-clock"></i> Recent Activity</h2>
            <div class="activity-list">
                <?php if (!empty($recent_activity)): ?>
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="iw iw-<?php echo $activity['type'] === 'user' ? 'user' : 'file-alt'; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <?php echo htmlspecialchars($activity['name']); ?>
                            </div>
                            <div class="activity-meta">
                                <?php echo ucfirst($activity['action']); ?> • 
                                <?php echo date('M j, Y g:i A', strtotime($activity['date'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-activity">No recent activity</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Tools Tab -->
    <div id="tools-tab" class="tab-content <?php echo $active_tab === 'tools' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-tools"></i> System Tools</h2>
                <div class="tools-grid">
                    <form method="POST" class="tool-form">
                        <input type="hidden" name="action" value="clear_cache">
                        <div class="tool-content">
                            <i class="iw iw-broom"></i>
                            <div>
                                <strong>Clear Cache</strong>
                                <small>Clear system cache and temporary files</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="iw iw-broom"></i> Clear Cache
                        </button>
                    </form>
                    
                    <div class="tool-form">
                        <div class="tool-content">
                            <i class="iw iw-database"></i>
                            <div>
                                <strong>Database Backup</strong>
                                <small>Create a backup of the database</small>
                            </div>
                        </div>
                        <a href="/admin/maintenance" class="btn btn-info">
                            <i class="iw iw-database"></i> Go to Maintenance
                        </a>
                    </div>
                    
                    <div class="tool-form">
                        <div class="tool-content">
                            <i class="iw iw-chart-line"></i>
                            <div>
                                <strong>View Analytics</strong>
                                <small>Access detailed analytics dashboard</small>
                            </div>
                        </div>
                        <a href="/admin/analytics" class="btn btn-primary">
                            <i class="iw iw-chart-line"></i> View Analytics
                        </a>
                    </div>
                    
                    <div class="tool-form">
                        <div class="tool-content">
                            <i class="iw iw-users-cog"></i>
                            <div>
                                <strong>Manage Users</strong>
                                <small>User management and permissions</small>
                            </div>
                        </div>
                        <a href="/admin/manage_users" class="btn btn-secondary">
                            <i class="iw iw-users-cog"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2><i class="iw iw-heartbeat"></i> System Health</h2>
                <div class="health-container">
                    <div class="health-item">
                        <div class="health-label">Database</div>
                        <div class="health-status status-<?php echo $system_health['database']; ?>">
                            <i class="iw iw-<?php echo $system_health['database'] === 'healthy' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo ucfirst($system_health['database']); ?>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="health-label">Storage</div>
                        <div class="health-status status-<?php echo $system_health['storage']; ?>">
                            <i class="iw iw-<?php echo $system_health['storage'] === 'healthy' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo ucfirst($system_health['storage']); ?>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="health-label">Memory</div>
                        <div class="health-status status-<?php echo $system_health['memory']; ?>">
                            <i class="iw iw-<?php echo $system_health['memory'] === 'healthy' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo ucfirst($system_health['memory']); ?>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="health-label">PHP Version</div>
                        <div class="health-status status-<?php echo $system_health['php']; ?>">
                            <i class="iw iw-<?php echo $system_health['php'] === 'healthy' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo ucfirst($system_health['php']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Extensions Tab -->
    <div id="extensions-tab" class="tab-content <?php echo $active_tab === 'extensions' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-puzzle-piece"></i> Extensions Management</h2>
                <p>Manage extensions that add additional functionality to your site.</p>
                
                <?php
                // Get extension settings
                $extension_settings = $extension_manager->getExtensionSettings();
                ?>
                
                <div class="extensions-container">
                    <?php foreach ($extension_settings as $name => $extension): ?>
                    <div class="extension-card">
                        <div class="extension-header">
                            <div class="extension-info">
                                <h3><?php echo htmlspecialchars($extension['name']); ?></h3>
                                <p><?php echo htmlspecialchars($extension['description']); ?></p>
                                <div class="extension-meta">
                                    <span class="extension-version">v<?php echo htmlspecialchars($extension['version']); ?></span>
                                    <span class="extension-status <?php echo $extension['enabled'] ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $extension['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="extension-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_extension">
                                    <input type="hidden" name="extension_name" value="<?php echo htmlspecialchars($name); ?>">
                                    <input type="hidden" name="current_tab" value="extensions">
                                    <button type="submit" class="btn <?php echo $extension['enabled'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <i class="iw iw-<?php echo $extension['enabled'] ? 'pause' : 'play'; ?>"></i>
                                        <?php echo $extension['enabled'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </form>
                                <?php if ($extension['enabled'] && !empty($extension['settings_form'])): ?>
                                <button type="button" class="btn btn-outline extension-settings-toggle" onclick="toggleExtensionSettings('<?php echo htmlspecialchars($name); ?>')" title="Show/Hide Extension Settings">
                                    <i class="iw iw-chevron-down"></i> More Options
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($extension['enabled'] && !empty($extension['settings_form'])): ?>
                        <div class="extension-settings" id="extension-settings-<?php echo htmlspecialchars($name); ?>" style="display: none;">
                            <h4>Settings</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_extension_settings">
                                <input type="hidden" name="extension_name" value="<?php echo htmlspecialchars($name); ?>">
                                <input type="hidden" name="current_tab" value="extensions">
                                <?php echo $extension['settings_form']; ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="iw iw-save"></i> Save Settings
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modules Tab -->
    <div id="modules-tab" class="tab-content <?php echo $active_tab === 'modules' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-cubes"></i> Core Modules</h2>
                <p>Manage core system modules and their settings.</p>
                
                <div class="modules-container">
                    <div class="module-card">
                        <div class="module-header">
                            <div class="module-info">
                                <h3><i class="iw iw-book"></i> Wiki Module</h3>
                                <p>Core wiki functionality for articles and content management</p>
                            </div>
                        </div>
                        <div class="module-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_module">
                                <input type="hidden" name="module_name" value="wiki">
                                <input type="hidden" name="current_tab" value="modules">
                                <button type="submit" class="btn <?php echo get_system_setting('enable_wiki', true) ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="iw iw-<?php echo get_system_setting('enable_wiki', true) ? 'pause' : 'play'; ?>"></i>
                                    <?php echo get_system_setting('enable_wiki', true) ? 'Disable' : 'Enable'; ?>
                                </button>
                            </form>
                            <a href="/admin/manage_categories" class="btn btn-secondary">
                                <i class="iw iw-tags"></i> Manage Categories
                            </a>
                            <a href="/admin/create_article" class="btn btn-primary">
                                <i class="iw iw-plus"></i> Create Article
                            </a>
                        </div>
                    </div>
                    
                    <div class="module-card">
                        <div class="module-header">
                            <div class="module-info">
                                <h3><i class="iw iw-users"></i> Social Module</h3>
                                <p>Social networking features including friends, messaging, and comments</p>
                            </div>
                        </div>
                        <div class="module-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_module">
                                <input type="hidden" name="module_name" value="social">
                                <input type="hidden" name="current_tab" value="modules">
                                <button type="submit" class="btn <?php echo get_system_setting('enable_social', true) ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="iw iw-<?php echo get_system_setting('enable_social', true) ? 'pause' : 'play'; ?>"></i>
                                    <?php echo get_system_setting('enable_social', true) ? 'Disable' : 'Enable'; ?>
                                </button>
                            </form>
                            <a href="/admin/manage_users" class="btn btn-secondary">
                                <i class="iw iw-users-cog"></i> Manage Users
                            </a>
                            <a href="/friends" class="btn btn-primary">
                                <i class="iw iw-users"></i> View Friends
                            </a>
                            <a href="/admin/content_moderation" class="btn btn-secondary">
                                <i class="iw iw-shield-alt"></i> Moderation
                            </a>
                        </div>
                    </div>
                    
                    
                    <div class="module-card">
                        <div class="module-header">
                            <div class="module-info">
                                <h3><i class="iw iw-chart-line"></i> Analytics Module</h3>
                                <p>Analytics and tracking functionality</p>
                            </div>
                        </div>
                        <div class="module-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_module">
                                <input type="hidden" name="module_name" value="analytics">
                                <input type="hidden" name="current_tab" value="modules">
                                <button type="submit" class="btn <?php echo get_system_setting('enable_analytics', true) ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="iw iw-<?php echo get_system_setting('enable_analytics', true) ? 'pause' : 'play'; ?>"></i>
                                    <?php echo get_system_setting('enable_analytics', true) ? 'Disable' : 'Enable'; ?>
                                </button>
                            </form>
                            <a href="/admin/analytics" class="btn btn-primary">
                                <i class="iw iw-chart-line"></i> View Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Skins Tab -->
    <div id="skins-tab" class="tab-content <?php echo $active_tab === 'skins' ? 'active' : ''; ?>">
        <div class="settings-grid">
            <div class="card">
                <h2><i class="iw iw-palette"></i> Skin Management</h2>
                <p>Manage and configure site themes and visual appearance.</p>
                
                <?php
                // Get skins data
                $skins = $skins_manager->getAllSkins();
                $current_skin = $skins_manager->getCurrentSkin();
                ?>
                
                <div class="skins-container">
                    <div class="skin-settings">
                        <h3>Current Skin Settings</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_skin_settings">
                            <div class="form-group">
                                <label for="default_skin">Default Skin:</label>
                                <select name="default_skin" id="default_skin">
                                    <?php foreach ($skins as $skin): ?>
                                    <option value="<?php echo htmlspecialchars($skin['name']); ?>" 
                                            <?php echo $skin['name'] === $current_skin ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($skin['display_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="allow_skin_selection" value="1" 
                                           <?php echo get_system_setting('allow_skin_selection', true) ? 'checked' : ''; ?>>
                                    Allow users to select their own skin
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="iw iw-save"></i> Save Skin Settings
                            </button>
                        </form>
                    </div>
                    
                    <div class="available-skins">
                        <h3>Available Skins</h3>
                        <div class="skins-grid">
                            <?php foreach ($skins as $skin): ?>
                            <div class="skin-card <?php echo $skin['name'] === $current_skin ? 'active' : ''; ?>">
                                <div class="skin-preview">
                                    <?php 
                                    $preview = $skins_manager->getSkinPreview($skin['name']);
                                    if ($preview): 
                                    ?>
                                    <img src="<?php echo $preview; ?>" alt="<?php echo htmlspecialchars($skin['display_name']); ?> Preview">
                                    <?php else: ?>
                                    <div class="skin-preview-placeholder">
                                        <i class="iw iw-palette"></i>
                                        <span>No Preview</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="skin-info">
                                    <h4><?php echo htmlspecialchars($skin['display_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($skin['description']); ?></p>
                                    <div class="skin-meta">
                                        <span class="skin-version">v<?php echo htmlspecialchars($skin['version']); ?></span>
                                        <span class="skin-author">by <?php echo htmlspecialchars($skin['author']); ?></span>
                                    </div>
                                    <div class="skin-actions">
                                        <?php if ($skin['name'] !== $current_skin): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="activate_skin">
                                            <input type="hidden" name="skin_name" value="<?php echo htmlspecialchars($skin['name']); ?>">
                                            <input type="hidden" name="current_tab" value="skins">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="iw iw-check"></i> Activate
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="btn btn-success btn-sm">
                                            <i class="iw iw-check-circle"></i> Active
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include "../../includes/footer.php"; ?>
