<?php
session_start();

// Set language preference
function setLanguagePreference($langCode) {
    global $languages;
    
    if (!isset($languages[$langCode])) {
        return false;
    }
    
    // Set session
    $_SESSION['language'] = $langCode;
    
    // Set cookie (1 year)
    setcookie('language', $langCode, time() + (365 * 24 * 60 * 60), '/');
    
    return true;
}


/**
 * User-Specific Language API Endpoints
 * 
 * Handles language switching and detection with user-specific preferences
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

// Mock language data
$languages = [
    'en' => [
        'code' => 'en',
        'name' => 'English',
        'native_name' => 'English',
        'direction' => 'ltr',
        'flag' => '��🇸',
        'is_active' => true,
        'is_default' => true
    ],
    'ar' => [
        'code' => 'ar',
        'name' => 'Arabic',
        'native_name' => 'العربية',
        'direction' => 'rtl',
        'flag' => '🇸🇦',
        'is_active' => true,
        'is_default' => false
    ],
    'fr' => [
        'code' => 'fr',
        'name' => 'French',
        'native_name' => 'Français',
        'direction' => 'ltr',
        'flag' => '🇫��',
        'is_active' => true,
        'is_default' => false
    ],
    'es' => [
        'code' => 'es',
        'name' => 'Spanish',
        'native_name' => 'Español',
        'direction' => 'ltr',
        'flag' => '🇪🇸',
        'is_active' => true,
        'is_default' => false
    ],
    'de' => [
        'code' => 'de',
        'name' => 'German',
        'native_name' => 'Deutsch',
        'direction' => 'ltr',
        'flag' => '🇩🇪',
        'is_active' => true,
        'is_default' => false
    ]
];

// Get current user from token
function getCurrentUser() {
    // Check if running under web server
    if (!function_exists('getallheaders')) {
        return null; // Not running under web server
    }
    
    $headers = getallheaders();
    $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
    
    if (empty($token)) {
        return null; // Not logged in
    }
    
    try {
        // Decode token to get username
        $tokenParts = explode('.', $token);
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            return $payload['sub'] ?? null; // username
        }
    } catch (Exception $e) {
        // Invalid token
    }
    
    return null;
}

// Get current language - user-specific
function getCurrentLanguage() {
    global $languages;
    
    // Check URL parameter first (for testing)
    if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
        return $_GET['lang'];
    }
    
    // Get current user
    $username = getCurrentUser();
    
    if ($username) {
        // User is logged in - get their language preference from database
        try {
            $dbConnection = new DatabaseConnection();
            $pdo = $dbConnection->getConnection();
            
            $stmt = $pdo->prepare("
                SELECT u.preferences 
                FROM users u 
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && $user['preferences']) {
                $preferences = json_decode($user['preferences'], true);
                $userLang = $preferences['language'] ?? 'en';
                if (isset($languages[$userLang])) {
                    return $userLang;
                }
            }
        } catch (Exception $e) {
            // Database error, default to English
        }
    }
    // Check session for non-authenticated users
    if (isset($_SESSION['language']) && isset($languages[$_SESSION['language']])) {
        return $_SESSION['language'];
    }
    
    // Check cookie for non-authenticated users
    if (isset($_COOKIE['language']) && isset($languages[$_COOKIE['language']])) {
        return $_COOKIE['language'];
    }
    
    // Not logged in or no preference - default to English
    return 'en';
}

// Set language preference for user
function setUserLanguagePreference($username, $langCode) {
    global $languages;
    
    if (!isset($languages[$langCode])) {
        return false;
    }
    
    if (!$username) {
        return false; // Can't set preference if not logged in
    }
    
    try {
        $dbConnection = new DatabaseConnection();
        $pdo = $dbConnection->getConnection();
        
        // Get current user preferences
        $stmt = $pdo->prepare("
            SELECT u.preferences 
            FROM users u 
            WHERE u.username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        $preferences = [];
        if ($user && $user['preferences']) {
            $preferences = json_decode($user['preferences'], true) ?: [];
        }
        
        // Update language preference
        $preferences['language'] = $langCode;
        
        // Save back to database
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET preferences = ? 
            WHERE username = ?
        ");
        $updateStmt->execute([json_encode($preferences), $username]);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle language endpoints
function handleLanguageEndpoints($endpoint) {
    global $languages;
    
    $languageEndpoint = str_replace('language/', '', $endpoint);
    $languageEndpoint = trim($languageEndpoint, '/');
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($languageEndpoint) {
                case 'current':
                    $currentLang = getCurrentLanguage();
                    echo json_encode($languages[$currentLang]);
                    break;
                    
                case 'supported':
                    echo json_encode(array_values($languages));
                    break;
                    
                case 'switcher':
                    $currentLang = getCurrentLanguage();
                    $switcherData = [
                        'current_language' => $languages[$currentLang],
                        'languages' => array_values($languages),
                        'is_rtl' => $languages[$currentLang]['direction'] === 'rtl'
                    ];
                    echo json_encode($switcherData);
                    break;
                    
                case 'detect':
                    $detectedLang = getCurrentLanguage();
                    echo json_encode(['detected_language' => $detectedLang]);
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Language endpoint not found']);
                    break;
            }
            break;
            
        case 'POST':
            switch ($languageEndpoint) {
                case 'switch':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $langCode = $input['lang'] ?? '';
                    
                    if (empty($langCode) || !isset($languages[$langCode])) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Invalid language code'
                        ]);
                        break;
                    }
                    
                    // Get current user
                    $username = getCurrentUser();
                    
                    if ($username) {
                        // User is logged in - save their preference
                        if (setUserLanguagePreference($username, $langCode)) {
                            echo json_encode([
                                'success' => true,
                                'code' => $langCode,
                                'name' => $languages[$langCode]['name'],
                                'native_name' => $languages[$langCode]['native_name'],
                                'direction' => $languages[$langCode]['direction'],
                                'message' => 'Language preference saved for user'
                            ]);
                        } else {
                            http_response_code(500);
                            echo json_encode([
                                'success' => false,
                                'error' => 'Failed to save language preference'
                            ]);
                        }
                        // Save language preference in session/cookie
                        setLanguagePreference($langCode);
                    } else {
                        // Not logged in - just return the language info (no saving)
                        // Save language preference in session/cookie
                        setLanguagePreference($langCode);
                        echo json_encode([
                            'success' => true,
                            'code' => $langCode,
                            'name' => $languages[$langCode]['name'],
                            'native_name' => $languages[$langCode]['native_name'],
                            'direction' => $languages[$langCode]['direction'],
                            'message' => 'Language switched and saved'
                        ]);
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Language endpoint not found']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

// Export the function for use in index.php
return 'handleLanguageEndpoints';
