<?php
/**
 * Achievement System Installation Script
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../public/includes/functions.php';
require_once __DIR__ . '/../public/config/database.php';

echo "Achievement System Installation Script\n";
echo "=====================================\n\n";

try {
    // Check if database connection is available
    if (!isset($pdo)) {
        throw new Exception("Database connection not available");
    }
    
    echo "1. Checking database connection... ";
    $pdo->query("SELECT 1");
    echo "✓ Connected\n";
    
    // Read and execute migration file
    echo "2. Executing database migration... ";
    $migration_file = __DIR__ . '/../database/database_migration_v0.0.0.21_achievement_system.sql';
    
    if (!file_exists($migration_file)) {
        throw new Exception("Migration file not found: $migration_file");
    }
    
    $migration_sql = file_get_contents($migration_file);
    if ($migration_sql === false) {
        throw new Exception("Failed to read migration file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $migration_sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        $pdo->exec($statement);
    }
    
    echo "✓ Migration executed\n";
    
    // Enable the achievement system
    echo "3. Enabling achievement system... ";
    $result = set_system_setting('achievements_enabled', true);
    if (!$result) {
        throw new Exception("Failed to enable achievement system");
    }
    echo "✓ Enabled\n";
    
    // Set default settings
    echo "4. Setting default configuration... ";
    $default_settings = [
        'achievements_xp_multiplier' => 1.0,
        'achievements_points_multiplier' => 1.0,
        'achievements_notifications_enabled' => true,
        'achievements_level_system_enabled' => true,
        'achievements_max_level' => 100,
        'achievements_xp_per_level' => 100,
        'achievements_level_scaling' => 1.2
    ];
    
    foreach ($default_settings as $key => $value) {
        set_system_setting($key, $value);
    }
    echo "✓ Configuration set\n";
    
    // Create admin menu item (skip if table doesn't exist)
    echo "5. Creating admin menu item... ";
    try {
        $admin_menu_sql = "
            INSERT INTO admin_menu (name, url, icon, sort_order, is_active) 
            VALUES ('Achievements', '/pages/admin/achievements.php', 'fas fa-trophy', 50, 1)
            ON DUPLICATE KEY UPDATE is_active = 1
        ";
        $pdo->exec($admin_menu_sql);
        echo "✓ Admin menu created\n";
    } catch (Exception $e) {
        echo "⚠️  Admin menu table not found (skipping)\n";
    }
    
    // Create user menu item (skip if table doesn't exist)
    echo "6. Creating user menu item... ";
    try {
        $user_menu_sql = "
            INSERT INTO user_menu (name, url, icon, sort_order, is_active) 
            VALUES ('Achievements', '/pages/user/achievements.php', 'fas fa-trophy', 30, 1)
            ON DUPLICATE KEY UPDATE is_active = 1
        ";
        $pdo->exec($user_menu_sql);
        echo "✓ User menu created\n";
    } catch (Exception $e) {
        echo "⚠️  User menu table not found (skipping)\n";
    }
    
    // Test the system
    echo "7. Testing achievement system... ";
    require_once __DIR__ . '/../public/extensions/achievements/extension.php';
    
    $achievements_extension = new AchievementsExtension();
    $status = $achievements_extension->getStatus();
    
    if (!$status['enabled']) {
        throw new Exception("Achievement system is not enabled");
    }
    
    if (empty($status['database_tables'])) {
        throw new Exception("Database tables were not created");
    }
    
    echo "✓ System working\n";
    
    echo "\n🎉 Achievement System installed successfully!\n\n";
    
    echo "Next steps:\n";
    echo "- Go to Admin Panel > System Settings > Extensions to configure the system\n";
    echo "- Visit /pages/admin/achievements.php to manage achievements\n";
    echo "- Visit /pages/user/achievements.php to view user achievements\n";
    echo "- The system will automatically track user activities\n\n";
    
    echo "Features installed:\n";
    echo "- Level system with XP progression\n";
    echo "- Achievement categories and types\n";
    echo "- Islamic learning focused achievements\n";
    echo "- Admin management interface\n";
    echo "- User achievement page\n";
    echo "- Sidebar widget\n";
    echo "- Automatic activity tracking\n";
    echo "- Notifications system\n";
    echo "- Leaderboard\n";
    echo "- Statistics and analytics\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
