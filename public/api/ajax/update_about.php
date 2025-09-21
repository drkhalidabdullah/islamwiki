<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$section = $_POST['section'] ?? '';

if (empty($section)) {
    echo json_encode(['success' => false, 'message' => 'No section specified']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    switch ($section) {
        case 'basic':
            // Update basic user information
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $user_id]);
            break;
            
        case 'contact':
            // Update contact information in user_profiles
            $location = trim($_POST['location'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            
            // Check if profile exists
            $stmt = $pdo->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch();
            
            if ($profile) {
                $stmt = $pdo->prepare("UPDATE user_profiles SET location = ?, website = ?, phone = ? WHERE user_id = ?");
                $stmt->execute([$location, $website, $phone, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, location, website, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $location, $website, $phone]);
            }
            break;
            
        case 'personal':
            // Update personal information
            $bio = trim($_POST['bio'] ?? '');
            $date_of_birth = $_POST['date_of_birth'] ?: null;
            $gender = trim($_POST['gender'] ?? '');
            $interests = trim($_POST['interests'] ?? '');
            
            // Update users table for bio
            $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
            $stmt->execute([$bio, $user_id]);
            
            // Update user_profiles table for other personal info
            $stmt = $pdo->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch();
            
            if ($profile) {
                $stmt = $pdo->prepare("UPDATE user_profiles SET date_of_birth = ?, gender = ?, interests = ? WHERE user_id = ?");
                $stmt->execute([$date_of_birth, $gender, $interests, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, date_of_birth, gender, interests) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $date_of_birth, $gender, $interests]);
            }
            break;
            
        case 'education':
            // Update education and work information
            $education = trim($_POST['education'] ?? '');
            $profession = trim($_POST['profession'] ?? '');
            $expertise_areas = trim($_POST['expertise_areas'] ?? '');
            
            $stmt = $pdo->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch();
            
            if ($profile) {
                $stmt = $pdo->prepare("UPDATE user_profiles SET education = ?, profession = ?, expertise_areas = ? WHERE user_id = ?");
                $stmt->execute([$education, $profession, $expertise_areas, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, education, profession, expertise_areas) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $education, $profession, $expertise_areas]);
            }
            break;
            
        default:
            throw new Exception('Invalid section specified');
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'About information updated successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error updating about information: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update about information: ' . $e->getMessage()]);
}
?>
