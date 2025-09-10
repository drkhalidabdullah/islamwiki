-- Database Migration v0.0.0.10 - Analytics and Monitoring
-- Created: 2025-01-08
-- Description: Adds analytics tracking tables for monitoring and insights

-- Page views tracking
CREATE TABLE IF NOT EXISTS page_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    page VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    referrer VARCHAR(500),
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_page (page),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address)
);

-- User actions tracking
CREATE TABLE IF NOT EXISTS user_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Performance metrics tracking
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric VARCHAR(100) NOT NULL,
    value DECIMAL(10,3) NOT NULL,
    page VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric (metric),
    INDEX idx_created_at (created_at),
    INDEX idx_page (page)
);

-- Search analytics
CREATE TABLE IF NOT EXISTS search_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    query VARCHAR(500) NOT NULL,
    results_count INT DEFAULT 0,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_query (query(100)),
    INDEX idx_created_at (created_at)
);

-- Content interactions tracking
CREATE TABLE IF NOT EXISTS content_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    content_type VARCHAR(50) NOT NULL,
    content_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_content (content_type, content_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Error logging table
CREATE TABLE IF NOT EXISTS error_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    file VARCHAR(255),
    line INT,
    context JSON,
    ip_address VARCHAR(45),
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_level (level),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
);

-- Analytics views for easy reporting
CREATE OR REPLACE VIEW daily_analytics AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as page_views,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(DISTINCT ip_address) as unique_ips
FROM page_views
WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

CREATE OR REPLACE VIEW popular_pages AS
SELECT 
    page,
    COUNT(*) as views,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(DISTINCT ip_address) as unique_ips,
    AVG(CASE WHEN JSON_EXTRACT(additional_data, '$.response_time') IS NOT NULL 
        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(additional_data, '$.response_time')) AS DECIMAL(10,3)) 
        ELSE NULL END) as avg_response_time
FROM page_views
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY page
ORDER BY views DESC;

CREATE OR REPLACE VIEW search_trends AS
SELECT 
    query,
    COUNT(*) as search_count,
    AVG(results_count) as avg_results,
    COUNT(DISTINCT user_id) as unique_searchers,
    MAX(created_at) as last_searched
FROM search_analytics
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY query
ORDER BY search_count DESC;

CREATE OR REPLACE VIEW content_popularity AS
SELECT 
    content_type,
    content_id,
    COUNT(*) as interactions,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(DISTINCT action) as action_types
FROM content_interactions
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY content_type, content_id
ORDER BY interactions DESC;

-- Insert default analytics settings
INSERT IGNORE INTO system_settings (`key`, value, type, description) VALUES
('analytics_enabled', '1', 'boolean', 'Enable analytics tracking'),
('analytics_retention_days', '90', 'integer', 'Number of days to retain analytics data'),
('performance_tracking', '1', 'boolean', 'Enable performance metrics tracking'),
('search_analytics', '1', 'boolean', 'Enable search analytics tracking'),
('error_logging', '1', 'boolean', 'Enable error logging'),
('privacy_mode', '0', 'boolean', 'Enable privacy mode (anonymize IP addresses)');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_page_views_user_date ON page_views(user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_user_actions_user_date ON user_actions(user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_performance_metric_date ON performance_metrics(metric, created_at);
CREATE INDEX IF NOT EXISTS idx_search_analytics_date ON search_analytics(created_at);
CREATE INDEX IF NOT EXISTS idx_content_interactions_date ON content_interactions(created_at);
