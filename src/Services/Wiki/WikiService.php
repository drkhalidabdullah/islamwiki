<?php

namespace IslamWiki\Services\Wiki;

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Cache\CacheInterface;
use Exception;
use PDO;

/**
 * Wiki Service - Enhanced wiki functionality for v0.0.4
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class WikiService
{
    private DatabaseManager $database;
    private CacheInterface $cache;

    public function __construct(DatabaseManager $database, CacheInterface $cache)
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

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name, 
                       c.name as category_name, c.slug as category_slug
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                WHERE a.id = ? AND a.status = 'published'";
        
        $stmt = $this->database->execute($sql, [$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($article) {
            // Increment view count
            $this->incrementViewCount($id);
            
            // Cache for 1 hour
            $this->cache->set($cacheKey, $article, 3600);
        }
        
        return $article;
    }

    /**
     * Get article by slug
     */
    public function getArticleBySlug(string $slug): ?array
    {
        $cacheKey = "article:slug:{$slug}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name, 
                       c.name as category_name, c.slug as category_slug
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                WHERE a.slug = ? AND a.status = 'published'";
        
        $stmt = $this->database->execute($sql, [$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($article) {
            // Increment view count
            $this->incrementViewCount($article['id']);
            
            // Cache for 1 hour
            $this->cache->set($cacheKey, $article, 3600);
        }
        
        return $article;
    }

    /**
     * Get articles by category
     */
    public function getArticlesByCategory(int $categoryId, int $limit = 20, int $offset = 0): array
    {
        $cacheKey = "articles:category:{$categoryId}:{$limit}:{$offset}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name,
                       c.name as category_name, c.slug as category_slug
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
            WHERE a.category_id = ? AND a.status = 'published'
            ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->database->execute($sql, [$categoryId, $limit, $offset]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache for 30 minutes
        $this->cache->set($cacheKey, $articles, 1800);
        
        return $articles;
    }

    /**
     * Get featured articles
     */
    public function getFeaturedArticles(int $limit = 10): array
    {
        $cacheKey = "articles:featured:{$limit}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name,
                       c.name as category_name, c.slug as category_slug
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                WHERE a.featured = 1 AND a.status = 'published'
                ORDER BY a.created_at DESC
                LIMIT ?";
        
        $stmt = $this->database->execute($sql, [$limit]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache for 1 hour
        $this->cache->set($cacheKey, $articles, 3600);
        
        return $articles;
    }

    /**
     * Search articles
     */
    public function searchArticles(string $query, int $limit = 20, int $offset = 0): array
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name,
                       c.name as category_name, c.slug as category_slug,
                       MATCH(a.title, a.content) AGAINST(? IN BOOLEAN MODE) as relevance
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
            WHERE (a.title LIKE ? OR a.content LIKE ?) AND a.status = 'published'
            ORDER BY 
                CASE 
                    WHEN a.title LIKE ? THEN 1
                    WHEN a.title LIKE ? THEN 2
                    ELSE 3
                END,
                    relevance DESC,
                a.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->database->execute($sql, [$query, $searchTerm, $searchTerm, $query, $searchTerm, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new article
     */
    public function createArticle(array $data): array
    {
        try {
            $this->database->beginTransaction();

            // Validate required fields
            $requiredFields = ['title', 'content', 'author_id', 'excerpt'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '{$field}' is required");
                }
            }

            // Generate slug from title
            $slug = $this->generateSlug($data['title']);

            // Insert article
            $sql = "INSERT INTO articles (title, slug, content, excerpt, author_id, category_id, status, meta_title, meta_description, meta_keywords) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $this->database->execute($sql, [
                $data['title'],
                $slug,
                $data['content'],
                $data['excerpt'],
                $data['author_id'],
                $data['category_id'] ?? null,
                $data['status'] ?? 'draft',
                $data['meta_title'] ?? $data['title'],
                $data['meta_description'] ?? $data['excerpt'],
                $data['meta_keywords'] ?? null
            ]);

            $articleId = $this->database->lastInsertId();

            // Create initial version
            $this->createArticleVersion($articleId, $data, 'Initial version');

            $this->database->commit();

            // Clear related caches
            $this->clearArticleCaches();

            return [
                'id' => $articleId,
                'slug' => $slug,
                'message' => 'Article created successfully'
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }

    /**
     * Update article
     */
    public function updateArticle(int $id, array $data): array
    {
        try {
            $this->database->beginTransaction();

            // Get current article
            $currentArticle = $this->getArticle($id);
            if (!$currentArticle) {
                throw new Exception("Article not found");
            }

            // Prepare update data
            $updateFields = [];
            $updateValues = [];

            $fields = ['title', 'content', 'excerpt', 'category_id', 'status', 'meta_title', 'meta_description', 'meta_keywords'];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "`{$field}` = ?";
                    $updateValues[] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }

            // Add updated_at
            $updateFields[] = "`updated_at` = NOW()";
            $updateValues[] = $id;

            // Update article
            $sql = "UPDATE articles SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $this->database->execute($sql, $updateValues);

            // Create new version if content changed
            if (isset($data['content']) && $data['content'] !== $currentArticle['content']) {
                $this->createArticleVersion($id, $data, $data['changes_summary'] ?? 'Content updated');
            }

            $this->database->commit();

            // Clear related caches
            $this->clearArticleCaches($id);

            return [
                'id' => $id,
                'message' => 'Article updated successfully'
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }

    /**
     * Delete article
     */
    public function deleteArticle(int $id): array
    {
        try {
            $this->database->beginTransaction();

            // Check if article exists
            $stmt = $this->database->execute("SELECT id FROM articles WHERE id = ?", [$id]);
            if (!$stmt->fetch()) {
                throw new Exception("Article not found");
            }

            // Soft delete - mark as archived
            $sql = "UPDATE articles SET status = 'archived', updated_at = NOW() WHERE id = ?";
            $this->database->execute($sql, [$id]);

            $this->database->commit();

            // Clear related caches
            $this->clearArticleCaches($id);

            return [
                'id' => $id,
                'message' => 'Article deleted successfully'
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }

    /**
     * Get article versions
     */
    public function getArticleVersions(int $articleId): array
    {
        $sql = "SELECT av.*, u.username as created_by_name, u.display_name as created_by_display_name
                FROM article_versions av
                LEFT JOIN users u ON av.created_by = u.id
                WHERE av.article_id = ?
                ORDER BY av.version_number DESC";
        
        $stmt = $this->database->execute($sql, [$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get article statistics
     */
    public function getArticleStats(): array
    {
        $cacheKey = "stats:articles";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stats = [];

        // Total articles
        $stmt = $this->database->execute("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
        $stats['total_articles'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Articles by status
        $stmt = $this->database->execute("SELECT status, COUNT(*) as count FROM articles GROUP BY status");
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Articles by category
        $stmt = $this->database->execute("SELECT c.name, COUNT(a.id) as count 
                                        FROM content_categories c 
                                        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
                                        GROUP BY c.id, c.name");
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total views
        $stmt = $this->database->execute("SELECT SUM(view_count) as total_views FROM articles");
        $stats['total_views'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_views'] ?? 0;

        // Featured articles count
        $stmt = $this->database->execute("SELECT COUNT(*) as count FROM articles WHERE featured = 1 AND status = 'published'");
        $stats['featured_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Cache for 1 hour
        $this->cache->set($cacheKey, $stats, 3600);

        return $stats;
    }

    /**
     * Get recent articles
     */
    public function getRecentArticles(int $limit = 10): array
    {
        $cacheKey = "articles:recent:{$limit}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name,
                       c.name as category_name, c.slug as category_slug
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC
                LIMIT ?";
        
        $stmt = $this->database->execute($sql, [$limit]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache for 15 minutes
        $this->cache->set($cacheKey, $articles, 900);
        
        return $articles;
    }

    /**
     * Get popular articles
     */
    public function getPopularArticles(int $limit = 10): array
    {
        $cacheKey = "articles:popular:{$limit}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name,
                       c.name as category_name, c.slug as category_slug
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                WHERE a.status = 'published'
                ORDER BY a.view_count DESC, a.created_at DESC
                LIMIT ?";
        
        $stmt = $this->database->execute($sql, [$limit]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache for 1 hour
        $this->cache->set($cacheKey, $articles, 3600);
        
        return $articles;
    }

    /**
     * Create article version
     */
    private function createArticleVersion(int $articleId, array $data, string $changesSummary): void
    {
        // Get current version number
        $stmt = $this->database->execute("SELECT MAX(version_number) as max_version FROM article_versions WHERE article_id = ?", [$articleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $versionNumber = ($result['max_version'] ?? 0) + 1;

        $sql = "INSERT INTO article_versions (article_id, version_number, title, content, excerpt, changes_summary, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->database->execute($sql, [
            $articleId,
            $versionNumber,
            $data['title'],
            $data['content'],
            $data['excerpt'],
            $changesSummary,
            $data['author_id']
        ]);
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists(string $slug): bool
    {
        $stmt = $this->database->execute("SELECT id FROM articles WHERE slug = ?", [$slug]);
        return (bool) $stmt->fetch();
    }

    /**
     * Increment view count
     */
    private function incrementViewCount(int $articleId): void
    {
        $sql = "UPDATE articles SET view_count = view_count + 1 WHERE id = ?";
        $this->database->execute($sql, [$articleId]);
    }

    /**
     * Clear article caches
     */
    private function clearArticleCaches(?int $articleId = null): void
    {
        if ($articleId) {
            $this->cache->delete("article:{$articleId}");
        }
        
        // Clear list caches
        $this->cache->delete("articles:featured:*");
        $this->cache->delete("articles:recent:*");
        $this->cache->delete("articles:popular:*");
        $this->cache->delete("articles:category:*");
        $this->cache->delete("stats:articles");
    }

    /**
     * Get article count
     */
    public function getArticleCount(): int
    {
        $cacheKey = "stats:article_count";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT COUNT(*) as count FROM articles WHERE status = 'published'";
        $stmt = $this->database->execute($sql);
        $count = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $this->cache->set($cacheKey, $count, 1800); // Cache for 30 minutes
        return $count;
    }

    /**
     * Get articles with pagination and filters
     */
    public function getArticles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $cacheKey = "articles:list:" . md5(serialize($filters) . $page . $perPage);
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $where = ["a.status = 'published'"];
        $params = [];
        $offset = ($page - 1) * $perPage;

        // Apply filters
        if (!empty($filters['category_id'])) {
            $where[] = "a.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['author_id'])) {
            $where[] = "a.author_id = ?";
            $params[] = $filters['author_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(a.title LIKE ? OR a.content LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT a.*, u.username as author_name, u.display_name as author_display_name,
                       c.name as category_name, c.slug as category_slug
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                WHERE {$whereClause}
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->database->execute($sql, $params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count FROM articles a WHERE {$whereClause}";
        $countStmt = $this->database->execute($countSql, array_slice($params, 0, -2));
        $totalCount = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $result = [
            'articles' => $articles,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage)
            ]
        ];

        $this->cache->set($cacheKey, $result, 1800); // Cache for 30 minutes
        return $result;
    }


} 