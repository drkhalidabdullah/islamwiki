<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request URI and extract the endpoint
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove the /api prefix to get the endpoint
$endpoint = str_replace('/api', '', $path);
$endpoint = trim($endpoint, '/');

// Database connection class
class DatabaseConnection {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=localhost;dbname=islamwiki;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Authentication service class
class AuthService {
    private $db;
    
    public function __construct(DatabaseConnection $dbConnection) {
        $this->db = $dbConnection->getConnection();
    }
    
    public function authenticate($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, GROUP_CONCAT(r.name) as roles
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.email = ?
                GROUP BY u.id
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last login and last seen
                $updateStmt = $this->db->prepare("
                    UPDATE users 
                    SET last_login_at = NOW(), last_seen_at = NOW() 
                    WHERE id = ?
                ");
                $updateStmt->execute([$user['id']]);
                
                // Convert roles string to array
                $user['roles'] = $user['roles'] ? explode(',', $user['roles']) : ['user'];
                
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception('Authentication failed: ' . $e->getMessage());
        }
    }
}

// Generate JWT-like token
function generateMockJWT($userId, $username, $role) {
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode([
        'sub' => (string)$userId,
        'username' => $username,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ]));
    $signature = base64_encode('mock_signature_' . $userId . '_' . time());
    
    return $header . '.' . $payload . '.' . $signature;
}

// Route the request based on endpoint
try {
    if ($endpoint === 'admin') {
        // Admin dashboard data
        $dbConnection = new DatabaseConnection();
        $pdo = $dbConnection->getConnection();
        
        // Get user statistics
        $userStats = $pdo->query("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_users_today
            FROM users
        ")->fetch();
        
        // Get role statistics
        $roleStats = $pdo->query("
            SELECT r.name as role_name, COUNT(*) as user_count
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            GROUP BY r.name
            ORDER BY user_count DESC
        ")->fetchAll();
        
        // Get recent activity
        $recentActivity = $pdo->query("
            SELECT u.username, u.display_name, u.last_login_at, u.last_seen_at, 
                   GROUP_CONCAT(r.name) as roles
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.last_login_at IS NOT NULL
            GROUP BY u.id
            ORDER BY u.last_login_at DESC
            LIMIT 10
        ")->fetchAll();
        
        // Get system info
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'mysql_version' => $pdo->query('SELECT VERSION() as version')->fetch()['version'],
            'server_time' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'user_statistics' => $userStats,
                'role_statistics' => $roleStats,
                'recent_activity' => $recentActivity,
                'system_info' => $systemInfo,
                'version' => 'v0.0.5.1',
                'status' => 'operational'
            ]
        ]);
        
    } elseif ($endpoint === 'health') {
        // Health check endpoint
        echo json_encode([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'timestamp' => date('c'),
                'version' => 'v0.0.5.1'
            ]
        ]);
        
    } elseif ($endpoint === 'user/settings') {
        // User settings endpoint
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get user settings
            $headers = getallheaders();
            $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
            
            if (empty($token)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit();
            }
            
            try {
                $dbConnection = new DatabaseConnection();
                $pdo = $dbConnection->getConnection();
                
                // Decode token to get user ID (simplified for demo)
                $tokenParts = explode('.', $token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode($tokenParts[1]), true);
                    $userId = $payload['sub'] ?? null;
                    
                    if ($userId) {
                        // Get user data
                        $stmt = $pdo->prepare("
                            SELECT u.*, uss.*, u.preferences as user_preferences
                            FROM users u
                            LEFT JOIN user_security_settings uss ON u.id = uss.user_id
                            WHERE u.id = ?
                        ");
                        $stmt->execute([$userId]);
                        $user = $stmt->fetch();
                        
                        if ($user) {
                            $preferences = json_decode($user['user_preferences'] ?? '{}', true);
                            
                            $settings = [
                                'account' => [
                                    'username' => $user['username'],
                                    'email' => $user['email'],
                                    'first_name' => $user['first_name'],
                                    'last_name' => $user['last_name'],
                                    'phone' => $user['phone'] ?? '',
                                    'date_of_birth' => $user['date_of_birth'] ?? '',
                                    'gender' => $user['gender'] ?? '',
                                    'location' => $user['location'] ?? '',
                                    'website' => $user['website'] ?? '',
                                    'bio' => $user['bio'] ?? '',
                                    'display_name' => $user['display_name'] ?? $user['username'],
                                    'avatar_url' => $user['avatar_url'] ?? '',
                                    'social_links' => json_decode($user['social_links'] ?? '{}', true)
                                ],
                                'preferences' => array_merge([
                                    'email_notifications' => true,
                                    'push_notifications' => true,
                                    'profile_public' => true,
                                    'show_email' => false,
                                    'show_last_seen' => true,
                                    'language' => 'en',
                                    'timezone' => 'UTC',
                                    'theme' => 'auto',
                                    'content_language' => 'en',
                                    'notification_sound' => true,
                                    'email_digest' => 'weekly',
                                    'content_preferences' => [
                                        'show_nsfw_content' => false,
                                        'content_rating' => 'G',
                                        'auto_translate' => false,
                                        'translation_language' => 'en'
                                    ]
                                ], $preferences),
                                'security' => [
                                    'two_factor_enabled' => (bool)($user['two_factor_enabled'] ?? false),
                                    'two_factor_method' => $user['two_factor_method'] ?? 'totp',
                                    'session_timeout' => (int)($user['session_timeout'] ?? 3600),
                                    'login_notifications' => (bool)($user['login_notifications'] ?? true),
                                    'password_change_required' => false,
                                    'security_alerts' => (bool)($user['security_alerts'] ?? true),
                                    'max_concurrent_sessions' => (int)($user['max_concurrent_sessions'] ?? 5),
                                    'trusted_devices' => json_decode($user['trusted_devices'] ?? '[]', true),
                                    'security_questions' => json_decode($user['security_questions'] ?? '[]', true)
                                ],
                                'privacy' => [
                                    'profile_visibility' => 'public',
                                    'activity_visibility' => 'friends',
                                    'search_visibility' => true,
                                    'analytics_consent' => true,
                                    'data_export' => false,
                                    'data_deletion' => false,
                                    'third_party_sharing' => false,
                                    'location_sharing' => false,
                                    'contact_info_visibility' => 'friends'
                                ],
                                'notifications' => [
                                    'content_updates' => true,
                                    'comment_replies' => true,
                                    'mentions' => true,
                                    'new_followers' => true,
                                    'security_alerts' => true,
                                    'system_announcements' => true,
                                    'marketing_emails' => false,
                                    'digest_frequency' => 'weekly'
                                ],
                                'accessibility' => [
                                    'high_contrast' => false,
                                    'large_text' => false,
                                    'screen_reader_support' => true,
                                    'keyboard_navigation' => true,
                                    'reduced_motion' => false,
                                    'color_blind_support' => false,
                                    'font_size' => 'medium'
                                ]
                            ];
                            
                            echo json_encode([
                                'success' => true,
                                'data' => $settings
                            ]);
                        } else {
                            http_response_code(404);
                            echo json_encode([
                                'success' => false,
                                'error' => 'User not found'
                            ]);
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Invalid token'
                        ]);
                    }
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid token format'
                    ]);
                }
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to get settings: ' . $e->getMessage()
                ]);
            }
            
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            // Update user settings
            $headers = getallheaders();
            $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
            
            if (empty($token)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit();
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                $section = $input['section'] ?? '';
                $data = $input['data'] ?? [];
                
                if (empty($section) || empty($data)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Section and data are required'
                    ]);
                    exit();
                }
                
                $dbConnection = new DatabaseConnection();
                $pdo = $dbConnection->getConnection();
                
                // Decode token to get user ID
                $tokenParts = explode('.', $token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode($tokenParts[1]), true);
                    $userId = $payload['sub'] ?? null;
                    
                    if ($userId) {
                        if ($section === 'account') {
                            // Update user account information
                            $stmt = $pdo->prepare("
                                UPDATE users SET 
                                    username = ?, email = ?, first_name = ?, last_name = ?,
                                    phone = ?, date_of_birth = ?, gender = ?, location = ?,
                                    website = ?, bio = ?, display_name = ?, updated_at = NOW()
                                WHERE id = ?
                            ");
                            $stmt->execute([
                                $data['username'] ?? '',
                                $data['email'] ?? '',
                                $data['first_name'] ?? '',
                                $data['last_name'] ?? '',
                                $data['phone'] ?? '',
                                $data['date_of_birth'] ?? '',
                                $data['gender'] ?? '',
                                $data['location'] ?? '',
                                $data['website'] ?? '',
                                $data['bio'] ?? '',
                                $data['display_name'] ?? '',
                                $userId
                            ]);
                        } elseif ($section === 'preferences') {
                            // Update user preferences
                            $stmt = $pdo->prepare("
                                UPDATE users SET 
                                    preferences = ?, updated_at = NOW()
                                WHERE id = ?
                            ");
                            $stmt->execute([
                                json_encode($data),
                                $userId
                            ]);
                        } elseif ($section === 'security') {
                            // Update security settings
                            $stmt = $pdo->prepare("
                                UPDATE user_security_settings SET 
                                    two_factor_enabled = ?, two_factor_method = ?, session_timeout = ?,
                                    login_notifications = ?, security_alerts = ?, max_concurrent_sessions = ?,
                                    trusted_devices = ?, security_questions = ?, updated_at = NOW()
                                WHERE user_id = ?
                            ");
                            $stmt->execute([
                                $data['two_factor_enabled'] ?? false,
                                $data['two_factor_method'] ?? 'totp',
                                $data['session_timeout'] ?? 3600,
                                $data['login_notifications'] ?? true,
                                $data['security_alerts'] ?? true,
                                $data['max_concurrent_sessions'] ?? 5,
                                json_encode($data['trusted_devices'] ?? []),
                                json_encode($data['security_questions'] ?? []),
                                $userId
                            ]);
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Settings updated successfully'
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Invalid token'
                        ]);
                    }
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid token format'
                    ]);
                }
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update settings: ' . $e->getMessage()
                ]);
            }
            
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed. Use GET or PUT.'
            ]);
        }
        
    } elseif ($endpoint === '') {
        // Root API endpoint - handle login/register
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            if ($action === 'login') {
                $email = $input['email'] ?? '';
                $password = $input['password'] ?? '';
                
                if (empty($email) || empty($password)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Email and password are required'
                    ]);
                    exit();
                }
                
                try {
                    $dbConnection = new DatabaseConnection();
                    $authService = new AuthService($dbConnection);
                    $user = $authService->authenticate($email, $password);
                    
                    if ($user) {
                        $token = generateMockJWT($user['id'], $user['username'], $user['roles'][0]);
                        
                        echo json_encode([
                            'success' => true,
                            'data' => [
                                'user' => [
                                    'id' => $user['id'],
                                    'username' => $user['username'],
                                    'email' => $user['email'],
                                    'first_name' => $user['first_name'],
                                    'last_name' => $user['last_name'],
                                    'display_name' => $user['display_name'] ?? $user['username'],
                                    'roles' => $user['roles'],
                                    'is_active' => $user['is_active']
                                ],
                                'token' => $token,
                                'expires_at' => time() + (60 * 60 * 24)
                            ],
                            'message' => 'Login successful'
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Invalid credentials'
                        ]);
                    }
                    
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Authentication failed: ' . $e->getMessage()
                    ]);
                }
                
            } elseif ($action === 'register') {
                http_response_code(501);
                echo json_encode([
                    'success' => false,
                    'error' => 'Registration not implemented yet'
                ]);
                
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid action. Use "login" or "register"'
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed. Use POST with action parameter.'
            ]);
        }
        
    } else {
        // Unknown endpoint
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Endpoint not found: ' . $endpoint
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
}
?> 