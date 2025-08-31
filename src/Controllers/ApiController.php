<?php

namespace IslamWiki\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Services\Wiki\WikiService;
use IslamWiki\Services\User\UserService;
use IslamWiki\Services\Content\ContentService;
use IslamWiki\Core\Authentication\AuthService;

/**
 * API Controller - Handles API requests
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class ApiController
{
    private WikiService $wikiService;
    private UserService $userService;
    private ContentService $contentService;
    private AuthService $authService;

    public function __construct(
        WikiService $wikiService,
        UserService $userService,
        ContentService $contentService,
        AuthService $authService
    ) {
        $this->wikiService = $wikiService;
        $this->userService = $userService;
        $this->contentService = $contentService;
        $this->authService = $authService;
    }

    /**
     * Get recent articles
     */
    public function getRecentArticles(Request $request): Response
    {
        try {
            $limit = (int) ($request->get('limit', 10));
            $articles = $this->wikiService->getRecentArticles($limit);
            
            return (new Response())->json([
                'success' => true,
                'data' => $articles,
                'count' => count($articles)
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to fetch recent articles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get article by ID
     */
    public function getArticle(Request $request, int $id): Response
    {
        try {
            $article = $this->wikiService->getArticle($id);
            
            if (!$article) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Article not found'
                ], 404);
            }
            
            return (new Response())->json([
                'success' => true,
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to fetch article',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search articles
     */
    public function searchArticles(Request $request): Response
    {
        try {
            $query = $request->get('q', '');
            $limit = (int) ($request->get('limit', 20));
            
            if (empty($query)) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Search query is required'
                ], 400);
            }
            
            $articles = $this->wikiService->searchArticles($query, $limit);
            
            return (new Response())->json([
                'success' => true,
                'data' => $articles,
                'query' => $query,
                'count' => count($articles)
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to search articles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories
     */
    public function getCategories(Request $request): Response
    {
        try {
            $categories = $this->wikiService->getCategories();
            
            return (new Response())->json([
                'success' => true,
                'data' => $categories,
                'count' => count($categories)
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to fetch categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get articles by category
     */
    public function getArticlesByCategory(Request $request, int $categoryId): Response
    {
        try {
            $limit = (int) ($request->get('limit', 20));
            $offset = (int) ($request->get('offset', 0));
            
            $articles = $this->wikiService->getArticlesByCategory($categoryId, $limit, $offset);
            
            return (new Response())->json([
                'success' => true,
                'data' => $articles,
                'category_id' => $categoryId,
                'count' => count($articles)
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to fetch articles by category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User login
     */
    public function login(Request $request): Response
    {
        try {
            $username = $request->post('username');
            $password = $request->post('password');
            
            if (!$username || !$password) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Username and password are required'
                ], 400);
            }
            
            $result = $this->authService->authenticate($username, $password);
            
            if (!$result) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Invalid credentials'
                ], 401);
            }
            
            return (new Response())->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Login failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User logout
     */
    public function logout(Request $request): Response
    {
        try {
            $token = $request->header('Authorization');
            if ($token && strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
                $this->authService->logout($token);
            }
            
            return (new Response())->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Logout failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function getCurrentUser(Request $request): Response
    {
        try {
            $token = $request->header('Authorization');
            if (!$token || strpos($token, 'Bearer ') !== 0) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Authorization token required'
                ], 401);
            }
            
            $token = substr($token, 7);
            $user = $this->authService->getCurrentUser($token);
            
            if (!$user) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Invalid or expired token'
                ], 401);
            }
            
            return (new Response())->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to get current user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wiki statistics
     */
    public function getStatistics(Request $request): Response
    {
        try {
            $stats = $this->wikiService->getStatistics();
            
            return (new Response())->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check endpoint
     */
    public function health(Request $request): Response
    {
        return (new Response())->json([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => '0.0.1'
        ]);
    }
} 