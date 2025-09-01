<?php

namespace IslamWiki\Core\Database;

use Exception;
use PDO;

/**
 * Migration Manager - Database migration system for v0.0.4
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class MigrationManager
{
    private DatabaseManager $database;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(DatabaseManager $database, string $migrationsPath = 'database/migrations/')
    {
        $this->database = $database;
        $this->migrationsPath = rtrim($migrationsPath, '/') . '/';
        $this->ensureMigrationsTable();
    }

    /**
     * Ensure migrations table exists
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->migrationsTable}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) NOT NULL,
            `batch` int(11) NOT NULL,
            `executed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_migration` (`migration`),
            KEY `idx_batch` (`batch`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->database->execute($sql);
        } catch (Exception $e) {
            throw new Exception("Failed to create migrations table: " . $e->getMessage());
        }
    }

    /**
     * Get all migration files
     */
    public function getMigrationFiles(): array
    {
        $files = [];
        $path = $this->migrationsPath;
        
        if (!is_dir($path)) {
            return $files;
        }

        $migrationFiles = glob($path . '*.php');
        
        foreach ($migrationFiles as $file) {
            $filename = basename($file, '.php');
            if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)$/', $filename, $matches)) {
                $files[] = [
                    'file' => $file,
                    'timestamp' => $matches[1],
                    'name' => $matches[2],
                    'full_name' => $filename
                ];
            }
        }

        // Sort by timestamp
        usort($files, function($a, $b) {
            return strcmp($a['timestamp'], $b['timestamp']);
        });

        return $files;
    }

    /**
     * Get executed migrations
     */
    public function getExecutedMigrations(): array
    {
        try {
            $stmt = $this->database->execute("SELECT * FROM `{$this->migrationsTable}` ORDER BY batch ASC, executed_at ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get pending migrations
     */
    public function getPendingMigrations(): array
    {
        $executed = array_column($this->getExecutedMigrations(), 'migration');
        $files = $this->getMigrationFiles();
        
        return array_filter($files, function($file) use ($executed) {
            return !in_array($file['full_name'], $executed);
        });
    }

    /**
     * Get next batch number
     */
    private function getNextBatch(): int
    {
        try {
            $stmt = $this->database->execute("SELECT MAX(batch) as max_batch FROM `{$this->migrationsTable}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['max_batch'] ?? 0) + 1;
        } catch (Exception $e) {
            return 1;
        }
    }

    /**
     * Run pending migrations
     */
    public function migrate(): array
    {
        $pending = $this->getPendingMigrations();
        $results = [];
        $batch = $this->getNextBatch();

        if (empty($pending)) {
            return ['message' => 'No pending migrations to run'];
        }

        try {
            $this->database->beginTransaction();

            foreach ($pending as $migration) {
                $result = $this->runMigration($migration, $batch);
                $results[] = $result;
                
                // If any migration failed, throw an exception to trigger rollback
                if ($result['status'] === 'error') {
                    throw new Exception("Migration '{$migration['full_name']}' failed: " . $result['message']);
                }
            }

            $this->database->commit();
            
            return [
                'message' => 'Migrations completed successfully',
                'batch' => $batch,
                'migrations' => $results
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            throw new Exception("Migration failed: " . $e->getMessage());
        }
    }

    /**
     * Run a single migration
     */
    private function runMigration(array $migration, int $batch): array
    {
        $filename = $migration['full_name'];
        $filepath = $migration['file'];
        
        try {
            // Include migration file
            require_once $filepath;
            
            // Get migration class name
            $className = $this->getMigrationClassName($filename);
            
            if (!class_exists($className)) {
                throw new Exception("Migration class '{$className}' not found in {$filename}");
            }

            // Instantiate and run migration
            $migrationInstance = new $className($this->database);
            
            if (!method_exists($migrationInstance, 'up')) {
                throw new Exception("Migration class '{$className}' must have an 'up' method");
            }

            $startTime = microtime(true);
            $migrationInstance->up();
            $endTime = microtime(true);

            // Record migration as executed
            $this->recordMigration($filename, $batch);

            return [
                'migration' => $filename,
                'status' => 'success',
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'message' => 'Migration executed successfully'
            ];

        } catch (Exception $e) {
            return [
                'migration' => $filename,
                'status' => 'error',
                'execution_time' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get migration class name from filename
     */
    private function getMigrationClassName(string $filename): string
    {
        // Convert filename to class name (e.g., 2025_01_27_123456_create_users_table -> CreateUsersTable)
        $parts = explode('_', $filename);
        
        // Remove timestamp parts (first 4 parts: YYYY_MM_DD_sequence)
        for ($i = 0; $i < 4; $i++) {
            array_shift($parts);
        }
        
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        
        return $className;
    }

    /**
     * Record migration as executed
     */
    private function recordMigration(string $migration, int $batch): void
    {
        $sql = "INSERT INTO `{$this->migrationsTable}` (migration, batch) VALUES (?, ?)";
        $this->database->execute($sql, [$migration, $batch]);
    }

    /**
     * Rollback last batch of migrations
     */
    public function rollback(): array
    {
        try {
            $lastBatch = $this->getLastBatch();
            if (!$lastBatch) {
                return ['message' => 'No migrations to rollback'];
            }

            $migrations = $this->getMigrationsByBatch($lastBatch);
            $results = [];

            $this->database->beginTransaction();

            foreach (array_reverse($migrations) as $migration) {
                $result = $this->rollbackMigration($migration);
                $results[] = $result;
            }

            $this->database->commit();

            return [
                'message' => 'Rollback completed successfully',
                'batch' => $lastBatch,
                'migrations' => $results
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            throw new Exception("Rollback failed: " . $e->getMessage());
        }
    }

    /**
     * Get last batch number
     */
    private function getLastBatch(): ?int
    {
        try {
            $stmt = $this->database->execute("SELECT MAX(batch) as max_batch FROM `{$this->migrationsTable}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['max_batch'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get migrations by batch number
     */
    private function getMigrationsByBatch(int $batch): array
    {
        try {
            $stmt = $this->database->execute("SELECT * FROM `{$this->migrationsTable}` WHERE batch = ? ORDER BY executed_at ASC", [$batch]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Rollback a single migration
     */
    private function rollbackMigration(array $migration): array
    {
        $filename = $migration['migration'];
        $filepath = $this->migrationsPath . $filename . '.php';
        
        try {
            require_once $filepath;
            
            $className = $this->getMigrationClassName($filename);
            
            if (!class_exists($className)) {
                throw new Exception("Migration class '{$className}' not found");
            }

            $migrationInstance = new $className($this->database);
            
            if (!method_exists($migrationInstance, 'down')) {
                throw new Exception("Migration class '{$className}' must have a 'down' method");
            }

            $startTime = microtime(true);
            $migrationInstance->down();
            $endTime = microtime(true);

            // Remove migration record
            $this->removeMigration($filename);

            return [
                'migration' => $filename,
                'status' => 'success',
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'message' => 'Migration rolled back successfully'
            ];

        } catch (Exception $e) {
            return [
                'migration' => $filename,
                'status' => 'error',
                'execution_time' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove migration record
     */
    private function removeMigration(string $migration): void
    {
        $sql = "DELETE FROM `{$this->migrationsTable}` WHERE migration = ?";
        $this->database->execute($sql, [$migration]);
    }

    /**
     * Get migration status
     */
    public function getStatus(): array
    {
        $files = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();
        $executedNames = array_column($executed, 'migration');

        $status = [];
        foreach ($files as $file) {
            $status[] = [
                'migration' => $file['full_name'],
                'status' => in_array($file['full_name'], $executedNames) ? 'executed' : 'pending',
                'executed_at' => $this->getExecutedAt($file['full_name'], $executed)
            ];
        }

        return [
            'total_migrations' => count($files),
            'executed_migrations' => count($executed),
            'pending_migrations' => count($files) - count($executed),
            'migrations' => $status
        ];
    }

    /**
     * Get execution time for a migration
     */
    private function getExecutedAt(string $migration, array $executed): ?string
    {
        foreach ($executed as $exec) {
            if ($exec['migration'] === $migration) {
                return $exec['executed_at'];
            }
        }
        return null;
    }

    /**
     * Reset all migrations (dangerous - use with caution)
     */
    public function reset(): array
    {
        try {
            $executed = $this->getExecutedMigrations();
            $results = [];

            $this->database->beginTransaction();

            foreach (array_reverse($executed) as $migration) {
                $result = $this->rollbackMigration($migration);
                $results[] = $result;
            }

            $this->database->commit();

            return [
                'message' => 'All migrations reset successfully',
                'migrations' => $results
            ];

        } catch (Exception $e) {
            $this->database->rollback();
            throw new Exception("Reset failed: " . $e->getMessage());
        }
    }
} 