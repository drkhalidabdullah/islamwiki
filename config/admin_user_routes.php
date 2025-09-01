<?php

/**
 * Admin User Routes - User management endpoints
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

// User Management Routes
$adminUserRoutes = [
    // User CRUD Operations
    'GET /admin/api/users' => [
        'controller' => 'UserController',
        'action' => 'getUsers',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/users/{id}' => [
        'controller' => 'UserController',
        'action' => 'getUser',
        'middleware' => ['admin']
    ],
    
    'POST /admin/api/users' => [
        'controller' => 'UserController',
        'action' => 'createUser',
        'middleware' => ['admin']
    ],
    
    'PUT /admin/api/users/{id}' => [
        'controller' => 'UserController',
        'action' => 'updateUser',
        'middleware' => ['admin']
    ],
    
    'DELETE /admin/api/users/{id}' => [
        'controller' => 'UserController',
        'action' => 'deleteUser',
        'middleware' => ['admin']
    ],
    
    // Role Management
    'POST /admin/api/users/{id}/roles' => [
        'controller' => 'UserController',
        'action' => 'assignRole',
        'middleware' => ['admin']
    ],
    
    'DELETE /admin/api/users/{id}/roles' => [
        'controller' => 'UserController',
        'action' => 'removeRole',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/users/{id}/roles' => [
        'controller' => 'UserController',
        'action' => 'getUserRoles',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/roles' => [
        'controller' => 'UserController',
        'action' => 'getAllRoles',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/users/{id}/has-role' => [
        'controller' => 'UserController',
        'action' => 'userHasRole',
        'middleware' => ['admin']
    ],
    
    // User Profile Management
    'PUT /admin/api/users/{id}/profile' => [
        'controller' => 'UserController',
        'action' => 'updateUserProfile',
        'middleware' => ['admin']
    ],
    
    // User Search and Statistics
    'GET /admin/api/users/search' => [
        'controller' => 'UserController',
        'action' => 'searchUsers',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/users/statistics' => [
        'controller' => 'UserController',
        'action' => 'getUserStatistics',
        'middleware' => ['admin']
    ],
    
    'GET /admin/api/users/{id}/activity' => [
        'controller' => 'UserController',
        'action' => 'getUserActivity',
        'middleware' => ['admin']
    ],
    
    // Bulk Operations
    'POST /admin/api/users/bulk-update' => [
        'controller' => 'UserController',
        'action' => 'bulkUpdateUsers',
        'middleware' => ['admin']
    ]
];

return $adminUserRoutes; 