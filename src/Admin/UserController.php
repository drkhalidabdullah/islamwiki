<?php

namespace IslamWiki\Admin;

use IslamWiki\Services\User\UserService;
use Exception;

/**
 * User Controller - Admin API endpoints for user management
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all users with pagination and filters
     */
    public function getUsers(array $data = []): array
    {
        try {
            $filters = $data['filters'] ?? [];
            $page = (int) ($data['page'] ?? 1);
            $perPage = (int) ($data['per_page'] ?? 20);
            
            $result = $this->userService->getUsers($filters, $page, $perPage);
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user by ID
     */
    public function getUser(array $data): array
    {
        try {
            $userId = (int) ($data['id'] ?? 0);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => 'User ID is required'
                ];
            }
            
            $user = $this->userService->getUser($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $user
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create new user
     */
    public function createUser(array $data): array
    {
        try {
            $result = $this->userService->createUser($data);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user
     */
    public function updateUser(array $data): array
    {
        try {
            $userId = (int) ($data['id'] ?? 0);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => 'User ID is required'
                ];
            }
            
            // Remove ID from update data
            unset($data['id']);
            
            $result = $this->userService->updateUser($userId, $data);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(array $data): array
    {
        try {
            $userId = (int) ($data['id'] ?? 0);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => 'User ID is required'
                ];
            }
            
            $result = $this->userService->deleteUser($userId);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(array $data): array
    {
        try {
            $userId = (int) ($data['user_id'] ?? 0);
            $roleName = $data['role_name'] ?? '';
            
            if (!$userId || !$roleName) {
                return [
                    'success' => false,
                    'error' => 'User ID and role name are required'
                ];
            }
            
            $result = $this->userService->assignRole($userId, $roleName);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(array $data): array
    {
        try {
            $userId = (int) ($data['user_id'] ?? 0);
            $roleName = $data['role_name'] ?? '';
            
            if (!$userId || !$roleName) {
                return [
                    'success' => false,
                    'error' => 'User ID and role name are required'
                ];
            }
            
            $result = $this->userService->removeRole($userId, $roleName);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user roles
     */
    public function getUserRoles(array $data): array
    {
        try {
            $userId = (int) ($data['user_id'] ?? 0);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => 'User ID is required'
                ];
            }
            
            $roles = $this->userService->getUserRoles($userId);
            
            return [
                'success' => true,
                'data' => $roles
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all roles
     */
    public function getAllRoles(): array
    {
        try {
            $roles = $this->userService->getAllRoles();
            
            return [
                'success' => true,
                'data' => $roles
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if user has role
     */
    public function userHasRole(array $data): array
    {
        try {
            $userId = (int) ($data['user_id'] ?? 0);
            $roleName = $data['role_name'] ?? '';
            
            if (!$userId || !$roleName) {
                return [
                    'success' => false,
                    'error' => 'User ID and role name are required'
                ];
            }
            
            $hasRole = $this->userService->userHasRole($userId, $roleName);
            
            return [
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'role_name' => $roleName,
                    'has_role' => $hasRole
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        try {
            $stats = $this->userService->getUserStatistics();
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update user profile
     */
    public function updateUserProfile(array $data): array
    {
        try {
            $userId = (int) ($data['user_id'] ?? 0);
            $profileData = $data['profile'] ?? [];
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => 'User ID is required'
                ];
            }
            
            if (empty($profileData)) {
                return [
                    'success' => false,
                    'error' => 'Profile data is required'
                ];
            }
            
            // Update user with profile data
            $result = $this->userService->updateUser($userId, ['profile' => $profileData]);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Search users
     */
    public function searchUsers(array $data): array
    {
        try {
            $query = $data['query'] ?? '';
            $page = (int) ($data['page'] ?? 1);
            $perPage = (int) ($data['per_page'] ?? 20);
            
            if (empty($query)) {
                return [
                    'success' => false,
                    'error' => 'Search query is required'
                ];
            }
            
            $filters = ['search' => $query];
            $result = $this->userService->getUsers($filters, $page, $perPage);
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Bulk update users
     */
    public function bulkUpdateUsers(array $data): array
    {
        try {
            $userIds = $data['user_ids'] ?? [];
            $updateData = $data['update_data'] ?? [];
            
            if (empty($userIds) || empty($updateData)) {
                return [
                    'success' => false,
                    'error' => 'User IDs and update data are required'
                ];
            }
            
            $results = [];
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($userIds as $userId) {
                $result = $this->userService->updateUser($userId, $updateData);
                $results[] = [
                    'user_id' => $userId,
                    'result' => $result
                ];
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            return [
                'success' => true,
                'data' => [
                    'results' => $results,
                    'summary' => [
                        'total' => count($userIds),
                        'successful' => $successCount,
                        'failed' => $errorCount
                    ]
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user activity
     */
    public function getUserActivity(array $data): array
    {
        try {
            $userId = (int) ($data['user_id'] ?? 0);
            $limit = (int) ($data['limit'] ?? 50);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => 'User ID is required'
                ];
            }
            
            // Get user's recent activity from various tables
            $activity = [];
            
            // Recent logins
            $user = $this->userService->getUser($userId);
            if ($user && $user['last_login_at']) {
                $activity[] = [
                    'type' => 'login',
                    'timestamp' => $user['last_login_at'],
                    'description' => 'User logged in'
                ];
            }
            
            // Recent profile updates
            if ($user && $user['updated_at'] && $user['updated_at'] !== $user['created_at']) {
                $activity[] = [
                    'type' => 'profile_update',
                    'timestamp' => $user['updated_at'],
                    'description' => 'Profile updated'
                ];
            }
            
            // Sort by timestamp (most recent first)
            usort($activity, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            // Limit results
            $activity = array_slice($activity, 0, $limit);
            
            return [
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'activity' => $activity,
                    'total' => count($activity)
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 