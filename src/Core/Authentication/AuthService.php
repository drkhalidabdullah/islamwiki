<?php

namespace IslamWiki\Core\Authentication;

use IslamWiki\Services\User\UserService;
use IslamWiki\Core\Cache\CacheInterface;

/**
 * Authentication Service
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class AuthService
{
    private UserService $userService;
    private CacheInterface $cache;
    private string $jwtSecret;
    private int $jwtExpiry;

    public function __construct(UserService $userService, CacheInterface $cache, string $jwtSecret, int $jwtExpiry = 3600)
    {
        $this->userService = $userService;
        $this->cache = $cache;
        $this->jwtSecret = $jwtSecret;
        $this->jwtExpiry = $jwtExpiry;
    }

    /**
     * Authenticate user with username and password
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->userService->verifyCredentials($username, $password);
        
        if (!$user) {
            return null;
        }

        // Generate JWT token
        $token = $this->generateJWT($user);
        
        // Store session in cache
        $this->cache->set("session:{$token}", $user, $this->jwtExpiry);
        
        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => time() + $this->jwtExpiry
        ];
    }

    /**
     * Validate JWT token
     */
    public function validateToken(string $token): ?array
    {
        // Check cache first
        $cached = $this->cache->get("session:{$token}");
        if ($cached) {
            return $cached;
        }

        // Validate JWT
        $payload = $this->validateJWT($token);
        if (!$payload) {
            return null;
        }

        // Get user data
        $user = $this->userService->getUserById($payload['user_id']);
        if (!$user) {
            return null;
        }

        // Cache session
        $this->cache->set("session:{$token}", $user, $this->jwtExpiry);
        
        return $user;
    }

    /**
     * Logout user
     */
    public function logout(string $token): bool
    {
        return $this->cache->delete("session:{$token}");
    }

    /**
     * Generate JWT token
     */
    private function generateJWT(array $user): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role_name'],
            'iat' => time(),
            'exp' => time() + $this->jwtExpiry
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->jwtSecret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Validate JWT token
     */
    private function validateJWT(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->jwtSecret, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        // Decode payload
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
        $payload = json_decode($payload, true);

        if (!$payload) {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Refresh token
     */
    public function refreshToken(string $token): ?array
    {
        $user = $this->validateToken($token);
        if (!$user) {
            return null;
        }

        // Generate new token
        $newToken = $this->generateJWT($user);
        
        // Remove old session and create new one
        $this->cache->delete("session:{$token}");
        $this->cache->set("session:{$newToken}", $user, $this->jwtExpiry);
        
        return [
            'user' => $user,
            'token' => $newToken,
            'expires_at' => time() + $this->jwtExpiry
        ];
    }

    /**
     * Get current user from token
     */
    public function getCurrentUser(string $token): ?array
    {
        return $this->validateToken($token);
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $token, string $permission): bool
    {
        $user = $this->getCurrentUser($token);
        if (!$user) {
            return false;
        }

        // Simple role-based permission check
        $role = $user['role_name'] ?? 'user';
        
        $permissions = [
            'admin' => ['read', 'write', 'delete', 'admin'],
            'moderator' => ['read', 'write', 'moderate'],
            'author' => ['read', 'write'],
            'user' => ['read']
        ];

        return in_array($permission, $permissions[$role] ?? ['read']);
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(string $token): array
    {
        $user = $this->getCurrentUser($token);
        if (!$user) {
            return [];
        }

        $role = $user['role_name'] ?? 'user';
        
        $permissions = [
            'admin' => ['read', 'write', 'delete', 'admin', 'moderate'],
            'moderator' => ['read', 'write', 'moderate'],
            'author' => ['read', 'write'],
            'user' => ['read']
        ];

        return $permissions[$role] ?? ['read'];
    }
} 