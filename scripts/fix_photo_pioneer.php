<?php
/**
 * Fix Photo Pioneer Achievement
 * Award the Photo Pioneer achievement to users who have uploaded profile photos
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

try {
    $achievements_extension = new AchievementsExtension();
    
    // Get all users who have avatars
    $stmt = $pdo->prepare("
        SELECT id, username, avatar 
        FROM users 
        WHERE avatar IS NOT NULL AND avatar != ''
    ");
    $stmt->execute();
    $users_with_avatars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($users_with_avatars) . " users with avatars:\n";
    
    foreach ($users_with_avatars as $user) {
        echo "Checking user: {$user['username']} (ID: {$user['id']})\n";
        
        // Check if user already has the Photo Pioneer achievement
        $stmt = $pdo->prepare("
            SELECT ua.*, a.name 
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND a.slug = 'photo-pioneer'
        ");
        $stmt->execute([$user['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            if ($existing['is_completed']) {
                echo "  ✅ Already has Photo Pioneer achievement\n";
            } else {
                echo "  🔄 Awarding Photo Pioneer achievement...\n";
                try {
                    $achievements_extension->awardAchievement($user['id'], 'photo-pioneer');
                    echo "  ✅ Successfully awarded Photo Pioneer achievement\n";
                } catch (Exception $e) {
                    echo "  ❌ Error awarding achievement: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "  🔄 Awarding Photo Pioneer achievement...\n";
            try {
                $achievements_extension->awardAchievement($user['id'], 'photo-pioneer');
                echo "  ✅ Successfully awarded Photo Pioneer achievement\n";
            } catch (Exception $e) {
                echo "  ❌ Error awarding achievement: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nPhoto Pioneer achievement fix completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
