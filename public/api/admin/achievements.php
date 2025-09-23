<?php
/**
 * Admin Achievement System API Endpoints
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../extensions/achievements/extension.php';

// Set JSON header
header('Content-Type: application/json');

// Check admin permissions
require_permission('admin.access');

$achievements_extension = new AchievementsExtension();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_all_achievements':
            $achievements = $achievements_extension->getAllAchievements();
            echo json_encode(['success' => true, 'achievements' => $achievements]);
            break;
            
        case 'get_achievement':
            $id = $_GET['id'] ?? 0;
            $achievement = $achievements_extension->getAchievement($id);
            echo json_encode(['success' => true, 'achievement' => $achievement]);
            break;
            
        case 'create_achievement':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $achievements_extension->createAchievement($input);
            echo json_encode(['success' => $result]);
            break;
            
        case 'update_achievement':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            unset($input['id']);
            
            $result = $achievements_extension->updateAchievement($id, $input);
            echo json_encode(['success' => $result]);
            break;
            
        case 'delete_achievement':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            
            $result = $achievements_extension->deleteAchievement($id);
            echo json_encode(['success' => $result]);
            break;
            
        case 'get_categories':
            $categories = $achievements_extension->getCategories();
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;
            
        case 'create_category':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $achievements_extension->createCategory($input);
            echo json_encode(['success' => $result]);
            break;
            
        case 'update_category':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            unset($input['id']);
            
            $result = $achievements_extension->updateCategory($id, $input);
            echo json_encode(['success' => $result]);
            break;
            
        case 'delete_category':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            
            $result = $achievements_extension->deleteCategory($id);
            echo json_encode(['success' => $result]);
            break;
            
        case 'get_system_stats':
            $stats = $achievements_extension->getSystemStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'reset_user_achievements':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $user_id = $input['user_id'] ?? 0;
            
            $result = $achievements_extension->resetUserAchievements($user_id);
            echo json_encode(['success' => $result]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
