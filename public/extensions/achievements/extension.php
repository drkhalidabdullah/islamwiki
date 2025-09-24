<?php
/**
 * Achievement System Extension
 * Comprehensive award/badge/achievement/goals system with Islamic learning focus
 * 
 * @version 1.0.0
 * @author IslamWiki Team
 */

class AchievementsExtension {
    public $name = 'Achievement System';
    public $version = '1.0.0';
    public $description = 'Comprehensive award/badge/achievement/goals system with Islamic learning focus';
    public $enabled = false;
    public $settings = [];
    
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->loadSettings();
        $this->enabled = $this->settings['enabled'];
    }
    
    /**
     * Load extension settings
     */
    private function loadSettings() {
        // Default settings
        $this->settings = [
            'enabled' => false,
            'xp_multiplier' => 1.0,
            'points_multiplier' => 1.0,
            'notifications_enabled' => true,
            'level_system_enabled' => true,
            'max_level' => 100,
            'xp_per_level' => 500,
            'level_scaling' => 1.3
        ];
        
        // Try to load from database if PDO is available
        if ($this->pdo) {
            try {
                $settings_keys = [
                    'achievements_enabled' => 'enabled',
                    'achievements_xp_multiplier' => 'xp_multiplier',
                    'achievements_points_multiplier' => 'points_multiplier',
                    'achievements_notifications_enabled' => 'notifications_enabled',
                    'achievements_level_system_enabled' => 'level_system_enabled',
                    'achievements_max_level' => 'max_level',
                    'achievements_xp_per_level' => 'xp_per_level',
                    'achievements_level_scaling' => 'level_scaling'
                ];
                
                foreach ($settings_keys as $db_key => $setting_key) {
                    $stmt = $this->pdo->prepare("SELECT value FROM system_settings WHERE `key` = ?");
                    $stmt->execute([$db_key]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $value = $result['value'];
                        // Convert to appropriate type
                        if ($setting_key === 'enabled' || $setting_key === 'notifications_enabled' || $setting_key === 'level_system_enabled') {
                            $this->settings[$setting_key] = (bool)$value;
                        } elseif ($setting_key === 'max_level' || $setting_key === 'xp_per_level') {
                            $this->settings[$setting_key] = (int)$value;
                        } else {
                            $this->settings[$setting_key] = (float)$value;
                        }
                    }
                }
            } catch (Exception $e) {
                // Database error, use defaults
            }
        }
    }
    
    /**
     * Render extension (called by extension manager)
     */
    public function render() {
        // This method is called when the extension is enabled
        // We can add any frontend rendering logic here if needed
    }
    
    /**
     * Load extension assets
     */
    public function loadAssets() {
        if ($this->enabled && is_logged_in()) {
            echo '<link rel="stylesheet" href="/extensions/achievements/assets/css/achievements.css">';
        }
    }
    
    /**
     * Load extension scripts
     */
    public function loadScripts() {
        if ($this->enabled && is_logged_in()) {
            echo '<script src="/extensions/achievements/assets/js/achievements.js"></script>';
        }
    }
    
    /**
     * Get settings form for admin interface
     */
    public function getSettingsForm() {
        if (!$this->enabled) {
            return '<p>Enable the extension to configure settings.</p>';
        }
        
        $settings = $this->getAdminSettings();
        $html = '<div class="extension-settings-form">';
        
        foreach ($settings as $key => $setting) {
            $html .= '<div class="form-group">';
            $html .= '<label for="' . $key . '">' . htmlspecialchars($setting['label']) . '</label>';
            
            if ($setting['type'] === 'boolean') {
                $checked = $setting['value'] ? 'checked' : '';
                $html .= '<label class="toggle-switch">';
                $html .= '<input type="checkbox" name="' . $key . '" value="1" ' . $checked . '>';
                $html .= '<span class="toggle-slider"></span>';
                $html .= '</label>';
            } elseif ($setting['type'] === 'number') {
                $html .= '<input type="number" name="' . $key . '" value="' . $setting['value'] . '"';
                if (isset($setting['min'])) $html .= ' min="' . $setting['min'] . '"';
                if (isset($setting['max'])) $html .= ' max="' . $setting['max'] . '"';
                if (isset($setting['step'])) $html .= ' step="' . $setting['step'] . '"';
                $html .= '>';
            }
            
            if (isset($setting['description'])) {
                $html .= '<small class="form-help">' . htmlspecialchars($setting['description']) . '</small>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Save extension settings
     */
    public function saveSettings($data) {
        $settings_to_save = [
            'achievements_enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : false,
            'achievements_xp_multiplier' => isset($data['xp_multiplier']) ? (float)$data['xp_multiplier'] : 1.0,
            'achievements_points_multiplier' => isset($data['points_multiplier']) ? (float)$data['points_multiplier'] : 1.0,
            'achievements_notifications_enabled' => isset($data['notifications_enabled']) ? (bool)$data['notifications_enabled'] : true,
            'achievements_level_system_enabled' => isset($data['level_system_enabled']) ? (bool)$data['level_system_enabled'] : true,
            'achievements_max_level' => isset($data['max_level']) ? (int)$data['max_level'] : 100,
            'achievements_xp_per_level' => isset($data['xp_per_level']) ? (int)$data['xp_per_level'] : 100,
            'achievements_level_scaling' => isset($data['level_scaling']) ? (float)$data['level_scaling'] : 1.2
        ];
        
        $saved = 0;
        foreach ($settings_to_save as $key => $value) {
            if (set_system_setting($key, $value)) {
                $saved++;
            }
        }
        
        $this->loadSettings(); // Reload settings
        return $saved > 0;
    }
    
    /**
     * Get admin settings configuration
     */
    public function getAdminSettings() {
        return [
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Achievement System',
                'description' => 'Enable the achievement system for all users',
                'value' => $this->settings['enabled']
            ],
            'xp_multiplier' => [
                'type' => 'number',
                'label' => 'XP Multiplier',
                'description' => 'Multiplier for XP rewards (1.0 = normal)',
                'value' => $this->settings['xp_multiplier'],
                'min' => 0.1,
                'max' => 5.0,
                'step' => 0.1
            ],
            'points_multiplier' => [
                'type' => 'number',
                'label' => 'Points Multiplier',
                'description' => 'Multiplier for points rewards (1.0 = normal)',
                'value' => $this->settings['points_multiplier'],
                'min' => 0.1,
                'max' => 5.0,
                'step' => 0.1
            ],
            'notifications_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Notifications',
                'description' => 'Show achievement notifications to users',
                'value' => $this->settings['notifications_enabled']
            ],
            'level_system_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Level System',
                'description' => 'Enable the level progression system',
                'value' => $this->settings['level_system_enabled']
            ],
            'max_level' => [
                'type' => 'number',
                'label' => 'Maximum Level',
                'description' => 'Maximum level users can reach',
                'value' => $this->settings['max_level'],
                'min' => 10,
                'max' => 1000
            ],
            'xp_per_level' => [
                'type' => 'number',
                'label' => 'Base XP per Level',
                'description' => 'Base XP required for level 2',
                'value' => $this->settings['xp_per_level'],
                'min' => 50,
                'max' => 1000
            ],
            'level_scaling' => [
                'type' => 'number',
                'label' => 'Level Scaling Factor',
                'description' => 'How much XP increases per level (1.2 = 20% increase)',
                'value' => $this->settings['level_scaling'],
                'min' => 1.0,
                'max' => 2.0,
                'step' => 0.1
            ]
        ];
    }
    
    /**
     * Get user level information
     */
    public function getUserLevel($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT ul.*
            FROM user_levels ul 
            WHERE ul.user_id = ?
        ");
        $stmt->execute([$user_id]);
        
        $level = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$level) {
            // Create new level record
            $this->createUserLevel($user_id);
            return $this->getUserLevel($user_id);
        }
        
        // Update total achievements count
        $achievement_count = $this->pdo->query("
            SELECT COUNT(*) FROM user_achievements 
            WHERE user_id = $user_id AND is_completed = 1
        ")->fetchColumn();
        $level['total_achievements'] = $achievement_count;
        
        // Update total points count (achievements + badges)
        $achievement_points = $this->pdo->query("
            SELECT SUM(a.points) FROM user_achievements ua 
            JOIN achievements a ON ua.achievement_id = a.id 
            WHERE ua.user_id = $user_id AND ua.is_completed = 1
        ")->fetchColumn();
        
        $badge_points = $this->pdo->query("
            SELECT SUM(b.points) FROM user_badges ub 
            JOIN badges b ON ub.badge_id = b.id 
            WHERE ub.user_id = $user_id
        ")->fetchColumn();
        
        $level['total_points'] = ($achievement_points ?: 0) + ($badge_points ?: 0);
        
        // Update total XP count (achievements + badges)
        $achievement_xp = $this->pdo->query("
            SELECT SUM(a.xp_reward) FROM user_achievements ua 
            JOIN achievements a ON ua.achievement_id = a.id 
            WHERE ua.user_id = $user_id AND ua.is_completed = 1
        ")->fetchColumn();
        
        $badge_xp = $this->pdo->query("
            SELECT SUM(b.xp_reward) FROM user_badges ub 
            JOIN badges b ON ub.badge_id = b.id 
            WHERE ub.user_id = $user_id
        ")->fetchColumn();
        
        $level['total_xp'] = ($achievement_xp ?: 0) + ($badge_xp ?: 0);
        
        // Calculate level based on actual total XP from achievements
        $correct_level = $this->calculateLevel($level['total_xp']);
        
        // Calculate XP requirements for the correct level
        $current_level_xp = $this->getXPForLevel($correct_level);
        $next_level_xp = $this->getXPForLevel($correct_level + 1);
        
        $level['level'] = $correct_level;
        $level['current_level_xp'] = $level['total_xp'] - $current_level_xp;
        $level['xp_to_next_level'] = max(0, $next_level_xp - $level['total_xp']);
        
        // Update the database with correct values
        $stmt = $this->pdo->prepare("
            UPDATE user_levels 
            SET level = ?, total_xp = ?, total_achievements = ?, total_points = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $level['level'],
            $level['total_xp'],
            $level['total_achievements'],
            $level['total_points'],
            $user_id
        ]);
        
        return $level;
    }
    
    /**
     * Create user level record
     */
    private function createUserLevel($user_id) {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_levels (user_id, level, total_xp, current_level_xp, xp_to_next_level, total_achievements, total_points)
            VALUES (?, 1, 0, 0, ?, 0, 0)
        ");
        $stmt->execute([$user_id, $this->settings['xp_per_level']]);
    }
    
    /**
     * Get user achievements
     */
    public function getUserAchievements($user_id, $completed_only = false) {
        $sql = "
            SELECT a.*, ua.progress, ua.is_completed, ua.completed_at,
                   ac.name as category_name, ac.color as category_color,
                   at.name as type_name, at.color as type_color
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
            LEFT JOIN achievement_categories ac ON a.category_id = ac.id
            LEFT JOIN achievement_types at ON a.type_id = at.id
            WHERE a.is_active = 1
        ";
        
        if ($completed_only) {
            $sql .= " AND ua.is_completed = 1";
        }
        
        $sql .= " ORDER BY a.sort_order, a.name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all achievements (for admin)
     */
    public function getAllAchievements() {
        $stmt = $this->pdo->prepare("
            SELECT a.*, ac.name as category_name, at.name as type_name
            FROM achievements a
            LEFT JOIN achievement_categories ac ON a.category_id = ac.id
            LEFT JOIN achievement_types at ON a.type_id = at.id
            ORDER BY a.sort_order, a.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get achievement categories
     */
    public function getCategories() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM achievement_categories 
            WHERE is_active = 1 
            ORDER BY sort_order, name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get achievement types
     */
    public function getTypes() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM achievement_types 
            WHERE is_active = 1 
            ORDER BY sort_order, name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Award XP to user
     */
    public function awardXP($user_id, $xp_amount, $activity_type = 'general', $activity_data = null) {
        if (!$this->settings['enabled'] || !$this->settings['level_system_enabled']) {
            return false;
        }
        
        $xp_amount = intval($xp_amount * $this->settings['xp_multiplier']);
        
        // Log activity
        $this->logActivity($user_id, $activity_type, $activity_data, $xp_amount, 0);
        
        // Update user level
        $this->updateUserLevel($user_id, $xp_amount);
        
        // Check for new achievements
        $this->checkAchievements($user_id);
        
        return true;
    }
    
    /**
     * Award points to user
     */
    public function awardPoints($user_id, $points_amount, $activity_type = 'general', $activity_data = null) {
        if (!$this->settings['enabled']) {
            return false;
        }
        
        $points_amount = intval($points_amount * $this->settings['points_multiplier']);
        
        // Log activity
        $this->logActivity($user_id, $activity_type, $activity_data, 0, $points_amount);
        
        // Update user level
        $this->updateUserLevel($user_id, 0, $points_amount);
        
        // Check for new achievements
        $this->checkAchievements($user_id);
        
        return true;
    }
    
    /**
     * Update user level
     */
    private function updateUserLevel($user_id, $xp_gained = 0, $points_gained = 0) {
        $level = $this->getUserLevel($user_id);
        
        $new_total_xp = $level['total_xp'] + $xp_gained;
        $new_total_points = $level['total_points'] + $points_gained;
        
        // Calculate new level
        $new_level = $this->calculateLevel($new_total_xp);
        $new_current_level_xp = $new_total_xp - $this->getXPForLevel($new_level);
        $new_xp_to_next_level = $this->getXPForLevel($new_level + 1) - $new_total_xp;
        
        // Update level record
        $stmt = $this->pdo->prepare("
            UPDATE user_levels 
            SET level = ?, total_xp = ?, current_level_xp = ?, xp_to_next_level = ?, total_points = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $new_level, $new_total_xp, $new_current_level_xp, $new_xp_to_next_level, $new_total_points, $user_id
        ]);
        
        // Check for level up
        if ($new_level > $level['level']) {
            $this->handleLevelUp($user_id, $new_level, $level['level']);
        }
    }
    
    /**
     * Calculate level from total XP
     */
    private function calculateLevel($total_xp) {
        $level = 1;
        $xp_needed = $this->settings['xp_per_level'];
        
        while ($total_xp >= $xp_needed && $level < $this->settings['max_level']) {
            $total_xp -= $xp_needed;
            $level++;
            $xp_needed = intval($xp_needed * $this->settings['level_scaling']);
        }
        
        return min($level, $this->settings['max_level']);
    }
    
    /**
     * Get XP required for a specific level
     */
    private function getXPForLevel($level) {
        if ($level <= 1) return 0;
        
        $total_xp = 0;
        $xp_needed = $this->settings['xp_per_level'];
        
        for ($i = 2; $i <= $level; $i++) {
            $total_xp += $xp_needed;
            $xp_needed = intval($xp_needed * $this->settings['level_scaling']);
        }
        
        return $total_xp;
    }
    
    /**
     * Handle level up
     */
    private function handleLevelUp($user_id, $new_level, $old_level) {
        // Create notification
        if ($this->settings['notifications_enabled']) {
            $this->createNotification($user_id, null, 'level_up', 
                "Level Up!", 
                "Congratulations! You've reached level {$new_level}!"
            );
        }
        
        // Check for level-based achievements
        $this->checkAchievements($user_id);
    }
    
    /**
     * Check for new achievements
     */
    public function checkAchievements($user_id) {
        $achievements = $this->getUserAchievements($user_id);
        
        foreach ($achievements as $achievement) {
            if ($achievement['is_completed']) continue;
            
            if ($this->checkAchievementRequirements($user_id, $achievement)) {
                $this->completeAchievement($user_id, $achievement['id']);
            }
        }
    }
    
    
    
    /**
     * Complete achievement
     */
    private function completeAchievement($user_id, $achievement_id) {
        // Get achievement details
        $stmt = $this->pdo->prepare("
            SELECT * FROM achievements WHERE id = ?
        ");
        $stmt->execute([$achievement_id]);
        $achievement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update user achievement
        $stmt = $this->pdo->prepare("
            INSERT INTO user_achievements (user_id, achievement_id, progress, is_completed, completed_at)
            VALUES (?, ?, 100, 1, NOW())
            ON DUPLICATE KEY UPDATE 
            progress = 100, is_completed = 1, completed_at = NOW()
        ");
        $stmt->execute([$user_id, $achievement_id]);
        
        // Award XP and points
        $this->awardXP($user_id, $achievement['xp_reward'], 'achievement_completed', ['achievement_id' => $achievement_id]);
        $this->awardPoints($user_id, $achievement['points'], 'achievement_completed', ['achievement_id' => $achievement_id]);
        
        // Create notification
        if ($this->settings['notifications_enabled']) {
            $this->createNotification($user_id, $achievement_id, 'achievement_unlocked',
                "Achievement Unlocked!",
                "You've earned the '{$achievement['name']}' achievement!"
            );
        }
        
        // Update total achievements count
        $this->updateTotalAchievements($user_id);
    }
    
    /**
     * Check if user meets achievement requirements
     */
    public function checkAchievementRequirements($user_id, $achievement) {
        $requirement_type = $achievement['requirement_type'] ?? 'count';
        $requirement_value = $achievement['requirement_value'] ?? 1;
        $requirement_data = $achievement['requirement_data'] ?? null;
        
        switch ($requirement_type) {
            case 'achievement_count':
                $user_level = $this->getUserLevel($user_id);
                return $user_level['total_achievements'] >= $requirement_value;
                
            case 'days_since_join':
                $stmt = $this->pdo->prepare("
                    SELECT DATEDIFF(NOW(), created_at) as days_since_join 
                    FROM users WHERE id = ?
                ");
                $stmt->execute([$user_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result && $result['days_since_join'] >= $requirement_value;
                
            case 'ref_tags':
                // Check for ref tags in user's content (when implemented)
                return false; // Not implemented yet
                
            case 'bug_reports':
                // Check bug reports count (when implemented)
                return false; // Not implemented yet
                
            case 'posts_count':
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = ? AND is_public = 1
                ");
                $stmt->execute([$user_id]);
                $count = $stmt->fetchColumn();
                return $count >= $requirement_value;
                
            case 'friends_count':
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM user_follows WHERE follower_id = ?
                ");
                $stmt->execute([$user_id]);
                $count = $stmt->fetchColumn();
                return $count >= $requirement_value;
                
            case 'profile_complete':
                $stmt = $this->pdo->prepare("
                    SELECT username, email, first_name, last_name, bio, display_name, avatar
                    FROM users WHERE id = ?
                ");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $fields_filled = 0;
                foreach (['first_name', 'last_name', 'bio', 'display_name', 'avatar'] as $field) {
                    if (!empty($user[$field])) $fields_filled++;
                }
                return $fields_filled >= $requirement_value;
                
            case 'settings_updated':
                // Check if user has updated settings (when implemented)
                return false; // Not implemented yet
                
            case 'first_login':
                return true; // Always true if user exists
                
            case 'count':
                // Handle generic count-based achievements
                if ($achievement['slug'] === 'photo-pioneer') {
                    // Check if user has uploaded a profile photo
                    $stmt = $this->pdo->prepare("
                        SELECT avatar FROM users WHERE id = ? AND avatar IS NOT NULL AND avatar != ''
                    ");
                    $stmt->execute([$user_id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $result && !empty($result['avatar']);
                }
                
                // Only auto-award achievements that should be given to all users
                $auto_award_achievements = [
                    'first-steps' // Only first steps should be auto-awarded
                ];
                
                if (in_array($achievement['slug'], $auto_award_achievements)) {
                    return true; // Auto-award only first steps
                }
                
                return false; // Default for other count-based achievements
                
            case 'status_count':
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = ? AND post_type = 'text'
                ");
                $stmt->execute([$user_id]);
                $count = $stmt->fetchColumn();
                return $count >= $requirement_value;
                
            case 'article_count':
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = ? AND post_type = 'article_share'
                ");
                $stmt->execute([$user_id]);
                $count = $stmt->fetchColumn();
                return $count >= $requirement_value;
                
            default:
                return false;
        }
    }
    
    /**
     * Award achievement by slug
     */
    public function awardAchievement($user_id, $achievement_slug) {
        // Get achievement by slug
        $stmt = $this->pdo->prepare("
            SELECT * FROM achievements WHERE slug = ?
        ");
        $stmt->execute([$achievement_slug]);
        $achievement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$achievement) {
            throw new Exception("Achievement not found: $achievement_slug");
        }
        
        // Check if user meets requirements
        if (!$this->checkAchievementRequirements($user_id, $achievement)) {
            throw new Exception("User does not meet requirements for achievement: $achievement_slug");
        }
        
        // Check if user already has this achievement
        $stmt = $this->pdo->prepare("
            SELECT * FROM user_achievements 
            WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([$user_id, $achievement['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing && $existing['is_completed']) {
            throw new Exception("Achievement already completed: $achievement_slug");
        }
        
        // Award the achievement
        $this->completeAchievement($user_id, $achievement['id']);
        
        return true;
    }
    
    /**
     * Update total achievements count
     */
    private function updateTotalAchievements($user_id) {
        $stmt = $this->pdo->prepare("
            UPDATE user_levels 
            SET total_achievements = (
                SELECT COUNT(*) FROM user_achievements 
                WHERE user_id = ? AND is_completed = 1
            )
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id, $user_id]);
    }
    
    /**
     * Log user activity
     */
    private function logActivity($user_id, $activity_type, $activity_data = null, $xp_earned = 0, $points_earned = 0) {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_activity_log (user_id, activity_type, activity_data, xp_earned, points_earned)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, 
            $activity_type, 
            $activity_data ? json_encode($activity_data) : null,
            $xp_earned, 
            $points_earned
        ]);
    }
    
    /**
     * Create notification
     */
    private function createNotification($user_id, $achievement_id, $type, $title, $message) {
        $stmt = $this->pdo->prepare("
            INSERT INTO achievement_notifications (user_id, achievement_id, notification_type, title, message)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $achievement_id, $type, $title, $message]);
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($user_id, $limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT an.*, a.name as achievement_name, a.icon as achievement_icon
            FROM achievement_notifications an
            LEFT JOIN achievements a ON an.achievement_id = a.id
            WHERE an.user_id = ?
            ORDER BY an.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($user_id, $notification_id) {
        $stmt = $this->pdo->prepare("
            UPDATE achievement_notifications 
            SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notification_id, $user_id]);
    }
    
    /**
     * Check and award badges for a user
     */
    public function checkAndAwardBadges($user_id) {
        // Get all active badges
        $stmt = $this->pdo->prepare("
            SELECT * FROM badges WHERE is_active = 1 ORDER BY sort_order
        ");
        $stmt->execute();
        $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $awarded_badges = [];
        
        foreach ($badges as $badge) {
            // Check if user already has this badge
            $stmt = $this->pdo->prepare("
                SELECT * FROM user_badges WHERE user_id = ? AND badge_id = ?
            ");
            $stmt->execute([$user_id, $badge['id']]);
            if ($stmt->fetch()) {
                continue; // User already has this badge
            }
            
            // Check if user meets badge requirements
            if ($this->checkBadgeRequirements($user_id, $badge)) {
                $this->awardBadge($user_id, $badge);
                $awarded_badges[] = $badge;
            }
        }
        
        return $awarded_badges;
    }
    
    /**
     * Check if user meets badge requirements
     */
    public function checkBadgeRequirements($user_id, $badge) {
        switch ($badge['slug']) {
            case 'social-butterfly':
                $friends_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_follows WHERE follower_id = $user_id
                ")->fetchColumn();
                $status_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND post_type = 'text'
                ")->fetchColumn();
                return $friends_count >= 5 && $status_count >= 10;
                
            case 'community-builder':
                $friends_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_follows WHERE follower_id = $user_id
                ")->fetchColumn();
                $status_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND post_type = 'text'
                ")->fetchColumn();
                return $friends_count >= 10 && $status_count >= 20;
                
            case 'content-creator':
                $posts_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND is_public = 1
                ")->fetchColumn();
                $articles_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND post_type = 'article_share'
                ")->fetchColumn();
                return $posts_count >= 5 && $articles_count >= 1;
                
            case 'knowledge-sharer':
                $posts_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND is_public = 1
                ")->fetchColumn();
                $articles_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND post_type = 'article_share'
                ")->fetchColumn();
                return $posts_count >= 10 && $articles_count >= 3;
                
            case 'profile-master':
                $profile_complete = $this->checkAchievementRequirements($user_id, [
                    'requirement_type' => 'profile_complete',
                    'requirement_value' => 3
                ]);
                $friends_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_follows WHERE follower_id = $user_id
                ")->fetchColumn();
                return $profile_complete && $friends_count >= 5;
                
            case 'public-figure':
                $profile_complete = $this->checkAchievementRequirements($user_id, [
                    'requirement_type' => 'profile_complete',
                    'requirement_value' => 3
                ]);
                $friends_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_follows WHERE follower_id = $user_id
                ")->fetchColumn();
                $posts_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_posts WHERE user_id = $user_id AND is_public = 1
                ")->fetchColumn();
                return $profile_complete && $friends_count >= 10 && $posts_count >= 5;
                
            case 'achievement-hunter':
                $achievement_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_achievements WHERE user_id = $user_id AND is_completed = 1
                ")->fetchColumn();
                return $achievement_count >= 10;
                
            case 'achievement-master':
                $achievement_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_achievements WHERE user_id = $user_id AND is_completed = 1
                ")->fetchColumn();
                return $achievement_count >= 25;
                
            case 'achievement-legend':
                $achievement_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_achievements WHERE user_id = $user_id AND is_completed = 1
                ")->fetchColumn();
                return $achievement_count >= 50;
                
            case 'early-adopter':
                $user_created = $this->pdo->query("
                    SELECT created_at FROM users WHERE id = $user_id
                ")->fetchColumn();
                $days_since_join = $this->pdo->query("
                    SELECT DATEDIFF(NOW(), '$user_created') as days
                ")->fetchColumn();
                $achievement_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_achievements WHERE user_id = $user_id AND is_completed = 1
                ")->fetchColumn();
                return $days_since_join <= 30 && $achievement_count >= 1;
                
            case 'pioneer':
                $user_created = $this->pdo->query("
                    SELECT created_at FROM users WHERE id = $user_id
                ")->fetchColumn();
                $days_since_join = $this->pdo->query("
                    SELECT DATEDIFF(NOW(), '$user_created') as days
                ")->fetchColumn();
                $achievement_count = $this->pdo->query("
                    SELECT COUNT(*) FROM user_achievements WHERE user_id = $user_id AND is_completed = 1
                ")->fetchColumn();
                return $days_since_join <= 30 && $achievement_count >= 5;
                
            case 'well-rounded':
                $category_count = $this->pdo->query("
                    SELECT COUNT(DISTINCT a.category_id) 
                    FROM user_achievements ua 
                    JOIN achievements a ON ua.achievement_id = a.id 
                    WHERE ua.user_id = $user_id AND ua.is_completed = 1
                ")->fetchColumn();
                return $category_count >= 3;
                
            case 'category-master':
                $category_count = $this->pdo->query("
                    SELECT COUNT(DISTINCT a.category_id) 
                    FROM user_achievements ua 
                    JOIN achievements a ON ua.achievement_id = a.id 
                    WHERE ua.user_id = $user_id AND ua.is_completed = 1
                ")->fetchColumn();
                return $category_count >= 5;
                
            default:
                return false;
        }
    }
    
    /**
     * Award a badge to a user
     */
    public function awardBadge($user_id, $badge) {
        // Insert user badge
        $stmt = $this->pdo->prepare("
            INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)
        ");
        $stmt->execute([$user_id, $badge['id']]);
        
        // Add XP and points to user level
        $stmt = $this->pdo->prepare("
            UPDATE user_levels 
            SET total_xp = total_xp + ?, total_points = total_points + ?
            WHERE user_id = ?
        ");
        $stmt->execute([$badge['xp_reward'], $badge['points'], $user_id]);
        
        return true;
    }
    
    /**
     * Get user badges
     */
    public function getUserBadges($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, ub.earned_at, ub.is_displayed
            FROM badges b
            JOIN user_badges ub ON b.id = ub.badge_id
            WHERE ub.user_id = ? AND b.is_active = 1
            ORDER BY ub.earned_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all badges
     */
    public function getAllBadges() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM badges WHERE is_active = 1 ORDER BY sort_order
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get leaderboard
     */
    public function getLeaderboard($limit = 10, $category_id = null) {
        $sql = "
            SELECT u.username, u.display_name, ul.level, ul.total_xp, ul.total_achievements, ul.total_points
            FROM users u
            JOIN user_levels ul ON u.id = ul.user_id
            WHERE u.is_active = 1
        ";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND u.id IN (
                SELECT DISTINCT ua.user_id 
                FROM user_achievements ua 
                JOIN achievements a ON ua.achievement_id = a.id 
                WHERE a.category_id = ? AND ua.is_completed = 1
            )";
            $params[] = $category_id;
        }
        
        $sql .= " ORDER BY ul.total_xp DESC, ul.level DESC, ul.total_achievements DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get achievement statistics
     */
    public function getAchievementStats($user_id) {
        $stats = [];
        
        // Total achievements
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM user_achievements 
            WHERE user_id = ? AND is_completed = 1
        ");
        $stmt->execute([$user_id]);
        $stats['total_achievements'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Achievements by category
        $stmt = $this->pdo->prepare("
            SELECT ac.name, ac.color, COUNT(*) as count
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            JOIN achievement_categories ac ON a.category_id = ac.id
            WHERE ua.user_id = ? AND ua.is_completed = 1
            GROUP BY ac.id, ac.name, ac.color
            ORDER BY count DESC
        ");
        $stmt->execute([$user_id]);
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Achievements by rarity
        $stmt = $this->pdo->prepare("
            SELECT a.rarity, COUNT(*) as count
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND ua.is_completed = 1
            GROUP BY a.rarity
            ORDER BY FIELD(a.rarity, 'common', 'uncommon', 'rare', 'epic', 'legendary')
        ");
        $stmt->execute([$user_id]);
        $stats['by_rarity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    /**
     * Get extension status
     */
    public function getStatus() {
        $status = [
            'enabled' => $this->enabled,
            'version' => $this->version,
            'database_tables' => [],
            'total_achievements' => 0,
            'total_users' => 0
        ];
        
        // Only check database if PDO is available
        if ($this->pdo) {
            try {
                $status['database_tables'] = $this->checkDatabaseTables();
                $status['total_achievements'] = $this->getTotalAchievements();
                $status['total_users'] = $this->getTotalUsers();
            } catch (Exception $e) {
                // Database not available or error
            }
        }
        
        return $status;
    }
    
    /**
     * Check if database tables exist
     */
    private function checkDatabaseTables() {
        $tables = [
            'achievement_categories',
            'achievement_types', 
            'achievements',
            'achievement_requirements',
            'user_achievements',
            'user_levels',
            'user_activity_log',
            'achievement_notifications'
        ];
        
        $existing = [];
        foreach ($tables as $table) {
            try {
                $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
                $existing[] = $stmt->fetch() ? $table : null;
            } catch (Exception $e) {
                // Table doesn't exist or error
                $existing[] = null;
            }
        }
        
        return array_filter($existing);
    }
    
    /**
     * Get total achievements count
     */
    private function getTotalAchievements() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM achievements WHERE is_active = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['count'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get total users with levels
     */
    private function getTotalUsers() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM user_levels");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['count'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
