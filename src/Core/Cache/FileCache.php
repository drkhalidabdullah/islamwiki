<?php

namespace IslamWiki\Core\Cache;

/**
 * File-based cache implementation
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class FileCache implements CacheInterface
{
    private string $cacheDir;
    private string $prefix;

    public function __construct(string $cacheDir = null, string $prefix = 'islamwiki_')
    {
        // Use absolute path for cache directory
        if ($cacheDir === null) {
            $cacheDir = __DIR__ . '/../../../storage/cache';
        }
        
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->prefix = $prefix;
        
        if (!is_dir($this->cacheDir)) {
            // Try to create directory with proper permissions
            if (!@mkdir($this->cacheDir, 0755, true)) {
                // Silently log to error log instead of outputting to response
                error_log("Warning: Could not create cache directory: " . $this->cacheDir);
            }
        }
    }

    /**
     * Get a value from cache
     */
    public function get(string $key): mixed
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }

        $data = file_get_contents($filename);
        $cached = json_decode($data, true);

        if (!$cached || !isset($cached['expires']) || !isset($cached['value'])) {
            return null;
        }

        if (time() > $cached['expires']) {
            $this->delete($key);
            return null;
        }

        return $cached['value'];
    }

    /**
     * Set a value in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        return file_put_contents($filename, json_encode($data)) !== false;
    }

    /**
     * Delete a value from cache
     */
    public function delete(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }

        $data = file_get_contents($filename);
        $cached = json_decode($data, true);

        if (!$cached || !isset($cached['expires'])) {
            return false;
        }

        return time() <= $cached['expires'];
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/' . $this->prefix . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get multiple values from cache
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Set multiple values in cache
     */
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Delete multiple values from cache
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get cache filename for a key
     */
    private function getCacheFilename(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . '/' . $this->prefix . $safeKey . '.cache';
    }

    /**
     * Clean expired cache files
     */
    public function cleanExpired(): int
    {
        $files = glob($this->cacheDir . '/' . $this->prefix . '*');
        $cleaned = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $data = file_get_contents($file);
                $cached = json_decode($data, true);

                if (!$cached || !isset($cached['expires']) || time() > $cached['expires']) {
                    unlink($file);
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }
} 