<?php
// Site configuration
define('SITE_NAME', 'IslamWiki');
define('SITE_URL', 'http://localhost');
define('SITE_VERSION', '0.0.0.3');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 6);

// File upload settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Start session
session_start();

// Include database
require_once __DIR__ . '/database.php';
?>
