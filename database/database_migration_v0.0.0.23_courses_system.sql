-- Courses System Database Migration
-- Version: 0.0.0.23
-- Description: Create tables for Islamic learning courses system

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    category VARCHAR(100) DEFAULT 'general',
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    estimated_duration INT DEFAULT 0, -- in minutes
    thumbnail_url VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_difficulty (difficulty_level),
    INDEX idx_published (is_published),
    INDEX idx_featured (is_featured)
);

-- Course lessons table
CREATE TABLE IF NOT EXISTS course_lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content TEXT,
    lesson_type ENUM('text', 'video', 'audio', 'quiz', 'assignment') DEFAULT 'text',
    duration INT DEFAULT 0, -- in minutes
    sort_order INT DEFAULT 0,
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_lesson (course_id, slug),
    INDEX idx_course_sort (course_id, sort_order),
    INDEX idx_published (is_published)
);

-- User course progress table
CREATE TABLE IF NOT EXISTS user_course_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id INT NOT NULL,
    lesson_id INT,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    time_spent INT DEFAULT 0, -- in minutes
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_course (user_id, course_id),
    INDEX idx_user_progress (user_id, progress_percentage),
    INDEX idx_course_progress (course_id, progress_percentage)
);

-- User course completions table
CREATE TABLE IF NOT EXISTS user_course_completions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_percentage DECIMAL(5,2) DEFAULT 100.00,
    time_spent INT DEFAULT 0, -- total time in minutes
    is_completed BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course_completion (user_id, course_id),
    INDEX idx_user_completions (user_id, completed_at),
    INDEX idx_course_completions (course_id, completed_at)
);

-- Course categories table
CREATE TABLE IF NOT EXISTS course_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    icon VARCHAR(50),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default course categories
INSERT INTO course_categories (name, slug, description, color, icon, sort_order) VALUES
('Quran Studies', 'quran-studies', 'Learn about the Holy Quran, its recitation, and interpretation', '#28a745', 'iw-book-quran', 1),
('Hadith Studies', 'hadith-studies', 'Study the sayings and teachings of Prophet Muhammad (PBUH)', '#17a2b8', 'iw-book', 2),
('Islamic History', 'islamic-history', 'Explore the rich history of Islam and Muslim civilizations', '#6f42c1', 'iw-history', 3),
('Fiqh & Jurisprudence', 'fiqh-jurisprudence', 'Learn Islamic law and legal principles', '#fd7e14', 'iw-balance', 4),
('Aqeedah & Theology', 'aqeedah-theology', 'Study Islamic beliefs and theological concepts', '#20c997', 'iw-heart', 5),
('Arabic Language', 'arabic-language', 'Learn the Arabic language for better understanding of Islamic texts', '#e83e8c', 'iw-language', 6),
('Seerah & Biography', 'seerah-biography', 'Learn about the life and teachings of Prophet Muhammad (PBUH)', '#6c757d', 'iw-user', 7),
('Contemporary Issues', 'contemporary-issues', 'Explore modern Islamic topics and current affairs', '#343a40', 'iw-globe', 8);

-- Insert sample courses
INSERT INTO courses (title, slug, description, short_description, category, difficulty_level, estimated_duration, is_featured, created_by) VALUES
('Introduction to Quran Reading', 'introduction-quran-reading', 
'Learn the basics of Quranic Arabic and proper recitation techniques. This course covers the Arabic alphabet, basic pronunciation rules, and simple verses from the Quran.',
'Master the fundamentals of Quranic Arabic and recitation',
'quran-studies', 'beginner', 120, TRUE, 1),

('Understanding Salah (Prayer)', 'understanding-salah-prayer',
'A comprehensive guide to Islamic prayer, covering the five daily prayers, their significance, proper performance, and spiritual benefits.',
'Learn the complete practice of Islamic prayer',
'fiqh-jurisprudence', 'beginner', 90, TRUE, 1),

('Life of Prophet Muhammad (PBUH)', 'life-prophet-muhammad',
'Explore the biography of Prophet Muhammad (PBUH), his early life, prophethood, migration to Medina, and his lasting impact on the world.',
'Discover the inspiring life story of the final Prophet',
'seerah-biography', 'beginner', 180, TRUE, 1),

('Basic Arabic Grammar', 'basic-arabic-grammar',
'Introduction to Arabic grammar fundamentals including nouns, verbs, sentence structure, and common grammatical rules.',
'Build a strong foundation in Arabic grammar',
'arabic-language', 'beginner', 150, FALSE, 1),

('Islamic Beliefs (Aqeedah)', 'islamic-beliefs-aqeedah',
'Study the fundamental beliefs of Islam including the six articles of faith, Tawheed (monotheism), and core theological concepts.',
'Understand the essential beliefs of Islam',
'aqeedah-theology', 'intermediate', 200, FALSE, 1);

-- Insert sample lessons for the first course
INSERT INTO course_lessons (course_id, title, slug, content, lesson_type, duration, sort_order) VALUES
(1, 'Arabic Alphabet Basics', 'arabic-alphabet-basics', 
'<h2>Arabic Alphabet Basics</h2><p>Welcome to your first lesson in Quranic Arabic! In this lesson, we will learn the 28 letters of the Arabic alphabet.</p><h3>Learning Objectives:</h3><ul><li>Recognize all 28 Arabic letters</li><li>Understand letter forms (isolated, initial, medial, final)</li><li>Practice basic pronunciation</li></ul><h3>Arabic Letters:</h3><p>The Arabic alphabet consists of 28 letters, each with different forms depending on their position in a word...</p>', 
'text', 30, 1),

(1, 'Pronunciation Rules', 'pronunciation-rules',
'<h2>Pronunciation Rules</h2><p>Proper pronunciation is essential for Quranic recitation. This lesson covers the basic rules of Arabic pronunciation.</p><h3>Key Points:</h3><ul><li>Makharij (articulation points)</li><li>Sifat (characteristics of letters)</li><li>Common pronunciation mistakes to avoid</li></ul>',
'text', 25, 2),

(1, 'Reading Simple Verses', 'reading-simple-verses',
'<h2>Reading Simple Verses</h2><p>Now that you know the alphabet and pronunciation rules, let\'s practice reading some simple verses from the Quran.</p><h3>Practice Verses:</h3><p>We will start with short, simple verses and gradually build up to longer passages...</p>',
'text', 35, 3),

(1, 'Quiz: Alphabet and Pronunciation', 'quiz-alphabet-pronunciation',
'<h2>Quiz: Alphabet and Pronunciation</h2><p>Test your knowledge of the Arabic alphabet and pronunciation rules.</p><h3>Questions:</h3><ol><li>How many letters are in the Arabic alphabet?</li><li>What are the three main articulation points?</li><li>Which letter is pronounced from the throat?</li></ol>',
'quiz', 20, 4);

-- Insert sample lessons for the second course
INSERT INTO course_lessons (course_id, title, slug, content, lesson_type, duration, sort_order) VALUES
(2, 'Introduction to Salah', 'introduction-salah',
'<h2>Introduction to Salah</h2><p>Salah is one of the five pillars of Islam and is the most important act of worship for Muslims.</p><h3>What is Salah?</h3><p>Salah is the ritual prayer performed five times a day by Muslims. It is a direct connection between the worshipper and Allah...</p>',
'text', 20, 1),

(2, 'The Five Daily Prayers', 'five-daily-prayers',
'<h2>The Five Daily Prayers</h2><p>Learn about the five obligatory daily prayers and their timings.</p><h3>Prayer Times:</h3><ul><li>Fajr (Dawn)</li><li>Dhuhr (Midday)</li><li>Asr (Afternoon)</li><li>Maghrib (Sunset)</li><li>Isha (Night)</li></ul>',
'text', 25, 2),

(2, 'Wudu (Ablution)', 'wudu-ablution',
'<h2>Wudu (Ablution)</h2><p>Wudu is the ritual washing required before performing Salah.</p><h3>Steps of Wudu:</h3><ol><li>Intention (Niyyah)</li><li>Wash hands three times</li><li>Rinse mouth three times</li><li>Rinse nose three times</li><li>Wash face three times</li><li>Wash arms up to elbows three times</li><li>Wipe head once</li><li>Wash feet up to ankles three times</li></ol>',
'text', 30, 3),

(2, 'Prayer Positions and Movements', 'prayer-positions-movements',
'<h2>Prayer Positions and Movements</h2><p>Learn the correct positions and movements for Salah.</p><h3>Positions:</h3><ul><li>Standing (Qiyam)</li><li>Bowing (Ruku)</li><li>Prostration (Sujood)</li><li>Sitting (Jalsa)</li></ul>',
'text', 35, 4);

-- Insert sample lessons for the third course
INSERT INTO course_lessons (course_id, title, slug, content, lesson_type, duration, sort_order) VALUES
(3, 'Early Life in Mecca', 'early-life-mecca',
'<h2>Early Life in Mecca</h2><p>Learn about Prophet Muhammad\'s (PBUH) early life in Mecca before his prophethood.</p><h3>Key Events:</h3><ul><li>Birth and childhood</li><li>Life with his grandfather and uncle</li><li>Marriage to Khadijah (RA)</li><li>Character and reputation</li></ul>',
'text', 40, 1),

(3, 'The First Revelation', 'first-revelation',
'<h2>The First Revelation</h2><p>Discover the momentous event when Prophet Muhammad (PBUH) received his first revelation from Allah.</p><h3>The Cave of Hira:</h3><p>It was in the Cave of Hira that Angel Jibril (Gabriel) first appeared to Prophet Muhammad (PBUH)...</p>',
'text', 35, 2),

(3, 'Early Muslims and Persecution', 'early-muslims-persecution',
'<h2>Early Muslims and Persecution</h2><p>Learn about the first Muslims and the challenges they faced in Mecca.</p><h3>First Muslims:</h3><ul><li>Khadijah (RA) - First believer</li><li>Abu Bakr (RA) - First male believer</li><li>Ali (RA) - First child believer</li><li>Zaid ibn Harithah (RA)</li></ul>',
'text', 30, 3),

(3, 'Migration to Medina', 'migration-medina',
'<h2>Migration to Medina (Hijrah)</h2><p>The Hijrah marks a turning point in Islamic history and the beginning of the Islamic calendar.</p><h3>Events of Hijrah:</h3><p>The migration was not just a physical journey but a spiritual transformation...</p>',
'text', 45, 4);
