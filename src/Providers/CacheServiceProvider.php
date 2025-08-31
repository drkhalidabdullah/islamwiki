<?php

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\Container;

/**
 * Cache Service Provider
 * 
 * This class registers caching-related services in the container.
 */
class CacheServiceProvider
{
    /**
     * @var Container
     */
    protected Container $container;
    
    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Register caching services
     */
    public function register(): void
    {
        // Register file cache service
        $this->container->singleton('cache', function () {
            $driver = $_ENV['CACHE_DRIVER'] ?? 'file';
            $prefix = $_ENV['CACHE_PREFIX'] ?? 'islamwiki_';
            $ttl = (int) ($_ENV['CACHE_TTL'] ?? 3600);
            
            if ($driver === 'file') {
                return new \stdClass(); // Placeholder for FileCache service
            }
            
            return new \stdClass(); // Placeholder for default cache service
        });
        
        // Register cache as 'cache'
        $this->container->singleton('cache.store', function () {
            return $this->container->make('cache');
        });
    }
} 