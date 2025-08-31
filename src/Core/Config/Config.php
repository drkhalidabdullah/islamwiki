<?php

namespace IslamWiki\Core\Config;

/**
 * Configuration Management
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load configuration from files
     */
    public static function load(string $configPath = 'config'): void
    {
        if (self::$loaded) {
            return;
        }

        $configFiles = [
            'app' => $configPath . '/app.php',
            'database' => $configPath . '/database.php',
            'cache' => $configPath . '/cache.php',
            'mail' => $configPath . '/mail.php',
            'security' => $configPath . '/security.php'
        ];

        foreach ($configFiles as $key => $file) {
            if (file_exists($file)) {
                $config = require $file;
                if (is_array($config)) {
                    self::$config[$key] = $config;
                }
            }
        }

        // Load environment variables
        self::loadEnvironmentVariables();
        
        self::$loaded = true;
    }

    /**
     * Load environment variables
     */
    private static function loadEnvironmentVariables(): void
    {
        $envFile = '.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    /**
     * Get a configuration value
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        $keys = explode('.', $key);
        $config = self::$config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Set a configuration value
     */
    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }

    /**
     * Check if configuration key exists
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$config;
    }

    /**
     * Get environment variable
     */
    public static function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if application is in debug mode
     */
    public static function isDebug(): bool
    {
        return self::env('APP_DEBUG', 'false') === 'true';
    }

    /**
     * Check if application is in production mode
     */
    public static function isProduction(): bool
    {
        return self::env('APP_ENV', 'production') === 'production';
    }

    /**
     * Get application name
     */
    public static function appName(): string
    {
        return self::get('app.name', 'IslamWiki');
    }

    /**
     * Get application version
     */
    public static function appVersion(): string
    {
        return self::get('app.version', '0.0.1');
    }

    /**
     * Get database configuration
     */
    public static function database(): array
    {
        return [
            'host' => self::env('DB_HOST', 'localhost'),
            'port' => self::env('DB_PORT', '3306'),
            'database' => self::env('DB_NAME', 'islamwiki'),
            'username' => self::env('DB_USER', 'root'),
            'password' => self::env('DB_PASSWORD', ''),
            'charset' => self::env('DB_CHARSET', 'utf8mb4'),
            'collation' => self::env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
    }

    /**
     * Get cache configuration
     */
    public static function cache(): array
    {
        return [
            'driver' => self::env('CACHE_DRIVER', 'file'),
            'path' => self::env('CACHE_PATH', 'storage/cache'),
            'ttl' => (int) self::env('CACHE_TTL', '3600'),
            'prefix' => self::env('CACHE_PREFIX', 'islamwiki_')
        ];
    }

    /**
     * Get security configuration
     */
    public static function security(): array
    {
        return [
            'jwt_secret' => self::env('JWT_SECRET', 'your-secret-key'),
            'jwt_expiry' => (int) self::env('JWT_EXPIRY', '3600'),
            'bcrypt_rounds' => (int) self::env('BCRYPT_ROUNDS', '12'),
            'session_lifetime' => (int) self::env('SESSION_LIFETIME', '120'),
            'csrf_token_lifetime' => (int) self::env('CSRF_TOKEN_LIFETIME', '3600')
        ];
    }

    /**
     * Reset configuration (for testing)
     */
    public static function reset(): void
    {
        self::$config = [];
        self::$loaded = false;
    }
} 