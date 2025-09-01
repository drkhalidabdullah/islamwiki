<?php

namespace IslamWiki\Core\Cache;

/**
 * Cache Interface - Defines contract for cache implementations
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
interface CacheInterface
{
    /**
     * Get a value from cache
     */
    public function get(string $key): mixed;

    /**
     * Set a value in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Delete a value from cache
     */
    public function delete(string $key): bool;

    /**
     * Check if a key exists in cache
     */
    public function has(string $key): bool;

    /**
     * Clear all cache
     */
    public function clear(): bool;

    /**
     * Get multiple values from cache
     */
    public function getMultiple(array $keys): array;

    /**
     * Set multiple values in cache
     */
    public function setMultiple(array $values, int $ttl = 3600): bool;

    /**
     * Delete multiple values from cache
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Clean expired cache files
     */
    public function cleanExpired(): int;
} 