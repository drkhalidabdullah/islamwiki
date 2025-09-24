<?php
/**
 * Test First Steps Achievement Logic
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/includes/functions.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

$achievements_extension = new AchievementsExtension();

echo "=== TESTING FIRST STEPS ACHIEVEMENT LOGIC ===\n\n";

// Get First Steps achievement
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE slug = 'first-steps'");
$stmt->execute();
$achievement = $stmt->fetch(PDO::FETCH_ASSOC);

if ($achievement) {
    echo "First Steps Achievement Details:\n";
    echo "  Name: {$achievement['name']}\n";
    echo "  Requirement Type: {$achievement['requirement_type']}\n";
    echo "  Requirement Value: {$achievement['requirement_value']}\n";
    echo "  Description: {$achievement['description']}\n\n";
    
    // Get all users
    $stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "Testing user: {$user['username']} (ID: {$user['id']})\n";
        
        // Check if user meets requirements
        $meets_requirements = $achievements_extension->checkAchievementRequirements($user['id'], $achievement);
        echo "  Meets requirements: " . ($meets_requirements ? "YES" : "NO") . "\n";
        
        // Check if user already has this achievement
        $stmt = $pdo->prepare("
            SELECT is_completed FROM user_achievements 
            WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([$user['id'], $achievement['id']]);
        $has_achievement = $stmt->fetchColumn();
        
        echo "  Already has achievement: " . ($has_achievement ? "YES" : "NO") . "\n";
        
        if ($has_achievement && !$meets_requirements) {
            echo "  ❌ PROBLEM: User has achievement but doesn't meet requirements!\n";
        } elseif (!$has_achievement && $meets_requirements) {
            echo "  ⚠️  User meets requirements but doesn't have achievement\n";
        } else {
            echo "  ✅ Status is correct\n";
        }
        
        echo "\n";
    }
} else {
    echo "❌ First Steps achievement not found!\n";
}

echo "=== TEST COMPLETED ===\n";
?>
