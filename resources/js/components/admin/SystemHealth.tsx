import React, { useState, useEffect } from 'react';
import { Card, Button, Modal } from '../index';
import { Activity, AlertTriangle, CheckCircle, Clock, Database, Monitor, Server, Shield, Wifi, Zap, RefreshCw } from 'lucide-react';

interface HealthCheck {
  id: string;
  name: string;
  category: 'system' | 'database' | 'network' | 'security' | 'performance';
  status: 'healthy' | 'warning' | 'critical' | 'unknown';
  lastCheck: string;
  responseTime: number;
  details: string;
  recommendations: string[];
}

interface SystemResource {
  name: string;
  current: number;
  max: number;
  unit: string;
  status: 'optimal' | 'good' | 'warning' | 'critical';
  trend: 'stable' | 'increasing' | 'decreasing';
  threshold: {
    warning: number;
    critical: number;
  };
}

interface DiagnosticReport {
  id: string;
  timestamp: string;
  overallHealth: 'excellent' | 'good' | 'fair' | 'poor';
  issues: number;
  warnings: number;
  recommendations: string[];
  systemInfo: Record<string, string>;
}

const SystemHealth: React.FC = () => {
  const [selectedHealthCheck, setSelectedHealthCheck] = useState<HealthCheck | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [isRunningDiagnostics, setIsRunningDiagnostics] = useState(false);

  // Mock data for system health diagnostics
  const healthChecks: HealthCheck[] = [
    {
      id: 'hc-001',
      name: 'Database Connection',
      category: 'database',
      status: 'healthy',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 45,
      details: 'Database connection pool is healthy with 8 active connections',
      recommendations: ['Monitor connection pool growth', 'Consider connection pooling optimization']
    },
    {
      id: 'hc-002',
      name: 'API Response Time',
      category: 'performance',
      status: 'healthy',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 145,
      details: 'Average API response time is 145ms, within acceptable limits',
      recommendations: ['Implement response caching', 'Monitor for performance degradation']
    },
    {
      id: 'hc-003',
      name: 'Disk Space',
      category: 'system',
      status: 'warning',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 12,
      details: 'Disk usage is at 78%, approaching warning threshold of 80%',
      recommendations: ['Clean up temporary files', 'Consider disk expansion', 'Implement log rotation']
    },
    {
      id: 'hc-004',
      name: 'Memory Usage',
      category: 'system',
      status: 'healthy',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 23,
      details: 'Memory usage is at 67%, well within healthy limits',
      recommendations: ['Continue monitoring memory trends', 'Optimize memory-intensive operations']
    },
    {
      id: 'hc-005',
      name: 'Network Latency',
      category: 'network',
      status: 'healthy',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 8,
      details: 'Network latency is 8ms, excellent connectivity',
      recommendations: ['Monitor for network congestion', 'Consider CDN for global users']
    },
    {
      id: 'hc-006',
      name: 'Security Headers',
      category: 'security',
      status: 'healthy',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 5,
      details: 'All security headers are properly configured and active',
      recommendations: ['Regular security header audits', 'Monitor for new security threats']
    },
    {
      id: 'hc-007',
      name: 'Cache Performance',
      category: 'performance',
      status: 'warning',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 67,
      details: 'Cache hit rate is 75%, below optimal threshold of 80%',
      recommendations: ['Optimize cache invalidation', 'Increase cache size', 'Review cache keys']
    },
    {
      id: 'hc-008',
      name: 'SSL Certificate',
      category: 'security',
      status: 'healthy',
      lastCheck: '2025-08-30 15:45:00',
      responseTime: 3,
      details: 'SSL certificate is valid and expires in 45 days',
      recommendations: ['Set reminder for certificate renewal', 'Monitor certificate expiration']
    }
  ];

  const systemResources: SystemResource[] = [
    {
      name: 'CPU Usage',
      current: 23.4,
      max: 100,
      unit: '%',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 70, critical: 90 }
    },
    {
      name: 'Memory Usage',
      current: 67.8,
      max: 100,
      unit: '%',
      status: 'good',
      trend: 'increasing',
      threshold: { warning: 80, critical: 95 }
    },
    {
      name: 'Disk Usage',
      current: 78.2,
      max: 100,
      unit: '%',
      status: 'warning',
      trend: 'increasing',
      threshold: { warning: 80, critical: 95 }
    },
    {
      name: 'Network I/O',
      current: 12.3,
      max: 100,
      unit: 'MB/s',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 50, critical: 100 }
    },
    {
      name: 'Database Connections',
      current: 8,
      max: 30,
      unit: '',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 20, critical: 30 }
    },
    {
      name: 'Active Users',
      current: 15,
      max: 100,
      unit: '',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 80, critical: 95 }
    }
  ];

  const diagnosticReport: DiagnosticReport = {
    id: 'report-001',
    timestamp: '2025-08-30 15:45:00',
    overallHealth: 'good',
    issues: 2,
    warnings: 2,
    recommendations: [
      'Monitor disk space usage and implement cleanup procedures',
      'Optimize cache performance to improve hit rates',
      'Consider implementing automated health check alerts',
      'Review and optimize database connection pooling'
    ],
    systemInfo: {
      'OS Version': 'Linux 6.14.0-29-generic',
      'PHP Version': '8.2.0',
      'Database': 'MySQL 8.0.33',
      'Web Server': 'Apache 2.4.57',
      'Framework': 'IslamWiki v0.0.2',
      'Uptime': '15 days, 8 hours',
      'Last Restart': '2025-08-15 07:30:00'
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'healthy':
      case 'optimal':
        return 'bg-green-100 text-green-800';
      case 'warning':
      case 'good':
        return 'bg-yellow-100 text-yellow-800';
      case 'critical':
        return 'bg-red-100 text-red-800';
      case 'unknown':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'system':
        return <Server className="w-4 h-4" />;
      case 'database':
        return <Database className="w-4 h-4" />;
      case 'network':
        return <Wifi className="w-4 h-4" />;
      case 'security':
        return <Shield className="w-4 h-4" />;
      case 'performance':
        return <Zap className="w-4 h-4" />;
      default:
        return <Monitor className="w-4 h-4" />;
    }
  };

  const getHealthIcon = (status: string) => {
    switch (status) {
      case 'healthy':
        return <CheckCircle className="w-5 h-5 text-green-500" />;
      case 'warning':
        return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
      case 'critical':
        return <AlertTriangle className="w-5 h-5 text-red-500" />;
      case 'unknown':
        return <Clock className="w-5 h-5 text-gray-500" />;
      default:
        return <Monitor className="w-5 h-5 text-gray-500" />;
    }
  };

  const runDiagnostics = () => {
    setIsRunningDiagnostics(true);
    // Simulate diagnostics running
    setTimeout(() => {
      setIsRunningDiagnostics(false);
    }, 5000);
  };

  const openHealthCheckDetails = (healthCheck: HealthCheck) => {
    setSelectedHealthCheck(healthCheck);
    setIsModalOpen(true);
  };

  // Auto-refresh effect
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(() => {
        // Refresh health check data
      }, 60000); // 1 minute
      return () => clearInterval(interval);
    }
  }, [autoRefresh]);

  // Calculate overall health metrics
  const healthyChecks = healthChecks.filter(check => check.status === 'healthy').length;
  const warningChecks = healthChecks.filter(check => check.status === 'warning').length;
  const criticalChecks = healthChecks.filter(check => check.status === 'critical').length;
  const totalChecks = healthChecks.length;

  const overallHealthScore = Math.round((healthyChecks / totalChecks) * 100);

  return (
    <div className="max-w-7xl mx-auto p-6 space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">System Health</h1>
          <p className="text-lg text-gray-600 mt-1">Comprehensive System Diagnostics & Health Monitoring</p>
        </div>
        <div className="flex items-center space-x-4">
          <div className="flex items-center space-x-2">
            <input
              type="checkbox"
              id="auto-refresh"
              checked={autoRefresh}
              onChange={(e) => setAutoRefresh(e.target.checked)}
              className="rounded border-gray-300"
            />
            <label htmlFor="auto-refresh" className="text-sm text-gray-600">
              Auto-refresh
            </label>
          </div>
          <Button
            onClick={runDiagnostics}
            disabled={isRunningDiagnostics}
            className="bg-blue-600 hover:bg-blue-700"
          >
            {isRunningDiagnostics ? (
              <>
                <Activity className="w-4 h-4 mr-2 animate-spin" />
                Running...
              </>
            ) : (
              <>
                <Monitor className="w-4 h-4 mr-2" />
                Run Diagnostics
              </>
            )}
          </Button>
          <Button variant="outline">
            <RefreshCw className="w-4 h-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>

      {/* Health Overview */}
      <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">{overallHealthScore}%</div>
            <div className="text-sm text-gray-600">Health Score</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">{healthyChecks}</div>
            <div className="text-sm text-gray-600">Healthy</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-yellow-600">{warningChecks}</div>
            <div className="text-sm text-gray-600">Warnings</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-red-600">{criticalChecks}</div>
            <div className="text-sm text-gray-600">Critical</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">{totalChecks}</div>
            <div className="text-sm text-gray-600">Total Checks</div>
          </div>
        </Card>
      </div>

      {/* Health Checks */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">System Health Checks</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {healthChecks.map((check) => (
            <div 
              key={check.id} 
              className="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
              onClick={() => openHealthCheckDetails(check)}
            >
              <div className="flex justify-between items-start mb-3">
                <div className="flex items-center space-x-2">
                  {getCategoryIcon(check.category)}
                  <h3 className="font-medium text-gray-900">{check.name}</h3>
                </div>
                {getHealthIcon(check.status)}
              </div>
              
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-500">Status:</span>
                  <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(check.status)}`}>
                    {check.status.toUpperCase()}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Response Time:</span>
                  <span className="font-medium">{check.responseTime}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Last Check:</span>
                  <span className="font-medium">{check.lastCheck}</span>
                </div>
              </div>
              
              <div className="mt-3">
                <p className="text-sm text-gray-600">{check.details}</p>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* System Resources */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">System Resources</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {systemResources.map((resource) => (
            <div key={resource.name} className="p-4 border rounded-lg">
              <div className="flex justify-between items-start mb-3">
                <h3 className="font-medium text-gray-900">{resource.name}</h3>
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(resource.status)}`}>
                  {resource.status.toUpperCase()}
                </span>
              </div>
              
              <div className="text-2xl font-bold text-gray-900 mb-2">
                {resource.current}{resource.unit} / {resource.max}{resource.unit}
              </div>
              
              <div className="mb-3">
                <div className="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Usage</span>
                  <span>{Math.round((resource.current / resource.max) * 100)}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all duration-500 ${
                      resource.status === 'critical' ? 'bg-red-600' :
                      resource.status === 'warning' ? 'bg-yellow-600' :
                      resource.status === 'good' ? 'bg-blue-600' :
                      'bg-green-600'
                    }`}
                    style={{ width: `${(resource.current / resource.max) * 100}%` }}
                  ></div>
                </div>
              </div>
              
              <div className="text-xs text-gray-500">
                <div>Warning: {resource.threshold.warning}{resource.unit}</div>
                <div>Critical: {resource.threshold.critical}{resource.unit}</div>
                <div className="mt-1">
                  Trend: <span className="capitalize">{resource.trend}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Diagnostic Report */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Latest Diagnostic Report</h2>
        <div className="space-y-4">
          <div className="flex justify-between items-center">
            <div>
              <span className="text-sm text-gray-500">Report ID:</span>
              <span className="ml-2 font-mono text-sm">{diagnosticReport.id}</span>
            </div>
            <div className="text-sm text-gray-500">
              Generated: {diagnosticReport.timestamp}
            </div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="text-center p-4 bg-gray-50 rounded-lg">
              <div className={`text-2xl font-bold ${
                diagnosticReport.overallHealth === 'excellent' ? 'text-green-600' :
                diagnosticReport.overallHealth === 'good' ? 'text-blue-600' :
                diagnosticReport.overallHealth === 'fair' ? 'text-yellow-600' :
                'text-red-600'
              }`}>
                {diagnosticReport.overallHealth.toUpperCase()}
              </div>
              <div className="text-sm text-gray-600">Overall Health</div>
            </div>
            
            <div className="text-center p-4 bg-gray-50 rounded-lg">
              <div className="text-2xl font-bold text-red-600">{diagnosticReport.issues}</div>
              <div className="text-sm text-gray-600">Issues Found</div>
            </div>
            
            <div className="text-center p-4 bg-gray-50 rounded-lg">
              <div className="text-2xl font-bold text-yellow-600">{diagnosticReport.warnings}</div>
              <div className="text-sm text-gray-600">Warnings</div>
            </div>
          </div>
          
          <div>
            <h3 className="text-lg font-medium mb-2">System Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              {Object.entries(diagnosticReport.systemInfo).map(([key, value]) => (
                <div key={key} className="flex justify-between">
                  <span className="text-gray-500">{key}:</span>
                  <span className="font-medium">{value}</span>
                </div>
              ))}
            </div>
          </div>
          
          <div>
            <h3 className="text-lg font-medium mb-2">Recommendations</h3>
            <ul className="space-y-2">
              {diagnosticReport.recommendations.map((rec, index) => (
                <li key={index} className="flex items-start text-sm text-gray-600">
                  <span className="w-2 h-2 bg-blue-400 rounded-full mr-2 mt-2"></span>
                  {rec}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </Card>

      {/* Health Check Details Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={`Health Check: ${selectedHealthCheck?.name}`}
        size="lg"
      >
        {selectedHealthCheck && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Check Information</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Category:</span>
                  <span className="ml-2 font-medium capitalize">{selectedHealthCheck.category}</span>
                </div>
                <div>
                  <span className="text-gray-500">Status:</span>
                  <span className={`ml-2 inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(selectedHealthCheck.status)}`}>
                    {selectedHealthCheck.status.toUpperCase()}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Response Time:</span>
                  <span className="ml-2 font-medium">{selectedHealthCheck.responseTime}ms</span>
                </div>
                <div>
                  <span className="text-gray-500">Last Check:</span>
                  <span className="ml-2 font-medium">{selectedHealthCheck.lastCheck}</span>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Details</h3>
              <p className="text-sm text-gray-600 mt-2">{selectedHealthCheck.details}</p>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Recommendations</h3>
              <ul className="mt-2 space-y-1">
                {selectedHealthCheck.recommendations.map((rec, index) => (
                  <li key={index} className="flex items-start text-sm text-gray-600">
                    <span className="w-2 h-2 bg-blue-400 rounded-full mr-2 mt-2"></span>
                    {rec}
                  </li>
                ))}
              </ul>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Actions</h3>
              <div className="flex space-x-2 mt-2">
                <Button size="sm">Run Check Now</Button>
                <Button variant="outline" size="sm">Configure Alerts</Button>
                <Button variant="outline" size="sm">View History</Button>
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default SystemHealth; 