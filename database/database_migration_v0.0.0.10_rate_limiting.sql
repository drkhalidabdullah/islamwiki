-- Database Migration v0.0.0.10 - Rate Limiting and Security Enhancements
-- Created: 2025-01-08
-- Description: Adds rate limiting, content moderation, and security enhancements

-- Rate limiting table
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip_address, action_type),
    INDEX idx_window_start (window_start),
    INDEX idx_action_window (action_type, window_start)
);

-- Content reports table
CREATE TABLE IF NOT EXISTS content_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_id BIGINT UNSIGNED NULL,
    reporter_ip VARCHAR(45) NOT NULL,
    content_type ENUM('wiki_article', 'user_post', 'comment', 'user_profile') NOT NULL,
    content_id INT NOT NULL,
    report_reason ENUM('spam', 'inappropriate', 'harassment', 'copyright', 'other') NOT NULL,
    report_description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_content (content_type, content_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- User security table for tracking failed attempts
CREATE TABLE IF NOT EXISTS user_security (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NOT NULL,
    action_type ENUM('login_failed', 'login_success', 'password_reset', 'account_locked', 'account_unlocked') NOT NULL,
    user_agent TEXT,
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_ip (user_id, ip_address),
    INDEX idx_action_type (action_type),
    INDEX idx_created (created_at)
);

-- Content quality metrics table
CREATE TABLE IF NOT EXISTS article_quality_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id BIGINT UNSIGNED NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(5,2) NOT NULL,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES wiki_articles(id) ON DELETE CASCADE,
    INDEX idx_article_metric (article_id, metric_type),
    INDEX idx_calculated (calculated_at)
);

-- Add quality score column to wiki_articles
ALTER TABLE wiki_articles 
ADD COLUMN IF NOT EXISTS quality_score INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_validated TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS is_flagged BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS flag_reason TEXT NULL;

-- Add security columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_failed_login TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS is_locked BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS two_factor_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(32) NULL;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_locked ON users(is_locked, locked_until);
CREATE INDEX IF NOT EXISTS idx_users_failed_logins ON users(failed_login_attempts, last_failed_login);
CREATE INDEX IF NOT EXISTS idx_wiki_articles_quality ON wiki_articles(quality_score, is_flagged);

-- Insert default rate limiting configuration
INSERT IGNORE INTO system_settings (`key`, value, type, description) VALUES
('rate_limit_wiki_views', '100', 'integer', 'Maximum wiki article views per IP per hour'),
('rate_limit_search_queries', '50', 'integer', 'Maximum search queries per IP per hour'),
('rate_limit_api_requests', '200', 'integer', 'Maximum API requests per IP per hour'),
('rate_limit_registration_attempts', '5', 'integer', 'Maximum registration attempts per IP per day'),
('rate_limit_login_attempts', '10', 'integer', 'Maximum login attempts per IP per hour'),
('rate_limit_report_content', '20', 'integer', 'Maximum content reports per IP per hour'),
('account_lockout_attempts', '5', 'integer', 'Number of failed login attempts before account lockout'),
('account_lockout_duration', '1800', 'integer', 'Account lockout duration in seconds (30 minutes)'),
('content_moderation_enabled', '1', 'boolean', 'Enable content moderation system'),
('quality_score_threshold', '5', 'integer', 'Minimum quality score for articles to be featured');

-- Create a view for rate limit monitoring
CREATE OR REPLACE VIEW rate_limit_monitoring AS
SELECT 
    action_type,
    ip_address,
    SUM(request_count) as total_requests,
    MIN(window_start) as first_request,
    MAX(window_start) as last_request,
    COUNT(*) as request_sessions
FROM rate_limits 
WHERE window_start > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY action_type, ip_address
ORDER BY total_requests DESC;

-- Create a view for content reports summary
CREATE OR REPLACE VIEW content_reports_summary AS
SELECT 
    content_type,
    report_reason,
    status,
    COUNT(*) as report_count,
    COUNT(DISTINCT content_id) as unique_content_reported,
    MIN(created_at) as first_report,
    MAX(created_at) as last_report
FROM content_reports 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY content_type, report_reason, status
ORDER BY report_count DESC;
