<?php

namespace IslamWiki\Services\Wiki;

use IslamWiki\Core\Database\Database;
use IslamWiki\Core\Cache\CacheInterface;

/**
 * Wiki Service - Core wiki functionality
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class WikiService
{
    private Database $database;
    private CacheInterface $cache;

    public function __construct(Database $database, CacheInterface $cache)
    {
        $this->database = $database;
        $this->cache = $cache;
    }

    /**
     * Get article by ID
     */
    public function getArticle(int $id): ?array
    {
        $cacheKey = "article:{$id}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->database->prepare("
            SELECT a.*, u.username as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.id = ? AND a.status = 'published'
        ");
        
        $stmt->execute([$id]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($article) {
            $this->cache->set($cacheKey, $article, 3600); // Cache for 1 hour
        }
        
        return $article;
    }

    /**
     * Get articles by category
     */
    public function getArticlesByCategory(int $categoryId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->database->prepare("
            SELECT a.*, u.username as author_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.category_id = ? AND a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$categoryId, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search articles
     */
    public function searchArticles(string $query, int $limit = 20): array
    {
        $searchTerm = "%{$query}%";
        
        $stmt = $this->database->prepare("
            SELECT a.*, u.username as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE (a.title LIKE ? OR a.content LIKE ?) AND a.status = 'published'
            ORDER BY 
                CASE 
                    WHEN a.title LIKE ? THEN 1
                    WHEN a.title LIKE ? THEN 2
                    ELSE 3
                END,
                a.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$searchTerm, $searchTerm, $query, $searchTerm, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recent articles
     */
    public function getRecentArticles(int $limit = 10): array
    {
        $stmt = $this->database->prepare("
            SELECT a.*, u.username as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get categories
     */
    public function getCategories(): array
    {
        $stmt = $this->database->prepare("
            SELECT c.*, COUNT(a.id) as article_count
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get article statistics
     */
    public function getStatistics(): array
    {
        $stats = $this->cache->get('wiki:statistics');
        
        if (!$stats) {
            $stmt = $this->database->prepare("
                SELECT 
                    COUNT(*) as total_articles,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
                    COUNT(DISTINCT author_id) as total_authors,
                    COUNT(DISTINCT category_id) as total_categories
                FROM articles
            ");
            
            $stmt->execute();
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($stats) {
                $this->cache->set('wiki:statistics', $stats, 1800); // Cache for 30 minutes
            } else {
                // Return default stats if no data
                $stats = [
                    'total_articles' => 0,
                    'published_articles' => 0,
                    'draft_articles' => 0,
                    'total_authors' => 0,
                    'total_categories' => 0
                ];
            }
        }
        
        return $stats;
    }
} 