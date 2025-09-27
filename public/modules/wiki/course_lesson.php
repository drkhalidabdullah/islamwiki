<?php
/**
 * Course Lesson Handler
 * Handles individual course lessons
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';
require_once __DIR__ . '/../../includes/markdown/MarkdownParser.php';
require_once __DIR__ . '/../../includes/markdown/WikiParser.php';

// Require login for all site access
require_login();

// Check maintenance mode
check_maintenance_mode();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

$page_title = 'Course Lesson';
$slug = $_GET['slug'] ?? '';
$title = $_GET['title'] ?? '';

// Handle course lesson URLs (e.g., Course:Introduction to Quran Reading/Lesson 1)
if ($title) {
    $parsed_title = parse_wiki_title($title);
    $namespace = $parsed_title['namespace'];
    $article_title = $parsed_title['title'];
    
    if ($namespace !== 'Course') {
        // Not a course lesson, redirect to regular article handler
        header('Location: /wiki/' . urlencode($title));
        exit;
    }
    
    // Split course title and lesson title
    if (strpos($article_title, '/') !== false) {
        $parts = explode('/', $article_title, 2);
        $course_slug = $parts[0];
        $lesson_slug = $parts[1];
    } else {
        // No lesson specified, redirect to course
        header('Location: /wiki/Course:' . urlencode($article_title));
        exit;
    }
} else {
    // Handle direct lesson access
    $lesson_slug = $slug;
}

// Get the lesson article
$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name, cc.color as category_color, cc.icon as category_icon,
           wn.name as namespace_name, wn.display_name as namespace_display_name,
           parent_wa.title as course_title, parent_wa.slug as course_slug
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
    LEFT JOIN wiki_articles parent_wa ON wa.parent_course_id = parent_wa.id
    WHERE wa.slug = ? AND wa.course_type = 'lesson' AND wa.status = 'published'
");

$stmt->execute([$lesson_slug]);
$lesson = $stmt->fetch();

if (!$lesson) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/../../pages/404.php';
    exit;
}

// Get the parent course
$course_stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name, cc.color as category_color, cc.icon as category_icon
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.id = ? AND wa.course_type = 'course' AND wa.status = 'published'
");

$course_stmt->execute([$lesson['parent_course_id']]);
$course = $course_stmt->fetch();

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/../../pages/404.php';
    exit;
}

// Get all lessons in the course for navigation
$lessons_stmt = $pdo->prepare("
    SELECT wa.*
    FROM wiki_articles wa
    WHERE wa.parent_course_id = ? AND wa.course_type = 'lesson' AND wa.status = 'published'
    ORDER BY wa.lesson_sort_order, wa.title
");

$lessons_stmt->execute([$course['id']]);
$all_lessons = $lessons_stmt->fetchAll();

// Find current lesson index
$current_lesson_index = -1;
foreach ($all_lessons as $index => $l) {
    if ($l['id'] == $lesson['id']) {
        $current_lesson_index = $index;
        break;
    }
}

// Get previous and next lessons
$previous_lesson = ($current_lesson_index > 0) ? $all_lessons[$current_lesson_index - 1] : null;
$next_lesson = ($current_lesson_index < count($all_lessons) - 1) ? $all_lessons[$current_lesson_index + 1] : null;

// Get user's progress if logged in
$user_progress = null;
if (is_logged_in()) {
    $user_id = get_current_user_id();
    
    // Get course progress
    $progress_stmt = $pdo->prepare("
        SELECT * FROM wiki_course_progress 
        WHERE user_id = ? AND course_article_id = ?
    ");
    $progress_stmt->execute([$user_id, $course['id']]);
    $user_progress = $progress_stmt->fetch();
    
    // Update progress to current lesson
    if ($user_progress) {
        $update_stmt = $pdo->prepare("
            UPDATE wiki_course_progress 
            SET current_lesson_id = ?, last_accessed_at = NOW()
            WHERE user_id = ? AND course_article_id = ?
        ");
        $update_stmt->execute([$lesson['id'], $user_id, $course['id']]);
    } else {
        // Create new progress record
        $create_stmt = $pdo->prepare("
            INSERT INTO wiki_course_progress (user_id, course_article_id, current_lesson_id, started_at)
            VALUES (?, ?, ?, NOW())
        ");
        $create_stmt->execute([$user_id, $course['id'], $lesson['id']]);
    }
}

// Increment view count
$view_stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
$view_stmt->execute([$lesson['id']]);

$page_title = $lesson['title'] . ' - ' . $course['title'];
$page_description = $lesson['excerpt'] ?: 'Course lesson';

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Lesson Header -->
            <div class="lesson-header">
                <div class="lesson-breadcrumb">
                    <a href="/wiki/Courses">Courses</a> / 
                    <a href="/wiki/Course:<?php echo urlencode($course['slug']); ?>"><?php echo htmlspecialchars($course['title']); ?></a> / 
                    <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                </div>

                <div class="lesson-title-section">
                    <div class="lesson-meta">
                        <div class="meta-item">
                            <i class="iw iw-<?php echo get_lesson_type_icon($lesson['lesson_type']); ?>"></i>
                            <span><?php echo ucfirst($lesson['lesson_type']); ?> Lesson</span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-clock"></i>
                            <span><?php echo format_duration($lesson['lesson_duration']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-book"></i>
                            <span>Lesson <?php echo $current_lesson_index + 1; ?> of <?php echo count($all_lessons); ?></span>
                        </div>
                    </div>

                    <h1 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lesson Content -->
        <div class="col-lg-8">
            <div class="lesson-content">
                <div class="content-section">
                    <?php 
                    // Parse and display the lesson content
                    $parser = new WikiParser();
                    echo $parser->parse($lesson['content']);
                    ?>
                </div>

                <!-- Lesson Navigation -->
                <div class="lesson-navigation">
                    <div class="nav-buttons">
                        <?php if ($previous_lesson): ?>
                        <a href="/wiki/Course:<?php echo urlencode($course['slug']); ?>/<?php echo $previous_lesson['slug']; ?>" class="btn btn-secondary">
                            <i class="iw iw-chevron-left"></i> Previous Lesson
                        </a>
                        <?php endif; ?>
                        
                        <a href="/wiki/Course:<?php echo urlencode($course['slug']); ?>" class="btn btn-outline">
                            <i class="iw iw-list"></i> Course Overview
                        </a>
                        
                        <?php if ($next_lesson): ?>
                        <a href="/wiki/Course:<?php echo urlencode($course['slug']); ?>/<?php echo $next_lesson['slug']; ?>" class="btn btn-primary">
                            Next Lesson <i class="iw iw-chevron-right"></i>
                        </a>
                        <?php else: ?>
                        <button class="btn btn-success" onclick="completeCourse()">
                            <i class="iw iw-trophy"></i> Complete Course
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lesson Sidebar -->
        <div class="col-lg-4">
            <div class="lesson-sidebar">
                <!-- Course Progress -->
                <div class="sidebar-section">
                    <h3>Course Progress</h3>
                    <div class="progress-info">
                        <div class="progress-text">
                            <span>Lesson <?php echo $current_lesson_index + 1; ?> of <?php echo count($all_lessons); ?></span>
                            <span><?php echo round((($current_lesson_index + 1) / count($all_lessons)) * 100); ?>% Complete</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo (($current_lesson_index + 1) / count($all_lessons)) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- Course Lessons -->
                <div class="sidebar-section">
                    <h3>Course Lessons</h3>
                    <div class="lessons-list">
                        <?php foreach ($all_lessons as $index => $l): ?>
                        <div class="lesson-item <?php echo $l['id'] == $lesson['id'] ? 'current' : ''; ?>">
                            <div class="lesson-number"><?php echo $index + 1; ?></div>
                            <div class="lesson-content">
                                <h4 class="lesson-title">
                                    <?php if ($l['id'] == $lesson['id']): ?>
                                        <span class="current-lesson"><?php echo htmlspecialchars($l['title']); ?></span>
                                    <?php else: ?>
                                        <a href="/wiki/Course:<?php echo urlencode($course['slug']); ?>/<?php echo $l['slug']; ?>">
                                            <?php echo htmlspecialchars($l['title']); ?>
                                        </a>
                                    <?php endif; ?>
                                </h4>
                                <div class="lesson-meta">
                                    <span class="lesson-type">
                                        <i class="iw iw-<?php echo get_lesson_type_icon($l['lesson_type']); ?>"></i>
                                        <?php echo ucfirst($l['lesson_type']); ?>
                                    </span>
                                    <span class="lesson-duration">
                                        <i class="iw iw-clock"></i>
                                        <?php echo format_duration($l['lesson_duration']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="lesson-status">
                                <?php if ($l['id'] == $lesson['id']): ?>
                                    <span class="status-current">Current</span>
                                <?php elseif ($index < $current_lesson_index): ?>
                                    <span class="status-completed">✓</span>
                                <?php else: ?>
                                    <span class="status-locked">🔒</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="sidebar-section">
                    <h3>Course Information</h3>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value">
                                <a href="/wiki/Course:<?php echo urlencode($course['slug']); ?>">
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </a>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Category</span>
                            <span class="info-value"><?php echo htmlspecialchars($course['category_name'] ?? 'General'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Difficulty</span>
                            <span class="info-value"><?php echo ucfirst($course['difficulty_level']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Duration</span>
                            <span class="info-value"><?php echo format_duration($course['estimated_duration']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lesson-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.lesson-breadcrumb {
    margin-bottom: 1rem;
    color: #6c757d;
}

.lesson-breadcrumb a {
    color: #007bff;
    text-decoration: none;
}

.lesson-title-section {
    text-align: center;
}

.lesson-meta {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
}

.lesson-title {
    font-size: 2.5rem;
    margin-bottom: 0;
    color: #212529;
}

.lesson-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.content-section {
    margin-bottom: 3rem;
}

.lesson-navigation {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.lesson-sidebar {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.sidebar-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sidebar-section h3 {
    margin-bottom: 1rem;
    color: #212529;
}

.progress-info {
    margin-bottom: 1rem;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s;
}

.lessons-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.lesson-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    transition: all 0.2s;
}

.lesson-item.current {
    border-color: #007bff;
    background: #f8f9ff;
}

.lesson-item:hover {
    border-color: #007bff;
}

.lesson-number {
    width: 30px;
    height: 30px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.lesson-item.current .lesson-number {
    background: #28a745;
}

.lesson-content {
    flex: 1;
}

.lesson-title {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 500;
}

.lesson-title a {
    color: #212529;
    text-decoration: none;
}

.lesson-title a:hover {
    color: #007bff;
}

.current-lesson {
    color: #007bff;
    font-weight: 600;
}

.lesson-meta {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: #6c757d;
}

.lesson-status {
    flex-shrink: 0;
    font-size: 0.8rem;
}

.status-completed {
    color: #28a745;
    font-weight: bold;
}

.status-current {
    color: #007bff;
    font-weight: bold;
}

.status-locked {
    color: #6c757d;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.info-value {
    font-weight: 500;
    color: #212529;
    font-size: 0.9rem;
}

.info-value a {
    color: #007bff;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .lesson-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .lesson-title {
        font-size: 2rem;
    }
    
    .nav-buttons {
        flex-direction: column;
    }
    
    .lesson-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .lesson-meta {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<script>
function completeCourse() {
    if (confirm('Have you completed this course? This will mark the course as completed in your progress.')) {
        // TODO: Implement course completion
        alert('Course completion functionality will be implemented soon!');
    }
}
</script>

<?php
// Helper function to get lesson type icon
function get_lesson_type_icon($type) {
    switch ($type) {
        case 'video': return 'play';
        case 'audio': return 'volume-up';
        case 'quiz': return 'question-circle';
        case 'assignment': return 'edit';
        default: return 'file-text';
    }
}

include __DIR__ . '/../../includes/footer.php';
?>
