<?php
/**
 * IslamWiki Framework - API Entry Point
 * Author: Khalid Abdullah
 * Version: 0.0.5
 * Date: 2025-01-27
 * License: AGPL-3.0
 */

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection class
class DatabaseConnection {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=localhost;dbname=islamwiki;charset=utf8mb4',
                'root',
                '', // Empty password as configured
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Authentication service class
class AuthService {
    private $db;
    
    public function __construct(DatabaseConnection $db) {
        $this->db = $db;
    }
    
    public function authenticate($email, $password) {
        try {
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare("
                SELECT u.*, GROUP_CONCAT(r.name) as roles
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.email = ? AND u.is_active = 1 
                GROUP BY u.id
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
            
            // Update last login and last seen
            $updateStmt = $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_seen_at = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            $roles = $user['roles'] ? explode(',', $user['roles']) : ['user'];
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'display_name' => $user['display_name'],
                    'roles' => $roles,
                    'is_active' => (bool)$user['is_active']
                ]
            ];
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Authentication failed'];
        }
    }
    
    public function getUserById($userId) {
        try {
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare("
                SELECT u.*, GROUP_CONCAT(r.name) as roles
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.id = ? 
                GROUP BY u.id
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
}

// JWT token generation (mock for now)
function generateMockJWT($userId, $username, $role) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'sub' => (string)$userId,
        'username' => $username,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + 3600
    ]);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    // Mock signature
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, 'mock_secret_key');
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

// Initialize database and auth service
try {
    $db = new DatabaseConnection();
    $authService = new AuthService($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Service temporarily unavailable'
    ]);
    exit();
}

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Simple endpoint detection for /api/admin
$endpoint = '';
if ($path === '/api/admin') {
    $endpoint = 'admin';
} elseif ($path === '/api/health') {
    $endpoint = 'health';
} elseif ($path === '/api') {
    $endpoint = '';
} elseif (strpos($path, '/api/') === 0) {
    // Extract endpoint from /api/{endpoint}
    $endpoint = substr($path, 5); // Remove '/api/' prefix
}

// Get POST data for action-based routing
$post_data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    if ($input) {
        $post_data = json_decode($input, true) ?: [];
    }
}

// Route the request
$method = $_SERVER['REQUEST_METHOD'];

// Handle action-based routing for POST requests
if ($method === 'POST' && isset($post_data['action'])) {
    $action = $post_data['action'];
    
    switch ($action) {
        case 'login':
            if (isset($post_data['email']) && isset($post_data['password'])) {
                // Real authentication
                $authResult = $authService->authenticate($post_data['email'], $post_data['password']);
                
                if ($authResult['success']) {
                    $user = $authResult['user'];
                    $primaryRole = $user['roles'][0] ?? 'user';
                    
                    // Generate JWT token
                    $token = generateMockJWT($user['id'], $user['username'], $primaryRole);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'user' => $user,
                            'token' => $token,
                            'expires_at' => time() + 3600
                        ],
                        'message' => 'Login successful'
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => $authResult['error']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Email and password are required'
                ]);
            }
            exit();
            
        case 'register':
            // For now, return error - registration not implemented yet
            http_response_code(501);
            echo json_encode([
                'success' => false,
                'error' => 'User registration not yet implemented'
            ]);
            exit();
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action specified'
            ]);
            exit();
    }
}

// Simple routing for direct endpoint access
switch ($endpoint) {
    case '':
    case 'health':
        echo json_encode([
            'success' => true,
            'message' => 'IslamWiki API is running',
            'timestamp' => date('c'),
            'version' => '0.0.5'
        ]);
        break;
        
    case 'admin':
        if ($method === 'GET') {
            // Admin dashboard data endpoint
            try {
                $pdo = $db->getConnection();
                
                // Get user statistics
                $userStats = $pdo->query("
                    SELECT 
                        COUNT(*) as total_users,
                        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users,
                        COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_users,
                        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_users_today
                    FROM users
                ")->fetch();
                
                // Get role statistics
                $roleStats = $pdo->query("
                    SELECT 
                        r.name as role_name,
                        COUNT(ur.user_id) as user_count
                    FROM roles r
                    LEFT JOIN user_roles ur ON r.id = ur.role_id
                    GROUP BY r.id, r.name
                    ORDER BY user_count DESC
                ")->fetchAll();
                
                // Get recent user activity
                $recentActivity = $pdo->query("
                    SELECT 
                        u.username,
                        u.display_name,
                        u.last_login_at,
                        u.last_seen_at,
                        GROUP_CONCAT(r.name) as roles
                    FROM users u
                    LEFT JOIN user_roles ur ON u.id = ur.user_id
                    LEFT JOIN roles r ON ur.role_id = r.id
                    WHERE u.is_active = 1
                    GROUP BY u.id
                    ORDER BY u.last_seen_at DESC
                    LIMIT 10
                ")->fetchAll();
                
                // Get system information
                $systemInfo = [
                    'php_version' => PHP_VERSION,
                    'mysql_version' => $pdo->query('SELECT VERSION() as version')->fetch()['version'],
                    'server_time' => date('c'),
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
                        'version' => '0.0.5',
                        'status' => 'operational'
                    ]
                ]);
                
            } catch (Exception $e) {
                error_log("Admin data error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to retrieve admin data'
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
        }
        break;
        
    case 'auth':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['email']) && isset($data['password'])) {
                // Real authentication
                $authResult = $authService->authenticate($data['email'], $data['password']);
                
                if ($authResult['success']) {
                    $user = $authResult['user'];
                    $primaryRole = $user['roles'][0] ?? 'user';
                    
                    // Generate JWT token
                    $token = generateMockJWT($user['id'], $user['username'], $primaryRole);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'user' => $user,
                            'token' => $token,
                            'expires_at' => time() + 3600
                        ],
                        'message' => 'Login successful'
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => $authResult['error']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Email and password are required'
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
        }
        break;
        
    case 'login':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['email']) && isset($data['password'])) {
                // Real authentication
                $authResult = $authService->authenticate($data['email'], $data['password']);
                
                if ($authResult['success']) {
                    $user = $authResult['user'];
                    $primaryRole = $user['roles'][0] ?? 'user';
                    
                    // Generate JWT token
                    $token = generateMockJWT($user['id'], $user['username'], $primaryRole);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'user' => $user,
                            'token' => $token,
                            'expires_at' => time() + 3600
                        ],
                        'message' => 'Login successful'
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => $authResult['error']
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Email and password are required'
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Endpoint not found',
            'requested_endpoint' => $endpoint,
            'method' => $method
        ]);
        break;
} 