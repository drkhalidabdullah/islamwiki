<?php

/**
 * Language API Endpoints
 * 
 * Handles language switching and detection
 */

// Mock language data
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
