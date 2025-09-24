<?php
/**
 * Individual Course Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Course.php';

// Require login for all site access
require_login();

// Get course slug from URL
$course_slug = $_GET['slug'] ?? '';

if (empty($course_slug)) {
    header('Location: /courses');
    exit;
}

// Initialize course model
$course_model = new Course($pdo);

// Get course details
$course = $course_model->getCourseBySlug($course_slug);

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/../pages/404.php';
    exit;
}

// Get course lessons
$lessons = $course_model->getCourseLessons($course['id']);

// Get user's progress if logged in
$user_progress = null;
$user_completed = false;
if (is_logged_in()) {
    $user_id = get_current_user_id();
    $user_progress = $course_model->getUserCourseProgress($user_id, $course['id']);
    $user_completed = $course_model->hasUserCompletedCourse($user_id, $course['id']);
}

// Get course statistics
$course_stats = $course_model->getCourseStats($course['id']);

$page_title = $course['title'] . ' - Islamic Learning Course';
$page_description = $course['short_description'];

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Course Header -->
            <div class="course-header">
                <div class="course-breadcrumb">
                    <a href="/courses">Courses</a> / 
                    <span><?php echo htmlspecialchars($course['category_name']); ?></span> / 
                    <span><?php echo htmlspecialchars($course['title']); ?></span>
                </div>

                <div class="course-title-section">
                    <div class="course-category-badge" style="background-color: <?php echo $course['category_color']; ?>">
                        <i class="iw <?php echo $course['category_icon']; ?>"></i>
                        <?php echo htmlspecialchars($course['category_name']); ?>
                    </div>

                    <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                    
                    <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>

                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="iw iw-clock"></i>
                            <span><?php echo format_duration($course['estimated_duration']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-book"></i>
                            <span><?php echo count($lessons); ?> lessons</span>
                        </div>
                        <div class="meta-item difficulty-<?php echo $course['difficulty_level']; ?>">
                            <i class="iw iw-signal"></i>
                            <span><?php echo ucfirst($course['difficulty_level']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="iw iw-users"></i>
                            <span><?php echo $course_stats['total_students']; ?> students</span>
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
                        <?php echo nl2br(htmlspecialchars($course['description'])); ?>
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
                                    <a href="/course/<?php echo $course['slug']; ?>/lesson/<?php echo $lesson['slug']; ?>">
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
                                        <?php echo format_duration($lesson['duration']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="lesson-status">
                                <?php if (is_logged_in() && $user_progress): ?>
                                    <?php if ($user_progress['lesson_id'] == $lesson['id']): ?>
                                        <span class="status-current">Current</span>
                                    <?php elseif ($index < array_search($user_progress['lesson_id'], array_column($lessons, 'id'))): ?>
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
                            <a href="/course/<?php echo $course['slug']; ?>/lesson/<?php echo $lessons[0]['slug']; ?>" class="btn btn-primary btn-block">
                                Continue Learning
                            </a>
                        <?php else: ?>
                            <a href="/course/<?php echo $course['slug']; ?>/lesson/<?php echo $lessons[0]['slug']; ?>" class="btn btn-primary btn-block">
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
                            <span class="stat-value"><?php echo $course_stats['total_students']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Completed</span>
                            <span class="stat-value"><?php echo $course_stats['completed_students']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Avg. Completion</span>
                            <span class="stat-value"><?php echo round($course_stats['avg_completion_percentage'], 1); ?>%</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Avg. Time</span>
                            <span class="stat-value"><?php echo format_duration($course_stats['avg_time_spent']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="sidebar-section">
                    <h3>Course Information</h3>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Created by</span>
                            <span class="info-value"><?php echo htmlspecialchars($course['created_by_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created</span>
                            <span class="info-value"><?php echo date('M j, Y', strtotime($course['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last updated</span>
                            <span class="info-value"><?php echo date('M j, Y', strtotime($course['updated_at'])); ?></span>
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

include __DIR__ . '/../includes/footer.php';
?>
