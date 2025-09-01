<?php

namespace IslamWiki\Services\Content;

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Cache\CacheInterface;
use Exception;
use PDO;

/**
 * Enhanced Content Service - Comprehensive content management for v0.0.4
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class ContentService
{
    private DatabaseManager $database;
    private CacheInterface $cache;
    private string $uploadPath;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct(DatabaseManager $database, CacheInterface $cache, string $uploadPath = 'uploads/')
    {
        $this->database = $database;
        $this->cache = $cache;
        $this->uploadPath = rtrim($uploadPath, '/') . '/';
        $this->allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'text/plain', 'text/markdown',
            'video/mp4', 'video/webm', 'audio/mpeg', 'audio/wav'
        ];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
    }

    // ==================== ARTICLE MANAGEMENT ====================

    /**
     * Create new article with comprehensive validation
     */
    public function createArticle(array $articleData): array
    {
        try {
            $this->database->beginTransaction();

            // Validate required fields
            $requiredFields = ['title', 'content', 'author_id'];
            foreach ($requiredFields as $field) {
                if (empty($articleData[$field])) {
                    throw new Exception("Required field '{$field}' is missing");
                }
            }

            // Generate slug
            $slug = $this->generateSlug($articleData['title']);
            if ($this->slugExists($slug)) {
                $slug = $this->generateUniqueSlug($slug);
            }

            // Insert article
            $sql = "INSERT INTO articles (title, slug, content, excerpt, author_id, category_id, status, featured, meta_title, meta_description, meta_keywords, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $this->database->execute($sql, [
                $articleData['title'],
                $slug,
                $articleData['content'],
                $articleData['excerpt'] ?? '',
                $articleData['author_id'],
                $articleData['category_id'] ?? null,
                $articleData['status'] ?? 'draft',
                $articleData['featured'] ?? 0,
                $articleData['meta_title'] ?? $articleData['title'],
                $articleData['meta_description'] ?? $articleData['excerpt'] ?? '',
                $articleData['meta_keywords'] ?? ''
            ]);

            $articleId = $this->database->lastInsertId();

            // Create article version
            $this->createArticleVersion($articleId, $articleData, 'Initial version');

            // Handle tags if provided
            if (!empty($articleData['tags'])) {
                $this->handleArticleTags($articleId, $articleData['tags']);
            }

            // Clear caches
            $this->clearArticleCaches($articleId, $articleData['category_id'] ?? null);

            $this->database->commit();

            return [
                'success' => true,
                'article_id' => $articleId,
                'slug' => $slug,
                'message' => 'Article created successfully'
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update article with versioning
     */
    public function updateArticle(int $articleId, array $articleData, string $changesSummary = ''): array
    {
        try {
            $this->database->beginTransaction();

            // Get current article data
            $currentArticle = $this->getArticle($articleId);
            if (!$currentArticle) {
                throw new Exception("Article not found");
            }

            // Check if content actually changed
            $contentChanged = $currentArticle['content'] !== ($articleData['content'] ?? $currentArticle['content']);

            // Update article
            $updateFields = [];
            $updateValues = [];

            $updatableFields = ['title', 'content', 'excerpt', 'category_id', 'status', 'featured', 'meta_title', 'meta_description', 'meta_keywords'];
            
            foreach ($updatableFields as $field) {
                if (isset($articleData[$field]) && $articleData[$field] !== $currentArticle[$field]) {
                    $updateFields[] = "{$field} = ?";
                    $updateValues[] = $articleData[$field];
                }
            }

            // Update slug if title changed
            if (isset($articleData['title']) && $articleData['title'] !== $currentArticle['title']) {
                $newSlug = $this->generateSlug($articleData['title']);
                if ($newSlug !== $currentArticle['slug'] && $this->slugExists($newSlug)) {
                    $newSlug = $this->generateUniqueSlug($newSlug);
                }
                $updateFields[] = "slug = ?";
                $updateValues[] = $newSlug;
            }

            if (!empty($updateFields)) {
                $updateFields[] = "updated_at = NOW()";
                $updateValues[] = $articleId;

                $sql = "UPDATE articles SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $this->database->execute($sql, $updateValues);

                // Create version if content changed
                if ($contentChanged) {
                    $this->createArticleVersion($articleId, $articleData, $changesSummary);
                }

                // Handle tags if provided
                if (isset($articleData['tags'])) {
                    $this->handleArticleTags($articleId, $articleData['tags']);
                }

                // Clear caches
                $this->clearArticleCaches($articleId, $articleData['category_id'] ?? $currentArticle['category_id']);
            }

            $this->database->commit();

            return [
                'success' => true,
                'message' => 'Article updated successfully',
                'changes_made' => !empty($updateFields)
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete article with cleanup
     */
    public function deleteArticle(int $articleId): array
    {
        try {
            $this->database->beginTransaction();

            // Get article info for cleanup
            $article = $this->getArticle($articleId);
            if (!$article) {
                throw new Exception("Article not found");
            }

            // Delete related data
            $this->database->execute("DELETE FROM article_versions WHERE article_id = ?", [$articleId]);
            $this->database->execute("DELETE FROM comments WHERE article_id = ?", [$articleId]);
            $this->database->execute("DELETE FROM likes WHERE likeable_type = 'Article' AND likeable_id = ?", [$articleId]);

            // Delete article
            $this->database->execute("DELETE FROM articles WHERE id = ?", [$articleId]);

            // Clear caches
            $this->clearArticleCaches($articleId, $article['category_id']);

            $this->database->commit();

            return [
                'success' => true,
                'message' => 'Article deleted successfully'
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get article by ID with full details
     */
    public function getArticle(int $articleId): ?array
    {
        $cacheKey = "article:{$articleId}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT a.*, 
                       u.display_name as author_name, 
                       c.name as category_name, 
                       c.slug as category_slug,
                       GROUP_CONCAT(t.name) as tags
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                WHERE a.id = ?
                GROUP BY a.id";

        $stmt = $this->database->execute($sql, [$articleId]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($article) {
            $article['tags'] = $article['tags'] ? explode(',', $article['tags']) : [];
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

        $sql = "SELECT a.*, 
                       u.display_name as author_name, 
                       c.name as category_name, 
                       c.slug as category_slug,
                       GROUP_CONCAT(t.name) as tags
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                WHERE a.slug = ? AND a.status = 'published'
                GROUP BY a.id";

        $stmt = $this->database->execute($sql, [$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($article) {
            $article['tags'] = $article['tags'] ? explode(',', $article['tags']) : [];
            $this->cache->set($cacheKey, $article, 3600);
        }

        return $article;
    }

    /**
     * Get articles with advanced filtering and pagination
     */
    public function getArticles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $cacheKey = "articles:" . md5(serialize($filters) . $page . $perPage);
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $whereConditions = ["a.status = 'published'"];
        $params = [];
        $joins = [];

        // Category filter
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "a.category_id = ?";
            $params[] = $filters['category_id'];
        }

        // Author filter
        if (!empty($filters['author_id'])) {
            $whereConditions[] = "a.author_id = ?";
            $params[] = $filters['author_id'];
        }

        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Featured filter
        if (isset($filters['featured'])) {
            $whereConditions[] = "a.featured = ?";
            $params[] = $filters['featured'];
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "a.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereConditions[] = "a.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        // Build query
        $sql = "SELECT a.*, 
                       u.display_name as author_name, 
                       c.name as category_name, 
                       c.slug as category_slug,
                       GROUP_CONCAT(t.name) as tags
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN content_categories c ON a.category_id = c.id
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                WHERE " . implode(' AND ', $whereConditions) . "
                GROUP BY a.id
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->database->execute($sql, $params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process tags
        foreach ($articles as &$article) {
            $article['tags'] = $article['tags'] ? explode(',', $article['tags']) : [];
        }

        // Get total count for pagination
        $countSql = "SELECT COUNT(DISTINCT a.id) as total FROM articles a WHERE " . implode(' AND ', $whereConditions);
        $countStmt = $this->database->execute($countSql, array_slice($params, 0, -2));
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $result = [
            'articles' => $articles,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ];

        $this->cache->set($cacheKey, $result, 1800); // 30 minutes
        return $result;
    }

    // ==================== CATEGORY MANAGEMENT ====================

    /**
     * Get all categories with hierarchy
     */
    public function getCategories(): array
    {
        $cacheKey = "categories:hierarchy";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $sql = "SELECT * FROM content_categories WHERE is_active = 1 ORDER BY sort_order, name";
        $stmt = $this->database->execute($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build hierarchy
        $hierarchy = [];
        $categoryMap = [];

        foreach ($categories as $category) {
            $categoryMap[$category['id']] = $category;
            $category['children'] = [];
        }

        foreach ($categories as $category) {
            if ($category['parent_id']) {
                if (isset($categoryMap[$category['parent_id']])) {
                    $categoryMap[$category['parent_id']]['children'][] = $category;
                }
            } else {
                $hierarchy[] = $category;
            }
        }

        $this->cache->set($cacheKey, $hierarchy, 3600);
        return $hierarchy;
    }

    /**
     * Create new category
     */
    public function createCategory(array $categoryData): array
    {
        try {
            // Generate slug
            $slug = $this->generateSlug($categoryData['name']);
            if ($this->categorySlugExists($slug)) {
                $slug = $this->generateUniqueSlug($slug);
            }

            $sql = "INSERT INTO content_categories (name, slug, description, parent_id, image, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $this->database->execute($sql, [
                $categoryData['name'],
                $slug,
                $categoryData['description'] ?? '',
                $categoryData['parent_id'] ?? null,
                $categoryData['image'] ?? null,
                $categoryData['sort_order'] ?? 0,
                $categoryData['is_active'] ?? 1
            ]);

            $categoryId = $this->database->lastInsertId();

            // Clear cache
            $this->cache->delete("categories:hierarchy");

            return [
                'success' => true,
                'category_id' => $categoryId,
                'slug' => $slug,
                'message' => 'Category created successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ==================== FILE MANAGEMENT ====================

    /**
     * Upload file with validation
     */
    public function uploadFile(array $file, string $directory = 'general'): array
    {
        try {
            // Validate file
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                throw new Exception("Invalid file upload");
            }

            // Check file size
            if ($file['size'] > $this->maxFileSize) {
                throw new Exception("File size exceeds maximum limit of " . ($this->maxFileSize / 1024 / 1024) . "MB");
            }

            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedMimeTypes)) {
                throw new Exception("File type not allowed");
            }

            // Create directory if it doesn't exist
            $uploadDir = $this->uploadPath . $directory . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception("Failed to move uploaded file");
            }

            // Record file in database
            $sql = "INSERT INTO files (filename, original_name, filepath, mime_type, size, directory, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->database->execute($sql, [
                $filename,
                $file['name'],
                $filepath,
                $mimeType,
                $file['size'],
                $directory,
                $file['uploaded_by'] ?? null
            ]);

            $fileId = $this->database->lastInsertId();

            return [
                'success' => true,
                'file_id' => $fileId,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => '/' . $filepath,
                'size' => $file['size'],
                'mime_type' => $mimeType,
                'message' => 'File uploaded successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete file
     */
    public function deleteFile(int $fileId): array
    {
        try {
            // Get file info
            $sql = "SELECT * FROM files WHERE id = ?";
            $stmt = $this->database->execute($sql, [$fileId]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$file) {
                throw new Exception("File not found");
            }

            // Delete physical file
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']);
            }

            // Delete database record
            $this->database->execute("DELETE FROM files WHERE id = ?", [$fileId]);

            return [
                'success' => true,
                'message' => 'File deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Check if slug exists
     */
    private function slugExists(string $slug): bool
    {
        $stmt = $this->database->execute("SELECT COUNT(*) as count FROM articles WHERE slug = ?", [$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug(string $slug): string
    {
        $counter = 1;
        $uniqueSlug = $slug;
        
        while ($this->slugExists($uniqueSlug)) {
            $uniqueSlug = $slug . '-' . $counter;
            $counter++;
        }
        
        return $uniqueSlug;
    }

    /**
     * Check if category slug exists
     */
    private function categorySlugExists(string $slug): bool
    {
        $stmt = $this->database->execute("SELECT COUNT(*) as count FROM content_categories WHERE slug = ?", [$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Create article version
     */
    private function createArticleVersion(int $articleId, array $articleData, string $changesSummary): void
    {
        // Get current version number
        $stmt = $this->database->execute("SELECT MAX(version_number) as max_version FROM article_versions WHERE article_id = ?", [$articleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $versionNumber = ($result['max_version'] ?? 0) + 1;

        // Get the author ID from the current article if not provided
        $authorId = $articleData['author_id'] ?? null;
        if (!$authorId) {
            $stmt = $this->database->execute("SELECT author_id FROM articles WHERE id = ?", [$articleId]);
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
            $authorId = $article['author_id'];
        }

        $sql = "INSERT INTO article_versions (article_id, version_number, title, content, excerpt, changes_summary, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->database->execute($sql, [
            $articleId,
            $versionNumber,
            $articleData['title'],
            $articleData['content'],
            $articleData['excerpt'] ?? '',
            $changesSummary,
            $authorId
        ]);
    }

    /**
     * Handle article tags
     */
    private function handleArticleTags(int $articleId, $tags): void
    {
        // Remove existing tags
        $this->database->execute("DELETE FROM article_tags WHERE article_id = ?", [$articleId]);

        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            // Get or create tag
            $stmt = $this->database->execute("SELECT id FROM tags WHERE name = ?", [$tagName]);
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tag) {
                $this->database->execute("INSERT INTO tags (name, slug, created_at) VALUES (?, ?, NOW())", [$tagName, $this->generateSlug($tagName)]);
                $tagId = $this->database->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }

            // Link tag to article
            $this->database->execute("INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)", [$articleId, $tagId]);
        }
    }

    /**
     * Clear article caches
     */
    private function clearArticleCaches(int $articleId, ?int $categoryId): void
    {
        $this->cache->delete("article:{$articleId}");
        $this->cache->delete("articles:list");
        
        if ($categoryId) {
            $this->cache->delete("articles:category:{$categoryId}");
        }
        
        $this->cache->delete("articles:*"); // Clear all article caches
    }

    /**
     * Get article versions
     */
    public function getArticleVersions(int $articleId): array
    {
        $sql = "SELECT av.*, u.display_name as created_by_name 
                FROM article_versions av
                LEFT JOIN users u ON av.created_by = u.id
                WHERE av.article_id = ?
                ORDER BY av.version_number DESC";

        $stmt = $this->database->execute($sql, [$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get content statistics
     */
    public function getContentStatistics(): array
    {
        $cacheKey = "content:statistics";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stats = [];

        // Article counts
        $stmt = $this->database->execute("SELECT status, COUNT(*) as count FROM articles GROUP BY status");
        $stats['articles'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Category counts
        $stmt = $this->database->execute("SELECT COUNT(*) as count FROM content_categories WHERE is_active = 1");
        $stats['categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // File counts
        $stmt = $this->database->execute("SELECT COUNT(*) as count FROM files");
        $stats['files'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Recent activity
        $stmt = $this->database->execute("SELECT COUNT(*) as count FROM articles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['recent_articles'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $this->cache->set($cacheKey, $stats, 1800); // 30 minutes
        return $stats;
    }

    /**
     * Get category count
     */
    public function getCategoryCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM content_categories WHERE is_active = 1";
            $stmt = $this->database->execute($sql);
            return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Error getting category count: " . $e->getMessage());
            return 0;
        }
    }
} 