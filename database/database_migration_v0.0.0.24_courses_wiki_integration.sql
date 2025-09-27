-- Courses Wiki Integration Database Migration
-- Version: 0.0.0.24
-- Description: Integrate courses system into wiki system

-- 1. Add course-specific fields to wiki_articles table
ALTER TABLE `wiki_articles` 
ADD COLUMN IF NOT EXISTS `course_type` ENUM('course', 'lesson', 'regular') DEFAULT 'regular' AFTER `content_model`,
ADD COLUMN IF NOT EXISTS `course_metadata` JSON NULL AFTER `course_type`,
ADD COLUMN IF NOT EXISTS `parent_course_id` BIGINT UNSIGNED NULL AFTER `course_metadata`,
ADD COLUMN IF NOT EXISTS `lesson_type` ENUM('text', 'video', 'audio', 'quiz', 'assignment') DEFAULT 'text' AFTER `parent_course_id`,
ADD COLUMN IF NOT EXISTS `lesson_duration` INT DEFAULT 0 AFTER `lesson_type`,
ADD COLUMN IF NOT EXISTS `lesson_sort_order` INT DEFAULT 0 AFTER `lesson_duration`,
ADD COLUMN IF NOT EXISTS `difficulty_level` ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner' AFTER `lesson_sort_order`,
ADD COLUMN IF NOT EXISTS `estimated_duration` INT DEFAULT 0 AFTER `difficulty_level`,
ADD COLUMN IF NOT EXISTS `thumbnail_url` VARCHAR(500) NULL AFTER `estimated_duration`,
ADD COLUMN IF NOT EXISTS `is_course_featured` BOOLEAN DEFAULT FALSE AFTER `thumbnail_url`;

-- 2. Add indexes for course-related fields
ALTER TABLE `wiki_articles` 
ADD INDEX `idx_course_type` (`course_type`),
ADD INDEX `idx_parent_course_id` (`parent_course_id`),
ADD INDEX `idx_lesson_sort_order` (`lesson_sort_order`),
ADD INDEX `idx_difficulty_level` (`difficulty_level`),
ADD INDEX `idx_is_course_featured` (`is_course_featured`);

-- 3. Add foreign key for parent course
ALTER TABLE `wiki_articles` 
ADD CONSTRAINT `fk_wiki_articles_parent_course` 
FOREIGN KEY (`parent_course_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE;

-- 4. Create course progress tracking table (updated to work with wiki articles)
CREATE TABLE IF NOT EXISTS `wiki_course_progress` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `course_article_id` BIGINT UNSIGNED NOT NULL,
    `current_lesson_id` BIGINT UNSIGNED NULL,
    `progress_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `time_spent` INT DEFAULT 0, -- in minutes
    `last_accessed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`current_lesson_id`) REFERENCES `wiki_articles` (`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_user_course` (`user_id`, `course_article_id`),
    INDEX `idx_user_progress` (`user_id`, `progress_percentage`),
    INDEX `idx_course_progress` (`course_article_id`, `progress_percentage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create course completions table (updated to work with wiki articles)
CREATE TABLE IF NOT EXISTS `wiki_course_completions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `course_article_id` BIGINT UNSIGNED NOT NULL,
    `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completion_percentage` DECIMAL(5,2) DEFAULT 100.00,
    `time_spent` INT DEFAULT 0, -- total time in minutes
    `is_completed` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_article_id`) REFERENCES `wiki_articles` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_course_completion` (`user_id`, `course_article_id`),
    INDEX `idx_user_completions` (`user_id`, `completed_at`),
    INDEX `idx_course_completions` (`course_article_id`, `completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create course categories as wiki categories
INSERT IGNORE INTO `content_categories` (`name`, `slug`, `description`, `sort_order`, `is_active`) VALUES
('Course: Quran Studies', 'course-quran-studies', 'Learn about the Holy Quran, its recitation, and interpretation', 1, 1),
('Course: Hadith Studies', 'course-hadith-studies', 'Study the sayings and teachings of Prophet Muhammad (PBUH)', 2, 1),
('Course: Islamic History', 'course-islamic-history', 'Explore the rich history of Islam and Muslim civilizations', 3, 1),
('Course: Fiqh & Jurisprudence', 'course-fiqh-jurisprudence', 'Learn Islamic law and legal principles', 4, 1),
('Course: Aqeedah & Theology', 'course-aqeedah-theology', 'Study Islamic beliefs and theological concepts', 5, 1),
('Course: Arabic Language', 'course-arabic-language', 'Learn the Arabic language for better understanding of Islamic texts', 6, 1),
('Course: Seerah & Biography', 'course-seerah-biography', 'Learn about the life and teachings of Prophet Muhammad (PBUH)', 7, 1),
('Course: Contemporary Issues', 'course-contemporary-issues', 'Explore modern Islamic topics and current affairs', 8, 1);

-- 7. Create a special "Courses" namespace for course articles
INSERT IGNORE INTO `wiki_namespaces` (`name`, `display_name`, `is_talk`, `sort_order`) VALUES
('Course', 'Course', FALSE, 15);

-- 8. Get the Course namespace ID for later use
SET @course_namespace_id = (SELECT id FROM wiki_namespaces WHERE name = 'Course');

-- 9. Migrate existing courses to wiki articles
-- First, create course overview articles
INSERT INTO `wiki_articles` (
    `title`, `slug`, `content`, `excerpt`, `category_id`, `author_id`, `status`, 
    `is_featured`, `namespace_id`, `course_type`, `course_metadata`, 
    `difficulty_level`, `estimated_duration`, `is_course_featured`, `published_at`
)
SELECT 
    c.title,
    CONCAT('Course:', c.slug) as slug,
    CONCAT('<h1>', c.title, '</h1><p>', COALESCE(c.description, ''), '</p>') as content,
    COALESCE(c.short_description, '') as excerpt,
    cc.id as category_id,
    COALESCE(c.created_by, 1) as author_id,
    CASE WHEN c.is_published = 1 THEN 'published' ELSE 'draft' END as status,
    c.is_featured,
    @course_namespace_id as namespace_id,
    'course' as course_type,
    JSON_OBJECT(
        'original_course_id', c.id,
        'category', c.category,
        'difficulty_level', c.difficulty_level,
        'estimated_duration', c.estimated_duration,
        'thumbnail_url', COALESCE(c.thumbnail_url, ''),
        'sort_order', c.sort_order
    ) as course_metadata,
    c.difficulty_level,
    c.estimated_duration,
    c.is_featured,
    CASE WHEN c.is_published = 1 THEN c.created_at ELSE NULL END as published_at
FROM courses c
LEFT JOIN content_categories cc ON CONCAT('course-', c.category) = cc.slug
WHERE c.id IS NOT NULL;

-- 10. Migrate course lessons to wiki articles
INSERT INTO `wiki_articles` (
    `title`, `slug`, `content`, `excerpt`, `category_id`, `author_id`, `status`,
    `namespace_id`, `course_type`, `parent_course_id`, `lesson_type`, 
    `lesson_duration`, `lesson_sort_order`, `published_at`
)
SELECT 
    cl.title,
    CONCAT('Course:', c.slug, '/', cl.slug) as slug,
    COALESCE(cl.content, CONCAT('<h1>', cl.title, '</h1>')) as content,
    LEFT(COALESCE(cl.content, ''), 200) as excerpt,
    cc.id as category_id,
    COALESCE(c.created_by, 1) as author_id,
    CASE WHEN cl.is_published = 1 THEN 'published' ELSE 'draft' END as status,
    @course_namespace_id as namespace_id,
    'lesson' as course_type,
    wa.id as parent_course_id,
    cl.lesson_type,
    cl.duration as lesson_duration,
    cl.sort_order as lesson_sort_order,
    CASE WHEN cl.is_published = 1 THEN cl.created_at ELSE NULL END as published_at
FROM course_lessons cl
JOIN courses c ON cl.course_id = c.id
JOIN wiki_articles wa ON wa.slug = CONCAT('Course:', c.slug) AND wa.course_type = 'course'
LEFT JOIN content_categories cc ON CONCAT('course-', c.category) = cc.slug
WHERE cl.id IS NOT NULL;

-- 11. Migrate user course progress to new wiki-based system
INSERT INTO `wiki_course_progress` (
    `user_id`, `course_article_id`, `current_lesson_id`, `progress_percentage`, 
    `time_spent`, `started_at`, `last_accessed_at`
)
SELECT 
    ucp.user_id,
    wa_course.id as course_article_id,
    wa_lesson.id as current_lesson_id,
    ucp.progress_percentage,
    ucp.time_spent,
    ucp.started_at,
    ucp.last_accessed_at
FROM user_course_progress ucp
JOIN courses c ON ucp.course_id = c.id
JOIN wiki_articles wa_course ON wa_course.slug = CONCAT('Course:', c.slug) AND wa_course.course_type = 'course'
LEFT JOIN course_lessons cl ON ucp.lesson_id = cl.id
LEFT JOIN wiki_articles wa_lesson ON wa_lesson.slug = CONCAT('Course:', c.slug, '/', cl.slug) AND wa_lesson.course_type = 'lesson'
WHERE ucp.id IS NOT NULL;

-- 12. Migrate user course completions to new wiki-based system
INSERT INTO `wiki_course_completions` (
    `user_id`, `course_article_id`, `completed_at`, `completion_percentage`, 
    `time_spent`, `is_completed`
)
SELECT 
    ucc.user_id,
    wa_course.id as course_article_id,
    ucc.completed_at,
    ucc.completion_percentage,
    ucc.time_spent,
    ucc.is_completed
FROM user_course_completions ucc
JOIN courses c ON ucc.course_id = c.id
JOIN wiki_articles wa_course ON wa_course.slug = CONCAT('Course:', c.slug) AND wa_course.course_type = 'course'
WHERE ucc.id IS NOT NULL;

-- 13. Create a special "Courses" index page
INSERT IGNORE INTO `wiki_articles` (
    `title`, `slug`, `content`, `excerpt`, `author_id`, `status`, 
    `is_featured`, `namespace_id`, `course_type`, `published_at`
) VALUES (
    'Courses',
    'Courses',
    '<h1>Islamic Learning Courses</h1>
    <p>Welcome to our comprehensive collection of Islamic learning courses. These courses are designed to help you deepen your understanding of Islam through structured, progressive learning.</p>
    
    <h2>Available Course Categories</h2>
    <div class="course-categories-grid">
        <div class="course-category">
            <h3><i class="iw iw-book-quran"></i> Quran Studies</h3>
            <p>Learn about the Holy Quran, its recitation, and interpretation</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-book"></i> Hadith Studies</h3>
            <p>Study the sayings and teachings of Prophet Muhammad (PBUH)</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-history"></i> Islamic History</h3>
            <p>Explore the rich history of Islam and Muslim civilizations</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-balance"></i> Fiqh & Jurisprudence</h3>
            <p>Learn Islamic law and legal principles</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-heart"></i> Aqeedah & Theology</h3>
            <p>Study Islamic beliefs and theological concepts</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-language"></i> Arabic Language</h3>
            <p>Learn the Arabic language for better understanding of Islamic texts</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-user"></i> Seerah & Biography</h3>
            <p>Learn about the life and teachings of Prophet Muhammad (PBUH)</p>
        </div>
        <div class="course-category">
            <h3><i class="iw iw-globe"></i> Contemporary Issues</h3>
            <p>Explore modern Islamic topics and current affairs</p>
        </div>
    </div>
    
    <h2>How to Use This Course System</h2>
    <p>Each course is organized into lessons that build upon each other. Start with beginner courses and progress to more advanced topics. Track your progress as you complete each lesson.</p>
    
    <h2>Getting Started</h2>
    <p>To begin learning, browse the course categories above or use the search function to find specific topics of interest.</p>',
    'Comprehensive collection of Islamic learning courses covering Quran studies, Hadith, Islamic history, and more.',
    1,
    'published',
    1,
    1, -- Main namespace
    'regular',
    NOW()
);

-- 14. Add URL rewrite rules for course redirects (will be handled in .htaccess)
-- Note: This will be implemented in the .htaccess file to redirect /courses to /wiki/Courses
