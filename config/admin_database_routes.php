<?php

/**
 * Admin Database Routes - Database management endpoints
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

// Database Management Routes
$adminDatabaseRoutes = [
    // Database Overview
    'GET /admin/api/database/overview' => [
        'controller' => 'DatabaseController',
        'action' => 'overview',
        'middleware' => ['admin']
    ],
    
    // Database Health
    'GET /admin/api/database/health' => [
        'controller' => 'DatabaseController',
        'action' => 'health',
        'middleware' => ['admin']
    ],
    
    // Migration Management
    'POST /admin/api/database/migrations/run' => [
        'controller' => 'DatabaseController',
        'action' => 'runMigrations',
        'middleware' => ['admin']
    ],
    
    'POST /admin/api/database/migrations/rollback' => [
        'controller' => 'DatabaseController',
        'action' => 'rollbackMigrations',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/database/migrations/status' => [
        'controller' => 'DatabaseController',
        'action' => 'migrationStatus',
        'middleware' => ['admin']
    ],
    
    // Query Execution
    'POST /admin/api/database/query' => [
        'controller' => 'DatabaseController',
        'action' => 'executeQuery',
        'middleware' => ['admin']
    ],
    
    // Query Log Management
    'GET /admin/api/database/query-log' => [
        'controller' => 'DatabaseController',
        'action' => 'getQueryLog',
        'middleware' => ['admin']
    ],
    
    'POST /admin/api/database/query-log/clear' => [
        'controller' => 'DatabaseController',
        'action' => 'clearQueryLog',
        'middleware' => ['admin']
    ]
];

return $adminDatabaseRoutes; 