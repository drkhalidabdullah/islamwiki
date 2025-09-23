-- Badge System Migration
-- Create badges table
CREATE TABLE IF NOT EXISTS badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    long_description TEXT,
    icon VARCHAR(255) DEFAULT 'fas fa-medal',
    color VARCHAR(7) DEFAULT '#f39c12',
    rarity ENUM('common','uncommon','rare','epic','legendary') DEFAULT 'common',
    points INT NOT NULL DEFAULT 0,
    xp_reward INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_hidden TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create badge requirements table (which achievements are needed)
CREATE TABLE IF NOT EXISTS badge_requirements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    badge_id BIGINT UNSIGNED NOT NULL,
    achievement_id BIGINT UNSIGNED NOT NULL,
    is_required TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_badge_achievement (badge_id, achievement_id)
);

-- Create user badges table
CREATE TABLE IF NOT EXISTS user_badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_displayed TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_badge (user_id, badge_id)
);

-- Insert some example badges
INSERT INTO badges (name, slug, description, long_description, icon, color, rarity, points, xp_reward, sort_order) VALUES
-- Social Badges
('Social Butterfly', 'social-butterfly', 'Make 5 friends and post 10 status updates', 'Connect with the community by making friends and sharing your thoughts', 'fas fa-users', '#e74c3c', 'uncommon', 50, 100, 1),
('Community Builder', 'community-builder', 'Make 10 friends and post 20 status updates', 'Become a pillar of the community through active social engagement', 'fas fa-building', '#9b59b6', 'rare', 100, 200, 2),

-- Content Creation Badges  
('Content Creator', 'content-creator', 'Create 5 posts and 1 article', 'Share your knowledge and thoughts with the community', 'fas fa-pen', '#3498db', 'uncommon', 75, 150, 3),
('Knowledge Sharer', 'knowledge-sharer', 'Create 10 posts and 3 articles', 'Become a trusted source of information and insights', 'fas fa-book', '#2ecc71', 'rare', 150, 300, 4),

-- Profile Badges
('Profile Master', 'profile-master', 'Complete your profile and make 5 friends', 'Show your commitment to the community with a complete profile', 'fas fa-user-check', '#f39c12', 'uncommon', 60, 120, 5),
('Public Figure', 'public-figure', 'Complete profile, make 10 friends, and create 5 posts', 'Establish yourself as an active and engaged community member', 'fas fa-star', '#e67e22', 'rare', 200, 400, 6),

-- Achievement Badges
('Achievement Hunter', 'achievement-hunter', 'Complete 10 achievements', 'Show your dedication to personal growth and community engagement', 'fas fa-trophy', '#e74c3c', 'uncommon', 100, 200, 7),
('Achievement Master', 'achievement-master', 'Complete 25 achievements', 'Demonstrate exceptional commitment to the platform and community', 'fas fa-crown', '#9b59b6', 'epic', 300, 600, 8),
('Achievement Legend', 'achievement-legend', 'Complete 50 achievements', 'Reach the pinnacle of achievement mastery', 'fas fa-gem', '#f1c40f', 'legendary', 500, 1000, 9),

-- Early User Badges
('Early Adopter', 'early-adopter', 'Join the platform and complete your first achievement', 'Be among the first to experience the platform', 'fas fa-rocket', '#3498db', 'rare', 150, 300, 10),
('Pioneer', 'pioneer', 'Join early and complete 5 achievements', 'Help build the foundation of our community', 'fas fa-flag', '#2ecc71', 'epic', 250, 500, 11),

-- Special Badges
('Well Rounded', 'well-rounded', 'Complete achievements in 3 different categories', 'Show versatility across different areas of engagement', 'fas fa-balance-scale', '#8e44ad', 'uncommon', 80, 160, 12),
('Category Master', 'category-master', 'Complete achievements in 5 different categories', 'Demonstrate expertise across multiple areas', 'fas fa-layer-group', '#34495e', 'rare', 200, 400, 13);

-- Insert badge requirements
INSERT INTO badge_requirements (badge_id, achievement_id, is_required, sort_order) VALUES
-- Social Butterfly: First Friend + Status Starter (and 3 more friends, 9 more status)
(1, (SELECT id FROM achievements WHERE slug = 'first-friend'), 1, 1),
(1, (SELECT id FROM achievements WHERE slug = 'status-starter'), 1, 2),

-- Community Builder: Social Butterfly requirements + more
(2, (SELECT id FROM achievements WHERE slug = 'first-friend'), 1, 1),
(2, (SELECT id FROM achievements WHERE slug = 'status-starter'), 1, 2),

-- Content Creator: First Article + Content Creator I
(3, (SELECT id FROM achievements WHERE slug = 'first-article'), 1, 1),
(3, (SELECT id FROM achievements WHERE slug = 'content-creator-1'), 1, 2),

-- Knowledge Sharer: Content Creator requirements + more
(4, (SELECT id FROM achievements WHERE slug = 'first-article'), 1, 1),
(4, (SELECT id FROM achievements WHERE slug = 'content-creator-1'), 1, 2),

-- Profile Master: Profile Perfect + First Friend
(5, (SELECT id FROM achievements WHERE slug = 'profile-perfect'), 1, 1),
(5, (SELECT id FROM achievements WHERE slug = 'first-friend'), 1, 2),

-- Public Figure: Profile Master + Content Creator
(6, (SELECT id FROM achievements WHERE slug = 'profile-perfect'), 1, 1),
(6, (SELECT id FROM achievements WHERE slug = 'first-friend'), 1, 2),
(6, (SELECT id FROM achievements WHERE slug = 'content-creator-1'), 1, 3),

-- Achievement Hunter: Complete 10 achievements (this will be checked programmatically)
(7, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1),

-- Achievement Master: Complete 25 achievements (this will be checked programmatically)
(8, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1),

-- Achievement Legend: Complete 50 achievements (this will be checked programmatically)
(9, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1),

-- Early Adopter: First Login + any other achievement
(10, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1),
(10, (SELECT id FROM achievements WHERE slug = 'profile-perfect'), 1, 2),

-- Pioneer: Early Adopter + more achievements
(11, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1),
(11, (SELECT id FROM achievements WHERE slug = 'profile-perfect'), 1, 2),
(11, (SELECT id FROM achievements WHERE slug = 'first-friend'), 1, 3),

-- Well Rounded: Achievements from different categories (checked programmatically)
(12, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1),

-- Category Master: Achievements from 5 categories (checked programmatically)
(13, (SELECT id FROM achievements WHERE slug = 'first-login'), 1, 1);
