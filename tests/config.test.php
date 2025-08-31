<?php

/**
 * Test Configuration for IslamWiki Framework
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */

return [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'islamwiki_test',
        'username' => 'test_user',
        'password' => 'test_password',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    
    'cache' => [
        'driver' => 'file',
        'path' => __DIR__ . '/../storage/test/cache',
        'prefix' => 'test_'
    ],
    
    'storage' => [
        'path' => __DIR__ . '/../storage/test',
        'logs' => __DIR__ . '/../storage/test/logs',
        'uploads' => __DIR__ . '/../storage/test/uploads',
        'temp' => __DIR__ . '/../storage/test/temp'
    ],
    
    'app' => [
        'env' => 'testing',
        'debug' => true,
        'url' => 'http://localhost:8000'
    ]
]; 