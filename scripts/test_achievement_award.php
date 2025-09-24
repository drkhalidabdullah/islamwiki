<?php
/**
 * Test Achievement Award
 * Debug the achievement awarding process
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

try {
    $achievements_extension = new AchievementsExtension();
    
    // Test with a specific achievement
    $test_slug = 'first-steps';
    $user_id = 1; // admin
    
    echo "Testing achievement award for: $test_slug\n";
    echo "User ID: $user_id\n\n";
    
    // Check if achievement exists
    $stmt = $pdo->prepare("SELECT * FROM achievements WHERE slug = ?");
    $stmt->execute([$test_slug]);
    $achievement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$achievement) {
        echo "❌ Achievement not found: $test_slug\n";
        exit;
    }
    
    echo "✅ Achievement found:\n";
    echo "  ID: {$achievement['id']}\n";
    echo "  Name: {$achievement['name']}\n";
    echo "  Slug: {$achievement['slug']}\n";
    echo "  Type: {$achievement['requirement_type']}\n";
    echo "  Value: {$achievement['requirement_value']}\n\n";
    
    // Check if user meets requirements
    echo "Checking requirements...\n";
    $meets_requirements = $achievements_extension->checkAchievementRequirements($user_id, $achievement);
    echo "Meets requirements: " . ($meets_requirements ? 'YES' : 'NO') . "\n\n";
    
    if ($meets_requirements) {
        // Check if user already has this achievement
        $stmt = $pdo->prepare("
            SELECT * FROM user_achievements 
            WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([$user_id, $achievement['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            echo "User already has this achievement:\n";
            echo "  Completed: " . ($existing['is_completed'] ? 'YES' : 'NO') . "\n";
            echo "  Progress: {$existing['progress']}%\n";
        } else {
            echo "User does not have this achievement yet.\n";
            echo "Attempting to award...\n";
            
            try {
                $achievements_extension->awardAchievement($user_id, $test_slug);
                echo "✅ Successfully awarded achievement!\n";
            } catch (Exception $e) {
                echo "❌ Error awarding achievement: " . $e->getMessage() . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
