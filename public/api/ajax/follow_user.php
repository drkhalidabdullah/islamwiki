<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;
$action = $input['action'] ?? null;

if (!$user_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

if ($user_id == $current_user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
    exit();
}

try {
    if ($action === 'follow') {
        $result = follow_user($current_user_id, $user_id);
        if ($result) {
            log_activity('follow_user', "Started following user ID: $user_id", $current_user_id);
        if ($result) {
            // Create notification for the user being followed
            $target_user = get_user($user_id);
            $current_user = get_user($current_user_id);
            if ($target_user && $current_user) {
                create_notification(
                    $user_id,
                    "friend_request",
                    "New Friend Request",
                    $current_user["username"] . " sent you a friend request",
                    ["from_user_id" => $current_user_id, "from_username" => $current_user["username"]]
                );
            }
        }
        }
    } elseif ($action === 'unfollow') {
        $result = unfollow_user($current_user_id, $user_id);
        if ($result) {
            log_activity('unfollow_user', "Stopped following user ID: $user_id", $current_user_id);
        }
    } elseif ($action === 'accept') {
        // Accept friend request
        $stmt = $pdo->prepare("UPDATE user_follows SET status = 'accepted' WHERE follower_id = ? AND following_id = ?");
        $result = $stmt->execute([$user_id, $current_user_id]);
        if ($result) {
            log_activity('accept_friend_request', "Accepted friend request from user ID: $user_id", $current_user_id);
        if ($result) {
            // Create notification for the user who sent the request
            $target_user = get_user($user_id);
            $current_user = get_user($current_user_id);
            if ($target_user && $current_user) {
                create_notification(
                    $user_id,
                    "friend_accepted",
                    "Friend Request Accepted",
                    $current_user["username"] . " accepted your friend request",
                    ["from_user_id" => $current_user_id, "from_username" => $current_user["username"]]
                );
            }
        }
        }
    } elseif ($action === 'decline') {
        // Decline friend request
        $stmt = $pdo->prepare("DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?");
        $result = $stmt->execute([$user_id, $current_user_id]);
        if ($result) {
            log_activity('decline_friend_request', "Declined friend request from user ID: $user_id", $current_user_id);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }
    
    echo json_encode(['success' => $result]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
