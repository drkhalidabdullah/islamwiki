<?php

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\Container;
use PDO;

/**
 * Database Service Provider
 * 
 * This class registers database-related services in the container.
 */
class DatabaseServiceProvider
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
     * Register database services
     */
    public function register(): void
    {
        // Register PDO connection
        $this->container->singleton(PDO::class, function () {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            return new PDO($dsn, $username, $password, $options);
        });
        
        // Register database connection as 'db'
        $this->container->singleton('db', function () {
            return $this->container->make(PDO::class);
        });
    }
} 