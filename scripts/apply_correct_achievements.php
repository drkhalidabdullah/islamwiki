<?php
/**
 * Apply achievements based on proper requirements
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
    
    // Get all achievements
    $stmt = $pdo->query("SELECT * FROM achievements WHERE is_active = 1");
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "🏆 Found " . count($achievements) . " active achievements\n\n";
    
    $total_awarded = 0;
    
    foreach ($users as $user_id) {
        echo "👤 Processing user $user_id:\n";
        $user_awarded = 0;
        
        foreach ($achievements as $achievement) {
            try {
                // Check if user already has this achievement
                $stmt = $pdo->prepare("
                    SELECT * FROM user_achievements 
                    WHERE user_id = ? AND achievement_id = ? AND is_completed = 1
                ");
                $stmt->execute([$user_id, $achievement['id']]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    continue; // Already has this achievement
                }
                
                // Check if user meets requirements
                if ($achievements_extension->checkAchievementRequirements($user_id, $achievement)) {
                    // Award the achievement
                    $achievements_extension->awardAchievement($user_id, $achievement['slug']);
                    echo "  ✅ Awarded: {$achievement['name']}\n";
                    $user_awarded++;
                    $total_awarded++;
                }
                
            } catch (Exception $e) {
                // Skip achievements that can't be awarded (requirements not met)
                if (strpos($e->getMessage(), 'does not meet requirements') === false) {
                    echo "  ❌ Error with {$achievement['name']}: " . $e->getMessage() . "\n";
                }
            }
        }
        
        if ($user_awarded > 0) {
            echo "  🎉 Awarded $user_awarded achievements\n";
        } else {
            echo "  ℹ️  No new achievements awarded\n";
        }
        echo "\n";
    }
    
    echo "🎉 Successfully processed all users\n";
    echo "📈 Total achievements awarded: $total_awarded\n";
    
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
