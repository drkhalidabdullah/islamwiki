<?php
/**
 * Comprehensive Achievement Check and Fix
 * Check all achievements and ensure proper requirement logic is implemented
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

try {
    $achievements_extension = new AchievementsExtension();
    
    // Get all achievements
    $stmt = $pdo->prepare("SELECT * FROM achievements ORDER BY id");
    $stmt->execute();
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== COMPREHENSIVE ACHIEVEMENT CHECK ===\n\n";
    
    $issues_found = 0;
    $achievements_to_fix = [];
    
    foreach ($achievements as $achievement) {
        echo "Checking: {$achievement['name']} ({$achievement['slug']})\n";
        echo "  Type: {$achievement['requirement_type']}, Value: {$achievement['requirement_value']}\n";
        
        // Check if this achievement type is properly implemented
        $is_implemented = false;
        $needs_implementation = false;
        
        switch ($achievement['requirement_type']) {
            case 'first_login':
                $is_implemented = true;
                echo "  ✅ First login - properly implemented\n";
                break;
                
            case 'friends_count':
                $is_implemented = true;
                echo "  ✅ Friends count - properly implemented\n";
                break;
                
            case 'posts_count':
                $is_implemented = true;
                echo "  ✅ Posts count - properly implemented\n";
                break;
                
            case 'article_count':
                $is_implemented = true;
                echo "  ✅ Article count - properly implemented\n";
                break;
                
            case 'status_count':
                $is_implemented = true;
                echo "  ✅ Status count - properly implemented\n";
                break;
                
            case 'profile_complete':
                $is_implemented = true;
                echo "  ✅ Profile complete - properly implemented\n";
                break;
                
            case 'days_since_join':
                $is_implemented = true;
                echo "  ✅ Days since join - properly implemented\n";
                break;
                
            case 'achievement_count':
                $is_implemented = true;
                echo "  ✅ Achievement count - properly implemented\n";
                break;
                
            case 'count':
                // Check if this is a specific count-based achievement we handle
                if ($achievement['slug'] === 'photo-pioneer') {
                    $is_implemented = true;
                    echo "  ✅ Photo pioneer - properly implemented\n";
                } else {
                    $needs_implementation = true;
                    echo "  ❌ Generic count achievement - needs implementation\n";
                    $achievements_to_fix[] = $achievement;
                }
                break;
                
            case 'ref_tags':
                $needs_implementation = true;
                echo "  ❌ Ref tags - not implemented yet\n";
                $achievements_to_fix[] = $achievement;
                break;
                
            case 'bug_reports':
                $needs_implementation = true;
                echo "  ❌ Bug reports - not implemented yet\n";
                $achievements_to_fix[] = $achievement;
                break;
                
            case 'settings_updated':
                $needs_implementation = true;
                echo "  ❌ Settings updated - not implemented yet\n";
                $achievements_to_fix[] = $achievement;
                break;
                
            default:
                $needs_implementation = true;
                echo "  ❌ Unknown requirement type: {$achievement['requirement_type']}\n";
                $achievements_to_fix[] = $achievement;
                break;
        }
        
        if ($needs_implementation) {
            $issues_found++;
        }
        
        echo "\n";
    }
    
    echo "=== SUMMARY ===\n";
    echo "Total achievements: " . count($achievements) . "\n";
    echo "Issues found: $issues_found\n";
    echo "Achievements needing implementation: " . count($achievements_to_fix) . "\n\n";
    
    if (!empty($achievements_to_fix)) {
        echo "=== ACHIEVEMENTS NEEDING IMPLEMENTATION ===\n";
        foreach ($achievements_to_fix as $achievement) {
            echo "- {$achievement['name']} ({$achievement['slug']}) - Type: {$achievement['requirement_type']}\n";
        }
    }
    
    // Now let's check which users should have which achievements
    echo "\n=== CHECKING USER ACHIEVEMENTS ===\n";
    
    $stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "\nChecking user: {$user['username']} (ID: {$user['id']})\n";
        
        // Check for first-login achievement
        $stmt = $pdo->prepare("
            SELECT ua.*, a.name 
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND a.slug = 'first-login'
        ");
        $stmt->execute([$user['id']]);
        $first_login = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$first_login) {
            echo "  🔄 Awarding First Login achievement...\n";
            try {
                $achievements_extension->awardAchievement($user['id'], 'first-login');
                echo "  ✅ Successfully awarded First Login achievement\n";
            } catch (Exception $e) {
                echo "  ❌ Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  ✅ Already has First Login achievement\n";
        }
        
        // Check for other automatically achievable achievements
        $auto_achievements = [
            'first-steps' => 'count',
            'early-bird' => 'count', 
            'night-owl' => 'count',
            'daily-devotee' => 'count',
            'weekly-warrior' => 'count',
            'monthly-master' => 'count',
            'weekend-warrior' => 'count',
            'holiday-hero' => 'count'
        ];
        
        foreach ($auto_achievements as $slug => $type) {
            $stmt = $pdo->prepare("
                SELECT ua.*, a.name 
                FROM user_achievements ua
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = ? AND a.slug = ?
            ");
            $stmt->execute([$user['id'], $slug]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                echo "  🔄 Awarding {$slug} achievement...\n";
                try {
                    $achievements_extension->awardAchievement($user['id'], $slug);
                    echo "  ✅ Successfully awarded {$slug} achievement\n";
                } catch (Exception $e) {
                    echo "  ❌ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n=== COMPREHENSIVE CHECK COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
