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

<div class="courses-layout">
    <!-- Left Sidebar -->
    <div class="courses-sidebar">
        <div class="sidebar-header">
            <h3><i class="iw iw-filter"></i> Filter Courses</h3>
        </div>
        
        <div class="sidebar-content">
            <!-- Course Statistics (if logged in) -->
            <?php if (is_logged_in() && isset($user_progress_summary)): ?>
            <div class="stats-section">
                <h4><i class="iw iw-chart-bar"></i> Your Progress</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user_progress_summary['courses_started']; ?></div>
                        <div class="stat-label">Started</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user_progress_summary['courses_completed']; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo format_duration($user_progress_summary['total_time_spent']); ?></div>
                        <div class="stat-label">Time Spent</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Category Filter -->
            <div class="filter-section">
                <h4><i class="iw iw-tags"></i> Category</h4>
                <div class="filter-options">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => null])); ?>" 
                       class="filter-option <?php echo !$category ? 'active' : ''; ?>">
                        <i class="iw iw-th-large"></i>
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $cat['slug']])); ?>" 
                       class="filter-option <?php echo $category === $cat['slug'] ? 'active' : ''; ?>">
                        <i class="iw <?php echo $cat['icon']; ?>" style="color: <?php echo $cat['color']; ?>"></i>
                        <?php echo htmlspecialchars($cat['name']); ?>
                        <span class="course-count"><?php echo $cat['course_count']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Difficulty Filter -->
            <div class="filter-section">
                <h4><i class="iw iw-signal"></i> Difficulty</h4>
                <div class="filter-options">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => null])); ?>" 
                       class="filter-option <?php echo !$difficulty ? 'active' : ''; ?>">
                        <i class="iw iw-th-large"></i>
                        All Levels
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => 'beginner'])); ?>" 
                       class="filter-option <?php echo $difficulty === 'beginner' ? 'active' : ''; ?>">
                        <i class="iw iw-signal" style="color: #28a745;"></i>
                        Beginner
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => 'intermediate'])); ?>" 
                       class="filter-option <?php echo $difficulty === 'intermediate' ? 'active' : ''; ?>">
                        <i class="iw iw-signal" style="color: #ffc107;"></i>
                        Intermediate
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['difficulty' => 'advanced'])); ?>" 
                       class="filter-option <?php echo $difficulty === 'advanced' ? 'active' : ''; ?>">
                        <i class="iw iw-signal" style="color: #dc3545;"></i>
                        Advanced
                    </a>
                </div>
            </div>

            <!-- Featured Filter -->
            <div class="filter-section">
                <h4><i class="iw iw-star"></i> Special</h4>
                <div class="filter-options">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['featured' => '1'])); ?>" 
                       class="filter-option <?php echo $featured ? 'active' : ''; ?>">
                        <i class="iw iw-star" style="color: #ffc107;"></i>
                        Featured Courses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="courses-main">
        <div class="page-header">
            <h1><i class="iw iw-book"></i> Islamic Learning Courses</h1>
            <p class="page-description">Enhance your Islamic knowledge with our comprehensive collection of courses</p>
        </div>

        <div class="courses-content">
            <?php if (empty($courses)): ?>
            <div class="no-courses">
                <div class="no-courses-icon">
                    <i class="iw iw-book"></i>
                </div>
                <h3>No courses found</h3>
                <p>Try adjusting your filters to find more courses.</p>
                <a href="/courses" class="btn btn-primary">View All Courses</a>
            </div>
            <?php else: ?>
            <div class="courses-grid">
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
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Courses Layout */
.courses-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Left Sidebar */
.courses-sidebar {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.sidebar-header {
    background: #007bff;
    color: white;
    padding: 1rem;
    border-radius: 8px 8px 0 0;
    margin: 0;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar-content {
    padding: 1.5rem;
}

/* Stats Section */
.stats-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.stats-section h4 {
    margin: 0 0 1rem 0;
    color: #495057;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.stat-number {
    font-size: 1.25rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Filter Sections */
.filter-section {
    margin-bottom: 1.5rem;
}

.filter-section h4 {
    margin: 0 0 0.75rem 0;
    color: #495057;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    text-decoration: none;
    color: #495057;
    border-radius: 6px;
    transition: all 0.2s;
    font-size: 0.9rem;
    border: 1px solid transparent;
}

.filter-option:hover {
    background: white;
    color: #007bff;
    border-color: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.filter-option.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.filter-option i {
    width: 16px;
    text-align: center;
}

.course-count {
    margin-left: auto;
    font-size: 0.8rem;
    opacity: 0.7;
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    min-width: 20px;
    text-align: center;
}

.filter-option.active .course-count {
    background: rgba(255,255,255,0.3);
}

/* Main Content */
.courses-main {
    min-width: 0; /* Prevent grid overflow */
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0 0 0.5rem 0;
    color: #212529;
    font-size: 2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-description {
    color: #6c757d;
    font-size: 1.1rem;
    margin: 0;
    line-height: 1.5;
}

/* Courses Grid */
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.course-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.course-header {
    position: relative;
    padding: 1.25rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.featured-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #ffc107;
    color: #212529;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.course-content {
    padding: 1.5rem;
}

.course-title {
    margin: 0 0 0.75rem 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.course-title a {
    color: #212529;
    text-decoration: none;
    transition: color 0.2s;
}

.course-title a:hover {
    color: #007bff;
}

.course-description {
    color: #6c757d;
    margin-bottom: 1.25rem;
    line-height: 1.6;
    font-size: 0.95rem;
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
    gap: 0.375rem;
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
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
    background: linear-gradient(90deg, #007bff, #0056b3);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.course-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    width: 100%;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* No Courses State */
.no-courses {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.no-courses-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1.5rem;
}

.no-courses h3 {
    margin: 0 0 1rem 0;
    color: #495057;
    font-size: 1.5rem;
    font-weight: 600;
}

.no-courses p {
    margin: 0 0 2rem 0;
    font-size: 1.1rem;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .courses-layout {
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 992px) {
    .courses-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .courses-sidebar {
        position: static;
        order: 2;
    }
    
    .courses-main {
        order: 1;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .courses-layout {
        padding: 0 0.5rem;
    }
    
    .courses-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .course-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .page-header h1 {
        font-size: 1.75rem;
    }
    
    .sidebar-content {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .course-card {
        border-radius: 8px;
    }
    
    .course-content {
        padding: 1rem;
    }
    
    .course-footer {
        padding: 0.75rem 1rem;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

