<?php
/**
 * Test Course Completion and First Steps Achievement
 */

require_once __DIR__ . '/../public/config/database.php';
require_once __DIR__ . '/../public/includes/functions.php';
require_once __DIR__ . '/../public/models/Course.php';
require_once __DIR__ . '/../public/extensions/achievements/extension.php';

$course_model = new Course($pdo);
$achievements_extension = new AchievementsExtension();

echo "=== TESTING COURSE COMPLETION AND FIRST STEPS ACHIEVEMENT ===\n\n";

// Get a test user (admin)
$user_id = 1; // admin user
echo "Testing with user ID: $user_id (admin)\n\n";

// Get the first course
$courses = $course_model->getAllCourses();
if (empty($courses)) {
    echo "❌ No courses found!\n";
    exit;
}

$course = $courses[0];
echo "Testing with course: {$course['title']} (ID: {$course['id']})\n\n";

// Check current achievements
echo "=== BEFORE COURSE COMPLETION ===\n";
$stmt = $pdo->prepare("
    SELECT a.name, a.slug FROM user_achievements ua 
    JOIN achievements a ON ua.achievement_id = a.id 
    WHERE ua.user_id = ? AND ua.is_completed = 1
    ORDER BY a.name
");
$stmt->execute([$user_id]);
$achievements_before = $stmt->fetchAll();

echo "Current achievements (" . count($achievements_before) . "):\n";
foreach ($achievements_before as $achievement) {
    echo "  - {$achievement['name']} ({$achievement['slug']})\n";
}

// Check if First Steps achievement exists
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE slug = 'first-steps'");
$stmt->execute();
$first_steps_achievement = $stmt->fetch();

if ($first_steps_achievement) {
    echo "\nFirst Steps achievement details:\n";
    echo "  - Name: {$first_steps_achievement['name']}\n";
    echo "  - Requirement Type: {$first_steps_achievement['requirement_type']}\n";
    echo "  - Requirement Value: {$first_steps_achievement['requirement_value']}\n";
    
    // Check if user meets requirements
    $meets_requirements = $achievements_extension->checkAchievementRequirements($user_id, $first_steps_achievement);
    echo "  - Meets requirements: " . ($meets_requirements ? "YES" : "NO") . "\n";
} else {
    echo "\n❌ First Steps achievement not found!\n";
}

echo "\n=== COMPLETING COURSE ===\n";

// Mark course as completed
$result = $course_model->markCourseCompleted($user_id, $course['id'], 100, 120);
if ($result) {
    echo "✅ Course marked as completed\n";
} else {
    echo "ℹ️  Course was already completed\n";
}

// Check for new achievements
echo "Checking for new achievements...\n";
try {
    $achievements_extension->checkAchievements($user_id);
    echo "✅ Achievement check completed\n";
} catch (Exception $e) {
    echo "❌ Achievement check failed: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING ACHIEVEMENTS AFTER COURSE COMPLETION ===\n";

// Check achievements again
$stmt = $pdo->prepare("
    SELECT a.name, a.slug FROM user_achievements ua 
    JOIN achievements a ON ua.achievement_id = a.id 
    WHERE ua.user_id = ? AND ua.is_completed = 1
    ORDER BY a.name
");
$stmt->execute([$user_id]);
$achievements_after = $stmt->fetchAll();

echo "Achievements after course completion (" . count($achievements_after) . "):\n";
foreach ($achievements_after as $achievement) {
    echo "  - {$achievement['name']} ({$achievement['slug']})\n";
}

// Check if First Steps was awarded
$first_steps_awarded = false;
foreach ($achievements_after as $achievement) {
    if ($achievement['slug'] === 'first-steps') {
        $first_steps_awarded = true;
        break;
    }
}

if ($first_steps_awarded) {
    echo "\n✅ First Steps achievement was awarded!\n";
} else {
    echo "\n❌ First Steps achievement was NOT awarded\n";
    
    // Check requirements again
    if ($first_steps_achievement) {
        $meets_requirements = $achievements_extension->checkAchievementRequirements($user_id, $first_steps_achievement);
        echo "  - Meets requirements now: " . ($meets_requirements ? "YES" : "NO") . "\n";
    }
}

echo "\n=== TEST COMPLETED ===\n";
?>
