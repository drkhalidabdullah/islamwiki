<?php

/**
 * Migration: Create v0.0.5 User Management System
 * 
 * This migration creates the enhanced user management system for v0.0.5
 * including authentication, security, and user profile features.
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @license AGPL-3.0
 */

use IslamWiki\Core\Database\DatabaseManager;

class CreateV005UserSystem
{
    private DatabaseManager $db;
    
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }
    
    /**
     * Run the migration
     */
    public function up(): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Create enhanced users table
            $this->createUsersTable();
            
            // Create user verification logs table
            $this->createUserVerificationLogsTable();
            
            // Create user login logs table
            $this->createUserLoginLogsTable();
            
            // Create user security settings table
            $this->createUserSecuritySettingsTable();
            
            // Insert default admin user
            $this->insertDefaultAdminUser();
            
            // Insert test user
            $this->insertTestUser();
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Migration failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rollback the migration
     */
    public function down(): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Drop tables in reverse order
            $dropTables = [
                "DROP TABLE IF EXISTS user_security_settings",
                "DROP TABLE IF EXISTS user_login_logs", 
                "DROP TABLE IF EXISTS user_verification_logs",
                "DROP TABLE IF EXISTS users"
            ];
            
            foreach ($dropTables as $sql) {
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Migration rollback failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create enhanced users table
     */
    private function createUsersTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            status ENUM('pending_verification', 'active', 'suspended', 'banned') DEFAULT 'pending_verification',
            email_verified_at TIMESTAMP NULL,
            password_reset_token VARCHAR(255) NULL,
            password_reset_expires_at TIMESTAMP NULL,
            two_factor_secret VARCHAR(255) NULL,
            login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            last_login_at TIMESTAMP NULL,
            preferences JSON NULL,
            role ENUM('admin', 'moderator', 'user', 'verified_user', 'trusted_user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_email (email),
            INDEX idx_username (username),
            INDEX idx_status (status),
            INDEX idx_role (role),
            INDEX idx_created_at (created_at),
            INDEX idx_email_verified (email_verified_at),
            INDEX idx_password_reset (password_reset_token, password_reset_expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
    
    /**
     * Create user verification logs table
     */
    private function createUserVerificationLogsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_verification_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            verification_type ENUM('email', 'password_reset', 'two_factor') NOT NULL,
            token VARCHAR(255) NOT NULL,
            status ENUM('pending', 'completed', 'expired', 'failed') DEFAULT 'pending',
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            
            INDEX idx_user_id (user_id),
            INDEX idx_verification_type (verification_type),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_token (token),
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
    
    /**
     * Create user login logs table
     */
    private function createUserLoginLogsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            login_type ENUM('success', 'failed', 'locked', 'password_reset') NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            location VARCHAR(255),
            device_info JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_user_id (user_id),
            INDEX idx_login_type (login_type),
            INDEX idx_created_at (created_at),
            INDEX idx_ip_address (ip_address),
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
    
    /**
     * Create user security settings table
     */
    private function createUserSecuritySettingsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_security_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            two_factor_enabled BOOLEAN DEFAULT FALSE,
            login_notifications BOOLEAN DEFAULT TRUE,
            security_alerts BOOLEAN DEFAULT TRUE,
            session_timeout INT DEFAULT 3600,
            max_concurrent_sessions INT DEFAULT 5,
            trusted_devices JSON,
            security_questions JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            UNIQUE KEY unique_user (user_id),
            INDEX idx_two_factor (two_factor_enabled),
            INDEX idx_created_at (created_at),
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
    
    /**
     * Insert default admin user
     */
    private function insertDefaultAdminUser(): void
    {
        $sql = "INSERT INTO users (
            username, email, password_hash, first_name, last_name, 
            status, email_verified_at, role, created_at, updated_at
        ) VALUES (
            'admin', 'admin@islamwiki.org', ?, 'Admin', 'User',
            'active', NOW(), 'admin', NOW(), NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([password_hash('password', PASSWORD_DEFAULT)]);
        
        $adminId = $this->db->lastInsertId();
        
        // Create security settings for admin
        $this->createUserSecuritySettings($adminId);
    }
    
    /**
     * Insert test user
     */
    private function insertTestUser(): void
    {
        $sql = "INSERT INTO users (
            username, email, password_hash, first_name, last_name, 
            status, email_verified_at, role, created_at, updated_at
        ) VALUES (
            'test', 'test@islamwiki.org', ?, 'Test', 'User',
            'active', NOW(), 'user', NOW(), NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([password_hash('password', PASSWORD_DEFAULT)]);
        
        $testId = $this->db->lastInsertId();
        
        // Create security settings for test user
        $this->createUserSecuritySettings($testId);
    }
    
    /**
     * Create default security settings for a user
     */
    private function createUserSecuritySettings(int $userId): void
    {
        $sql = "INSERT INTO user_security_settings (
            user_id, two_factor_enabled, login_notifications, security_alerts,
            session_timeout, max_concurrent_sessions, trusted_devices, security_questions
        ) VALUES (?, FALSE, TRUE, TRUE, 3600, 5, '[]', '[]')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
    }
} 