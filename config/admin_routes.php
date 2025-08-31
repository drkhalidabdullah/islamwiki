<?php

/**
 * Admin Routes Configuration
 * 
 * Defines routes for the admin section of IslamWiki Framework
 * 
 * @author Khalid Abdullah
 * @version 0.0.2
 * @date 2025-08-30
 * @license AGPL-3.0
 */

use IslamWiki\Controllers\AdminController;

return [
    'admin' => [
        'prefix' => '/admin',
        'middleware' => ['auth', 'admin'],
        'routes' => [
            'GET /dashboard' => [AdminController::class, 'getDashboard'],
            'GET /health' => [AdminController::class, 'getSystemHealth'],
            'GET /tests/history' => [AdminController::class, 'getTestHistory'],
        ]
    ]
]; 