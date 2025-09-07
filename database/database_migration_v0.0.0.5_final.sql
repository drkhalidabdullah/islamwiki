-- Migration for v0.0.0.5 - Messages System (Final)
-- Add messages table for chat functionality

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT(20) UNSIGNED NOT NULL,
    recipient_id BIGINT(20) UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender_recipient (sender_id, recipient_id),
    INDEX idx_recipient_sender (recipient_id, sender_id),
    INDEX idx_created_at (created_at)
);

-- Add some sample messages for testing
INSERT INTO messages (sender_id, recipient_id, message, created_at) VALUES
(1, 2, 'Assalamu alaikum! How are you doing?', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 1, 'Wa alaikum assalam! I am doing well, thank you. How about you?', DATE_SUB(NOW(), INTERVAL 50 MINUTE)),
(1, 2, 'Alhamdulillah, I am also doing well. Just working on some projects.', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, 1, 'That sounds great! What kind of projects are you working on?', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(1, 3, 'Hello! I hope you are having a good day.', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 1, 'Hello! Yes, I am having a wonderful day. Thank you for asking!', DATE_SUB(NOW(), INTERVAL 105 MINUTE));
