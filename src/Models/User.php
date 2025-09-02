<?php

namespace IslamWiki\Models;

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Exceptions\DatabaseException;

/**
 * User Model for IslamWiki Framework
 * Handles user authentication, profiles, and management
 * 
 * @author Khalid Abdullah
 * @version 0.0.5
 * @license AGPL-3.0
 */
class User
{
    private DatabaseManager $db;
    
    // User table fields
    public ?int $id = null;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password_hash = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $status = 'pending_verification';
    public ?string $email_verified_at = null;
    public ?string $password_reset_token = null;
    public ?string $password_reset_expires_at = null;
    public ?string $two_factor_secret = null;
    public ?int $login_attempts = 0;
    public ?string $locked_until = null;
    public ?string $last_login_at = null;
    public ?string $preferences = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    
    // Role and permission fields
    public ?string $role = 'user';
    public array $permissions = [];
    
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create a new user
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO users (
                username, email, password_hash, first_name, last_name, 
                status, role, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['status'] ?? 'pending_verification',
                $data['role'] ?? 'user'
            ]);
            
            if ($result) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find user by ID
     */
    public function findById(int $id): ?self
    {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            if ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return $this->hydrate($userData);
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Error finding user by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?self
    {
        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            
            if ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return $this->hydrate($userData);
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?self
    {
        try {
            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            
            if ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return $this->hydrate($userData);
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Error finding user by username: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user
     */
    public function update(array $data): bool
    {
        try {
            $sql = "UPDATE users SET 
                username = ?, email = ?, first_name = ?, last_name = ?,
                status = ?, role = ?, updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['username'] ?? $this->username,
                $data['email'] ?? $this->email,
                $data['first_name'] ?? $this->first_name,
                $data['last_name'] ?? $this->last_name,
                $data['status'] ?? $this->status,
                $data['role'] ?? $this->role,
                $this->id
            ]);
        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update password
     */
    public function updatePassword(string $newPassword): bool
    {
        try {
            $sql = "UPDATE users SET 
                password_hash = ?, 
                password_reset_token = NULL,
                password_reset_expires_at = NULL,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                $this->id
            ]);
        } catch (\Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify email
     */
    public function verifyEmail(): bool
    {
        try {
            $sql = "UPDATE users SET 
                email_verified_at = NOW(),
                status = 'active',
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\Exception $e) {
            error_log("Error verifying email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set password reset token
     */
    public function setPasswordResetToken(string $token, int $expiresIn = 3600): bool
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
            
            $sql = "UPDATE users SET 
                password_reset_token = ?,
                password_reset_expires_at = ?,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$token, $expiresAt, $this->id]);
        } catch (\Exception $e) {
            error_log("Error setting password reset token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear password reset token
     */
    public function clearPasswordResetToken(): bool
    {
        try {
            $sql = "UPDATE users SET 
                password_reset_token = NULL,
                password_reset_expires_at = NULL,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\Exception $e) {
            error_log("Error clearing password reset token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update login attempts
     */
    public function updateLoginAttempts(int $attempts, ?string $lockedUntil = null): bool
    {
        try {
            $sql = "UPDATE users SET 
                login_attempts = ?,
                locked_until = ?,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$attempts, $lockedUntil, $this->id]);
        } catch (\Exception $e) {
            error_log("Error updating login attempts: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin(): bool
    {
        try {
            $sql = "UPDATE users SET 
                last_login_at = NOW(),
                login_attempts = 0,
                locked_until = NULL,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\Exception $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is locked
     */
    public function isLocked(): bool
    {
        if (!$this->locked_until) {
            return false;
        }
        
        return strtotime($this->locked_until) > time();
    }
    
    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
    
    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }
    
    /**
     * Get all users with pagination
     */
    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $whereClause .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['role'])) {
                $whereClause .= " AND role = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['search'])) {
                $whereClause .= " AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            $sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $users = [];
            while ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $users[] = $this->hydrate($userData);
            }
            
            return $users;
        } catch (\Exception $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user count with filters
     */
    public function getCount(array $filters = []): int
    {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $whereClause .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['role'])) {
                $whereClause .= " AND role = ?";
                $params[] = $filters['role'];
            }
            
            $sql = "SELECT COUNT(*) as count FROM users $whereClause";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) $result['count'];
        } catch (\Exception $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Delete user
     */
    public function delete(): bool
    {
        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$this->id]);
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hydrate user object from data
     */
    private function hydrate(array $data): self
    {
        $user = new self($this->db);
        $user->id = (int) $data['id'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->status = $data['status'];
        $user->email_verified_at = $data['email_verified_at'];
        $user->password_reset_token = $data['password_reset_token'];
        $user->password_reset_expires_at = $data['password_reset_expires_at'];
        $user->two_factor_secret = $data['two_factor_secret'];
        $user->login_attempts = (int) $data['login_attempts'];
        $user->locked_until = $data['locked_until'];
        $user->last_login_at = $data['last_login_at'];
        $user->preferences = $data['preferences'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];
        $user->role = $data['role'];
        
        return $user;
    }
    
    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at,
            'last_login_at' => $this->last_login_at,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
} 