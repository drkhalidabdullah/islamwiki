<?php
/**
 * Rate Limiter Class
 * Provides rate limiting functionality to prevent abuse
 */

class RateLimiter {
    private $pdo;
    private $config;
    
    public function __construct($pdo, $config = []) {
        $this->pdo = $pdo;
        $this->config = array_merge([
            'wiki_views' => ['limit' => 100, 'window' => 3600], // 100 views per hour
            'search_queries' => ['limit' => 50, 'window' => 3600], // 50 searches per hour
            'api_requests' => ['limit' => 200, 'window' => 3600], // 200 API calls per hour
            'registration_attempts' => ['limit' => 5, 'window' => 86400], // 5 attempts per day
            'login_attempts' => ['limit' => 10, 'window' => 3600], // 10 attempts per hour
            'report_content' => ['limit' => 20, 'window' => 3600], // 20 reports per hour
        ], $config);
    }
    
    /**
     * Check if an action is allowed for the given IP
     */
    public function isAllowed($action, $ip = null) {
        if (!$ip) {
            $ip = $this->getClientIP();
        }
        
        if (!isset($this->config[$action])) {
            return true; // No limit configured
        }
        
        $limit = $this->config[$action]['limit'];
        $window = $this->config[$action]['window'];
        
        // Clean old records
        $this->cleanOldRecords($action, $window);
        
        // Check current count
        $count = $this->getCurrentCount($action, $ip, $window);
        
        if ($count >= $limit) {
            return false;
        }
        
        // Record this request
        $this->recordRequest($action, $ip);
        
        return true;
    }
    
    /**
     * Get remaining requests for an action
     */
    public function getRemainingRequests($action, $ip = null) {
        if (!$ip) {
            $ip = $this->getClientIP();
        }
        
        if (!isset($this->config[$action])) {
            return PHP_INT_MAX;
        }
        
        $limit = $this->config[$action]['limit'];
        $window = $this->config[$action]['window'];
        
        $count = $this->getCurrentCount($action, $ip, $window);
        
        return max(0, $limit - $count);
    }
    
    /**
     * Get time until rate limit resets
     */
    public function getResetTime($action, $ip = null) {
        if (!$ip) {
            $ip = $this->getClientIP();
        }
        
        if (!isset($this->config[$action])) {
            return 0;
        }
        
        $window = $this->config[$action]['window'];
        
        $stmt = $this->pdo->prepare("
            SELECT MIN(window_start) as oldest_request 
            FROM rate_limits 
            WHERE ip_address = ? AND action_type = ? AND window_start > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $action, $window]);
        $result = $stmt->fetch();
        
        if (!$result || !$result['oldest_request']) {
            return 0;
        }
        
        $resetTime = strtotime($result['oldest_request']) + $window;
        return max(0, $resetTime - time());
    }
    
    /**
     * Get current request count for an action
     */
    private function getCurrentCount($action, $ip, $window) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(request_count) as total_count 
            FROM rate_limits 
            WHERE ip_address = ? AND action_type = ? AND window_start > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $action, $window]);
        $result = $stmt->fetch();
        
        return (int)($result['total_count'] ?? 0);
    }
    
    /**
     * Record a request
     */
    private function recordRequest($action, $ip) {
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (ip_address, action_type, request_count, window_start) 
            VALUES (?, ?, 1, NOW()) 
            ON DUPLICATE KEY UPDATE request_count = request_count + 1
        ");
        $stmt->execute([$ip, $action]);
    }
    
    /**
     * Clean old records
     */
    private function cleanOldRecords($action, $window) {
        $stmt = $this->pdo->prepare("
            DELETE FROM rate_limits 
            WHERE action_type = ? AND window_start < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$action, $window]);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get rate limit status for display
     */
    public function getRateLimitStatus($action, $ip = null) {
        if (!$ip) {
            $ip = $this->getClientIP();
        }
        
        $remaining = $this->getRemainingRequests($action, $ip);
        $resetTime = $this->getResetTime($action, $ip);
        $isAllowed = $this->isAllowed($action, $ip);
        
        return [
            'allowed' => $isAllowed,
            'remaining' => $remaining,
            'reset_time' => $resetTime,
            'limit' => $this->config[$action]['limit'] ?? 0,
            'window' => $this->config[$action]['window'] ?? 0
        ];
    }
}

/**
 * Rate limiting helper functions
 */

function check_rate_limit($action, $ip = null) {
    global $pdo;
    static $rateLimiter = null;
    
    if (!$rateLimiter) {
        $rateLimiter = new RateLimiter($pdo);
    }
    
    return $rateLimiter->isAllowed($action, $ip);
}

function get_rate_limit_status($action, $ip = null) {
    global $pdo;
    static $rateLimiter = null;
    
    if (!$rateLimiter) {
        $rateLimiter = new RateLimiter($pdo);
    }
    
    return $rateLimiter->getRateLimitStatus($action, $ip);
}

function enforce_rate_limit($action, $ip = null, $error_message = 'Rate limit exceeded. Please try again later.') {
    if (!check_rate_limit($action, $ip)) {
        $status = get_rate_limit_status($action, $ip);
        $resetTime = $status['reset_time'];
        $hours = floor($resetTime / 3600);
        $minutes = floor(($resetTime % 3600) / 60);
        
        $timeMessage = '';
        if ($hours > 0) {
            $timeMessage = "Try again in {$hours} hour" . ($hours > 1 ? 's' : '');
            if ($minutes > 0) {
                $timeMessage .= " and {$minutes} minute" . ($minutes > 1 ? 's' : '');
            }
        } elseif ($minutes > 0) {
            $timeMessage = "Try again in {$minutes} minute" . ($minutes > 1 ? 's' : '');
        } else {
            $timeMessage = "Try again in a few seconds";
        }
        
        http_response_code(429);
        die(json_encode([
            'error' => true,
            'message' => $error_message . ' ' . $timeMessage,
            'retry_after' => $resetTime
        ]));
    }
}
?>
