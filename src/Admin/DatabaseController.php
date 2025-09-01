<?php

namespace IslamWiki\Admin;

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Database\MigrationManager;
use Exception;

/**
 * Database Controller - Admin API endpoints for database management
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class DatabaseController
{
    private AdminDatabaseManager $databaseManager;

    public function __construct(DatabaseManager $database, MigrationManager $migrationManager)
    {
        $this->databaseManager = new AdminDatabaseManager($database, $migrationManager);
    }

    /**
     * Get database overview
     */
    public function overview(): array
    {
        return $this->databaseManager->getOverview();
    }

    /**
     * Get database health
     */
    public function health(): array
    {
        return $this->databaseManager->getDatabaseHealth();
    }

    /**
     * Run migrations
     */
    public function runMigrations(): array
    {
        return $this->databaseManager->runMigrations();
    }

    /**
     * Rollback migrations
     */
    public function rollbackMigrations(): array
    {
        return $this->databaseManager->rollbackMigrations();
    }

    /**
     * Get migration status
     */
    public function migrationStatus(): array
    {
        return $this->databaseManager->getMigrationStatus();
    }

    /**
     * Execute custom query
     */
    public function executeQuery(array $data): array
    {
        $sql = $data['sql'] ?? '';
        
        if (empty($sql)) {
            return [
                'success' => false,
                'error' => 'SQL query is required'
            ];
        }

        return $this->databaseManager->executeQuery($sql);
    }

    /**
     * Get query log
     */
    public function getQueryLog(): array
    {
        return $this->databaseManager->getQueryLog();
    }

    /**
     * Clear query log
     */
    public function clearQueryLog(): array
    {
        return $this->databaseManager->clearQueryLog();
    }
} 