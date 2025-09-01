<?php

use IslamWiki\Core\Database\DatabaseManager;

/**
 * Add Content Enhancement Tables Migration
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class AddContentEnhancementTables
{
    private DatabaseManager $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->createTagsTable();
        $this->createArticleTagsTable();
        $this->createFilesTable();
        $this->insertDefaultTags();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropTables();
    }

    /**
     * Create tags table
     */
    private function createTagsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `tags` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL UNIQUE,
            `slug` varchar(100) NOT NULL UNIQUE,
            `description` text,
            `color` varchar(7) DEFAULT '#3B82F6',
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_name` (`name`),
            KEY `idx_slug` (`slug`),
            KEY `idx_is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create article_tags table
     */
    private function createArticleTagsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `article_tags` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `article_id` bigint(20) unsigned NOT NULL,
            `tag_id` bigint(20) unsigned NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_article_tag` (`article_id`, `tag_id`),
            KEY `idx_article_id` (`article_id`),
            KEY `idx_tag_id` (`tag_id`),
            FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create files table
     */
    private function createFilesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `files` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `filename` varchar(255) NOT NULL,
            `original_name` varchar(255) NOT NULL,
            `filepath` varchar(500) NOT NULL,
            `mime_type` varchar(100) NOT NULL,
            `size` bigint(20) unsigned NOT NULL,
            `directory` varchar(100) NOT NULL DEFAULT 'general',
            `uploaded_by` bigint(20) unsigned,
            `is_public` tinyint(1) NOT NULL DEFAULT 1,
            `download_count` bigint(20) unsigned NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_filename` (`filename`),
            KEY `idx_directory` (`directory`),
            KEY `idx_uploaded_by` (`uploaded_by`),
            KEY `idx_mime_type` (`mime_type`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Insert default tags
     */
    private function insertDefaultTags(): void
    {
        $defaultTags = [
            ['name' => 'Islamic Law', 'slug' => 'islamic-law', 'description' => 'Islamic jurisprudence and legal rulings', 'color' => '#10B981'],
            ['name' => 'Quran', 'slug' => 'quran', 'description' => 'Quranic studies and interpretation', 'color' => '#3B82F6'],
            ['name' => 'Hadith', 'slug' => 'hadith', 'description' => 'Prophetic traditions and sayings', 'color' => '#8B5CF6'],
            ['name' => 'Islamic History', 'slug' => 'islamic-history', 'description' => 'Islamic civilization and history', 'color' => '#F59E0B'],
            ['name' => 'Islamic Ethics', 'slug' => 'islamic-ethics', 'description' => 'Moral and ethical teachings', 'color' => '#EF4444'],
            ['name' => 'Islamic Practices', 'slug' => 'islamic-practices', 'description' => 'Daily Islamic practices and rituals', 'color' => '#06B6D4'],
            ['name' => 'Islamic Beliefs', 'slug' => 'islamic-beliefs', 'description' => 'Core Islamic beliefs and theology', 'color' => '#84CC16'],
            ['name' => 'Islamic Scholars', 'slug' => 'islamic-scholars', 'description' => 'Notable Islamic scholars and their works', 'color' => '#F97316'],
            ['name' => 'Islamic Education', 'slug' => 'islamic-education', 'description' => 'Islamic learning and education', 'color' => '#EC4899'],
            ['name' => 'Islamic Culture', 'slug' => 'islamic-culture', 'description' => 'Islamic culture and traditions', 'color' => '#6366F1']
        ];

        foreach ($defaultTags as $tag) {
            $sql = "INSERT IGNORE INTO `tags` (name, slug, description, color) VALUES (?, ?, ?, ?)";
            $this->database->execute($sql, [
                $tag['name'],
                $tag['slug'],
                $tag['description'],
                $tag['color']
            ]);
        }
    }

    /**
     * Drop all tables
     */
    private function dropTables(): void
    {
        $tables = ['article_tags', 'tags', 'files'];

        foreach ($tables as $table) {
            $this->database->execute("DROP TABLE IF EXISTS `{$table}`");
        }
    }
} 