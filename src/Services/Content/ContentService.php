<?php

namespace IslamWiki\Services\Content;

use IslamWiki\Core\Database\Database;
use IslamWiki\Core\Cache\CacheInterface;

/**
 * Content Service - Content management functionality
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class ContentService
{
    private Database $database;
    private CacheInterface $cache;

    public function __construct(Database $database, CacheInterface $cache)
    {
        $this->database = $database;
        $this->cache = $cache;
    }

    /**
     * Create new article
     */
    public function createArticle(array $articleData): ?int
    {
        $stmt = $this->database->prepare("
            INSERT INTO articles (title, content, excerpt, author_id, category_id, tags, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $articleData['title'],
            $articleData['content'],
            $articleData['excerpt'] ?? '',
            $articleData['author_id'],
            $articleData['category_id'] ?? null,
            $articleData['tags'] ?? '',
            $articleData['status'] ?? 'draft'
        ]);

        if ($success) {
            $articleId = $this->database->lastInsertId();
            $this->cache->delete("articles:list");
            $this->cache->delete("articles:category:{$articleData['category_id']}");
            return $articleId;
        }

        return null;
    }

    /**
     * Update article
     */
    public function updateArticle(int $id, array $articleData): bool
    {
        $fields = [];
        $values = [];

        foreach ($articleData as $field => $value) {
            if (in_array($field, ['title', 'content', 'excerpt', 'category_id', 'tags', 'status'])) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE articles SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $success = $stmt->execute($values);

        if ($success) {
            $this->cache->delete("article:{$id}");
            $this->cache->delete("articles:list");
            
            // Get current category to clear cache
            $stmt = $this->database->prepare("SELECT category_id FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($current) {
                $this->cache->delete("articles:category:{$current['category_id']}");
            }
        }

        return $success;
    }

    /**
     * Delete article
     */
    public function deleteArticle(int $id): bool
    {
        $stmt = $this->database->prepare("
            UPDATE articles 
            SET status = 'deleted', updated_at = NOW() 
            WHERE id = ?
        ");
        
        $success = $stmt->execute([$id]);

        if ($success) {
            $this->cache->delete("article:{$id}");
            $this->cache->delete("articles:list");
        }

        return $success;
    }

    /**
     * Get article by ID
     */
    public function getArticleById(int $id): ?array
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
            WHERE a.id = ? AND a.status != 'deleted'
        ");
        
        $stmt->execute([$id]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($article) {
            $this->cache->set($cacheKey, $article, 3600); // Cache for 1 hour
        }
        
        return $article;
    }

    /**
     * Get articles list
     */
    public function getArticles(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $cacheKey = "articles:list:" . md5(serialize([$limit, $offset, $filters]));
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $where = ["a.status != 'deleted'"];
        $values = [];

        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $values[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "a.category_id = ?";
            $values[] = $filters['category_id'];
        }

        if (!empty($filters['author_id'])) {
            $where[] = "a.author_id = ?";
            $values[] = $filters['author_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(a.title LIKE ? OR a.content LIKE ? OR a.tags LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $values = array_merge($values, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $where);
        $values = array_merge($values, [$limit, $offset]);

        $sql = "
            SELECT a.*, u.username as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE {$whereClause}
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->cache->set($cacheKey, $articles, 900); // Cache for 15 minutes
        return $articles;
    }

    /**
     * Get article count
     */
    public function getArticleCount(array $filters = []): int
    {
        $where = ["status != 'deleted'"];
        $values = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $values[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = ?";
            $values[] = $filters['category_id'];
        }

        if (!empty($filters['author_id'])) {
            $where[] = "author_id = ?";
            $values[] = $filters['author_id'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as count FROM articles WHERE {$whereClause}";

        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'];
    }

    /**
     * Create category
     */
    public function createCategory(array $categoryData): ?int
    {
        $stmt = $this->database->prepare("
            INSERT INTO categories (name, description, slug, parent_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $categoryData['name'],
            $categoryData['description'] ?? '',
            $categoryData['slug'] ?? $this->generateSlug($categoryData['name']),
            $categoryData['parent_id'] ?? null
        ]);

        if ($success) {
            $categoryId = $this->database->lastInsertId();
            $this->cache->delete("categories:list");
            return $categoryId;
        }

        return null;
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $categoryData): bool
    {
        $fields = [];
        $values = [];

        foreach ($categoryData as $field => $value) {
            if (in_array($field, ['name', 'description', 'slug', 'parent_id'])) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE categories SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $success = $stmt->execute($values);

        if ($success) {
            $this->cache->delete("category:{$id}");
            $this->cache->delete("categories:list");
        }

        return $success;
    }

    /**
     * Get categories
     */
    public function getCategories(): array
    {
        $cacheKey = "categories:list";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->database->prepare("
            SELECT c.*, COUNT(a.id) as article_count
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        
        $stmt->execute();
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->cache->set($cacheKey, $categories, 1800); // Cache for 30 minutes
        return $categories;
    }

    /**
     * Get category by ID
     */
    public function getCategoryById(int $id): ?array
    {
        $cacheKey = "category:{$id}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->database->prepare("
            SELECT c.*, COUNT(a.id) as article_count
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
            WHERE c.id = ?
            GROUP BY c.id
        ");
        
        $stmt->execute([$id]);
        $category = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($category) {
            $this->cache->set($cacheKey, $category, 1800); // Cache for 30 minutes
        }
        
        return $category;
    }

    /**
     * Generate slug from title
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Get content statistics
     */
    public function getContentStatistics(): array
    {
        $stats = $this->cache->get('content:statistics');
        
        if (!$stats) {
            $stmt = $this->database->prepare("
                SELECT 
                    COUNT(*) as total_articles,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_articles,
                    COUNT(DISTINCT author_id) as total_authors,
                    COUNT(DISTINCT category_id) as total_categories
                FROM articles
                WHERE status != 'deleted'
            ");
            
            $stmt->execute();
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $this->cache->set('content:statistics', $stats, 1800); // Cache for 30 minutes
        }
        
        return $stats;
    }
} 