<?php

/**
 * Language API Endpoints
 * 
 * Handles language switching and detection
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mock language data (in a real app, this would come from database)
$languages = [
    'en' => [
        'code' => 'en',
        'name' => 'English',
        'native_name' => 'English',
        'direction' => 'ltr',
        'flag' => '🇺🇸',
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
        'flag' => '🇫🇷',
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

// Get current language from session, cookie, or default
function getCurrentLanguage() {
    global $languages;
    
    // Check URL parameter first
    if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
        return $_GET['lang'];
    }
    
    // Check session
    if (isset($_SESSION['language']) && isset($languages[$_SESSION['language']])) {
        return $_SESSION['language'];
    }
    
    // Check cookie
    if (isset($_COOKIE['language']) && isset($languages[$_COOKIE['language']])) {
        return $_COOKIE['language'];
    }
    
    // Check browser language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (isset($languages[$browserLang])) {
            return $browserLang;
        }
    }
    
    // Default to English
    return 'en';
}

// Set language preference
function setLanguagePreference($langCode) {
    global $languages;
    
    if (!isset($languages[$langCode])) {
        return false;
    }
    
    // Set session
    $_SESSION['language'] = $langCode;
    
    // Set cookie (1 year)
    setcookie('language', $langCode, time() + (365 * 24 * 60 * 60), '/', '', false, true);
    
    return true;
}

// Handle different endpoints
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove /api/language from the path
$endpoint = str_replace('/api/language', '', $path);
$endpoint = trim($endpoint, '/');

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($endpoint) {
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
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
            break;
            
        case 'POST':
            switch ($endpoint) {
                case 'switch':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $langCode = $input['lang'] ?? null;
                    
                    if (!$langCode || !isset($languages[$langCode])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid language code']);
                        break;
                    }
                    
                    if (setLanguagePreference($langCode)) {
                        echo json_encode($languages[$langCode]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to set language preference']);
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
