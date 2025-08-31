<?php

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\Container;

/**
 * Authentication Service Provider
 * 
 * This class registers authentication-related services in the container.
 */
class AuthServiceProvider
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
     * Register authentication services
     */
    public function register(): void
    {
        // Register JWT service
        $this->container->singleton('jwt', function () {
            $secret = $_ENV['JWT_SECRET'] ?? 'default_jwt_secret';
            return new \stdClass(); // Placeholder for JWT service
        });
        
        // Register authentication service
        $this->container->singleton('auth', function () {
            return new \stdClass(); // Placeholder for Auth service
        });
        
        // Register session service
        $this->container->singleton('session', function () {
            return new \stdClass(); // Placeholder for Session service
        });
    }
} 