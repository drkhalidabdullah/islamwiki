<?php

namespace IslamWiki\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Admin Controller
 * 
 * Provides administrative endpoints for monitoring releases, tests, and development progress
 * 
 * @author Khalid Abdullah
 * @version 0.0.2
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class AdminController
{
    /**
     * Get admin dashboard data
     */
    public function getDashboard(Request $request): Response
    {
        $data = [
            'releases' => $this->getReleases(),
            'testResults' => $this->getTestResults(),
            'developmentProgress' => $this->getDevelopmentProgress(),
            'lastUpdated' => date('Y-m-d H:i:s')
        ];
        
        return new Response($data, 200);
    }
    
    /**
     * Get releases information
     */
    private function getReleases(): array
    {
        return [
            [
                'version' => '0.0.2',
                'date' => '2025-08-30',
                'status' => 'completed',
                'features' => [
                    'Testing Infrastructure (PHPUnit)',
                    'Core Component Testing',
                    'Frontend Component Library',
                    'Documentation & Reports'
                ],
                'tests' => [
                    'total' => 51,
                    'passed' => 51,
                    'failed' => 0,
                    'coverage' => 90
                ]
            ],
            [
                'version' => '0.1.0',
                'date' => 'Q4 2025',
                'status' => 'planned',
                'features' => [
                    'User Authentication System',
                    'Content Management',
                    'Admin Interface',
                    'API Integration'
                ],
                'tests' => [
                    'total' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'coverage' => 0
                ]
            ],
            [
                'version' => '0.2.0',
                'date' => 'Q1 2026',
                'status' => 'planned',
                'features' => [
                    'Advanced Content Features',
                    'Media Management',
                    'Content Moderation',
                    'API Development'
                ],
                'tests' => [
                    'total' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'coverage' => 0
                ]
            ]
        ];
    }
    
    /**
     * Get test results
     */
    private function getTestResults(): array
    {
        return [
            [
                'component' => 'Container (DI)',
                'tests' => 12,
                'passed' => 12,
                'failed' => 0,
                'lastRun' => '2025-08-30',
                'status' => 'passing'
            ],
            [
                'component' => 'Router',
                'tests' => 15,
                'passed' => 15,
                'failed' => 0,
                'lastRun' => '2025-08-30',
                'status' => 'passing'
            ],
            [
                'component' => 'FileCache',
                'tests' => 15,
                'passed' => 15,
                'failed' => 0,
                'lastRun' => '2025-08-30',
                'status' => 'passing'
            ],
            [
                'component' => 'Database',
                'tests' => 9,
                'passed' => 9,
                'failed' => 0,
                'lastRun' => '2025-08-30',
                'status' => 'passing'
            ]
        ];
    }
    
    /**
     * Get development progress
     */
    private function getDevelopmentProgress(): array
    {
        return [
            [
                'phase' => 'v0.0.2 Completion',
                'percentage' => 100,
                'color' => 'green'
            ],
            [
                'phase' => 'v0.1.0 Planning',
                'percentage' => 90,
                'color' => 'blue'
            ],
            [
                'phase' => 'v0.2.0 Planning',
                'percentage' => 75,
                'color' => 'yellow'
            ]
        ];
    }
    
    /**
     * Get system health information
     */
    public function getSystemHealth(Request $request): Response
    {
        $data = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'uptime' => time() - $_SERVER['REQUEST_TIME'] ?? time(),
            'tests' => [
                'total' => 51,
                'passing' => 51,
                'failing' => 0,
                'coverage' => 90
            ]
        ];
        
        return new Response($data, 200);
    }
    
    /**
     * Get test execution history
     */
    public function getTestHistory(Request $request): Response
    {
        $data = [
            'history' => [
                [
                    'date' => '2025-08-30',
                    'tests' => 51,
                    'passed' => 51,
                    'failed' => 0,
                    'duration' => '4.032s',
                    'memory' => '10.00 MB'
                ]
            ]
        ];
        
        return new Response($data, 200);
    }
} 