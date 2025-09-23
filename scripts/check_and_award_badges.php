<?php
/**
 * Check and award badges for all users
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
    
    $total_badges_awarded = 0;
    
    foreach ($users as $user_id) {
        echo "👤 Checking badges for user $user_id:\n";
        
        try {
            $awarded_badges = $achievements_extension->checkAndAwardBadges($user_id);
            
            if (!empty($awarded_badges)) {
                echo "  🏆 Awarded " . count($awarded_badges) . " badges:\n";
                foreach ($awarded_badges as $badge) {
                    echo "    - {$badge['name']} ({$badge['rarity']}) - {$badge['xp_reward']} XP, {$badge['points']} points\n";
                }
                $total_badges_awarded += count($awarded_badges);
            } else {
                echo "  ℹ️  No new badges awarded\n";
            }
            
        } catch (Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "🎉 Badge check completed\n";
    echo "📈 Total badges awarded: $total_badges_awarded\n";
    
    // Show badge summary
    echo "\n📊 Badge Summary:\n";
    $stmt = $pdo->query("
        SELECT 
            b.name, 
            b.rarity, 
            COUNT(ub.id) as earned_count,
            b.xp_reward,
            b.points
        FROM badges b
        LEFT JOIN user_badges ub ON b.id = ub.badge_id
        WHERE b.is_active = 1
        GROUP BY b.id, b.name, b.rarity, b.xp_reward, b.points
        ORDER BY b.sort_order
    ");
    $badge_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($badge_summary as $badge) {
        echo "- {$badge['name']} ({$badge['rarity']}): {$badge['earned_count']} users - {$badge['xp_reward']} XP, {$badge['points']} points\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
