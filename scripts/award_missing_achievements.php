<?php
/**
 * Award Missing Achievements
 * Award the remaining missing achievements to users
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

try {
    $achievements_extension = new AchievementsExtension();
    
    // Get all users
    $stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== AWARDING MISSING ACHIEVEMENTS ===\n\n";
    
    // Award Quran Reader to all users
    foreach ($users as $user) {
        echo "Processing user: {$user['username']} (ID: {$user['id']})\n";
        
        // Check if user already has Quran Reader
        $stmt = $pdo->prepare("
            SELECT ua.*, a.name 
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND a.slug = 'quran-reader'
        ");
        $stmt->execute([$user['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            echo "  🔄 Awarding Quran Reader achievement...\n";
            try {
                $achievements_extension->awardAchievement($user['id'], 'quran-reader');
                echo "  ✅ Successfully awarded Quran Reader achievement\n";
            } catch (Exception $e) {
                echo "  ❌ Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  ✅ Already has Quran Reader achievement\n";
        }
        
        echo "\n";
    }
    
    echo "=== CHECKING OTHER MISSING ACHIEVEMENTS ===\n\n";
    
    // Check which users have articles for "First Article" achievement
    foreach ($users as $user) {
        echo "Checking user: {$user['username']} (ID: {$user['id']})\n";
        
        // Check article count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_posts 
            WHERE user_id = ? AND post_type = 'article_share'
        ");
        $stmt->execute([$user['id']]);
        $article_count = $stmt->fetchColumn();
        
        echo "  Articles: $article_count\n";
        
        if ($article_count >= 1) {
            // Check if user has First Article achievement
            $stmt = $pdo->prepare("
                SELECT ua.*, a.name 
                FROM user_achievements ua
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = ? AND a.slug = 'first-article'
            ");
            $stmt->execute([$user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                echo "  🔄 Awarding First Article achievement...\n";
                try {
                    $achievements_extension->awardAchievement($user['id'], 'first-article');
                    echo "  ✅ Successfully awarded First Article achievement\n";
                } catch (Exception $e) {
                    echo "  ❌ Error: " . $e->getMessage() . "\n";
                }
            } else {
                echo "  ✅ Already has First Article achievement\n";
            }
        } else {
            echo "  ⏳ No articles yet - First Article achievement not available\n";
        }
        
        // Check posts count for Ambassador achievement
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_posts 
            WHERE user_id = ? AND is_public = 1
        ");
        $stmt->execute([$user['id']]);
        $posts_count = $stmt->fetchColumn();
        
        echo "  Posts: $posts_count\n";
        
        if ($posts_count >= 20) {
            // Check if user has Ambassador achievement
            $stmt = $pdo->prepare("
                SELECT ua.*, a.name 
                FROM user_achievements ua
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = ? AND a.slug = 'ambassador'
            ");
            $stmt->execute([$user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                echo "  🔄 Awarding Ambassador achievement...\n";
                try {
                    $achievements_extension->awardAchievement($user['id'], 'ambassador');
                    echo "  ✅ Successfully awarded Ambassador achievement\n";
                } catch (Exception $e) {
                    echo "  ❌ Error: " . $e->getMessage() . "\n";
                }
            } else {
                echo "  ✅ Already has Ambassador achievement\n";
            }
        } else {
            echo "  ⏳ Not enough posts yet - Ambassador achievement not available\n";
        }
        
        echo "\n";
    }
    
    echo "=== MISSING ACHIEVEMENTS SUMMARY ===\n";
    echo "✅ Quran Reader - Auto-awarded to all users\n";
    echo "⏳ First Article - Requires 1+ articles (article_count)\n";
    echo "⏳ Ambassador - Requires 20+ posts (posts_count)\n";
    echo "❌ Citation Scholar - Requires ref_tags (not implemented)\n";
    echo "❌ Bug Hunter - Requires bug_reports (not implemented)\n";
    echo "⏳ Anniversary Ace - Requires 365+ days since join\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
