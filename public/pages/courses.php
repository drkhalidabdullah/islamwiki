<?php
/**
 * Courses Listing Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Course.php';

// Initialize course model
$course_model = new Course($pdo);

// Get filter parameters
$category = $_GET['category'] ?? null;
$difficulty = $_GET['difficulty'] ?? null;
$featured = $_GET['featured'] ?? false;

// Get courses
$courses = $course_model->getAllCourses($category, $difficulty, $featured);
$categories = $course_model->getCourseCategories();

// Get user's course progress if logged in
$user_progress = [];
if (is_logged_in()) {
    $user_id = get_current_user_id();
    $user_progress_summary = $course_model->getUserCourseProgressSummary($user_id);
    
    // Get progress for each course
    foreach ($courses as $course) {
        $progress = $course_model->getUserCourseProgress($user_id, $course['id']);
        $user_progress[$course['id']] = $progress;
    }
}

$page_title = 'Islamic Learning Courses';
$page_description = 'Explore our comprehensive collection of Islamic learning courses covering Quran studies, Hadith, Islamic history, and more.';

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="iw iw-book"></i> Islamic Learning Courses</h1>
                <p class="page-description">Enhance your Islamic knowledge with our comprehensive collection of courses</p>
            </div>
        </div>
    </div>

    <!-- Course Statistics (if logged in) -->
    <?php if (is_logged_in() && isset($user_progress_summary)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="course-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $user_progress_summary['courses_started']; ?></div>
                    <div class="stat-label">Courses Started</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $user_progress_summary['courses_completed']; ?></div>
                    <div class="stat-label">Courses Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo format_duration($user_progress_summary['total_time_spent']); ?></div>
                    <div class="stat-label">Total Time Spent</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="course-filters">
                <h4>Filter Courses</h4>
                
                <!-- Category Filter -->
                <div class="filter-group">
                    <h5>Category</h5>
                    <div class="filter-options">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => null])); ?>" 
                           class="filter-option <?php echo !$category ? 'active' : ''; ?>">
                            All Categories
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $cat['slug']])); ?>" 
                           class="filter-option <?php echo $category === $cat['slug'] ? 'active' : ''; ?>">
                            <i class="iw <?php echo $cat['icon']; ?>" style="color: <?php echo $cat['color']; ?>"></i>
                            <?php echo htmlspecialchars($cat['name']); ?>
                            <span class="course-count">(<?php echo $cat['course_count']; ?>)</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Difficulty Filter -->
                <div class="filter-group">
                    <h5>Difficulty</h5>
                    <div class="filter-options">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => null])); ?>" 
                           class="filter-option <?php echo !$difficulty ? 'active' : ''; ?>">
                            All Levels
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => 'beginner'])); ?>" 
                           class="filter-option <?php echo $difficulty === 'beginner' ? 'active' : ''; ?>">
                            Beginner
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => 'intermediate'])); ?>" 
                           class="filter-option <?php echo $difficulty === 'intermediate' ? 'active' : ''; ?>">
                            Intermediate
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => 'advanced'])); ?>" 
                           class="filter-option <?php echo $difficulty === 'advanced' ? 'active' : ''; ?>">
                            Advanced
                        </a>
                    </div>
                </div>

                <!-- Featured Filter -->
                <div class="filter-group">
                    <h5>Special</h5>
                    <div class="filter-options">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['featured' => '1'])); ?>" 
                           class="filter-option <?php echo $featured ? 'active' : ''; ?>">
                            <i class="iw iw-star"></i> Featured Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Grid -->
        <div class="col-lg-9">
            <div class="courses-grid">
                <?php if (empty($courses)): ?>
                <div class="no-courses">
                    <i class="iw iw-book" style="font-size: 4rem; color: #ccc;"></i>
                    <h3>No courses found</h3>
                    <p>Try adjusting your filters to find more courses.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <?php if ($course['is_featured']): ?>
                            <div class="featured-badge">
                                <i class="iw iw-star"></i> Featured
                            </div>
                            <?php endif; ?>
                            
                            <div class="course-category" style="background-color: <?php echo $course['category_color']; ?>">
                                <i class="iw <?php echo $course['category_icon']; ?>"></i>
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </div>
                        </div>

                        <div class="course-content">
                            <h3 class="course-title">
                                <a href="/course/<?php echo $course['slug']; ?>">
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </a>
                            </h3>
                            
                            <p class="course-description">
                                <?php echo htmlspecialchars($course['short_description']); ?>
                            </p>

                            <div class="course-meta">
                                <div class="meta-item">
                                    <i class="iw iw-clock"></i>
                                    <?php echo format_duration($course['estimated_duration']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="iw iw-book"></i>
                                    <?php echo $course['lesson_count']; ?> lessons
                                </div>
                                <div class="meta-item difficulty-<?php echo $course['difficulty_level']; ?>">
                                    <i class="iw iw-signal"></i>
                                    <?php echo ucfirst($course['difficulty_level']); ?>
                                </div>
                            </div>

                            <?php if (is_logged_in() && isset($user_progress[$course['id']])): ?>
                            <div class="course-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $user_progress[$course['id']]['progress_percentage']; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $user_progress[$course['id']]['progress_percentage']; ?>% Complete</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="course-footer">
                            <a href="/course/<?php echo $course['slug']; ?>" class="btn btn-primary">
                                <?php if (is_logged_in() && isset($user_progress[$course['id']])): ?>
                                    <?php if ($user_progress[$course['id']]['progress_percentage'] > 0): ?>
                                        Continue Learning
                                    <?php else: ?>
                                        Start Course
                                    <?php endif; ?>
                                <?php else: ?>
                                    View Course
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.course-stats {
    display: flex;
    gap: 2rem;
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.course-filters {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.filter-group {
    margin-bottom: 1.5rem;
}

.filter-group h5 {
    margin-bottom: 0.5rem;
    color: #495057;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    text-decoration: none;
    color: #495057;
    border-radius: 4px;
    transition: all 0.2s;
}

.filter-option:hover {
    background: #e9ecef;
    color: #007bff;
}

.filter-option.active {
    background: #007bff;
    color: white;
}

.course-count {
    margin-left: auto;
    font-size: 0.8rem;
    opacity: 0.7;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.course-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.course-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.course-header {
    position: relative;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.featured-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #ffc107;
    color: #212529;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
}

.course-category {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.course-content {
    padding: 1.5rem;
}

.course-title {
    margin-bottom: 0.5rem;
}

.course-title a {
    color: #212529;
    text-decoration: none;
}

.course-title a:hover {
    color: #007bff;
}

.course-description {
    color: #6c757d;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.course-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.difficulty-beginner { color: #28a745; }
.difficulty-intermediate { color: #ffc107; }
.difficulty-advanced { color: #dc3545; }

.course-progress {
    margin-bottom: 1rem;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: #007bff;
    transition: width 0.3s;
}

.progress-text {
    font-size: 0.9rem;
    color: #6c757d;
}

.course-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.no-courses {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.no-courses h3 {
    margin: 1rem 0;
    color: #495057;
}

@media (max-width: 768px) {
    .course-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .courses-grid {
        grid-template-columns: 1fr;
    }
    
    .course-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
