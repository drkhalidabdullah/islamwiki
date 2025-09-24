<?php
/**
 * Course Lesson Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../extensions/achievements/extension.php';

// Get course and lesson slugs from URL
$course_slug = $_GET['course'] ?? '';
$lesson_slug = $_GET['lesson'] ?? '';

if (empty($course_slug) || empty($lesson_slug)) {
    header('Location: /courses');
    exit;
}

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = get_current_user_id();

// Initialize models
$course_model = new Course($pdo);
$achievements_extension = new AchievementsExtension();

// Get course details
$course = $course_model->getCourseBySlug($course_slug);

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/../pages/404.php';
    exit;
}

// Get lesson details
$lesson = $course_model->getCourseLessonBySlug($course['id'], $lesson_slug);

if (!$lesson) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/../pages/404.php';
    exit;
}

// Get all lessons for navigation
$all_lessons = $course_model->getCourseLessons($course['id']);
$current_lesson_index = array_search($lesson['id'], array_column($all_lessons, 'id'));

// Get user's course progress
$user_progress = $course_model->getUserCourseProgress($user_id, $course['id']);

// Handle lesson completion
if ($_POST['action'] ?? '' === 'complete_lesson') {
    // Update progress
    $progress_percentage = (($current_lesson_index + 1) / count($all_lessons)) * 100;
    $course_model->updateUserCourseProgress($user_id, $course['id'], $lesson['id'], $progress_percentage, $lesson['duration']);
    
    // Check if course is completed
    if ($progress_percentage >= 100) {
        $course_model->markCourseCompleted($user_id, $course['id'], 100, $user_progress['time_spent'] + $lesson['duration']);
        
        // Check for achievements
        try {
            $achievements_extension->checkAchievements($user_id);
        } catch (Exception $e) {
            // Log error but don't break the flow
            error_log("Achievement check failed: " . $e->getMessage());
        }
    }
    
    // Redirect to next lesson or course completion
    if ($current_lesson_index < count($all_lessons) - 1) {
        $next_lesson = $all_lessons[$current_lesson_index + 1];
        header('Location: /course/' . $course['slug'] . '/lesson/' . $next_lesson['slug']);
    } else {
        header('Location: /course/' . $course['slug'] . '?completed=1');
    }
    exit;
}

// Get updated progress
$user_progress = $course_model->getUserCourseProgress($user_id, $course['id']);

$page_title = $lesson['title'] . ' - ' . $course['title'];
$page_description = 'Learn: ' . $lesson['title'];

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Lesson Header -->
            <div class="lesson-header">
                <div class="lesson-breadcrumb">
                    <a href="/courses">Courses</a> / 
                    <a href="/course/<?php echo $course['slug']; ?>"><?php echo htmlspecialchars($course['title']); ?></a> / 
                    <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                </div>

                <div class="lesson-title-section">
                    <h1 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                    
                    <div class="lesson-meta">
                        <div class="meta-item">
                            <i class="iw iw-clock"></i>
                            <span><?php echo format_duration($lesson['duration']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-<?php echo get_lesson_type_icon($lesson['lesson_type']); ?>"></i>
                            <span><?php echo ucfirst($lesson['lesson_type']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span>Lesson <?php echo $current_lesson_index + 1; ?> of <?php echo count($all_lessons); ?></span>
                        </div>
                    </div>

                    <?php if ($user_progress): ?>
                    <div class="course-progress-section">
                        <div class="progress-header">
                            <span>Course Progress</span>
                            <span><?php echo round($user_progress['progress_percentage'], 1); ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $user_progress['progress_percentage']; ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lesson Content -->
        <div class="col-lg-8">
            <div class="lesson-content">
                <div class="content-wrapper">
                    <?php echo $lesson['content']; ?>
                </div>

                <!-- Lesson Actions -->
                <div class="lesson-actions">
                    <?php if ($current_lesson_index > 0): ?>
                    <a href="/course/<?php echo $course['slug']; ?>/lesson/<?php echo $all_lessons[$current_lesson_index - 1]['slug']; ?>" class="btn btn-secondary">
                        <i class="iw iw-arrow-left"></i> Previous Lesson
                    </a>
                    <?php endif; ?>

                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="complete_lesson">
                        <button type="submit" class="btn btn-primary">
                            <?php if ($current_lesson_index < count($all_lessons) - 1): ?>
                                Complete & Continue
                            <?php else: ?>
                                Complete Course
                            <?php endif; ?>
                        </button>
                    </form>

                    <?php if ($current_lesson_index < count($all_lessons) - 1): ?>
                    <a href="/course/<?php echo $course['slug']; ?>/lesson/<?php echo $all_lessons[$current_lesson_index + 1]['slug']; ?>" class="btn btn-outline-primary">
                        Next Lesson <i class="iw iw-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lesson Sidebar -->
        <div class="col-lg-4">
            <div class="lesson-sidebar">
                <!-- Course Navigation -->
                <div class="sidebar-section">
                    <h3>Course Lessons</h3>
                    <div class="lessons-navigation">
                        <?php foreach ($all_lessons as $index => $nav_lesson): ?>
                        <div class="nav-lesson-item <?php echo $nav_lesson['id'] == $lesson['id'] ? 'current' : ''; ?>">
                            <div class="nav-lesson-number"><?php echo $index + 1; ?></div>
                            <div class="nav-lesson-content">
                                <a href="/course/<?php echo $course['slug']; ?>/lesson/<?php echo $nav_lesson['slug']; ?>" class="nav-lesson-title">
                                    <?php echo htmlspecialchars($nav_lesson['title']); ?>
                                </a>
                                <div class="nav-lesson-meta">
                                    <span class="nav-lesson-type"><?php echo ucfirst($nav_lesson['lesson_type']); ?></span>
                                    <span class="nav-lesson-duration"><?php echo format_duration($nav_lesson['duration']); ?></span>
                                </div>
                            </div>
                            <div class="nav-lesson-status">
                                <?php if ($nav_lesson['id'] == $lesson['id']): ?>
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
                    <div class="course-info">
                        <div class="info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value">
                                <a href="/course/<?php echo $course['slug']; ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Category</span>
                            <span class="info-value"><?php echo htmlspecialchars($course['category_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Difficulty</span>
                            <span class="info-value difficulty-<?php echo $course['difficulty_level']; ?>">
                                <?php echo ucfirst($course['difficulty_level']); ?>
                            </span>
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

.lesson-title {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #212529;
}

.lesson-meta {
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
}

.progress-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s;
}

.lesson-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.content-wrapper {
    line-height: 1.6;
    color: #495057;
    margin-bottom: 2rem;
}

.content-wrapper h1, .content-wrapper h2, .content-wrapper h3 {
    color: #212529;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.content-wrapper h1:first-child, .content-wrapper h2:first-child, .content-wrapper h3:first-child {
    margin-top: 0;
}

.content-wrapper ul, .content-wrapper ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.content-wrapper li {
    margin-bottom: 0.5rem;
}

.lesson-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    color: white;
}

.btn-outline-primary {
    background: transparent;
    color: #007bff;
    border: 1px solid #007bff;
}

.btn-outline-primary:hover {
    background: #007bff;
    color: white;
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

.lessons-navigation {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.nav-lesson-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 6px;
    transition: all 0.2s;
}

.nav-lesson-item:hover {
    background: #f8f9fa;
}

.nav-lesson-item.current {
    background: #e3f2fd;
    border: 1px solid #2196f3;
}

.nav-lesson-number {
    width: 30px;
    height: 30px;
    background: #6c757d;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: bold;
    flex-shrink: 0;
}

.nav-lesson-item.current .nav-lesson-number {
    background: #2196f3;
}

.nav-lesson-content {
    flex: 1;
}

.nav-lesson-title {
    color: #212529;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

.nav-lesson-title:hover {
    color: #007bff;
}

.nav-lesson-meta {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: #6c757d;
}

.nav-lesson-status {
    flex-shrink: 0;
}

.status-completed {
    color: #28a745;
    font-weight: bold;
}

.status-current {
    color: #2196f3;
    font-weight: bold;
    font-size: 0.8rem;
}

.status-locked {
    color: #6c757d;
}

.course-info {
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

.difficulty-beginner { color: #28a745; }
.difficulty-intermediate { color: #ffc107; }
.difficulty-advanced { color: #dc3545; }

@media (max-width: 768px) {
    .lesson-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .lesson-title {
        font-size: 2rem;
    }
    
    .lesson-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .nav-lesson-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .nav-lesson-meta {
        flex-direction: column;
        gap: 0.25rem;
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

include __DIR__ . '/../includes/footer.php';
?>
