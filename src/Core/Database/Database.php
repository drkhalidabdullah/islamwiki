<?php

namespace IslamWiki\Core\Database;

use PDO;
use PDOException;

/**
 * Database abstraction layer
 * 
 * @author Khalid Abdullah
 * @version 0.0.1
 * @date 2025-08-30
 * @license AGPL-3.0
 */
class Database
{
    private ?PDO $connection = null;
    private array $config;

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
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            throw new DatabaseException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Prepare a statement
     */
    public function prepare(string $sql): \PDOStatement
    {
        return $this->connection->prepare($sql);
    }

    /**
     * Execute a query
     */
    public function query(string $sql): \PDOStatement
    {
        return $this->connection->query($sql);
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Get connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        $this->connection = null;
    }
}

/**
 * Database exception class
 */
class DatabaseException extends \Exception
{
} 