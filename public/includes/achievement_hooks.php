<?php
/**
 * Achievement System Integration Hooks
 * Automatically tracks user activities for achievements
 * 
 * @version 1.0.0
 */

// Include the achievement extension
require_once __DIR__ . '/../extensions/achievements/extension.php';

/**
 * Hook into article creation
 */
function achievement_article_created($article_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 50, 'article_create', ['article_id' => $article_id]);
        $achievements_extension->awardPoints($user_id, 10, 'article_create', ['article_id' => $article_id]);
    }
}

/**
 * Hook into article editing
 */
function achievement_article_edited($article_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 25, 'article_edit', ['article_id' => $article_id]);
        $achievements_extension->awardPoints($user_id, 5, 'article_edit', ['article_id' => $article_id]);
    }
}

/**
 * Hook into wiki page creation
 */
function achievement_wiki_page_created($page_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 30, 'wiki_create', ['page_id' => $page_id]);
        $achievements_extension->awardPoints($user_id, 8, 'wiki_create', ['page_id' => $page_id]);
    }
}

/**
 * Hook into wiki page editing
 */
function achievement_wiki_page_edited($page_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 15, 'wiki_edit', ['page_id' => $page_id]);
        $achievements_extension->awardPoints($user_id, 3, 'wiki_edit', ['page_id' => $page_id]);
    }
}

/**
 * Hook into wiki page visits
 */
function achievement_wiki_page_visited($page_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 2, 'wiki_visit', ['page_id' => $page_id]);
    }
}

/**
 * Hook into user registration
 */
function achievement_user_registered($user_id) {
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 100, 'user_registered', ['user_id' => $user_id]);
        $achievements_extension->awardPoints($user_id, 20, 'user_registered', ['user_id' => $user_id]);
    }
}

/**
 * Hook into user login
 */
function achievement_user_logged_in($user_id) {
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 5, 'user_login', ['user_id' => $user_id]);
    }
}

/**
 * Hook into friend addition
 */
function achievement_friend_added($user_id, $friend_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 20, 'friend_add', ['friend_id' => $friend_id]);
        $achievements_extension->awardPoints($user_id, 5, 'friend_add', ['friend_id' => $friend_id]);
    }
}

/**
 * Hook into comment creation
 */
function achievement_comment_created($comment_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 10, 'comment_write', ['comment_id' => $comment_id]);
        $achievements_extension->awardPoints($user_id, 2, 'comment_write', ['comment_id' => $comment_id]);
    }
}

/**
 * Hook into like given
 */
function achievement_like_given($user_id, $target_id, $target_type) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 1, 'like_given', ['target_id' => $target_id, 'target_type' => $target_type]);
    }
}

/**
 * Hook into like received
 */
function achievement_like_received($user_id, $target_id, $target_type) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 2, 'like_received', ['target_id' => $target_id, 'target_type' => $target_type]);
        $achievements_extension->awardPoints($user_id, 1, 'like_received', ['target_id' => $target_id, 'target_type' => $target_type]);
    }
}

/**
 * Hook into daily activity
 */
function achievement_daily_activity($user_id) {
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 10, 'daily_active', ['date' => date('Y-m-d')]);
    }
}

/**
 * Hook into Islamic learning activities
 */
function achievement_islamic_learning($user_id, $activity_type, $data = []) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $xp_rewards = [
            'quran_reading' => 20,
            'hadith_study' => 15,
            'islamic_history' => 10,
            'tajweed_lesson' => 25,
            'fiqh_study' => 12,
            'sunnah_practice' => 18,
            'surah_memorization' => 30
        ];
        
        $points_rewards = [
            'quran_reading' => 5,
            'hadith_study' => 4,
            'islamic_history' => 3,
            'tajweed_lesson' => 6,
            'fiqh_study' => 3,
            'sunnah_practice' => 4,
            'surah_memorization' => 8
        ];
        
        $xp = $xp_rewards[$activity_type] ?? 10;
        $points = $points_rewards[$activity_type] ?? 2;
        
        $achievements_extension->awardXP($user_id, $xp, 'islamic_learning', array_merge($data, ['activity_type' => $activity_type]));
        $achievements_extension->awardPoints($user_id, $points, 'islamic_learning', array_merge($data, ['activity_type' => $activity_type]));
    }
}

/**
 * Hook into content rating
 */
function achievement_content_rated($user_id, $content_id, $rating) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 5, 'content_rating', ['content_id' => $content_id, 'rating' => $rating]);
        $achievements_extension->awardPoints($user_id, 1, 'content_rating', ['content_id' => $content_id, 'rating' => $rating]);
    }
}

/**
 * Hook into discussion creation
 */
function achievement_discussion_created($discussion_id, $user_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 15, 'discussion_start', ['discussion_id' => $discussion_id]);
        $achievements_extension->awardPoints($user_id, 3, 'discussion_start', ['discussion_id' => $discussion_id]);
    }
}

/**
 * Hook into help given to other users
 */
function achievement_help_given($user_id, $helped_user_id, $help_type) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 25, 'help_other', ['helped_user_id' => $helped_user_id, 'help_type' => $help_type]);
        $achievements_extension->awardPoints($user_id, 5, 'help_other', ['helped_user_id' => $helped_user_id, 'help_type' => $help_type]);
    }
}

/**
 * Hook into mention received
 */
function achievement_mention_received($user_id, $mentioned_by, $content_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 10, 'mention_received', ['mentioned_by' => $mentioned_by, 'content_id' => $content_id]);
        $achievements_extension->awardPoints($user_id, 2, 'mention_received', ['mentioned_by' => $mentioned_by, 'content_id' => $content_id]);
    }
}

/**
 * Hook into wiki moderation
 */
function achievement_wiki_moderated($user_id, $page_id, $action) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 20, 'wiki_moderate', ['page_id' => $page_id, 'action' => $action]);
        $achievements_extension->awardPoints($user_id, 4, 'wiki_moderate', ['page_id' => $page_id, 'action' => $action]);
    }
}

/**
 * Hook into wiki expertise
 */
function achievement_wiki_expertise($user_id, $category_id) {
    if (!is_logged_in()) return;
    
    $achievements_extension = new AchievementsExtension();
    if ($achievements_extension->settings['enabled']) {
        $achievements_extension->awardXP($user_id, 50, 'wiki_expert', ['category_id' => $category_id]);
        $achievements_extension->awardPoints($user_id, 10, 'wiki_expert', ['category_id' => $category_id]);
    }
}

/**
 * Initialize achievement hooks
 * This function should be called when the system starts
 */
function init_achievement_hooks() {
    // Register hooks with the system
    if (function_exists('add_action')) {
        add_action('article_created', 'achievement_article_created', 10, 2);
        add_action('article_edited', 'achievement_article_edited', 10, 2);
        add_action('wiki_page_created', 'achievement_wiki_page_created', 10, 2);
        add_action('wiki_page_edited', 'achievement_wiki_page_edited', 10, 2);
        add_action('wiki_page_visited', 'achievement_wiki_page_visited', 10, 2);
        add_action('user_registered', 'achievement_user_registered', 10, 1);
        add_action('user_logged_in', 'achievement_user_logged_in', 10, 1);
        add_action('friend_added', 'achievement_friend_added', 10, 2);
        add_action('comment_created', 'achievement_comment_created', 10, 2);
        add_action('like_given', 'achievement_like_given', 10, 3);
        add_action('like_received', 'achievement_like_received', 10, 3);
        add_action('daily_activity', 'achievement_daily_activity', 10, 1);
        add_action('islamic_learning', 'achievement_islamic_learning', 10, 3);
        add_action('content_rated', 'achievement_content_rated', 10, 3);
        add_action('discussion_created', 'achievement_discussion_created', 10, 2);
        add_action('help_given', 'achievement_help_given', 10, 3);
        add_action('mention_received', 'achievement_mention_received', 10, 3);
        add_action('wiki_moderated', 'achievement_wiki_moderated', 10, 3);
        add_action('wiki_expertise', 'achievement_wiki_expertise', 10, 2);
    }
}

// Auto-initialize hooks if the system supports it
if (function_exists('add_action')) {
    init_achievement_hooks();
}
