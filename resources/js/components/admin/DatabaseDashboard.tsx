import React, { useState, useEffect } from 'react';
import Card from '../ui/Card';
import Button from '../ui/Button';
import Modal from '../ui/Modal';
import Textarea from '../ui/Textarea';
import { buildApiUrl, API_ENDPOINTS } from '../../config/api';

interface DatabaseOverview {
  connection: {
    status: string;
    response_time: number;
    server_version: string;
    client_version: string;
    is_connected: boolean;
  };
  statistics: {
    query_count: number;
    config: {
      host: string;
      port: number;
      database: string;
    };
  };
  migrations: {
    total_migrations: number;
    executed_migrations: number;
    pending_migrations: number;
    migrations: Array<{
      migration: string;
      status: string;
      executed_at: string | null;
    }>;
  };
  tables: Array<{
    name: string;
    size_mb: number;
    row_count: number;
  }>;
  performance: {
    slow_queries: Array<any>;
    connection_count: {
      current_connections: number;
    };
    query_performance: {
      total_queries: number;
    };
  };
}

interface DatabaseHealth {
  overall_health: string;
  checks: {
    connection: boolean;
    migrations: {
      healthy: boolean;
      total: number;
      executed: number;
      pending: number;
    };
    tables: {
      healthy: boolean;
      total_required: number;
      existing: number;
      missing: number;
      missing_tables: string[];
    };
    performance: {
      healthy: boolean;
      response_time: number;
      threshold: number;
      status: string;
    };
  };
}

const DatabaseDashboard: React.FC = () => {
  const [overview, setOverview] = useState<DatabaseOverview | null>(null);
  const [health, setHealth] = useState<DatabaseHealth | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showQueryModal, setShowQueryModal] = useState(false);
  const [querySql, setQuerySql] = useState('');
  const [queryResults, setQueryResults] = useState<any[]>([]);
  const [queryLoading, setQueryLoading] = useState(false);

  useEffect(() => {
    loadDatabaseData();
  }, []);

  const loadDatabaseData = async () => {
    try {
      setLoading(true);
      
      // Load overview and health data
      const [overviewRes, healthRes] = await Promise.all([
        fetch(buildApiUrl(API_ENDPOINTS.DATABASE_OVERVIEW)),
        fetch(buildApiUrl(API_ENDPOINTS.DATABASE_HEALTH))
      ]);

      if (overviewRes.ok && healthRes.ok) {
        const overviewData = await overviewRes.json();
        const healthData = await healthRes.json();
        
        if (overviewData.success) {
          setOverview(overviewData.data);
        }
        
        if (healthData.success) {
          setHealth(healthData.data);
        }
      } else {
        throw new Error('Failed to load database data');
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    } finally {
      setLoading(false);
    }
  };

  const runMigrations = async () => {
    try {
      const response = await fetch(buildApiUrl(API_ENDPOINTS.DATABASE_MIGRATIONS_RUN), {
        method: 'POST'
      });
      
      if (response.ok) {
        const result = await response.json();
        if (result.success) {
          alert('Migrations completed successfully!');
          loadDatabaseData(); // Reload data
        } else {
          alert('Migration failed: ' + result.error);
        }
      }
    } catch (err) {
      alert('Failed to run migrations: ' + err);
    }
  };

  const rollbackMigrations = async () => {
    if (confirm('Are you sure you want to rollback the last batch of migrations?')) {
      try {
        const response = await fetch(buildApiUrl(API_ENDPOINTS.DATABASE_MIGRATIONS_ROLLBACK), {
          method: 'POST'
        });
        
        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            alert('Rollback completed successfully!');
            loadDatabaseData(); // Reload data
          } else {
            alert('Rollback failed: ' + result.error);
          }
        }
      } catch (err) {
        alert('Failed to rollback migrations: ' + err);
      }
    }
  };

  const executeQuery = async () => {
    if (!querySql.trim()) return;
    
    try {
      setQueryLoading(true);
              const response = await fetch(buildApiUrl(API_ENDPOINTS.DATABASE_QUERY), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ sql: querySql })
      });
      
      if (response.ok) {
        const result = await response.json();
        if (result.success) {
          setQueryResults(result.data);
        } else {
          alert('Query failed: ' + result.error);
        }
      }
    } catch (err) {
      alert('Failed to execute query: ' + err);
    } finally {
      setQueryLoading(false);
    }
  };

  const getHealthColor = (status: string) => {
    switch (status) {
      case 'healthy': return 'text-green-600';
      case 'warning': return 'text-yellow-600';
      case 'critical': return 'text-red-600';
      default: return 'text-gray-600';
    }
  };

  const getHealthIcon = (status: string) => {
    switch (status) {
      case 'healthy': return '✅';
      case 'warning': return '⚠️';
      case 'critical': return '❌';
      default: return '❓';
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-lg">Loading database information...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <div className="text-red-800">Error: {error}</div>
        <Button onClick={loadDatabaseData} className="mt-2">
          Retry
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-900">Database Management</h1>
        <div className="space-x-2">
          <Button onClick={runMigrations} variant="primary">
            Run Migrations
          </Button>
          <Button onClick={rollbackMigrations} variant="secondary">
            Rollback
          </Button>
          <Button onClick={() => setShowQueryModal(true)} variant="outline">
            Execute Query
          </Button>
        </div>
      </div>

      {/* Health Status */}
      {health && (
        <Card>
          <div className="p-6">
            <h2 className="text-lg font-semibold mb-4">Database Health</h2>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div className="text-center">
                <div className={`text-2xl ${getHealthColor(health.overall_health)}`}>
                  {getHealthIcon(health.overall_health)}
                </div>
                <div className="text-sm font-medium">Overall</div>
                <div className={`text-lg font-bold ${getHealthColor(health.overall_health)}`}>
                  {health.overall_health}
                </div>
              </div>
              
              <div className="text-center">
                <div className={`text-2xl ${health.checks?.connection ? 'text-green-600' : 'text-red-600'}`}>
                  {health.checks?.connection ? '✅' : '❌'}
                </div>
                <div className="text-sm font-medium">Connection</div>
                <div className="text-lg font-bold">
                  {health.checks?.connection ? 'Connected' : 'Disconnected'}
                </div>
              </div>
              
              <div className="text-center">
                <div className={`text-2xl ${health.checks?.migrations?.healthy ? 'text-green-600' : 'text-yellow-600'}`}>
                  {health.checks?.migrations?.healthy ? '✅' : '⚠️'}
                </div>
                <div className="text-sm font-medium">Migrations</div>
                <div className="text-lg font-bold">
                  {health.checks?.migrations?.executed || 0}/{health.checks?.migrations?.total || 0}
                </div>
              </div>
              
              <div className="text-center">
                <div className={`text-2xl ${health.checks?.tables?.healthy ? 'text-green-600' : 'text-red-600'}`}>
                  {health.checks?.tables?.healthy ? '✅' : '❌'}
                </div>
                <div className="text-sm font-medium">Tables</div>
                <div className="text-lg font-bold">
                  {health.checks?.tables?.existing || 0}/{health.checks?.tables?.total_required || 0}
                </div>
              </div>
            </div>
          </div>
        </Card>
      )}

      {/* Connection Information */}
      {overview && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <div className="p-6">
              <h2 className="text-lg font-semibold mb-4">Connection Information</h2>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-gray-600">Status:</span>
                  <span className={`font-medium ${
                    overview.connection.is_connected ? 'text-green-600' : 'text-red-600'
                  }`}>
                    {overview.connection.status}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Response Time:</span>
                  <span className="font-medium">{overview.connection.response_time}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Server Version:</span>
                  <span className="font-medium">{overview.connection.server_version}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Host:</span>
                  <span className="font-medium">{overview.statistics.config.host}:{overview.statistics.config.port}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Database:</span>
                  <span className="font-medium">{overview.statistics.config.database}</span>
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <h2 className="text-lg font-semibold mb-4">Statistics</h2>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-gray-600">Total Queries:</span>
                  <span className="font-medium">{overview.statistics.query_count}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Tables:</span>
                  <span className="font-medium">{overview.tables.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Migrations:</span>
                  <span className="font-medium">
                    {overview.migrations.executed_migrations}/{overview.migrations.total_migrations}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Pending:</span>
                  <span className="font-medium">{overview.migrations.pending_migrations}</span>
                </div>
              </div>
            </div>
          </Card>
        </div>
      )}

      {/* Tables Information */}
      {overview && (
        <Card>
          <div className="p-6">
            <h2 className="text-lg font-semibold mb-4">Database Tables</h2>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Table Name
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Size (MB)
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Row Count
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {overview.tables.map((table) => (
                    <tr key={table.name}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {table.name}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {typeof table.size_mb === 'number' ? table.size_mb.toFixed(2) : parseFloat(table.size_mb || 0).toFixed(2)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {typeof table.row_count === 'number' ? table.row_count.toLocaleString() : parseInt(table.row_count || 0).toLocaleString()}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </Card>
      )}

      {/* Query Modal */}
      <Modal
        isOpen={showQueryModal}
        onClose={() => setShowQueryModal(false)}
        title="Execute Database Query"
      >
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              SQL Query (SELECT, SHOW, DESCRIBE, EXPLAIN only)
            </label>
            <Textarea
              value={querySql}
              onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setQuerySql(e.target.value)}
              placeholder="SELECT * FROM users LIMIT 10"
              rows={4}
            />
          </div>
          
          <div className="flex justify-end space-x-2">
            <Button onClick={() => setShowQueryModal(false)} variant="secondary">
              Cancel
            </Button>
            <Button 
              onClick={executeQuery} 
              variant="primary"
              disabled={queryLoading || !querySql.trim()}
            >
              {queryLoading ? 'Executing...' : 'Execute Query'}
            </Button>
          </div>

          {queryResults.length > 0 && (
            <div className="mt-4">
              <h3 className="text-sm font-medium text-gray-700 mb-2">Results:</h3>
              <div className="overflow-x-auto max-h-64">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                  <thead className="bg-gray-50">
                    <tr>
                      {Object.keys(queryResults[0] || {}).map((key) => (
                        <th key={key} className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                          {key}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {queryResults.map((row, index) => (
                      <tr key={index}>
                        {Object.values(row).map((value, colIndex) => (
                          <td key={colIndex} className="px-3 py-2 text-xs text-gray-900">
                            {String(value)}
                          </td>
                        ))}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      </Modal>
    </div>
  );
};

export default DatabaseDashboard; 