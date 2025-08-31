<?php

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Database;

/**
 * User Model
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class User
{
    private Database $database;
    private ?int $id = null;
    private string $username;
    private string $email;
    private string $password;
    private string $first_name;
    private string $last_name;
    private int $role_id;
    private string $status;
    private ?string $bio = null;
    private ?string $avatar = null;
    private string $created_at;
    private ?string $updated_at = null;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Find user by ID
     */
    public static function find(Database $database, int $id): ?self
    {
        $stmt = $database->prepare("
            SELECT * FROM users WHERE id = ? AND status != 'deleted'
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        return self::createFromArray($database, $data);
    }

    /**
     * Find user by username
     */
    public static function findByUsername(Database $database, string $username): ?self
    {
        $stmt = $database->prepare("
            SELECT * FROM users WHERE username = ? AND status != 'deleted'
        ");
        $stmt->execute([$username]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        return self::createFromArray($database, $data);
    }

    /**
     * Find user by email
     */
    public static function findByEmail(Database $database, string $email): ?self
    {
        $stmt = $database->prepare("
            SELECT * FROM users WHERE email = ? AND status != 'deleted'
        ");
        $stmt->execute([$email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        return self::createFromArray($database, $data);
    }

    /**
     * Create user from array data
     */
    private static function createFromArray(Database $database, array $data): self
    {
        $user = new self($database);
        $user->id = $data['id'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->role_id = $data['role_id'];
        $user->status = $data['status'];
        $user->bio = $data['bio'];
        $user->avatar = $data['avatar'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];
        
        return $user;
    }

    /**
     * Save user to database
     */
    public function save(): bool
    {
        if ($this->id) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    /**
     * Create new user
     */
    private function create(): bool
    {
        $stmt = $this->database->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role_id, status, bio, avatar, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $this->username,
            $this->email,
            $this->password,
            $this->first_name,
            $this->last_name,
            $this->role_id,
            $this->status,
            $this->bio,
            $this->avatar
        ]);

        if ($success) {
            $this->id = (int) $this->database->lastInsertId();
        }

        return $success;
    }

    /**
     * Update existing user
     */
    private function update(): bool
    {
        $stmt = $this->database->prepare("
            UPDATE users 
            SET username = ?, email = ?, first_name = ?, last_name = ?, 
                role_id = ?, status = ?, bio = ?, avatar = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $this->username,
            $this->email,
            $this->first_name,
            $this->last_name,
            $this->role_id,
            $this->status,
            $this->bio,
            $this->avatar,
            $this->id
        ]);
    }

    /**
     * Delete user (soft delete)
     */
    public function delete(): bool
    {
        $stmt = $this->database->prepare("
            UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?
        ");
        
        return $stmt->execute([$this->id]);
    }

    /**
     * Update password
     */
    public function updatePassword(string $newPassword): bool
    {
        $this->password = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->database->prepare("
            UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?
        ");
        
        return $stmt->execute([$this->password, $this->id]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role_id === 1; // Assuming role_id 1 is admin
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getFirstName(): string { return $this->first_name; }
    public function getLastName(): string { return $this->last_name; }
    public function getRoleId(): int { return $this->role_id; }
    public function getStatus(): string { return $this->status; }
    public function getBio(): ?string { return $this->bio; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function getCreatedAt(): string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters
    public function setUsername(string $username): self { $this->username = $username; return $this; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setFirstName(string $firstName): self { $this->first_name = $firstName; return $this; }
    public function setLastName(string $lastName): self { $this->last_name = $lastName; return $this; }
    public function setRoleId(int $roleId): self { $this->role_id = $roleId; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setBio(?string $bio): self { $this->bio = $bio; return $this; }
    public function setAvatar(?string $avatar): self { $this->avatar = $avatar; return $this; }
} 