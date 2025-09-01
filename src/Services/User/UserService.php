<?php

namespace IslamWiki\Services\User;

use IslamWiki\Core\Database\DatabaseManager;
use Exception;
use PDO;

/**
 * Enhanced User Service - Real database integration for v0.0.4
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class UserService
{
    private DatabaseManager $database;

    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    /**
     * Get user by ID
     */
    public function getUser(int $id): ?array
    {
        try {
            $sql = "SELECT 
                u.*,
                GROUP_CONCAT(r.name) as roles,
                GROUP_CONCAT(r.display_name) as role_display_names
            FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.id = ?
                GROUP BY u.id";
            
            $stmt = $this->database->execute($sql, [$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
                $user['roles'] = $user['roles'] ? explode(',', $user['roles']) : [];
                $user['role_display_names'] = $user['role_display_names'] ? explode(',', $user['role_display_names']) : [];
                $user['profile'] = $this->getUserProfile($id);
        }
        
        return $user;
        } catch (Exception $e) {
            error_log("Error getting user {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by username
     */
    public function getUserByUsername(string $username): ?array
    {
        try {
            $sql = "SELECT 
                u.*,
                GROUP_CONCAT(r.name) as roles,
                GROUP_CONCAT(r.display_name) as role_display_names
            FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.username = ?
                GROUP BY u.id";
            
            $stmt = $this->database->execute($sql, [$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['roles'] = $user['roles'] ? explode(',', $user['roles']) : [];
                $user['role_display_names'] = $user['role_display_names'] ? explode(',', $user['role_display_names']) : [];
                $user['profile'] = $this->getUserProfile($user['id']);
            }
        
        return $user;
        } catch (Exception $e) {
            error_log("Error getting user by username {$username}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT 
                u.*,
                GROUP_CONCAT(r.name) as roles,
                GROUP_CONCAT(r.display_name) as role_display_names
            FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.email = ?
                GROUP BY u.id";
            
            $stmt = $this->database->execute($sql, [$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['roles'] = $user['roles'] ? explode(',', $user['roles']) : [];
                $user['role_display_names'] = $user['role_display_names'] ? explode(',', $user['role_display_names']) : [];
                $user['profile'] = $this->getUserProfile($user['id']);
            }
        
        return $user;
        } catch (Exception $e) {
            error_log("Error getting user by email {$email}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users with pagination and filters
     */
    public function getUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = [];
            $params = [];
            
            // Build filter conditions
            if (!empty($filters['search'])) {
                $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.display_name LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (isset($filters['is_active'])) {
                $whereConditions[] = "u.is_active = ?";
                $params[] = $filters['is_active'];
            }
            
            if (!empty($filters['role'])) {
                $whereConditions[] = "r.name = ?";
                $params[] = $filters['role'];
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Count total users
            $countSql = "SELECT COUNT(DISTINCT u.id) as total FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id {$whereClause}";
            $countStmt = $this->database->execute($countSql, $params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get users with pagination
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT 
                u.*,
                GROUP_CONCAT(r.name) as roles,
                GROUP_CONCAT(r.display_name) as role_display_names
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                {$whereClause}
                GROUP BY u.id
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->database->execute($sql, $params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process roles for each user
            foreach ($users as &$user) {
                $user['roles'] = $user['roles'] ? explode(',', $user['roles']) : [];
                $user['role_display_names'] = $user['role_display_names'] ? explode(',', $user['role_display_names']) : [];
            }
            
            return [
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ]
            ];
        } catch (Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            return ['users' => [], 'pagination' => ['current_page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1]];
        }
    }

    /**
     * Create new user
     */
    public function createUser(array $userData): array
    {
        try {
            $this->database->beginTransaction();
            
            // Validate required fields
            $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("Field '{$field}' is required");
                }
            }
            
            // Check if username or email already exists
            if ($this->userExists($userData['username'], $userData['email'])) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, display_name, bio, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->database->execute($sql, [
            $userData['username'],
            $userData['email'],
                $userData['password_hash'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['display_name'] ?? $userData['first_name'] . ' ' . $userData['last_name'],
                $userData['bio'] ?? null,
                $userData['is_active'] ?? true
            ]);
            
            $userId = $this->database->lastInsertId();
            
            // Create user profile
            $this->createUserProfile($userId, $userData);
            
            // Assign default role (user)
            $this->assignRole($userId, 'user');
            
            $this->database->commit();
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'User created successfully'
            ];
            
        } catch (Exception $e) {
            $this->database->rollback();
            error_log("Error creating user: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user
     */
    public function updateUser(int $userId, array $userData): array
    {
        try {
            $this->database->beginTransaction();
            
            // Check if user exists
            $existingUser = $this->getUser($userId);
            if (!$existingUser) {
                throw new Exception("User not found");
            }
            
            // Build update fields
            $updateFields = [];
            $params = [];
            
            $allowedFields = ['first_name', 'last_name', 'display_name', 'bio', 'is_active'];
            foreach ($allowedFields as $field) {
                if (isset($userData[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $userData[$field];
                }
            }
            
            if (empty($updateFields)) {
                throw new Exception("No fields to update");
            }
            
            $updateFields[] = "updated_at = NOW()";
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $this->database->execute($sql, $params);
            
            // Update profile if provided
            if (!empty($userData['profile'])) {
                $this->updateUserProfile($userId, $userData['profile']);
            }
            
            $this->database->commit();
            
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->database->rollback();
            error_log("Error updating user {$userId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): array
    {
        try {
            $this->database->beginTransaction();
            
            // Check if user exists
            $existingUser = $this->getUser($userId);
            if (!$existingUser) {
                throw new Exception("User not found");
            }
            
            // Delete user roles
            $this->database->execute("DELETE FROM user_roles WHERE user_id = ?", [$userId]);
            
            // Delete user profile
            $this->database->execute("DELETE FROM user_profiles WHERE user_id = ?", [$userId]);
            
            // Delete user
            $this->database->execute("DELETE FROM users WHERE id = ?", [$userId]);
            
            $this->database->commit();
            
            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
            
        } catch (Exception $e) {
            $this->database->rollback();
            error_log("Error deleting user {$userId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user profile
     */
    public function getUserProfile(int $userId): ?array
    {
        try {
            $sql = "SELECT * FROM user_profiles WHERE user_id = ?";
            $stmt = $this->database->execute($sql, [$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user profile {$userId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create user profile
     */
    private function createUserProfile(int $userId, array $profileData): bool
    {
        try {
            $sql = "INSERT INTO user_profiles (user_id, date_of_birth, gender, location, website, social_links, preferences, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $this->database->execute($sql, [
                $userId,
                $profileData['date_of_birth'] ?? null,
                $profileData['gender'] ?? null,
                $profileData['location'] ?? null,
                $profileData['website'] ?? null,
                json_encode($profileData['social_links'] ?? []),
                json_encode($profileData['preferences'] ?? [])
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error creating user profile {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user profile
     */
    private function updateUserProfile(int $userId, array $profileData): bool
    {
        try {
            $sql = "UPDATE user_profiles SET 
                    date_of_birth = ?, gender = ?, location = ?, website = ?, 
                    social_links = ?, preferences = ?, updated_at = NOW() 
                    WHERE user_id = ?";
            
            $this->database->execute($sql, [
                $profileData['date_of_birth'] ?? null,
                $profileData['gender'] ?? null,
                $profileData['location'] ?? null,
                $profileData['website'] ?? null,
                json_encode($profileData['social_links'] ?? []),
                json_encode($profileData['preferences'] ?? []),
                $userId
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating user profile {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, string $roleName): array
    {
        try {
            // Get role ID
            $roleSql = "SELECT id FROM roles WHERE name = ?";
            $roleStmt = $this->database->execute($roleSql, [$roleName]);
            $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$role) {
                throw new Exception("Role '{$roleName}' not found");
            }
            
            // Check if role is already assigned
            $existingSql = "SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?";
            $existingStmt = $this->database->execute($existingSql, [$userId, $role['id']]);
            
            if ($existingStmt->fetch()) {
                return [
                    'success' => true,
                    'message' => 'Role already assigned'
                ];
            }
            
            // Assign role
            $assignSql = "INSERT INTO user_roles (user_id, role_id, granted_at) VALUES (?, ?, NOW())";
            $this->database->execute($assignSql, [$userId, $role['id']]);
            
            return [
                'success' => true,
                'message' => "Role '{$roleName}' assigned successfully"
            ];
            
        } catch (Exception $e) {
            error_log("Error assigning role {$roleName} to user {$userId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(int $userId, string $roleName): array
    {
        try {
            // Get role ID
            $roleSql = "SELECT id FROM roles WHERE name = ?";
            $roleStmt = $this->database->execute($roleSql, [$roleName]);
            $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$role) {
                throw new Exception("Role '{$roleName}' not found");
            }
            
            // Remove role
            $removeSql = "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?";
            $this->database->execute($removeSql, [$userId, $role['id']]);
            
            return [
                'success' => true,
                'message' => "Role '{$roleName}' removed successfully"
            ];
            
        } catch (Exception $e) {
            error_log("Error removing role {$roleName} from user {$userId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if user has role
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        try {
            $sql = "SELECT 1 FROM user_roles ur 
                    JOIN roles r ON ur.role_id = r.id 
                    WHERE ur.user_id = ? AND r.name = ?";
            
            $stmt = $this->database->execute($sql, [$userId, $roleName]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking role {$roleName} for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user roles
     */
    public function getUserRoles(int $userId): array
    {
        try {
            $sql = "SELECT r.* FROM roles r 
                    JOIN user_roles ur ON r.id = ur.role_id 
                    WHERE ur.user_id = ?";
            
            $stmt = $this->database->execute($sql, [$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting roles for user {$userId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all roles
     */
    public function getAllRoles(): array
    {
        try {
            $sql = "SELECT * FROM roles ORDER BY name";
            $stmt = $this->database->execute($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting all roles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user exists
     */
    private function userExists(string $username, string $email): bool
    {
        try {
            $sql = "SELECT 1 FROM users WHERE username = ? OR email = ?";
            $stmt = $this->database->execute($sql, [$username, $email]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking if user exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        try {
            $stats = [];
            
            // Total users
            $totalSql = "SELECT COUNT(*) as total FROM users";
            $totalStmt = $this->database->execute($totalSql);
            $stats['total_users'] = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Active users
            $activeSql = "SELECT COUNT(*) as active FROM users WHERE is_active = 1";
            $activeStmt = $this->database->execute($activeSql);
            $stats['active_users'] = $activeStmt->fetch(PDO::FETCH_ASSOC)['active'];
            
            // Users by role
            $roleSql = "SELECT r.name, COUNT(ur.user_id) as count 
                        FROM roles r 
                        LEFT JOIN user_roles ur ON r.id = ur.role_id 
                        GROUP BY r.id, r.name";
            $roleStmt = $this->database->execute($roleSql);
            $stats['users_by_role'] = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent registrations
            $recentSql = "SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 10";
            $recentStmt = $this->database->execute($recentSql);
            $stats['recent_registrations'] = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting user statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update last login
     */
    public function updateLastLogin(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET last_login_at = NOW(), last_seen_at = NOW() WHERE id = ?";
            $this->database->execute($sql, [$userId]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating last login for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last seen
     */
    public function updateLastSeen(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET last_seen_at = NOW() WHERE id = ?";
            $this->database->execute($sql, [$userId]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating last seen for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user count
     */
    public function getUserCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users";
            $stmt = $this->database->execute($sql);
            return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active user count
     */
    public function getActiveUserCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE is_active = 1";
            $stmt = $this->database->execute($sql);
            return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Error getting active user count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get role distribution
     */
    public function getRoleDistribution(): array
    {
        try {
            $sql = "SELECT r.name, COUNT(ur.user_id) as count 
                    FROM roles r 
                    LEFT JOIN user_roles ur ON r.id = ur.role_id 
                    GROUP BY r.id, r.name
                    ORDER BY count DESC";
            $stmt = $this->database->execute($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting role distribution: " . $e->getMessage());
            return [];
        }
    }


} 