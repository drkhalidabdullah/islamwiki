<?php

namespace IslamWiki\Services\User;

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * User Service for IslamWiki Framework
 * Handles user authentication, management, and security
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @license AGPL-3.0
 */
class UserService
{
    private DatabaseManager $db;
    private string $jwtSecret;
    private int $jwtExpiry;
    
    public function __construct(DatabaseManager $db, string $jwtSecret = '', int $jwtExpiry = 3600)
    {
        $this->db = $db;
        $this->jwtSecret = $jwtSecret ?: (getenv('JWT_SECRET') ?: 'default_secret_key_change_in_production');
        $this->jwtExpiry = $jwtExpiry;
    }
    
    /**
     * Register a new user
     */
    public function register(array $userData): array
    {
        try {
            // Validate required fields
            if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
                return ['success' => false, 'message' => 'Username, email, and password are required'];
            }
            
            // Check if username already exists
            if ($this->userExists('username', $userData['username'])) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email already exists
            if ($this->userExists('email', $userData['email'])) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Validate password strength
            if (!$this->validatePassword($userData['password'])) {
                return ['success' => false, 'message' => 'Password does not meet security requirements'];
            }
            
            // Create user
            $user = new User($this->db);
            $userData['status'] = 'pending_verification';
            $userData['role'] = 'user';
            
            if ($user->create($userData)) {
                // Generate verification token
                $verificationToken = $this->generateVerificationToken($user->id);
                
                // Send verification email (placeholder for now)
                $this->sendVerificationEmail($user->email, $verificationToken);
                
                return [
                    'success' => true,
                    'message' => 'User registered successfully. Please check your email for verification.',
                    'user_id' => $user->id
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create user'];
            
        } catch (\Exception $e) {
            error_log("Error in user registration: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Authenticate user login
     */
    public function login(string $email, string $password): array
    {
        try {
            // Find user by email
            $user = (new User($this->db))->findByEmail($email);
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Check if account is locked
            if ($user->isLocked()) {
                return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
            }
            
            // Check if account is verified
            if (!$user->isVerified()) {
                return ['success' => false, 'message' => 'Please verify your email before logging in'];
            }
            
            // Check if account is active
            if (!$user->isActive()) {
                return ['success' => false, 'message' => 'Account is not active'];
            }
            
            // Verify password
            if (!password_verify($password, $user->password_hash)) {
                // Increment login attempts
                $newAttempts = $user->login_attempts + 1;
                $lockedUntil = null;
                
                // Lock account after 5 failed attempts
                if ($newAttempts >= 5) {
                    $lockedUntil = date('Y-m-d H:i:s', time() + 900); // 15 minutes
                }
                
                $user->updateLoginAttempts($newAttempts, $lockedUntil);
                
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Reset login attempts and update last login
            $user->updateLastLogin();
            
            // Generate JWT token
            $token = $this->generateJWTToken($user);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user->toArray()
            ];
            
        } catch (\Exception $e) {
            error_log("Error in user login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Verify JWT token
     */
    public function verifyToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Find user
            $user = (new User($this->db))->findById($decoded->user_id);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Check if user is still active
            if (!$user->isActive()) {
                return ['success' => false, 'message' => 'User account is not active'];
            }
            
            return [
                'success' => true,
                'user' => $user->toArray()
            ];
            
        } catch (\Exception $e) {
            error_log("Error verifying token: " . $e->getMessage());
            return ['success' => false, 'message' => 'Invalid token'];
        }
    }
    
    /**
     * Verify user email
     */
    public function verifyEmail(string $token): array
    {
        try {
            // Decode verification token
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Find user
            $user = (new User($this->db))->findById($decoded->user_id);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Check if already verified
            if ($user->isVerified()) {
                return ['success' => false, 'message' => 'Email already verified'];
            }
            
            // Verify email
            if ($user->verifyEmail()) {
                return [
                    'success' => true,
                    'message' => 'Email verified successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to verify email'];
            
        } catch (\Exception $e) {
            error_log("Error in email verification: " . $e->getMessage());
            return ['success' => false, 'message' => 'Invalid verification token'];
        }
    }
    
    /**
     * Initiate password reset
     */
    public function forgotPassword(string $email): array
    {
        try {
            // Find user by email
            $user = (new User($this->db))->findByEmail($email);
            if (!$user) {
                return ['success' => false, 'message' => 'If an account exists with this email, you will receive a reset link'];
            }
            
            // Generate reset token
            $resetToken = $this->generatePasswordResetToken($user->id);
            
            // Set reset token in database
            if ($user->setPasswordResetToken($resetToken)) {
                // Send reset email (placeholder for now)
                $this->sendPasswordResetEmail($user->email, $resetToken);
                
                return [
                    'success' => true,
                    'message' => 'Password reset link sent to your email'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to initiate password reset'];
            
        } catch (\Exception $e) {
            error_log("Error in forgot password: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password reset failed. Please try again.'];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            // Decode reset token
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Find user
            $user = (new User($this->db))->findById($decoded->user_id);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Check if token is valid
            if ($user->password_reset_token !== $token) {
                return ['success' => false, 'message' => 'Invalid reset token'];
            }
            
            // Check if token is expired
            if (strtotime($user->password_reset_expires_at) < time()) {
                return ['success' => false, 'message' => 'Reset token has expired'];
            }
            
            // Validate new password
            if (!$this->validatePassword($newPassword)) {
                return ['success' => false, 'message' => 'Password does not meet security requirements'];
            }
            
            // Update password
            if ($user->updatePassword($newPassword)) {
                return [
                    'success' => true,
                    'message' => 'Password reset successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to reset password'];
            
        } catch (\Exception $e) {
            error_log("Error in password reset: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password reset failed. Please try again.'];
        }
    }
    
    /**
     * Change password for authenticated user
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            // Find user
            $user = (new User($this->db))->findById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user->password_hash)) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Validate new password
            if (!$this->validatePassword($newPassword)) {
                return ['success' => false, 'message' => 'Password does not meet security requirements'];
            }
            
            // Update password
            if ($user->updatePassword($newPassword)) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to change password'];
            
        } catch (\Exception $e) {
            error_log("Error in password change: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password change failed. Please try again.'];
        }
    }
    
    /**
     * Get user profile
     */
    public function getProfile(int $userId): array
    {
        try {
            $user = (new User($this->db))->findById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            return [
                'success' => true,
                'user' => $user->toArray()
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting user profile: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get user profile'];
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $profileData): array
    {
        try {
            $user = (new User($this->db))->findById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Update profile
            if ($user->update($profileData)) {
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'user' => $user->toArray()
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update profile'];
            
        } catch (\Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return ['success' => false, 'message' => 'Profile update failed. Please try again.'];
        }
    }
    
    /**
     * Get all users with pagination and filters
     */
    public function getAllUsers(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        try {
            $user = new User($this->db);
            $users = $user->getAll($page, $perPage, $filters);
            $total = $user->getCount($filters);
            
            return [
                'success' => true,
                'users' => array_map(fn($u) => $u->toArray(), $users),
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'pages' => ceil($total / $perPage)
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get users'];
        }
    }
    
    /**
     * Delete user
     */
    public function deleteUser(int $userId): array
    {
        try {
            $user = (new User($this->db))->findById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            if ($user->delete()) {
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to delete user'];
            
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete user'];
        }
    }
    
    /**
     * Check if user exists
     */
    private function userExists(string $field, string $value): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE $field = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$value]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) $result['count'] > 0;
            
        } catch (\Exception $e) {
            error_log("Error checking if user exists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate password strength
     */
    private function validatePassword(string $password): bool
    {
        // Minimum 8 characters, at least one uppercase, one lowercase, one number
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
    
    /**
     * Generate verification token
     */
    private function generateVerificationToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'type' => 'email_verification',
            'iat' => time(),
            'exp' => time() + 86400 // 24 hours
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Generate password reset token
     */
    private function generatePasswordResetToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'type' => 'password_reset',
            'iat' => time(),
            'exp' => time() + 3600 // 1 hour
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Generate JWT token for user
     */
    public function generateJWTToken(User $user): string
    {
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + $this->jwtExpiry
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Send verification email (placeholder)
     */
    private function sendVerificationEmail(string $email, string $token): void
    {
        // TODO: Implement actual email sending
        error_log("Verification email would be sent to $email with token: $token");
    }
    
    /**
     * Send password reset email (placeholder)
     */
    private function sendPasswordResetEmail(string $email, string $token): void
    {
        // TODO: Implement actual email sending
        error_log("Password reset email would be sent to $email with token: $token");
    }
} 