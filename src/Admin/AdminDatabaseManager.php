<?php

namespace IslamWiki\Admin;

use IslamWiki\Core\Database\DatabaseManager as CoreDatabaseManager;
use IslamWiki\Core\Database\MigrationManager;
use Exception;

/**
 * Admin Database Manager Class - Database management for admin panel
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */
class AdminDatabaseManager
{
    private CoreDatabaseManager $database;
    private MigrationManager $migrationManager;

    public function __construct(CoreDatabaseManager $database, MigrationManager $migrationManager)
    {
        $this->database = $database;
        $this->migrationManager = $migrationManager;
    }

    /**
     * Get database overview
     */
    public function getOverview(): array
    {
        try {
            $overview = [
                'connection' => $this->getConnectionStatus(),
                'statistics' => $this->getDatabaseStatistics(),
                'migrations' => $this->getMigrationStatus(),
                'tables' => $this->getTableInformation(),
                'performance' => $this->getPerformanceMetrics()
            ];

            return [
                'success' => true,
                'data' => $overview
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get connection status
     */
    private function getConnectionStatus(): array
    {
        $connectionTest = $this->database->testConnection();
        
        return [
            'status' => $connectionTest['status'],
            'response_time' => $connectionTest['response_time'],
            'server_version' => $connectionTest['server_version'],
            'client_version' => $connectionTest['client_version'],
            'connection_status' => $connectionTest['connection_status'],
            'is_connected' => $this->database->isConnected()
        ];
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStatistics(): array
    {
        $stats = $this->database->getStats();
        
        return [
            'query_count' => $stats['query_count'],
            'config' => $stats['config'],
            'query_log' => array_slice($stats['query_log'], -10) // Last 10 queries
        ];
    }

    /**
     * Get migration status
     */
    public function getMigrationStatus(): array
    {
        return $this->migrationManager->getStatus();
    }

    /**
     * Get table information
     */
    private function getTableInformation(): array
    {
        $tables = [];
        
        // Get all tables
        $stmt = $this->database->execute("SHOW TABLES");
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tableName = $row[0];
            
            // Get table size and row count
            $sizeQuery = "SELECT 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
                table_rows AS 'row_count'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = ?";
            
            $sizeStmt = $this->database->execute($sizeQuery, [$tableName]);
            $sizeInfo = $sizeStmt->fetch(\PDO::FETCH_ASSOC);
            
            $tables[] = [
                'name' => $tableName,
                'size_mb' => (float)($sizeInfo['size_mb'] ?? 0),
                'row_count' => (int)($sizeInfo['row_count'] ?? 0)
            ];
        }
        
        return $tables;
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $metrics = [];
        
        // Get slow query log (if enabled)
        $slowQueries = $this->getSlowQueries();
        
        // Get connection count
        $connectionCount = $this->getConnectionCount();
        
        // Get query performance
        $queryPerformance = $this->getQueryPerformance();
        
        return [
            'slow_queries' => $slowQueries,
            'connection_count' => $connectionCount,
            'query_performance' => $queryPerformance
        ];
    }

    /**
     * Get slow queries
     */
    private function getSlowQueries(): array
    {
        try {
            $sql = "SELECT 
                start_time,
                query_time,
                lock_time,
                rows_sent,
                rows_examined,
                sql_text
                FROM mysql.slow_log 
                WHERE start_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY start_time DESC 
                LIMIT 10";
            
            $stmt = $this->database->execute($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return []; // Slow log might not be enabled
        }
    }

    /**
     * Get connection count
     */
    private function getConnectionCount(): array
    {
        try {
            $sql = "SHOW STATUS LIKE 'Threads_connected'";
            $stmt = $this->database->execute($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return [
                'current_connections' => $result['Value'] ?? 0
            ];
        } catch (Exception $e) {
            return ['current_connections' => 0];
        }
    }

    /**
     * Get query performance
     */
    private function getQueryPerformance(): array
    {
        try {
            $sql = "SHOW STATUS LIKE 'Questions'";
            $stmt = $this->database->execute($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return [
                'total_queries' => $result['Value'] ?? 0
            ];
        } catch (Exception $e) {
            return ['total_queries' => 0];
        }
    }

    /**
     * Run migrations
     */
    public function runMigrations(): array
    {
        try {
            $result = $this->migrationManager->migrate();
            
            return [
                'success' => true,
                'data' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Rollback migrations
     */
    public function rollbackMigrations(): array
    {
        try {
            $result = $this->migrationManager->rollback();
            
            return [
                'success' => true,
                'data' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute custom query
     */
    public function executeQuery(string $sql, array $params = []): array
    {
        try {
            // Validate SQL (basic security check)
            if (preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', trim($sql))) {
                $stmt = $this->database->execute($sql, $params);
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                return [
                    'success' => true,
                    'data' => $results,
                    'row_count' => count($results)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Only SELECT, SHOW, DESCRIBE, and EXPLAIN queries are allowed'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get database health
     */
    public function getDatabaseHealth(): array
    {
        try {
            $health = [
                'connection' => $this->database->isConnected(),
                'migrations' => $this->getMigrationHealth(),
                'tables' => $this->getTableHealth(),
                'performance' => $this->getPerformanceHealth()
            ];

            $overallHealth = 'healthy';
            if (!$health['connection']) {
                $overallHealth = 'critical';
            } elseif (!$health['migrations']['healthy']) {
                $overallHealth = 'warning';
            }

            return [
                'success' => true,
                'data' => [
                    'overall_health' => $overallHealth,
                    'checks' => $health
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get migration health
     */
    private function getMigrationHealth(): array
    {
        $status = $this->migrationManager->getStatus();
        
        return [
            'healthy' => $status['pending_migrations'] === 0,
            'total' => $status['total_migrations'],
            'executed' => $status['executed_migrations'],
            'pending' => $status['pending_migrations']
        ];
    }

    /**
     * Get table health
     */
    private function getTableHealth(): array
    {
        try {
            $requiredTables = [
                'users', 'roles', 'user_roles', 'user_profiles', 'content_categories',
                'articles', 'article_versions', 'comments', 'posts', 'likes',
                'follows', 'courses', 'lessons', 'sessions', 'activity_logs', 'scholars'
            ];
            
            $existingTables = [];
            $stmt = $this->database->execute("SHOW TABLES");
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $existingTables[] = $row[0];
            }
            
            $missingTables = array_diff($requiredTables, $existingTables);
            
            return [
                'healthy' => empty($missingTables),
                'total_required' => count($requiredTables),
                'existing' => count($existingTables),
                'missing' => count($missingTables),
                'missing_tables' => array_values($missingTables)
            ];
        } catch (Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get performance health
     */
    private function getPerformanceHealth(): array
    {
        try {
            $connectionTest = $this->database->testConnection();
            $responseTime = $connectionTest['response_time'];
            
            return [
                'healthy' => $responseTime < 100, // Less than 100ms
                'response_time' => $responseTime,
                'threshold' => 100,
                'status' => $responseTime < 50 ? 'excellent' : ($responseTime < 100 ? 'good' : 'poor')
            ];
        } catch (Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear query log
     */
    public function clearQueryLog(): array
    {
        try {
            $this->database->clearQueryLog();
            
            return [
                'success' => true,
                'message' => 'Query log cleared successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get query log
     */
    public function getQueryLog(): array
    {
        try {
            $stats = $this->database->getStats();
            
            return [
                'success' => true,
                'data' => [
                    'query_count' => $stats['query_count'],
                    'row_count' => count($stats['query_log']),
                    'query_log' => $stats['query_log']
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

} 