<?php

namespace IslamWiki\Core\Database;

use PDO;
use PDOException;
use Exception;

/**
 * Database Manager - Enhanced database management for v0.0.4
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class DatabaseManager
{
    private ?PDO $connection = null;
    private array $config;
    private bool $isConnected = false;
    private array $queryLog = [];
    private int $queryCount = 0;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4";
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                ]
            );
            
            $this->isConnected = true;
            
            // Set timezone if specified
            if (isset($this->config['timezone'])) {
                try {
                    $this->connection->exec("SET time_zone = '{$this->config['timezone']}'");
                } catch (Exception $e) {
                    // Ignore timezone errors - not critical for functionality
                }
            }
            
        } catch (PDOException $e) {
            $this->isConnected = false;
            throw new DatabaseException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Check if database is connected
     */
    public function isConnected(): bool
    {
        return $this->isConnected && $this->connection instanceof PDO;
    }

    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Prepare a statement with query logging
     */
    public function prepare(string $sql): \PDOStatement
    {
        $this->logQuery($sql);
        return $this->getConnection()->prepare($sql);
    }

    /**
     * Execute a query with logging
     */
    public function query(string $sql): \PDOStatement
    {
        $this->logQuery($sql);
        return $this->getConnection()->query($sql);
    }

    /**
     * Execute a prepared statement
     */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Test database connection
     */
    public function testConnection(): array
    {
        try {
            $startTime = microtime(true);
            $this->getConnection()->query('SELECT 1');
            $endTime = microtime(true);
            
            return [
                'status' => 'connected',
                'response_time' => round(($endTime - $startTime) * 1000, 2), // milliseconds
                'server_version' => $this->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_version' => $this->getConnection()->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => $this->getConnection()->getAttribute(PDO::ATTR_CONNECTION_STATUS)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'response_time' => 0
            ];
        }
    }

    /**
     * Get database statistics
     */
    public function getStats(): array
    {
        return [
            'is_connected' => $this->isConnected(),
            'query_count' => $this->queryCount,
            'query_log' => $this->queryLog,
            'config' => [
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'database' => $this->config['database'],
                'charset' => $this->config['charset'] ?? 'utf8mb4'
            ]
        ];
    }

    /**
     * Log query for debugging
     */
    private function logQuery(string $sql): void
    {
        $this->queryCount++;
        $this->queryLog[] = [
            'sql' => $sql,
            'timestamp' => microtime(true),
            'query_number' => $this->queryCount
        ];
        
        // Keep only last 100 queries
        if (count($this->queryLog) > 100) {
            $this->queryLog = array_slice($this->queryLog, -100);
        }
    }

    /**
     * Clear query log
     */
    public function clearQueryLog(): void
    {
        $this->queryLog = [];
        $this->queryCount = 0;
    }

    /**
     * Get query log
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Get query count
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Close database connection
     */
    public function close(): void
    {
        $this->connection = null;
        $this->isConnected = false;
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        $this->close();
    }
} 