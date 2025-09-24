<?php
/**
 * Award Auto-Achievements
 * Award all auto-awardable achievements to existing users
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

try {
    $achievements_extension = new AchievementsExtension();
    
    // Auto-awardable achievements
    $auto_award_achievements = [
        'first-steps', 'early-bird', 'night-owl', 'daily-devotee', 
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
    
    // Get all users
    $stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== AWARDING AUTO-ACHIEVEMENTS ===\n\n";
    echo "Auto-awardable achievements: " . count($auto_award_achievements) . "\n";
    echo "Users: " . count($users) . "\n\n";
    
    $total_awarded = 0;
    $total_errors = 0;
    
    foreach ($users as $user) {
        echo "Processing user: {$user['username']} (ID: {$user['id']})\n";
        
        foreach ($auto_award_achievements as $achievement_slug) {
            // Check if user already has this achievement
            $stmt = $pdo->prepare("
                SELECT ua.*, a.name 
                FROM user_achievements ua
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = ? AND a.slug = ?
            ");
            $stmt->execute([$user['id'], $achievement_slug]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                try {
                    $achievements_extension->awardAchievement($user['id'], $achievement_slug);
                    $total_awarded++;
                    echo "  ✅ Awarded: $achievement_slug\n";
                } catch (Exception $e) {
                    $total_errors++;
                    echo "  ❌ Error awarding $achievement_slug: " . $e->getMessage() . "\n";
                }
            }
        }
        echo "\n";
    }
    
    echo "=== SUMMARY ===\n";
    echo "Total achievements awarded: $total_awarded\n";
    echo "Total errors: $total_errors\n";
    echo "Auto-achievement awarding completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
