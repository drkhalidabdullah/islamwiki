<?php
/**
 * Achievement System Integration
 * Main integration file that ties everything together
 * 
 * @version 1.0.0
 */

// Include achievement hooks
require_once __DIR__ . '/achievement_hooks.php';

/**
 * Initialize the achievement system
 * This function should be called during system initialization
 */
function init_achievement_system() {
    // Check if achievement system is enabled
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) {
        return;
    }
    
    // Initialize achievement hooks
    init_achievement_hooks();
}

/**
 * Add achievement widget to sidebar
 */
function add_achievement_widget() {
    include __DIR__ . '/achievement_widget.php';
}

/**
 * Add achievement notifications to header
 */
function add_achievement_notifications() {
    if (!is_logged_in()) return;
    
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return;
    
    $achievements_extension = new AchievementsExtension();
    $user_id = $_SESSION['user_id'];
    $notifications = $achievements_extension->getUserNotifications($user_id, 5);
    
    if (!empty($notifications)) {
        echo '<div id="achievement-notifications-container"></div>';
        echo '<script>window.achievementNotifications = ' . json_encode($notifications) . ';</script>';
    }
}

/**
 * Add achievement styles to header
 */
function add_achievement_styles() {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return;
    
    echo '<link rel="stylesheet" href="/extensions/achievements/assets/css/achievements.css">';
}

/**
 * Add achievement scripts to footer
 */
function add_achievement_scripts() {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return;
    
    echo '<script src="/extensions/achievements/assets/js/achievements.js"></script>';
}

/**
 * Award XP for specific activities
 * This function can be called from anywhere in the system
 */
function award_achievement_xp($user_id, $xp_amount, $activity_type = 'general', $activity_data = null) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->awardXP($user_id, $xp_amount, $activity_type, $activity_data);
}

/**
 * Award points for specific activities
 * This function can be called from anywhere in the system
 */
function award_achievement_points($user_id, $points_amount, $activity_type = 'general', $activity_data = null) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->awardPoints($user_id, $points_amount, $activity_type, $activity_data);
}

/**
 * Check achievements for a user
 * This function can be called from anywhere in the system
 */
function check_user_achievements($user_id) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    $achievements_extension = new AchievementsExtension();
    $achievements_extension->checkAchievements($user_id);
    return true;
}

/**
 * Get user level information
 * This function can be called from anywhere in the system
 */
function get_user_achievement_level($user_id) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return null;
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->getUserLevel($user_id);
}

/**
 * Get user achievements
 * This function is now defined in functions.php to avoid conflicts
 */

/**
 * Get achievement statistics for a user
 * This function can be called from anywhere in the system
 */
function get_user_achievement_stats($user_id) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return [];
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->getAchievementStats($user_id);
}

/**
 * Get achievement leaderboard
 * This function can be called from anywhere in the system
 */
function get_achievement_leaderboard($limit = 10, $category_id = null) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return [];
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->getLeaderboard($limit, $category_id);
}

/**
 * Track Islamic learning activity
 * This function can be called from anywhere in the system
 */
function track_islamic_learning($user_id, $activity_type, $data = []) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    achievement_islamic_learning($user_id, $activity_type, $data);
    return true;
}

/**
 * Track content creation activity
 * This function can be called from anywhere in the system
 */
function track_content_creation($user_id, $content_type, $content_id) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    switch ($content_type) {
        case 'article':
            achievement_article_created($content_id, $user_id);
            break;
        case 'wiki_page':
            achievement_wiki_page_created($content_id, $user_id);
            break;
        case 'comment':
            achievement_comment_created($content_id, $user_id);
            break;
        case 'discussion':
            achievement_discussion_created($content_id, $user_id);
            break;
    }
    return true;
}

/**
 * Track social activity
 * This function can be called from anywhere in the system
 */
function track_social_activity($user_id, $activity_type, $target_id = null, $target_type = null) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    switch ($activity_type) {
        case 'friend_added':
            achievement_friend_added($user_id, $target_id);
            break;
        case 'like_given':
            achievement_like_given($user_id, $target_id, $target_type);
            break;
        case 'like_received':
            achievement_like_received($user_id, $target_id, $target_type);
            break;
        case 'mention_received':
            achievement_mention_received($user_id, $target_id, $target_id);
            break;
    }
    return true;
}

/**
 * Track wiki activity
 * This function can be called from anywhere in the system
 */
function track_wiki_activity($user_id, $activity_type, $page_id, $additional_data = []) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    switch ($activity_type) {
        case 'page_created':
            achievement_wiki_page_created($page_id, $user_id);
            break;
        case 'page_edited':
            achievement_wiki_page_edited($page_id, $user_id);
            break;
        case 'page_visited':
            achievement_wiki_page_visited($page_id, $user_id);
            break;
        case 'moderated':
            achievement_wiki_moderated($user_id, $page_id, $additional_data['action'] ?? '');
            break;
        case 'expertise':
            achievement_wiki_expertise($user_id, $additional_data['category_id'] ?? 0);
            break;
    }
    return true;
}

/**
 * Track daily activity
 * This function should be called once per day per user
 */
function track_daily_activity($user_id) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    achievement_daily_activity($user_id);
    return true;
}

/**
 * Get achievement system status
 * This function can be called from anywhere in the system
 */
function get_achievement_system_status() {
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->getStatus();
}

/**
 * Check if achievement system is enabled
 * This function can be called from anywhere in the system
 */
function is_achievement_system_enabled() {
    return get_system_setting('achievements_enabled', false);
}

/**
 * Enable achievement system
 * This function can be called from admin panel
 */
function enable_achievement_system() {
    return set_system_setting('achievements_enabled', true);
}

/**
 * Disable achievement system
 * This function can be called from admin panel
 */
function disable_achievement_system() {
    return set_system_setting('achievements_enabled', false);
}

/**
 * Reset user achievements
 * This function can be called from admin panel
 */
function reset_user_achievements($user_id) {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return false;
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->resetUserAchievements($user_id);
}

/**
 * Get achievement categories
 * This function can be called from anywhere in the system
 */
function get_achievement_categories() {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return [];
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->getCategories();
}

/**
 * Get achievement types
 * This function can be called from anywhere in the system
 */
function get_achievement_types() {
    $achievements_enabled = get_system_setting('achievements_enabled', false);
    if (!$achievements_enabled) return [];
    
    $achievements_extension = new AchievementsExtension();
    return $achievements_extension->getTypes();
}

// Achievement system will be initialized when needed
