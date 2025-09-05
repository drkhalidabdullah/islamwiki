<?php

use IslamWiki\Core\Database\DatabaseManager;

/**
 * Create Translation System Migration
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class CreateTranslationSystem
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
        $this->createLanguagesTable();
        $this->createTranslationsTable();
        $this->createTranslationMemoryTable();
        $this->createTranslationJobsTable();
        $this->createUserLanguageSkillsTable();
        $this->insertDefaultLanguages();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->dropTables();
    }

    /**
     * Create languages table
     */
    private function createLanguagesTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `languages` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(5) NOT NULL UNIQUE,
            `name` varchar(100) NOT NULL,
            `native_name` varchar(100) NOT NULL,
            `direction` enum('ltr', 'rtl') NOT NULL DEFAULT 'ltr',
            `flag` varchar(10) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `is_default` tinyint(1) NOT NULL DEFAULT 0,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_code` (`code`),
            KEY `idx_is_active` (`is_active`),
            KEY `idx_is_default` (`is_default`),
            KEY `idx_sort_order` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create translations table
     */
    private function createTranslationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `translations` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `source_text` text NOT NULL,
            `translated_text` text NOT NULL,
            `source_language` varchar(5) NOT NULL,
            `target_language` varchar(5) NOT NULL,
            `provider` varchar(50) NOT NULL,
            `confidence_score` decimal(3,2) DEFAULT NULL,
            `context` varchar(255) DEFAULT NULL,
            `content_type` enum('text', 'article', 'comment', 'title', 'description') NOT NULL DEFAULT 'text',
            `content_id` bigint(20) unsigned DEFAULT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_source_language` (`source_language`),
            KEY `idx_target_language` (`target_language`),
            KEY `idx_provider` (`provider`),
            KEY `idx_content_type` (`content_type`),
            KEY `idx_content_id` (`content_id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_created_at` (`created_at`),
            FULLTEXT KEY `idx_source_text` (`source_text`),
            FULLTEXT KEY `idx_translated_text` (`translated_text`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create translation_memory table
     */
    private function createTranslationMemoryTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `translation_memory` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `source_text_hash` varchar(64) NOT NULL,
            `source_text` text NOT NULL,
            `translated_text` text NOT NULL,
            `source_language` varchar(5) NOT NULL,
            `target_language` varchar(5) NOT NULL,
            `provider` varchar(50) NOT NULL,
            `usage_count` int(11) NOT NULL DEFAULT 1,
            `last_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_source_hash_lang_pair` (`source_text_hash`, `source_language`, `target_language`),
            KEY `idx_source_language` (`source_language`),
            KEY `idx_target_language` (`target_language`),
            KEY `idx_provider` (`provider`),
            KEY `idx_usage_count` (`usage_count`),
            KEY `idx_last_used` (`last_used`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create translation_jobs table
     */
    private function createTranslationJobsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `translation_jobs` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `job_type` enum('article', 'batch', 'content') NOT NULL,
            `source_language` varchar(5) NOT NULL,
            `target_language` varchar(5) NOT NULL,
            `status` enum('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
            `priority` int(11) NOT NULL DEFAULT 0,
            `total_items` int(11) NOT NULL DEFAULT 0,
            `processed_items` int(11) NOT NULL DEFAULT 0,
            `failed_items` int(11) NOT NULL DEFAULT 0,
            `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
            `content_data` longtext DEFAULT NULL,
            `result_data` longtext DEFAULT NULL,
            `error_message` text DEFAULT NULL,
            `created_by` bigint(20) unsigned DEFAULT NULL,
            `started_at` timestamp NULL DEFAULT NULL,
            `completed_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_job_type` (`job_type`),
            KEY `idx_status` (`status`),
            KEY `idx_priority` (`priority`),
            KEY `idx_created_by` (`created_by`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Create user_language_skills table
     */
    private function createUserLanguageSkillsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `user_language_skills` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL,
            `language_code` varchar(5) NOT NULL,
            `proficiency_level` enum('beginner', 'intermediate', 'advanced', 'native') NOT NULL DEFAULT 'beginner',
            `is_preferred` tinyint(1) NOT NULL DEFAULT 0,
            `is_learning` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_user_language` (`user_id`, `language_code`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_language_code` (`language_code`),
            KEY `idx_proficiency_level` (`proficiency_level`),
            KEY `idx_is_preferred` (`is_preferred`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->database->execute($sql);
    }

    /**
     * Insert default languages
     */
    private function insertDefaultLanguages(): void
    {
        $languages = [
            ['en', 'English', 'English', 'ltr', '🇺🇸', 1, 1, 1],
            ['ar', 'Arabic', 'العربية', 'rtl', '🇸🇦', 1, 0, 2],
            ['fr', 'French', 'Français', 'ltr', '🇫🇷', 1, 0, 3],
            ['es', 'Spanish', 'Español', 'ltr', '🇪🇸', 1, 0, 4],
            ['de', 'German', 'Deutsch', 'ltr', '🇩🇪', 1, 0, 5],
            ['it', 'Italian', 'Italiano', 'ltr', '🇮🇹', 1, 0, 6],
            ['pt', 'Portuguese', 'Português', 'ltr', '🇵🇹', 1, 0, 7],
            ['ru', 'Russian', 'Русский', 'ltr', '🇷🇺', 1, 0, 8],
            ['zh', 'Chinese', '中文', 'ltr', '🇨🇳', 1, 0, 9],
            ['ja', 'Japanese', '日本語', 'ltr', '��🇵', 1, 0, 10],
            ['ko', 'Korean', '한국어', 'ltr', '🇰🇷', 1, 0, 11],
            ['ur', 'Urdu', 'اردو', 'rtl', '🇵🇰', 1, 0, 12],
            ['tr', 'Turkish', 'Türkçe', 'ltr', '🇹🇷', 1, 0, 13],
            ['id', 'Indonesian', 'Bahasa Indonesia', 'ltr', '🇮🇩', 1, 0, 14],
            ['ms', 'Malay', 'Bahasa Melayu', 'ltr', '🇲🇾', 1, 0, 15]
        ];

        foreach ($languages as $lang) {
            $sql = "INSERT IGNORE INTO `languages` 
                    (`code`, `name`, `native_name`, `direction`, `flag`, `is_active`, `is_default`, `sort_order`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->database->execute($sql, $lang);
        }
    }

    /**
     * Drop all translation tables
     */
    private function dropTables(): void
    {
        $tables = [
            'user_language_skills',
            'translation_jobs',
            'translation_memory',
            'translations',
            'languages'
        ];

        foreach ($tables as $table) {
            $this->database->execute("DROP TABLE IF EXISTS `{$table}`");
        }
    }
}
