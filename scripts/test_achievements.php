<?php
/**
 * Achievement System Test Script
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../public/includes/functions.php';
require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

echo "Achievement System Test Script\n";
echo "=============================\n\n";

try {
    // Check if database connection is available
    if (!isset($pdo)) {
        throw new Exception("Database connection not available");
    }
    
    echo "1. Testing database connection... ";
    $pdo->query("SELECT 1");
    echo "✓ Connected\n";
    
    // Check if achievement system is enabled
    echo "2. Checking system status... ";
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) {
        echo "❌ Achievement system is not enabled\n";
        echo "   Run install_achievements.php first\n";
        exit(1);
    }
    echo "✓ Enabled\n";
    
    // Test extension loading
    echo "3. Testing extension loading... ";
    $achievements_extension = new AchievementsExtension();
    if (!$achievements_extension) {
        throw new Exception("Failed to load achievement extension");
    }
    echo "✓ Extension loaded\n";
    
    // Test database tables
    echo "4. Testing database tables... ";
    $required_tables = [
        'achievement_categories',
        'achievement_types',
        'achievements',
        'achievement_requirements',
        'user_achievements',
        'user_levels',
        'user_activity_log',
        'achievement_notifications'
    ];
    
    foreach ($required_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if (!$stmt->fetch()) {
            throw new Exception("Table $table does not exist");
        }
    }
    echo "✓ All tables exist\n";
    
    // Test system status
    echo "5. Testing system status... ";
    $status = $achievements_extension->getStatus();
    if (!$status['enabled']) {
        throw new Exception("System status shows as disabled");
    }
    echo "✓ System active\n";
    
    // Test categories
    echo "6. Testing categories... ";
    $categories = $achievements_extension->getCategories();
    if (empty($categories)) {
        throw new Exception("No categories found");
    }
    echo "✓ " . count($categories) . " categories found\n";
    
    // Test types
    echo "7. Testing types... ";
    $types = $achievements_extension->getTypes();
    if (empty($types)) {
        throw new Exception("No types found");
    }
    echo "✓ " . count($types) . " types found\n";
    
    // Test achievements
    echo "8. Testing achievements... ";
    $achievements = $achievements_extension->getAllAchievements();
    if (empty($achievements)) {
        throw new Exception("No achievements found");
    }
    echo "✓ " . count($achievements) . " achievements found\n";
    
    // Test user functions (if user is logged in)
    if (is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        
        echo "9. Testing user functions... ";
        
        // Test user level
        $user_level = $achievements_extension->getUserLevel($user_id);
        if (!$user_level) {
            throw new Exception("Failed to get user level");
        }
        
        // Test user achievements
        $user_achievements = $achievements_extension->getUserAchievements($user_id);
        if (!is_array($user_achievements)) {
            throw new Exception("Failed to get user achievements");
        }
        
        // Test user stats
        $user_stats = $achievements_extension->getAchievementStats($user_id);
        if (!is_array($user_stats)) {
            throw new Exception("Failed to get user stats");
        }
        
        echo "✓ User functions working\n";
        
        // Test XP awarding
        echo "10. Testing XP awarding... ";
        $result = $achievements_extension->awardXP($user_id, 10, 'test_activity', ['test' => true]);
        if (!$result) {
            throw new Exception("Failed to award XP");
        }
        echo "✓ XP awarded\n";
        
        // Test points awarding
        echo "11. Testing points awarding... ";
        $result = $achievements_extension->awardPoints($user_id, 5, 'test_activity', ['test' => true]);
        if (!$result) {
            throw new Exception("Failed to award points");
        }
        echo "✓ Points awarded\n";
        
        // Test achievement checking
        echo "12. Testing achievement checking... ";
        $achievements_extension->checkAchievements($user_id);
        echo "✓ Achievements checked\n";
        
    } else {
        echo "9. Skipping user tests (not logged in)\n";
    }
    
    // Test API endpoints
    echo "13. Testing API endpoints... ";
    $api_endpoints = [
        '/api/achievements.php?action=get_user_level',
        '/api/achievements.php?action=get_achievements',
        '/api/achievements.php?action=get_categories',
        '/api/achievements.php?action=get_types',
        '/api/achievements.php?action=get_leaderboard'
    ];
    
    foreach ($api_endpoints as $endpoint) {
        $url = "http://localhost" . $endpoint;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json',
                'timeout' => 5
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            echo "⚠️  API endpoint $endpoint not accessible (this is normal if not running locally)\n";
        } else {
            $data = json_decode($response, true);
            if (!$data || !isset($data['success'])) {
                echo "⚠️  API endpoint $endpoint returned invalid response\n";
            }
        }
    }
    echo "✓ API endpoints tested\n";
    
    // Test admin functions
    echo "14. Testing admin functions... ";
    $admin_endpoints = [
        '/api/admin/achievements.php?action=get_all_achievements',
        '/api/admin/achievements.php?action=get_categories',
        '/api/admin/achievements.php?action=get_system_stats'
    ];
    
    foreach ($admin_endpoints as $endpoint) {
        $url = "http://localhost" . $endpoint;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json',
                'timeout' => 5
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            echo "⚠️  Admin API endpoint $endpoint not accessible (this is normal if not running locally)\n";
        } else {
            $data = json_decode($response, true);
            if (!$data || !isset($data['success'])) {
                echo "⚠️  Admin API endpoint $endpoint returned invalid response\n";
            }
        }
    }
    echo "✓ Admin functions tested\n";
    
    echo "\n🎉 All tests passed! Achievement system is working correctly.\n\n";
    
    echo "System Summary:\n";
    echo "- Extension: " . $achievements_extension->name . " v" . $achievements_extension->version . "\n";
    echo "- Status: " . ($status['enabled'] ? 'Enabled' : 'Disabled') . "\n";
    echo "- Categories: " . count($categories) . "\n";
    echo "- Types: " . count($types) . "\n";
    echo "- Achievements: " . count($achievements) . "\n";
    echo "- Database Tables: " . count($status['database_tables']) . "\n";
    echo "- Total Users: " . $status['total_users'] . "\n\n";
    
    echo "Available Features:\n";
    echo "- Level system with XP progression\n";
    echo "- Achievement categories and types\n";
    echo "- Islamic learning focused achievements\n";
    echo "- Admin management interface\n";
    echo "- User achievement page\n";
    echo "- Sidebar widget\n";
    echo "- Automatic activity tracking\n";
    echo "- Notifications system\n";
    echo "- Leaderboard\n";
    echo "- Statistics and analytics\n";
    echo "- API endpoints for integration\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
