<?php

/**
 * Migration: Add User Authentication Fields for v0.0.5
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @date 2025-01-27
 * @license AGPL-3.0
 */

class AddUserAuthenticationFields
{
    public function up()
    {
        $sql = "
        -- Add missing fields to users table
        ALTER TABLE `users` 
        ADD COLUMN `status` enum('pending_verification', 'active', 'suspended', 'banned') NOT NULL DEFAULT 'pending_verification' AFTER `is_banned`,
        ADD COLUMN `password_reset_token` varchar(100) NULL AFTER `email_verification_token`,
        ADD COLUMN `password_reset_expires_at` timestamp NULL AFTER `password_reset_token`,
        ADD COLUMN `two_factor_secret` varchar(255) NULL AFTER `password_reset_expires_at`,
        ADD COLUMN `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `two_factor_secret`,
        ADD COLUMN `login_attempts` int(11) NOT NULL DEFAULT 0 AFTER `two_factor_enabled`,
        ADD COLUMN `locked_until` timestamp NULL AFTER `login_attempts`,
        ADD COLUMN `preferences` json NULL AFTER `locked_until`;

        -- Add indexes for new fields
        ALTER TABLE `users` 
        ADD INDEX `idx_status` (`status`),
        ADD INDEX `idx_password_reset_token` (`password_reset_token`),
        ADD INDEX `idx_password_reset_expires_at` (`password_reset_expires_at`),
        ADD INDEX `idx_login_attempts` (`login_attempts`),
        ADD INDEX `idx_locked_until` (`locked_until`);

        -- Update existing users to have 'active' status
        UPDATE `users` SET `status` = 'active' WHERE `email_verified_at` IS NOT NULL;
        UPDATE `users` SET `status` = 'pending_verification' WHERE `email_verified_at` IS NULL;

        -- Add new system settings for authentication
        INSERT INTO `system_settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
        ('max_login_attempts', '5', 'integer', 'Maximum login attempts before account lockout', 0),
        ('lockout_duration', '900', 'integer', 'Account lockout duration in seconds', 0),
        ('password_min_length', '8', 'integer', 'Minimum password length', 0),
        ('password_require_special', '1', 'boolean', 'Require special characters in password', 0),
        ('password_require_numbers', '1', 'boolean', 'Require numbers in password', 0),
        ('password_require_uppercase', '1', 'boolean', 'Require uppercase letters in password', 0),
        ('session_timeout', '3600', 'integer', 'Session timeout in seconds', 0),
        ('two_factor_required', '0', 'boolean', 'Require two-factor authentication', 0),
        ('jwt_secret', '', 'string', 'JWT secret key for authentication', 0),
        ('jwt_expiration', '3600', 'integer', 'JWT token expiration in seconds', 0);

        -- Add new roles for enhanced user management
        INSERT INTO `roles` (`name`, `display_name`, `description`, `permissions`, `is_system`) VALUES
        ('verified_user', 'Verified User', 'User with verified email address', '[\"content.create\", \"content.edit\", \"comments.create\", \"profile.edit\"]', 1),
        ('trusted_user', 'Trusted User', 'User with good standing and extended permissions', '[\"content.create\", \"content.edit\", \"content.publish\", \"comments.create\", \"comments.moderate\", \"profile.edit\"]', 1);

        -- Create user_verification_logs table for tracking verification attempts
        CREATE TABLE `user_verification_logs` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL,
            `verification_type` enum('email', 'password_reset', 'two_factor') NOT NULL,
            `token` varchar(255) NOT NULL,
            `ip_address` varchar(45),
            `user_agent` text,
            `status` enum('pending', 'completed', 'expired', 'failed') NOT NULL DEFAULT 'pending',
            `attempts` int(11) NOT NULL DEFAULT 0,
            `expires_at` timestamp NOT NULL,
            `completed_at` timestamp NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_verification_type` (`verification_type`),
            KEY `idx_token` (`token`),
            KEY `idx_status` (`status`),
            KEY `idx_expires_at` (`expires_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- Create user_login_logs table for security monitoring
        CREATE TABLE `user_login_logs` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NULL,
            `username` varchar(50) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `user_agent` text,
            `status` enum('success', 'failed', 'locked', 'two_factor_required') NOT NULL,
            `failure_reason` varchar(255) NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_username` (`username`),
            KEY `idx_ip_address` (`ip_address`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- Create user_security_settings table for user-specific security preferences
        CREATE TABLE `user_security_settings` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL UNIQUE,
            `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
            `two_factor_method` enum('totp', 'sms', 'email') NULL,
            `login_notifications` tinyint(1) NOT NULL DEFAULT 1,
            `password_change_notifications` tinyint(1) NOT NULL DEFAULT 1,
            `session_management` tinyint(1) NOT NULL DEFAULT 1,
            `trusted_devices` json NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        return $sql;
    }

    public function down()
    {
        $sql = "
        -- Remove user security settings table
        DROP TABLE IF EXISTS `user_security_settings`;
        
        -- Remove user login logs table
        DROP TABLE IF EXISTS `user_login_logs`;
        
        -- Remove user verification logs table
        DROP TABLE IF EXISTS `user_verification_logs`;
        
        -- Remove new roles
        DELETE FROM `roles` WHERE `name` IN ('verified_user', 'trusted_user');
        
        -- Remove new system settings
        DELETE FROM `system_settings` WHERE `key` IN (
            'max_login_attempts', 'lockout_duration', 'password_min_length', 
            'password_require_special', 'password_require_numbers', 'password_require_uppercase',
            'session_timeout', 'two_factor_required', 'jwt_secret', 'jwt_expiration'
        );
        
        -- Remove indexes
        ALTER TABLE `users` 
        DROP INDEX `idx_status`,
        DROP INDEX `idx_password_reset_token`,
        DROP INDEX `idx_password_reset_expires_at`,
        DROP INDEX `idx_login_attempts`,
        DROP INDEX `idx_locked_until`;
        
        -- Remove new columns
        ALTER TABLE `users` 
        DROP COLUMN `status`,
        DROP COLUMN `password_reset_token`,
        DROP COLUMN `password_reset_expires_at`,
        DROP COLUMN `two_factor_secret`,
        DROP COLUMN `two_factor_enabled`,
        DROP COLUMN `login_attempts`,
        DROP COLUMN `locked_until`,
        DROP COLUMN `preferences`;
        ";

        return $sql;
    }
} 