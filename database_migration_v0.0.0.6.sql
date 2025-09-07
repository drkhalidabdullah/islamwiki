-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('friend_request', 'friend_accepted', 'message', 'system', 'like', 'comment') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Insert some sample notifications
INSERT INTO notifications (user_id, type, title, message, data) VALUES
(1, 'friend_request', 'New Friend Request', 'testuser sent you a friend request', '{"from_user_id": 2, "from_username": "testuser"}'),
(1, 'message', 'New Message', 'You have a new message from testuser', '{"from_user_id": 2, "from_username": "testuser", "message_id": 1}'),
(2, 'friend_accepted', 'Friend Request Accepted', 'admin accepted your friend request', '{"from_user_id": 1, "from_username": "admin"}'),
(2, 'system', 'Welcome!', 'Welcome to IslamWiki! Start exploring the community.', '{}');
