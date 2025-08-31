<?php

/**
 * IslamWiki Framework - Front Controller
 * 
 * Author: Khalid Abdullah
 * Version: 0.0.1
 * Date: 2025-08-30
 * License: AGPL-3.0
 * 
 * This file serves as the entry point for all HTTP requests
 * to the IslamWiki application.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set error reporting based on environment
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

try {
    // Create and bootstrap the application
    $app = new IslamWiki\Core\Application();
    
    // Handle the request and get response
    $response = $app->handle();
    
    // Send the response
    $response->send();
    
} catch (Exception $e) {
    // Handle exceptions
    if ($_ENV['APP_DEBUG'] ?? false) {
        // In debug mode, show detailed error
        http_response_code(500);
        echo '<h1>Application Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // In production, show generic error
        http_response_code(500);
        echo '<h1>Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
    
    // Log the error
    error_log('IslamWiki Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
} 