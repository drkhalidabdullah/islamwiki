<?php

namespace IslamWiki\Services\User;

use IslamWiki\Core\Database\Database;
use IslamWiki\Core\Cache\CacheInterface;

/**
 * User Service - User management functionality
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class UserService
{
    private Database $database;
    private CacheInterface $cache;

    public function __construct(Database $database, CacheInterface $cache)
    {
        $this->database = $database;
        $this->cache = $cache;
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?array
    {
        $cacheKey = "user:{$id}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->database->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.status = 'active'
        ");
        
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($user) {
            unset($user['password']); // Don't cache passwords
            $this->cache->set($cacheKey, $user, 1800); // Cache for 30 minutes
        }
        
        return $user;
    }

    /**
     * Get user by username
     */
    public function getUserByUsername(string $username): ?array
    {
        $stmt = $this->database->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.username = ? AND u.status = 'active'
        ");
        
        $stmt->execute([$username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->database->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? AND u.status = 'active'
        ");
        
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user;
    }

    /**
     * Create new user
     */
    public function createUser(array $userData): ?int
    {
        $stmt = $this->database->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $userData['username'],
            $userData['email'],
            password_hash($userData['password'], PASSWORD_DEFAULT),
            $userData['first_name'] ?? '',
            $userData['last_name'] ?? '',
            $userData['role_id'] ?? 2, // Default to regular user
            'active'
        ]);

        if ($success) {
            $userId = $this->database->lastInsertId();
            $this->cache->delete("users:list");
            return $userId;
        }

        return null;
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $userData): bool
    {
        $fields = [];
        $values = [];

        foreach ($userData as $field => $value) {
            if (in_array($field, ['username', 'email', 'first_name', 'last_name', 'bio', 'avatar'])) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $success = $stmt->execute($values);

        if ($success) {
            $this->cache->delete("user:{$id}");
            $this->cache->delete("users:list");
        }

        return $success;
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $stmt = $this->database->prepare("
            UPDATE users 
            SET password = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $success = $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $id
        ]);

        if ($success) {
            $this->cache->delete("user:{$id}");
        }

        return $success;
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool
    {
        $stmt = $this->database->prepare("
            UPDATE users 
            SET status = 'deleted', updated_at = NOW() 
            WHERE id = ?
        ");
        
        $success = $stmt->execute([$id]);

        if ($success) {
            $this->cache->delete("user:{$id}");
            $this->cache->delete("users:list");
        }

        return $success;
    }

    /**
     * Get users list
     */
    public function getUsers(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $cacheKey = "users:list:" . md5(serialize([$limit, $offset, $filters]));
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $where = ["u.status != 'deleted'"];
        $values = [];

        if (!empty($filters['role_id'])) {
            $where[] = "u.role_id = ?";
            $values[] = $filters['role_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "u.status = ?";
            $values[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $values = array_merge($values, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $where);
        $values = array_merge($values, [$limit, $offset]);

        $sql = "
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE {$whereClause}
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Remove passwords from results
        foreach ($users as &$user) {
            unset($user['password']);
        }

        $this->cache->set($cacheKey, $users, 900); // Cache for 15 minutes
        return $users;
    }

    /**
     * Get user count
     */
    public function getUserCount(array $filters = []): int
    {
        $where = ["status != 'deleted'"];
        $values = [];

        if (!empty($filters['role_id'])) {
            $where[] = "role_id = ?";
            $values[] = $filters['role_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $values[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as count FROM users WHERE {$whereClause}";

        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'];
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $username, string $password): ?array
    {
        $user = $this->getUserByUsername($username);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        unset($user['password']); // Don't return password
        return $user;
    }

    /**
     * Get user roles
     */
    public function getRoles(): array
    {
        $stmt = $this->database->prepare("SELECT * FROM roles ORDER BY level ASC");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
} 