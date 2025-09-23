<?php
/**
 * Achievement System Sidebar Widget
 * Displays user level and recent achievements
 * 
 * @version 1.0.0
 */

// Check if user is logged in and achievement system is enabled
if (!is_logged_in()) return;

$achievements_enabled = get_system_setting('achievements_enabled', false);
if (!$achievements_enabled) return;

// Include achievement extension
require_once __DIR__ . '/../extensions/achievements/extension.php';

$achievements_extension = new AchievementsExtension();
$user_id = $_SESSION['user_id'];

// Get user level and recent achievements
$user_level = $achievements_extension->getUserLevel($user_id);
$recent_achievements = $achievements_extension->getUserAchievements($user_id, true);
$recent_achievements = array_slice($recent_achievements, 0, 3); // Show only 3 recent

?>

<div class="achievement-widget-sidebar">
    <div class="widget-header">
        <h3><i class="fas fa-trophy"></i> Achievements</h3>
    </div>
    
    <!-- Level Info -->
    <div class="level-info-sidebar">
        <div class="level-badge-sidebar">
            <div class="level-number"><?php echo $user_level['level']; ?></div>
            <div class="level-text">Level</div>
        </div>
        <div class="level-details-sidebar">
            <div class="level-title">Level <?php echo $user_level['level']; ?></div>
            <div class="level-progress-sidebar">
                <div class="level-progress-bar" style="width: <?php echo $user_level['xp_to_next_level'] > 0 ? ($user_level['current_level_xp'] / ($user_level['current_level_xp'] + $user_level['xp_to_next_level'])) * 100 : 100; ?>%"></div>
            </div>
            <div class="level-stats-sidebar">
                <span><?php echo $user_level['current_level_xp']; ?> XP</span>
                <span><?php echo $user_level['total_achievements']; ?> Achievements</span>
            </div>
        </div>
    </div>
    
    <!-- Recent Achievements -->
    <?php if (!empty($recent_achievements)): ?>
        <div class="recent-achievements">
            <h4>Recent Achievements</h4>
            <?php foreach ($recent_achievements as $achievement): ?>
                <div class="achievement-item-sidebar">
                    <div class="achievement-icon-sidebar">
                        <i class="<?php echo $achievement['icon']; ?>"></i>
                    </div>
                    <div class="achievement-info-sidebar">
                        <div class="achievement-name-sidebar"><?php echo htmlspecialchars($achievement['name']); ?></div>
                        <div class="achievement-date-sidebar"><?php echo date('M j', strtotime($achievement['completed_at'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Widget Footer -->
    <div class="widget-footer">
        <a href="/achievements" class="btn btn-primary btn-sm">
            <i class="fas fa-trophy"></i> View All
        </a>
    </div>
</div>

<!-- Include Achievement System CSS -->
<link rel="stylesheet" href="/extensions/achievements/assets/css/achievements.css">
