<?php
/**
 * Update achievement counts for all users
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/includes/functions.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

try {
    // $pdo is already created in database.php
    echo "✅ Database connected successfully\n";
    
    $achievements_extension = new AchievementsExtension();
    
    // Get all users
    $stmt = $pdo->query("SELECT id FROM users WHERE is_active = 1");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 Found " . count($users) . " active users\n";
    
    $updated_count = 0;
    
    foreach ($users as $user_id) {
        try {
            // Get user level data (this will recalculate and update the database)
            $user_level = $achievements_extension->getUserLevel($user_id);
            
            echo "✅ Updated user $user_id: Level {$user_level['level']}, {$user_level['total_achievements']} achievements, {$user_level['total_points']} points\n";
            $updated_count++;
            
        } catch (Exception $e) {
            echo "❌ Error updating user $user_id: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Successfully updated $updated_count users\n";
    
    // Show final leaderboard
    echo "\n📈 Final Leaderboard:\n";
    $leaderboard = $achievements_extension->getLeaderboard(5);
    foreach ($leaderboard as $index => $user) {
        $rank = $index + 1;
        echo "$rank. {$user['username']} - Level {$user['level']}, {$user['total_achievements']} achievements, {$user['total_xp']} XP\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
