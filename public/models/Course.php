<?php
/**
 * Course Model
 * Handles course-related database operations
 */

class Course {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all published courses
     */
    public function getAllCourses($category = null, $difficulty = null, $featured_only = false) {
        $sql = "
            SELECT c.*, cc.name as category_name, cc.color as category_color, cc.icon as category_icon,
                   u.display_name as created_by_name,
                   (SELECT COUNT(*) FROM course_lessons cl WHERE cl.course_id = c.id AND cl.is_published = 1) as lesson_count
            FROM courses c
            LEFT JOIN course_categories cc ON c.category = cc.slug
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.is_published = 1
        ";
        
        $params = [];
        
        if ($category) {
            $sql .= " AND c.category = ?";
            $params[] = $category;
        }
        
        if ($difficulty) {
            $sql .= " AND c.difficulty_level = ?";
            $params[] = $difficulty;
        }
        
        if ($featured_only) {
            $sql .= " AND c.is_featured = 1";
        }
        
        $sql .= " ORDER BY c.sort_order, c.title";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get course by slug
     */
    public function getCourseBySlug($slug) {
        $sql = "
            SELECT c.*, cc.name as category_name, cc.color as category_color, cc.icon as category_icon,
                   u.display_name as created_by_name
            FROM courses c
            LEFT JOIN course_categories cc ON c.category = cc.slug
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.slug = ? AND c.is_published = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    /**
     * Get course by ID
     */
    public function getCourseById($id) {
        $sql = "
            SELECT c.*, cc.name as category_name, cc.color as category_color, cc.icon as category_icon,
                   u.display_name as created_by_name
            FROM courses c
            LEFT JOIN course_categories cc ON c.category = cc.slug
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.id = ? AND c.is_published = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get course lessons
     */
    public function getCourseLessons($course_id) {
        $sql = "
            SELECT * FROM course_lessons 
            WHERE course_id = ? AND is_published = 1 
            ORDER BY sort_order, title
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$course_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get course lesson by slug
     */
    public function getCourseLessonBySlug($course_id, $lesson_slug) {
        $sql = "
            SELECT * FROM course_lessons 
            WHERE course_id = ? AND slug = ? AND is_published = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$course_id, $lesson_slug]);
        return $stmt->fetch();
    }
    
    /**
     * Get course categories
     */
    public function getCourseCategories() {
        $sql = "
            SELECT cc.*, COUNT(c.id) as course_count
            FROM course_categories cc
            LEFT JOIN courses c ON cc.slug = c.category AND c.is_published = 1
            WHERE cc.is_active = 1
            GROUP BY cc.id
            ORDER BY cc.sort_order, cc.name
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's course progress
     */
    public function getUserCourseProgress($user_id, $course_id) {
        $sql = "
            SELECT * FROM user_course_progress 
            WHERE user_id = ? AND course_id = ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $course_id]);
        return $stmt->fetch();
    }
    
    /**
     * Update user's course progress
     */
    public function updateUserCourseProgress($user_id, $course_id, $lesson_id = null, $progress_percentage = 0, $time_spent = 0) {
        // Check if progress record exists
        $existing = $this->getUserCourseProgress($user_id, $course_id);
        
        if ($existing) {
            // Update existing progress
            $sql = "
                UPDATE user_course_progress 
                SET lesson_id = ?, progress_percentage = ?, time_spent = time_spent + ?, last_accessed_at = NOW()
                WHERE user_id = ? AND course_id = ?
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$lesson_id, $progress_percentage, $time_spent, $user_id, $course_id]);
        } else {
            // Create new progress record
            $sql = "
                INSERT INTO user_course_progress (user_id, course_id, lesson_id, progress_percentage, time_spent, started_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $course_id, $lesson_id, $progress_percentage, $time_spent]);
        }
    }
    
    /**
     * Mark course as completed for user
     */
    public function markCourseCompleted($user_id, $course_id, $completion_percentage = 100, $total_time_spent = 0) {
        // Check if already completed
        $sql = "SELECT id FROM user_course_completions WHERE user_id = ? AND course_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $course_id]);
        
        if (!$stmt->fetch()) {
            // Insert completion record
            $sql = "
                INSERT INTO user_course_completions (user_id, course_id, completion_percentage, time_spent, completed_at)
                VALUES (?, ?, ?, ?, NOW())
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $course_id, $completion_percentage, $total_time_spent]);
            
            // Update progress to 100%
            $this->updateUserCourseProgress($user_id, $course_id, null, 100, 0);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user's completed courses
     */
    public function getUserCompletedCourses($user_id) {
        $sql = "
            SELECT c.*, ucc.completed_at, ucc.completion_percentage, ucc.time_spent
            FROM user_course_completions ucc
            JOIN courses c ON ucc.course_id = c.id
            WHERE ucc.user_id = ? AND ucc.is_completed = 1
            ORDER BY ucc.completed_at DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user's course progress summary
     */
    public function getUserCourseProgressSummary($user_id) {
        $sql = "
            SELECT 
                COUNT(DISTINCT ucp.course_id) as courses_started,
                COUNT(DISTINCT ucc.course_id) as courses_completed,
                COALESCE(SUM(ucc.time_spent), 0) as total_time_spent
            FROM user_course_progress ucp
            LEFT JOIN user_course_completions ucc ON ucp.user_id = ucc.user_id AND ucp.course_id = ucc.course_id
            WHERE ucp.user_id = ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    /**
     * Check if user has completed a course
     */
    public function hasUserCompletedCourse($user_id, $course_id) {
        $sql = "
            SELECT id FROM user_course_completions 
            WHERE user_id = ? AND course_id = ? AND is_completed = 1
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $course_id]);
        return (bool) $stmt->fetch();
    }
    
    /**
     * Get course statistics
     */
    public function getCourseStats($course_id) {
        $sql = "
            SELECT 
                COUNT(DISTINCT ucp.user_id) as total_students,
                COUNT(DISTINCT ucc.user_id) as completed_students,
                AVG(ucc.completion_percentage) as avg_completion_percentage,
                AVG(ucc.time_spent) as avg_time_spent
            FROM user_course_progress ucp
            LEFT JOIN user_course_completions ucc ON ucp.user_id = ucc.user_id AND ucp.course_id = ucc.course_id
            WHERE ucp.course_id = ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$course_id]);
        return $stmt->fetch();
    }
}
?>
