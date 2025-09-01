<?php

namespace IslamWiki\Controllers;

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Services\Wiki\WikiService;
use IslamWiki\Services\User\UserService;
use IslamWiki\Services\Content\ContentService;
use IslamWiki\Core\Cache\FileCache;
use Exception;

/**
 * API Controller - REST API endpoints for v0.0.4
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class ApiController
{
    private DatabaseManager $database;
    private WikiService $wikiService;
    private UserService $userService;
    private ContentService $contentService;
    private FileCache $cache;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
        $this->cache = new FileCache('storage/cache/');
        $this->wikiService = new WikiService($database, $this->cache);
        $this->userService = new UserService($database);
        $this->contentService = new ContentService($database, $this->cache);
    }

    /**
     * Handle API requests
     */
    public function handleRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            switch ($endpoint) {
                case 'wiki/overview':
                    return $this->getWikiOverview();
                case 'wiki/articles':
                    return $this->handleWikiArticles($method, $data);
                case 'users':
                    return $this->handleUsers($method, $data);
                case 'content/articles':
                    return $this->handleContentArticles($method, $data);
                case 'content/categories':
                    return $this->handleContentCategories($method, $data);
                case 'content/tags':
                    return $this->handleContentTags($method, $data);
                case 'content/files':
                    return $this->handleContentFiles($method, $data);
                case 'system/health':
                    return $this->getSystemHealth();
                case 'system/stats':
                    return $this->getSystemStats();
                default:
                    return ['error' => 'Endpoint not found', 'code' => 404];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'code' => 500];
        }
    }

    /**
     * Get wiki overview data
     */
    private function getWikiOverview(): array
    {
        $cacheKey = 'wiki_overview';
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        $overview = [
            'total_articles' => $this->wikiService->getArticleCount(),
            'total_users' => $this->userService->getUserCount(),
            'total_categories' => $this->contentService->getCategoryCount(),
            'recent_articles' => $this->wikiService->getRecentArticles(5),
            'popular_articles' => $this->wikiService->getPopularArticles(5),
            'system_status' => $this->getSystemHealth()
        ];

        $this->cache->set($cacheKey, $overview, 300); // Cache for 5 minutes
        return $overview;
    }

    /**
     * Handle wiki articles CRUD
     */
    private function handleWikiArticles(string $method, array $data): array
    {
        switch ($method) {
            case 'GET':
                $filters = $data['filters'] ?? [];
                $page = $data['page'] ?? 1;
                $perPage = $data['per_page'] ?? 20;
                return $this->wikiService->getArticles($filters, $page, $perPage);
            
            case 'POST':
                return $this->wikiService->createArticle($data);
            
            case 'PUT':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'Article ID required', 'code' => 400];
                }
                unset($data['id']);
                return $this->wikiService->updateArticle($id, $data);
            
            case 'DELETE':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'Article ID required', 'code' => 400];
                }
                return $this->wikiService->deleteArticle($id);
            
            default:
                return ['error' => 'Method not allowed', 'code' => 405];
        }
    }

    /**
     * Handle users CRUD
     */
    private function handleUsers(string $method, array $data): array
    {
        switch ($method) {
            case 'GET':
                $filters = $data['filters'] ?? [];
                $page = $data['page'] ?? 1;
                $perPage = $data['per_page'] ?? 20;
                return $this->userService->getUsers($filters, $page, $perPage);
            
            case 'POST':
                return $this->userService->createUser($data);
            
            case 'PUT':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'User ID required', 'code' => 400];
                }
                unset($data['id']);
                return $this->userService->updateUser($id, $data);
            
            case 'DELETE':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'User ID required', 'code' => 400];
                }
                return $this->userService->deleteUser($id);
            
            default:
                return ['error' => 'Method not allowed', 'code' => 405];
        }
    }

    /**
     * Handle content articles CRUD
     */
    private function handleContentArticles(string $method, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($data['id'])) {
                    return $this->contentService->getArticle($data['id']);
                }
                if (isset($data['slug'])) {
                    return $this->contentService->getArticleBySlug($data['slug']);
                }
                $filters = $data['filters'] ?? [];
                $page = $data['page'] ?? 1;
                $perPage = $data['per_page'] ?? 20;
                return $this->contentService->getArticles($filters, $page, $perPage);
            
            case 'POST':
                return $this->contentService->createArticle($data);
            
            case 'PUT':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'Article ID required', 'code' => 400];
                }
                unset($data['id']);
                $changesSummary = $data['changes_summary'] ?? '';
                unset($data['changes_summary']);
                return $this->contentService->updateArticle($id, $data, $changesSummary);
            
            case 'DELETE':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'Article ID required', 'code' => 400];
                }
                return $this->contentService->deleteArticle($id);
            
            default:
                return ['error' => 'Method not allowed', 'code' => 405];
        }
    }

    /**
     * Handle content categories CRUD
     */
    private function handleContentCategories(string $method, array $data): array
    {
        switch ($method) {
            case 'GET':
                return $this->contentService->getCategories();
            
            case 'POST':
                return $this->contentService->createCategory($data);
            
            default:
                return ['error' => 'Method not allowed', 'code' => 405];
        }
    }

    /**
     * Handle content tags
     */
    private function handleContentTags(string $method, array $data): array
    {
        if ($method === 'GET') {
            $sql = "SELECT * FROM tags ORDER BY name ASC";
            $stmt = $this->database->query($sql);
            return $stmt->fetchAll();
        }
        
        return ['error' => 'Method not allowed', 'code' => 405];
    }

    /**
     * Handle content files
     */
    private function handleContentFiles(string $method, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($data['id'])) {
                    $sql = "SELECT * FROM files WHERE id = ?";
                    $stmt = $this->database->execute($sql, [$data['id']]);
                    return $stmt->fetch() ?: ['error' => 'File not found'];
                }
                $sql = "SELECT * FROM files ORDER BY created_at DESC";
                $stmt = $this->database->query($sql);
                return $stmt->fetchAll();
            
            case 'POST':
                if (!isset($data['file'])) {
                    return ['error' => 'File data required', 'code' => 400];
                }
                return $this->contentService->uploadFile($data['file'], $data['directory'] ?? 'general');
            
            case 'DELETE':
                $id = $data['id'] ?? null;
                if (!$id) {
                    return ['error' => 'File ID required', 'code' => 400];
                }
                return $this->contentService->deleteFile($id);
            
            default:
                return ['error' => 'Method not allowed', 'code' => 405];
        }
    }

    /**
     * Get system health status
     */
    private function getSystemHealth(): array
    {
        $health = [
            'database' => $this->database->testConnection(),
            'cache' => $this->cache->has('health_check') ? 'OK' : 'WARNING',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Update cache health check
        $this->cache->set('health_check', true, 60);
        
        return $health;
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        $cacheKey = 'system_stats';
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        $stats = [
            'database' => $this->database->getStats(),
            'content' => $this->contentService->getContentStatistics(),
            'users' => [
                'total' => $this->userService->getUserCount(),
                'active' => $this->userService->getActiveUserCount(),
                'roles' => $this->userService->getRoleDistribution()
            ],
            'performance' => [
                'cache_hits' => $this->cache->get('cache_hits') ?: 0,
                'cache_misses' => $this->cache->get('cache_misses') ?: 0
            ]
        ];

        $this->cache->set($cacheKey, $stats, 600); // Cache for 10 minutes
        return $stats;
    }
} 