<?php

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\Container;

/**
 * Security Service Provider
 * 
 * This class registers security-related services in the container.
 */
class SecurityServiceProvider
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
     * Register security services
     */
    public function register(): void
    {
        // Register CSRF protection service
        $this->container->singleton('csrf', function () {
            $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
            return new \stdClass(); // Placeholder for CSRF service
        });
        
        // Register encryption service
        $this->container->singleton('encryption', function () {
            $key = $_ENV['APP_KEY'] ?? 'default_encryption_key';
            return new \stdClass(); // Placeholder for Encryption service
        });
        
        // Register hash service
        $this->container->singleton('hash', function () {
            return new \stdClass(); // Placeholder for Hash service
        });
        
        // Register rate limiting service
        $this->container->singleton('rate.limiter', function () {
            return new \stdClass(); // Placeholder for RateLimiter service
        });
    }
} 