import React, { useState, useEffect } from 'react';
import { Card, Button, Input } from '../index';

interface ErrorLog {
  id: number;
  level: 'error' | 'warning' | 'info' | 'debug';
  message: string;
  stack_trace?: string;
  file: string;
  line: number;
  timestamp: string;
  user_id?: number;
  ip_address: string;
  user_agent: string;
  resolved: boolean;
  assigned_to?: string;
}

interface DebugInfo {
  php_version: string;
  memory_usage: string;
  memory_limit: string;
  execution_time: string;
  database_connections: number;
  cache_hit_rate: number;
  active_sessions: number;
  last_error: string;
}

const ErrorDebugManager: React.FC = () => {
  const [errorLogs, setErrorLogs] = useState<ErrorLog[]>([]);
  const [debugInfo, setDebugInfo] = useState<DebugInfo | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedError, setSelectedError] = useState<ErrorLog | null>(null);
  const [isErrorModalOpen, setIsErrorModalOpen] = useState(false);
  const [filterLevel, setFilterLevel] = useState<'all' | 'error' | 'warning' | 'info' | 'debug'>('all');
  const [searchQuery, setSearchQuery] = useState('');

  // Mock data for development
  const mockErrorLogs: ErrorLog[] = [
    {
      id: 1,
      level: 'error',
      message: 'Database connection failed: Connection refused',
      stack_trace: 'Error: Connection refused\n    at Database.php:45\n    at Connection.php:23\n    at DatabaseManager.php:67',
      file: '/var/www/html/src/Core/Database/Database.php',
      line: 45,
      timestamp: '2025-01-27T15:30:00Z',
      user_id: 123,
      ip_address: '192.168.1.100',
      user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      resolved: false,
      assigned_to: 'admin'
    },
    {
      id: 2,
      level: 'warning',
      message: 'Memory usage is approaching limit: 85% used',
      file: '/var/www/html/src/Core/Application.php',
      line: 156,
      timestamp: '2025-01-27T15:25:00Z',
      user_id: 456,
      ip_address: '203.0.113.45',
      user_agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
      resolved: true,
      assigned_to: 'system'
    },
    {
      id: 3,
      level: 'info',
      message: 'User login successful: user@example.com',
      file: '/var/www/html/src/Services/User/UserService.php',
      line: 89,
      timestamp: '2025-01-27T15:20:00Z',
      user_id: 789,
      ip_address: '10.0.0.50',
      user_agent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
      resolved: true
    },
    {
      id: 4,
      level: 'debug',
      message: 'Cache miss for key: user_profile_123',
      file: '/var/www/html/src/Core/Cache/FileCache.php',
      line: 234,
      timestamp: '2025-01-27T15:15:00Z',
      user_id: 123,
      ip_address: '192.168.1.100',
      user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      resolved: true
    }
  ];

  const mockDebugInfo: DebugInfo = {
    php_version: '8.2.15',
    memory_usage: '128.5 MB',
    memory_limit: '256M',
    execution_time: '0.045 seconds',
    database_connections: 12,
    cache_hit_rate: 87.3,
    active_sessions: 45,
    last_error: '2025-01-27T15:30:00Z - Database connection failed'
  };

  useEffect(() => {
    // Simulate API call
    setTimeout(() => {
      setErrorLogs(mockErrorLogs);
      setDebugInfo(mockDebugInfo);
      setIsLoading(false);
    }, 1000);
  }, []);

  const filteredErrors = errorLogs.filter(error => {
    const matchesLevel = filterLevel === 'all' || error.level === filterLevel;
    const matchesSearch = searchQuery === '' || 
      error.message.toLowerCase().includes(searchQuery.toLowerCase()) ||
      error.file.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesLevel && matchesSearch;
  });

  const getLevelColor = (level: string) => {
    switch (level) {
      case 'error':
        return 'bg-red-100 text-red-800';
      case 'warning':
        return 'bg-yellow-100 text-yellow-800';
      case 'info':
        return 'bg-blue-100 text-blue-800';
      case 'debug':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getLevelIcon = (level: string) => {
    switch (level) {
      case 'error':
        return (
          <svg className="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        );
      case 'warning':
        return (
          <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        );
      case 'info':
        return (
          <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      case 'debug':
        return (
          <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        );
      default:
        return (
          <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
    }
  };

  const handleResolveError = (errorId: number) => {
    setErrorLogs(prev => 
      prev.map(error => 
        error.id === errorId ? { ...error, resolved: true } : error
      )
    );
  };

  const handleAssignError = (errorId: number, assignedTo: string) => {
    setErrorLogs(prev => 
      prev.map(error => 
        error.id === errorId ? { ...error, assigned_to: assignedTo } : error
      )
    );
  };

  const handleViewError = (error: ErrorLog) => {
    setSelectedError(error);
    setIsErrorModalOpen(true);
  };

  const handleClearLogs = () => {
    if (confirm('Are you sure you want to clear all resolved logs? This action cannot be undone.')) {
      setErrorLogs(prev => prev.filter(error => !error.resolved));
    }
  };

  const handleExportLogs = () => {
    // TODO: Implement log export
    alert('Log export functionality coming soon!');
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Error & Debug Management</h1>
          <p className="text-gray-600">Monitor errors, debug issues, and manage system logs</p>
        </div>
        <div className="flex space-x-3">
          <Button variant="outline" onClick={handleExportLogs}>
            Export Logs
          </Button>
          <Button variant="outline" onClick={handleClearLogs}>
            Clear Resolved
          </Button>
          <Button variant="primary">
            Run Debug Scan
          </Button>
        </div>
      </div>

      {/* Debug Information */}
      {debugInfo && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">PHP Version</p>
                  <p className="text-2xl font-semibold text-gray-900">{debugInfo.php_version}</p>
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Memory Usage</p>
                  <p className="text-2xl font-semibold text-gray-900">{debugInfo.memory_usage}</p>
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">DB Connections</p>
                  <p className="text-2xl font-semibold text-gray-900">{debugInfo.database_connections}</p>
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Cache Hit Rate</p>
                  <p className="text-2xl font-semibold text-gray-900">{debugInfo.cache_hit_rate}%</p>
                </div>
              </div>
            </div>
          </Card>
        </div>
      )}

      {/* Filters and Search */}
      <Card>
        <div className="p-6">
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="flex-1">
              <Input
                type="text"
                placeholder="Search error messages, files, or stack traces..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <div className="w-full sm:w-48">
              <select
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                value={filterLevel}
                onChange={(e) => setFilterLevel(e.target.value as any)}
              >
                <option value="all">All Levels</option>
                <option value="error">Errors</option>
                <option value="warning">Warnings</option>
                <option value="info">Info</option>
                <option value="debug">Debug</option>
              </select>
            </div>
          </div>
        </div>
      </Card>

      {/* Error Logs Table */}
      <Card>
        <div className="p-6">
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-lg font-semibold text-gray-900">Error Logs</h3>
            <div className="text-sm text-gray-600">
              {filteredErrors.length} of {errorLogs.length} logs
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredErrors.map((error) => (
                  <tr key={error.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="flex-shrink-0">
                          {getLevelIcon(error.level)}
                        </div>
                        <div className="ml-3">
                          <div className="text-sm font-medium text-gray-900">
                            {error.message}
                          </div>
                          <div className="text-sm text-gray-500">
                            Line {error.line}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getLevelColor(error.level)}`}>
                        {error.level.charAt(0).toUpperCase() + error.level.slice(1)}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div className="max-w-xs truncate" title={error.file}>
                        {error.file.split('/').pop()}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {new Date(error.timestamp).toLocaleString()}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {error.resolved ? (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Resolved
                        </span>
                      ) : (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          Active
                        </span>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex space-x-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleViewError(error)}
                        >
                          View
                        </Button>
                        {!error.resolved && (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleResolveError(error.id)}
                          >
                            Resolve
                          </Button>
                        )}
                        <select
                          className="px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-green-500"
                          value={error.assigned_to || ''}
                          onChange={(e) => handleAssignError(error.id, e.target.value)}
                        >
                          <option value="">Unassigned</option>
                          <option value="admin">Admin</option>
                          <option value="developer">Developer</option>
                          <option value="system">System</option>
                        </select>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </Card>

      {/* Error Detail Modal */}
      {isErrorModalOpen && selectedError && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900">Error Details</h3>
                <button
                  onClick={() => setIsErrorModalOpen(false)}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Error Level</label>
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getLevelColor(selectedError.level)}`}>
                    {selectedError.level.charAt(0).toUpperCase() + selectedError.level.slice(1)}
                  </span>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Message</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedError.message}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">File</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedError.file}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Line</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedError.line}</p>
                </div>
                
                {selectedError.stack_trace && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Stack Trace</label>
                    <pre className="mt-1 text-xs text-gray-900 bg-gray-50 p-3 rounded overflow-auto max-h-32">
                      {selectedError.stack_trace}
                    </pre>
                  </div>
                )}
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Timestamp</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {new Date(selectedError.timestamp).toLocaleString()}
                  </p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">IP Address</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedError.ip_address}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">User Agent</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedError.user_agent}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Status</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {selectedError.resolved ? 'Resolved' : 'Active'}
                  </p>
                </div>
                
                {selectedError.assigned_to && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Assigned To</label>
                    <p className="mt-1 text-sm text-gray-900">{selectedError.assigned_to}</p>
                  </div>
                )}
              </div>
              
              <div className="mt-6 flex justify-end space-x-3">
                <Button variant="outline" onClick={() => setIsErrorModalOpen(false)}>
                  Close
                </Button>
                {!selectedError.resolved && (
                  <Button variant="primary" onClick={() => {
                    handleResolveError(selectedError.id);
                    setIsErrorModalOpen(false);
                  }}>
                    Mark as Resolved
                  </Button>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ErrorDebugManager; 