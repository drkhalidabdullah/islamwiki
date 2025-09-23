<?php
/**
 * Achievement System API Endpoints
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../extensions/achievements/extension.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$achievements_extension = new AchievementsExtension();
$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_user_level':
            $level = $achievements_extension->getUserLevel($user_id);
            echo json_encode(['success' => true, 'level' => $level]);
            break;
            
        case 'get_achievements':
            $completed_only = $_GET['completed_only'] ?? false;
            $achievements = $achievements_extension->getUserAchievements($user_id, $completed_only);
            echo json_encode(['success' => true, 'achievements' => $achievements]);
            break;
            
        case 'get_achievement_details':
            $achievement_id = $_GET['id'] ?? 0;
            $achievement = $achievements_extension->getAchievementDetails($achievement_id);
            echo json_encode(['success' => true, 'achievement' => $achievement]);
            break;
            
        case 'get_notifications':
            $limit = $_GET['limit'] ?? 10;
            $notifications = $achievements_extension->getUserNotifications($user_id, $limit);
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            break;
            
        case 'mark_notification_read':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $notification_id = $input['notification_id'] ?? 0;
            
            $result = $achievements_extension->markNotificationAsRead($user_id, $notification_id);
            echo json_encode(['success' => $result]);
            break;
            
        case 'get_leaderboard':
            $limit = $_GET['limit'] ?? 10;
            $category_id = $_GET['category_id'] ?? null;
            $leaderboard = $achievements_extension->getLeaderboard($limit, $category_id);
            echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
            break;
            
        case 'get_achievement_stats':
            $stats = $achievements_extension->getAchievementStats($user_id);
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'award_xp':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $xp_amount = $input['xp_amount'] ?? 0;
            $activity_type = $input['activity_type'] ?? 'general';
            $activity_data = $input['activity_data'] ?? null;
            
            $result = $achievements_extension->awardXP($user_id, $xp_amount, $activity_type, $activity_data);
            echo json_encode(['success' => $result]);
            break;
            
        case 'award_points':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $points_amount = $input['points_amount'] ?? 0;
            $activity_type = $input['activity_type'] ?? 'general';
            $activity_data = $input['activity_data'] ?? null;
            
            $result = $achievements_extension->awardPoints($user_id, $points_amount, $activity_type, $activity_data);
            echo json_encode(['success' => $result]);
            break;
            
        case 'check_achievements':
            $achievements_extension->checkAchievements($user_id);
            echo json_encode(['success' => true, 'message' => 'Achievements checked']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
