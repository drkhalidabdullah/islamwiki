<?php
/**
 * Remove Incorrectly Awarded Achievements
 * Remove achievements that were auto-awarded but shouldn't have been
 */

require_once __DIR__ . '/../public/config/database.php';

try {
    // List of achievements that should NOT be auto-awarded
    $incorrect_achievements = [
        'quran-reader', 'early-bird', 'night-owl', 'daily-devotee', 
        'weekly-warrior', 'monthly-master', 'weekend-warrior', 'holiday-hero',
        'quran-journey-1', 'quran-journey-2', 'quran-journey-3',
        'hadith-collection-1', 'hadith-collection-2', 'hadith-collection-3',
        'fiqh-fundamentals', 'aqeedah-master', 'seerah-scholar', 'tafseer-explorer',
        'message-master', 'group-creator', 'event-organizer', 'share-champion',
        'tag-team', 'notification-ninja', 'social-butterfly-pro', 'grammar-guru',
        'fact-checker', 'editors-choice', 'community-favorite', 'educational-contributor',
        'research-master', 'translation-hero', 'visual-storyteller', 'content-curator',
        'search-savant', 'settings-specialist', 'keyboard-master', 'mobile-maven',
        'feature-explorer', 'help-helper', 'feedback-champion', 'tutorial-teacher',
        'platform-pioneer', 'milestone-master', 'level-legend', 'point-powerhouse',
        'streak-supreme', 'community-champion', 'ramadan-ready', 'eid-celebrator',
        'hajj-helper', 'umrah-supporter', 'laylat-al-qadr-seeker', 'ashura-scholar',
        'mawlid-celebrator', 'winter-warrior', 'summer-scholar', 'friend-finder-1',
        'friend-finder-2', 'friend-finder-3', 'content-creator-2', 'content-creator-3',
        'wiki-warrior-1', 'wiki-warrior-2', 'wiki-warrior-3', 'learning-leader-1',
        'learning-leader-2', 'learning-leader-3', 'quran-expert', 'hadith-expert',
        'fiqh-expert', 'aqeedah-expert', 'seerah-expert', 'tafseer-expert',
        'arabic-scholar', 'islamic-history-expert', 'comparative-religion-expert',
        'islamic-art-expert', 'mentor', 'moderator', 'event-coordinator',
        'discussion-leader', 'knowledge-keeper', 'community-builder', 'visionary', 'legend'
    ];
    
    echo "=== REMOVING INCORRECTLY AWARDED ACHIEVEMENTS ===\n\n";
    
    $total_removed = 0;
    
    foreach ($incorrect_achievements as $achievement_slug) {
        echo "Removing achievement: $achievement_slug\n";
        
        // Get achievement ID
        $stmt = $pdo->prepare("SELECT id FROM achievements WHERE slug = ?");
        $stmt->execute([$achievement_slug]);
        $achievement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$achievement) {
            echo "  ❌ Achievement not found\n";
            continue;
        }
        
        $achievement_id = $achievement['id'];
        
        // Remove from all users
        $stmt = $pdo->prepare("DELETE FROM user_achievements WHERE achievement_id = ?");
        $stmt->execute([$achievement_id]);
        $removed_count = $stmt->rowCount();
        
        echo "  ✅ Removed from $removed_count users\n";
        $total_removed += $removed_count;
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Total achievement removals: $total_removed\n";
    echo "Incorrect achievements cleaned up!\n";
    
    // Update user levels to recalculate totals
    echo "\n=== UPDATING USER LEVELS ===\n";
    
    $stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "Updating user: {$user['username']} (ID: {$user['id']})\n";
        
        // Recalculate total achievements
        $stmt = $pdo->prepare("
            UPDATE user_levels 
            SET total_achievements = (
                SELECT COUNT(*) FROM user_achievements 
                WHERE user_id = ? AND is_completed = 1
            )
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id'], $user['id']]);
        
        // Get new count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_achievements 
            WHERE user_id = ? AND is_completed = 1
        ");
        $stmt->execute([$user['id']]);
        $new_count = $stmt->fetchColumn();
        
        echo "  ✅ New achievement count: $new_count\n";
    }
    
    echo "\n=== CLEANUP COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
