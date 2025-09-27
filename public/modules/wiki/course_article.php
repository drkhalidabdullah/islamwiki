<?php
/**
 * Course Article Handler
 * Handles course articles with special course functionality
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

$page_title = 'Course';
$slug = $_GET['slug'] ?? '';
$title = $_GET['title'] ?? '';

// Handle namespace titles (e.g., Course:Introduction to Quran Reading)
if ($title) {
    $parsed_title = parse_wiki_title($title);
    $namespace = $parsed_title['namespace'];
    $article_title = $parsed_title['title'];
    
    if ($namespace !== 'Course') {
        // Not a course article, redirect to regular article handler
        header('Location: /wiki/' . urlencode($title));
        exit;
    }
    
    $slug = $article_title;
}

// Get the course article
$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name, cc.color as category_color, cc.icon as category_icon,
           wn.name as namespace_name, wn.display_name as namespace_display_name
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
    WHERE wa.slug = ? AND wa.course_type = 'course' AND wa.status = 'published'
");

$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/../../pages/404.php';
    exit;
}

// Get course lessons
$lessons_stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    WHERE wa.parent_course_id = ? AND wa.course_type = 'lesson' AND wa.status = 'published'
    ORDER BY wa.lesson_sort_order, wa.title
");

$lessons_stmt->execute([$article['id']]);
$lessons = $lessons_stmt->fetchAll();

// Get user's progress if logged in
$user_progress = null;
$user_completed = false;
if (is_logged_in()) {
    $user_id = get_current_user_id();
    
    // Get course progress
    $progress_stmt = $pdo->prepare("
        SELECT * FROM wiki_course_progress 
        WHERE user_id = ? AND course_article_id = ?
    ");
    $progress_stmt->execute([$user_id, $article['id']]);
    $user_progress = $progress_stmt->fetch();
    
    // Check if course is completed
    $completion_stmt = $pdo->prepare("
        SELECT * FROM wiki_course_completions 
        WHERE user_id = ? AND course_article_id = ? AND is_completed = 1
    ");
    $completion_stmt->execute([$user_id, $article['id']]);
    $user_completed = (bool) $completion_stmt->fetch();
}

// Get course statistics
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT wcp.user_id) as total_students,
        COUNT(DISTINCT wcc.user_id) as completed_students,
        AVG(wcc.completion_percentage) as avg_completion_percentage,
        AVG(wcc.time_spent) as avg_time_spent
    FROM wiki_course_progress wcp
    LEFT JOIN wiki_course_completions wcc ON wcp.user_id = wcc.user_id AND wcp.course_article_id = wcc.course_article_id
    WHERE wcp.course_article_id = ?
");
$stats_stmt->execute([$article['id']]);
$course_stats = $stats_stmt->fetch();

// Parse course metadata
$course_metadata = json_decode($article['course_metadata'] ?? '{}', true);

// Increment view count
$view_stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
$view_stmt->execute([$article['id']]);

$page_title = $article['title'] . ' - Islamic Learning Course';
$page_description = $article['excerpt'] ?: 'Islamic learning course';

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Course Header -->
            <div class="course-header">
                <div class="course-breadcrumb">
                    <a href="/wiki/Courses">Courses</a> / 
                    <span><?php echo htmlspecialchars($article['category_name'] ?? 'General'); ?></span> / 
                    <span><?php echo htmlspecialchars($article['title']); ?></span>
                </div>

                <div class="course-title-section">
                    <?php if ($article['category_name']): ?>
                    <div class="course-category-badge" style="background-color: <?php echo $article['category_color'] ?? '#007bff'; ?>">
                        <i class="iw <?php echo $article['category_icon'] ?? 'iw-book'; ?>"></i>
                        <?php echo htmlspecialchars($article['category_name']); ?>
                    </div>
                    <?php endif; ?>

                    <h1 class="course-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                    
                    <p class="course-description"><?php echo htmlspecialchars($article['excerpt']); ?></p>

                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="iw iw-clock"></i>
                            <span><?php echo format_duration($article['estimated_duration']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-book"></i>
                            <span><?php echo count($lessons); ?> lessons</span>
                        </div>
                        <div class="meta-item difficulty-<?php echo $article['difficulty_level']; ?>">
                            <i class="iw iw-signal"></i>
                            <span><?php echo ucfirst($article['difficulty_level']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-users"></i>
                            <span><?php echo $course_stats['total_students'] ?? 0; ?> students</span>
                        </div>
                    </div>

                    <?php if (is_logged_in() && $user_progress): ?>
                    <div class="course-progress-section">
                        <div class="progress-header">
                            <span>Your Progress</span>
                            <span><?php echo $user_progress['progress_percentage']; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $user_progress['progress_percentage']; ?>%"></div>
                        </div>
                        <div class="progress-details">
                            <span>Time spent: <?php echo format_duration($user_progress['time_spent']); ?></span>
                            <span>Started: <?php echo date('M j, Y', strtotime($user_progress['started_at'])); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Course Content -->
        <div class="col-lg-8">
            <div class="course-content">
                <div class="content-section">
                    <h2>About This Course</h2>
                    <div class="course-description-content">
                        <?php 
                        // Parse and display the course content
                        $parser = new WikiParser();
                        echo $parser->parse($article['content']);
                        ?>
                    </div>
                </div>

                <div class="content-section">
                    <h2>Course Lessons</h2>
                    <div class="lessons-list">
                        <?php foreach ($lessons as $index => $lesson): ?>
                        <div class="lesson-item">
                            <div class="lesson-number"><?php echo $index + 1; ?></div>
                            <div class="lesson-content">
                                <h4 class="lesson-title">
                                    <a href="/wiki/Course:<?php echo urlencode($slug); ?>/<?php echo $lesson['slug']; ?>">
                                        <?php echo htmlspecialchars($lesson['title']); ?>
                                    </a>
                                </h4>
                                <div class="lesson-meta">
                                    <span class="lesson-type">
                                        <i class="iw iw-<?php echo get_lesson_type_icon($lesson['lesson_type']); ?>"></i>
                                        <?php echo ucfirst($lesson['lesson_type']); ?>
                                    </span>
                                    <span class="lesson-duration">
                                        <i class="iw iw-clock"></i>
                                        <?php echo format_duration($lesson['lesson_duration']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="lesson-status">
                                <?php if (is_logged_in() && $user_progress): ?>
                                    <?php if ($user_progress['current_lesson_id'] == $lesson['id']): ?>
                                        <span class="status-current">Current</span>
                                    <?php elseif ($index < array_search($user_progress['current_lesson_id'], array_column($lessons, 'id'))): ?>
                                        <span class="status-completed">✓</span>
                                    <?php else: ?>
                                        <span class="status-locked">🔒</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-available">Available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Sidebar -->
        <div class="col-lg-4">
            <div class="course-sidebar">
                <!-- Course Actions -->
                <div class="sidebar-section">
                    <h3>Course Actions</h3>
                    <?php if (is_logged_in()): ?>
                        <?php if ($user_completed): ?>
                            <div class="completion-badge">
                                <i class="iw iw-trophy"></i>
                                Course Completed!
                            </div>
                        <?php elseif ($user_progress): ?>
                            <a href="/wiki/Course:<?php echo urlencode($slug); ?>/<?php echo $lessons[0]['slug']; ?>" class="btn btn-primary btn-block">
                                Continue Learning
                            </a>
                        <?php else: ?>
                            <a href="/wiki/Course:<?php echo urlencode($slug); ?>/<?php echo $lessons[0]['slug']; ?>" class="btn btn-primary btn-block">
                                Start Course
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-block">
                            Login to Start Course
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Course Statistics -->
                <div class="sidebar-section">
                    <h3>Course Statistics</h3>
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">Total Students</span>
                            <span class="stat-value"><?php echo $course_stats['total_students'] ?? 0; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Completed</span>
                            <span class="stat-value"><?php echo $course_stats['completed_students'] ?? 0; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Avg. Completion</span>
                            <span class="stat-value"><?php echo round($course_stats['avg_completion_percentage'] ?? 0, 1); ?>%</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Avg. Time</span>
                            <span class="stat-value"><?php echo format_duration($course_stats['avg_time_spent'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="sidebar-section">
                    <h3>Course Information</h3>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Created by</span>
                            <span class="info-value"><?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created</span>
                            <span class="info-value"><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last updated</span>
                            <span class="info-value"><?php echo date('M j, Y', strtotime($article['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.course-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.course-breadcrumb {
    margin-bottom: 1rem;
    color: #6c757d;
}

.course-breadcrumb a {
    color: #007bff;
    text-decoration: none;
}

.course-title-section {
    text-align: center;
}

.course-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.course-title {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #212529;
}

.course-description {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 2rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.course-meta {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
}

.difficulty-beginner { color: #28a745; }
.difficulty-intermediate { color: #ffc107; }
.difficulty-advanced { color: #dc3545; }

.course-progress-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 500px;
    margin: 0 auto;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.progress-bar {
    width: 100%;
    height: 12px;
    background: #e9ecef;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s;
}

.progress-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #6c757d;
}

.course-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.content-section {
    margin-bottom: 3rem;
}

.content-section h2 {
    margin-bottom: 1.5rem;
    color: #212529;
}

.course-description-content {
    line-height: 1.6;
    color: #495057;
}

.lessons-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.lesson-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s;
}

.lesson-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.lesson-number {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.lesson-content {
    flex: 1;
}

.lesson-title a {
    color: #212529;
    text-decoration: none;
    font-weight: 500;
}

.lesson-title a:hover {
    color: #007bff;
}

.lesson-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.lesson-status {
    flex-shrink: 0;
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

.status-available {
    color: #6c757d;
    font-size: 0.9rem;
}

.course-sidebar {
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

.completion-badge {
    background: #28a745;
    color: white;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
}

.btn-block {
    width: 100%;
    padding: 0.75rem;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
}

.stats-list, .info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stat-item, .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label, .info-label {
    color: #6c757d;
}

.stat-value, .info-value {
    font-weight: 500;
    color: #212529;
}

@media (max-width: 768px) {
    .course-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .course-title {
        font-size: 2rem;
    }
    
    .lesson-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .lesson-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

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
