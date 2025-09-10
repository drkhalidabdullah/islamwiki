<?php
/**
 * Content Moderation Functions
 * Handles content reporting, moderation, and quality assessment
 */

/**
 * Report content for moderation
 */
function report_content($content_type, $content_id, $reason, $description = '', $reporter_id = null) {
    global $pdo;
    
    // Check rate limit for reporting
    if (!check_rate_limit('report_content')) {
        return [
            'success' => false,
            'message' => 'Too many reports submitted. Please try again later.'
        ];
    }
    
    $reporter_ip = get_client_ip();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO content_reports (reporter_id, reporter_ip, content_type, content_id, report_reason, report_description) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $reporter_id,
            $reporter_ip,
            $content_type,
            $content_id,
            $reason,
            $description
        ]);
        
        if ($result) {
            // Log the report
            log_activity('content_reported', "Content reported: {$content_type} #{$content_id}", $reporter_id, [
                'content_type' => $content_type,
                'content_id' => $content_id,
                'reason' => $reason
            ]);
            
            return [
                'success' => true,
                'message' => 'Content reported successfully. Thank you for helping maintain quality.'
            ];
        }
    } catch (PDOException $e) {
        error_log("Content report error: " . $e->getMessage());
    }
    
    return [
        'success' => false,
        'message' => 'Failed to submit report. Please try again.'
    ];
}

/**
 * Get content reports for moderation
 */
function get_content_reports($status = 'pending', $limit = 50, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT cr.*, u.username as reporter_username, u.display_name as reporter_display_name,
               reviewer.username as reviewer_username, reviewer.display_name as reviewer_display_name
        FROM content_reports cr
        LEFT JOIN users u ON cr.reporter_id = u.id
        LEFT JOIN users reviewer ON cr.reviewed_by = reviewer.id
        WHERE cr.status = ?
        ORDER BY cr.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$status, $limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Update report status
 */
function update_report_status($report_id, $status, $reviewer_id, $resolution_notes = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE content_reports 
            SET status = ?, reviewed_by = ?, reviewed_at = NOW(), resolution_notes = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$status, $reviewer_id, $resolution_notes, $report_id]);
        
        if ($result) {
            // Get report details for logging
            $stmt = $pdo->prepare("SELECT * FROM content_reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $report = $stmt->fetch();
            
            if ($report) {
                log_activity('report_reviewed', "Report #{$report_id} status changed to {$status}", $reviewer_id, [
                    'report_id' => $report_id,
                    'content_type' => $report['content_type'],
                    'content_id' => $report['content_id'],
                    'status' => $status
                ]);
            }
            
            return true;
        }
    } catch (PDOException $e) {
        error_log("Report status update error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Get content by type and ID
 */
function get_content_for_moderation($content_type, $content_id) {
    global $pdo;
    
    switch ($content_type) {
        case 'wiki_article':
            $stmt = $pdo->prepare("
                SELECT wa.*, u.username, u.display_name, cc.name as category_name
                FROM wiki_articles wa
                JOIN users u ON wa.author_id = u.id
                LEFT JOIN content_categories cc ON wa.category_id = cc.id
                WHERE wa.id = ?
            ");
            break;
            
        case 'user_post':
            $stmt = $pdo->prepare("
                SELECT up.*, u.username, u.display_name
                FROM user_posts up
                JOIN users u ON up.user_id = u.id
                WHERE up.id = ?
            ");
            break;
            
        case 'user_profile':
            $stmt = $pdo->prepare("
                SELECT u.*
                FROM users u
                WHERE u.id = ?
            ");
            break;
            
        default:
            return null;
    }
    
    $stmt->execute([$content_id]);
    return $stmt->fetch();
}

/**
 * Flag content as inappropriate
 */
function flag_content($content_type, $content_id, $reason) {
    global $pdo;
    
    try {
        switch ($content_type) {
            case 'wiki_article':
                $stmt = $pdo->prepare("
                    UPDATE wiki_articles 
                    SET is_flagged = TRUE, flag_reason = ?
                    WHERE id = ?
                ");
                break;
                
            case 'user_post':
                $stmt = $pdo->prepare("
                    UPDATE user_posts 
                    SET is_flagged = TRUE, flag_reason = ?
                    WHERE id = ?
                ");
                break;
                
            default:
                return false;
        }
        
        $result = $stmt->execute([$reason, $content_id]);
        
        if ($result) {
            log_activity('content_flagged', "Content flagged: {$content_type} #{$content_id}", null, [
                'content_type' => $content_type,
                'content_id' => $content_id,
                'reason' => $reason
            ]);
            
            return true;
        }
    } catch (PDOException $e) {
        error_log("Content flagging error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Unflag content
 */
function unflag_content($content_type, $content_id) {
    global $pdo;
    
    try {
        switch ($content_type) {
            case 'wiki_article':
                $stmt = $pdo->prepare("
                    UPDATE wiki_articles 
                    SET is_flagged = FALSE, flag_reason = NULL
                    WHERE id = ?
                ");
                break;
                
            case 'user_post':
                $stmt = $pdo->prepare("
                    UPDATE user_posts 
                    SET is_flagged = FALSE, flag_reason = NULL
                    WHERE id = ?
                ");
                break;
                
            default:
                return false;
        }
        
        $result = $stmt->execute([$content_id]);
        
        if ($result) {
            log_activity('content_unflagged', "Content unflagged: {$content_type} #{$content_id}", null, [
                'content_type' => $content_type,
                'content_id' => $content_id
            ]);
            
            return true;
        }
    } catch (PDOException $e) {
        error_log("Content unflagging error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Calculate article quality score
 */
function calculate_article_quality_score($article_id) {
    global $pdo;
    
    // Get article data
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name
        FROM wiki_articles wa
        JOIN users u ON wa.author_id = u.id
        WHERE wa.id = ?
    ");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        return 0;
    }
    
    $score = 0;
    $maxScore = 100;
    
    // Content length (0-20 points)
    $contentLength = strlen(strip_tags($article['content']));
    if ($contentLength > 1000) {
        $score += 20;
    } elseif ($contentLength > 500) {
        $score += 15;
    } elseif ($contentLength > 200) {
        $score += 10;
    } else {
        $score += 5;
    }
    
    // Has excerpt (0-10 points)
    if (!empty($article['excerpt']) && strlen($article['excerpt']) > 50) {
        $score += 10;
    }
    
    // Has category (0-10 points)
    if (!empty($article['category_id'])) {
        $score += 10;
    }
    
    // View count (0-20 points)
    $viewCount = $article['view_count'];
    if ($viewCount > 1000) {
        $score += 20;
    } elseif ($viewCount > 500) {
        $score += 15;
    } elseif ($viewCount > 100) {
        $score += 10;
    } elseif ($viewCount > 10) {
        $score += 5;
    }
    
    // Recent activity (0-15 points)
    $daysSinceUpdate = (time() - strtotime($article['updated_at'])) / 86400;
    if ($daysSinceUpdate < 30) {
        $score += 15;
    } elseif ($daysSinceUpdate < 90) {
        $score += 10;
    } elseif ($daysSinceUpdate < 365) {
        $score += 5;
    }
    
    // Author reputation (0-15 points)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as article_count
        FROM wiki_articles 
        WHERE author_id = ? AND status = 'published'
    ");
    $stmt->execute([$article['author_id']]);
    $authorStats = $stmt->fetch();
    
    $articleCount = $authorStats['article_count'];
    if ($articleCount > 10) {
        $score += 15;
    } elseif ($articleCount > 5) {
        $score += 10;
    } elseif ($articleCount > 1) {
        $score += 5;
    }
    
    // Content quality indicators (0-10 points)
    $content = $article['content'];
    $qualityIndicators = 0;
    
    // Check for headings
    if (preg_match('/<h[1-6][^>]*>/i', $content)) {
        $qualityIndicators += 2;
    }
    
    // Check for lists
    if (preg_match('/<[uo]l[^>]*>/i', $content)) {
        $qualityIndicators += 2;
    }
    
    // Check for links
    if (preg_match('/<a[^>]*href/i', $content)) {
        $qualityIndicators += 2;
    }
    
    // Check for images
    if (preg_match('/<img[^>]*>/i', $content)) {
        $qualityIndicators += 2;
    }
    
    // Check for code blocks
    if (preg_match('/<pre[^>]*>/i', $content) || preg_match('/```/', $content)) {
        $qualityIndicators += 2;
    }
    
    $score += min($qualityIndicators, 10);
    
    // Ensure score is within bounds
    $score = max(0, min($score, $maxScore));
    
    // Save quality score
    $stmt = $pdo->prepare("
        INSERT INTO article_quality_metrics (article_id, metric_type, metric_value) 
        VALUES (?, 'overall_score', ?)
        ON DUPLICATE KEY UPDATE metric_value = VALUES(metric_value), calculated_at = NOW()
    ");
    $stmt->execute([$article_id, $score]);
    
    // Update article quality score
    $stmt = $pdo->prepare("
        UPDATE wiki_articles 
        SET quality_score = ?, last_validated = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$score, $article_id]);
    
    return $score;
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get moderation statistics
 */
function get_moderation_stats($days = 7) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_reports,
            SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) as dismissed_reports,
            COUNT(DISTINCT content_id) as unique_content_reported
        FROM content_reports 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
    ");
    $stmt->execute([$days]);
    $stats = $stmt->fetch();
    
    // Get reports by reason
    $stmt = $pdo->prepare("
        SELECT report_reason, COUNT(*) as count
        FROM content_reports 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY report_reason
        ORDER BY count DESC
    ");
    $stmt->execute([$days]);
    $stats['by_reason'] = $stmt->fetchAll();
    
    // Get reports by content type
    $stmt = $pdo->prepare("
        SELECT content_type, COUNT(*) as count
        FROM content_reports 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY content_type
        ORDER BY count DESC
    ");
    $stmt->execute([$days]);
    $stats['by_content_type'] = $stmt->fetchAll();
    
    return $stats;
}
?>
