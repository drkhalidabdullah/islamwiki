import React, { useState, useEffect } from 'react';
import { Card, Button, Modal } from '../index';
import { Activity, TrendingUp, TrendingDown, AlertTriangle, Zap, Database, Globe, Server, BarChart3, Settings, RefreshCw } from 'lucide-react';

interface SystemMetric {
  name: string;
  value: number;
  unit: string;
  status: 'optimal' | 'good' | 'warning' | 'critical';
  trend: 'up' | 'down' | 'stable';
  threshold: {
    warning: number;
    critical: number;
  };
  description: string;
}

interface PerformanceAlert {
  id: string;
  type: 'performance' | 'resource' | 'security' | 'error';
  severity: 'low' | 'medium' | 'high' | 'critical';
  title: string;
  message: string;
  timestamp: string;
  status: 'active' | 'acknowledged' | 'resolved';
  affectedComponent: string;
}

interface OptimizationSuggestion {
  id: string;
  category: 'database' | 'cache' | 'assets' | 'code' | 'infrastructure';
  title: string;
  description: string;
  impact: 'low' | 'medium' | 'high';
  effort: 'low' | 'medium' | 'high';
  estimatedImprovement: string;
  priority: 'low' | 'medium' | 'high' | 'critical';
}

const PerformanceMonitor: React.FC = () => {
  const [selectedMetric, setSelectedMetric] = useState<SystemMetric | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [refreshInterval, setRefreshInterval] = useState(30);

  // Mock data for comprehensive performance monitoring
  const systemMetrics: SystemMetric[] = [
    {
      name: 'CPU Usage',
      value: 23.4,
      unit: '%',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 70, critical: 90 },
      description: 'Current CPU utilization across all cores'
    },
    {
      name: 'Memory Usage',
      value: 67.8,
      unit: '%',
      status: 'good',
      trend: 'up',
      threshold: { warning: 80, critical: 95 },
      description: 'RAM utilization including cache and buffers'
    },
    {
      name: 'Disk Usage',
      value: 45.2,
      unit: '%',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 80, critical: 95 },
      description: 'Storage space utilization on main drive'
    },
    {
      name: 'Network I/O',
      value: 12.3,
      unit: 'MB/s',
      status: 'optimal',
      trend: 'down',
      threshold: { warning: 50, critical: 100 },
      description: 'Current network input/output throughput'
    },
    {
      name: 'Database Connections',
      value: 8,
      unit: '',
      status: 'optimal',
      trend: 'stable',
      threshold: { warning: 20, critical: 30 },
      description: 'Active database connections'
    },
    {
      name: 'Cache Hit Rate',
      value: 89.5,
      unit: '%',
      status: 'optimal',
      trend: 'up',
      threshold: { warning: 70, critical: 50 },
      description: 'Percentage of cache requests served from cache'
    },
    {
      name: 'Response Time',
      value: 145,
      unit: 'ms',
      status: 'good',
      trend: 'stable',
      threshold: { warning: 300, critical: 500 },
      description: 'Average API response time'
    },
    {
      name: 'Error Rate',
      value: 0.12,
      unit: '%',
      status: 'optimal',
      trend: 'down',
      threshold: { warning: 1, critical: 5 },
      description: 'Percentage of requests resulting in errors'
    }
  ];

  const performanceAlerts: PerformanceAlert[] = [
    {
      id: 'alert-001',
      type: 'performance',
      severity: 'medium',
      title: 'High Memory Usage Detected',
      message: 'Memory usage has increased to 67.8%, approaching warning threshold of 80%',
      timestamp: '2025-08-30 14:45:00',
      status: 'active',
      affectedComponent: 'System Memory'
    },
    {
      id: 'alert-002',
      type: 'resource',
      severity: 'low',
      title: 'Database Connection Pool Growing',
      message: 'Database connections have increased to 8, monitoring for potential connection pool exhaustion',
      timestamp: '2025-08-30 14:42:00',
      status: 'acknowledged',
      affectedComponent: 'Database Service'
    },
    {
      id: 'alert-003',
      type: 'performance',
      severity: 'low',
      title: 'Cache Hit Rate Improving',
      message: 'Cache hit rate has improved to 89.5%, indicating good cache performance',
      timestamp: '2025-08-30 14:40:00',
      status: 'resolved',
      affectedComponent: 'Cache System'
    }
  ];

  const optimizationSuggestions: OptimizationSuggestion[] = [
    {
      id: 'opt-001',
      category: 'database',
      title: 'Database Query Optimization',
      description: 'Several database queries are taking longer than optimal. Consider adding database indexes and optimizing query structure.',
      impact: 'high',
      effort: 'medium',
      estimatedImprovement: '30-50% faster query execution',
      priority: 'high'
    },
    {
      id: 'opt-002',
      category: 'cache',
      title: 'Implement Redis Caching',
      description: 'Current file-based caching can be improved with Redis for better performance and scalability.',
      impact: 'high',
      effort: 'medium',
      estimatedImprovement: '40-60% faster response times',
      priority: 'high'
    },
    {
      id: 'opt-003',
      category: 'assets',
      title: 'Asset Compression & CDN',
      description: 'Implement asset compression and CDN integration for faster static resource delivery.',
      impact: 'medium',
      effort: 'low',
      estimatedImprovement: '20-30% faster page loads',
      priority: 'medium'
    },
    {
      id: 'opt-004',
      category: 'code',
      title: 'Code Profiling & Optimization',
      description: 'Profile application code to identify performance bottlenecks and optimize critical paths.',
      impact: 'medium',
      effort: 'high',
      estimatedImprovement: '15-25% overall performance improvement',
      priority: 'medium'
    },
    {
      id: 'opt-005',
      category: 'infrastructure',
      title: 'Load Balancer Implementation',
      description: 'Implement load balancing for better resource distribution and improved availability.',
      impact: 'high',
      effort: 'high',
      estimatedImprovement: 'Better resource utilization and availability',
      priority: 'low'
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'optimal':
        return 'bg-green-100 text-green-800';
      case 'good':
        return 'bg-blue-100 text-blue-800';
      case 'warning':
        return 'bg-yellow-100 text-yellow-800';
      case 'critical':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'critical':
        return 'bg-red-100 text-red-800';
      case 'high':
        return 'bg-orange-100 text-orange-800';
      case 'medium':
        return 'bg-yellow-100 text-yellow-800';
      case 'low':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getAlertTypeIcon = (type: string) => {
    switch (type) {
      case 'performance':
        return <Zap className="w-4 h-4" />;
      case 'resource':
        return <Server className="w-4 h-4" />;
      case 'security':
        return <AlertTriangle className="w-4 h-4" />;
      case 'error':
        return <AlertTriangle className="w-4 h-4" />;
      default:
        return <Activity className="w-4 h-4" />;
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'database':
        return <Database className="w-4 h-4" />;
      case 'cache':
        return <Zap className="w-4 h-4" />;
      case 'assets':
        return <Globe className="w-4 h-4" />;
      case 'code':
        return <BarChart3 className="w-4 h-4" />;
      case 'infrastructure':
        return <Server className="w-4 h-4" />;
      default:
        return <Settings className="w-4 h-4" />;
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'critical':
        return 'bg-red-100 text-red-800';
      case 'high':
        return 'bg-orange-100 text-orange-800';
      case 'medium':
        return 'bg-yellow-100 text-yellow-800';
      case 'low':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getImpactColor = (impact: string) => {
    switch (impact) {
      case 'high':
        return 'text-red-600';
      case 'medium':
        return 'text-yellow-600';
      case 'low':
        return 'text-blue-600';
      default:
        return 'text-gray-600';
    }
  };

  const getEffortColor = (effort: string) => {
    switch (effort) {
      case 'high':
        return 'text-red-600';
      case 'medium':
        return 'text-yellow-600';
      case 'low':
        return 'text-blue-600';
      default:
        return 'text-gray-600';
    }
  };

  const openMetricDetails = (metric: SystemMetric) => {
    setSelectedMetric(metric);
    setIsModalOpen(true);
  };

  // Auto-refresh effect
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(() => {
        // Refresh metrics data
      }, refreshInterval * 1000);
      return () => clearInterval(interval);
    }
  }, [autoRefresh, refreshInterval]);

  // Calculate overall system health
  const criticalMetrics = systemMetrics.filter(m => m.status === 'critical').length;
  const warningMetrics = systemMetrics.filter(m => m.status === 'warning').length;
  const goodMetrics = systemMetrics.filter(m => m.status === 'good').length;
  const optimalMetrics = systemMetrics.filter(m => m.status === 'optimal').length;
  
  const overallHealth = criticalMetrics > 0 ? 'critical' : 
                       warningMetrics > 0 ? 'warning' : 
                       goodMetrics > 0 ? 'good' : 'optimal';



  return (
    <div className="max-w-7xl mx-auto p-6 space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Performance Monitor</h1>
          <p className="text-lg text-gray-600 mt-1">Real-time System Health & Performance Metrics</p>
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
          <select
            value={refreshInterval}
            onChange={(e) => setRefreshInterval(Number(e.target.value))}
            className="text-sm border border-gray-300 rounded px-2 py-1"
          >
            <option value={15}>15s</option>
            <option value={30}>30s</option>
            <option value={60}>1m</option>
            <option value={300}>5m</option>
          </select>
          <Button
            variant="outline"
            size="sm"
            onClick={() => window.location.reload()}
          >
            <RefreshCw className="w-4 h-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>

      {/* System Health Overview */}
      <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
        <Card>
          <div className="text-center">
            <div className={`text-2xl font-bold ${
              overallHealth === 'optimal' ? 'text-green-600' :
              overallHealth === 'good' ? 'text-blue-600' :
              overallHealth === 'warning' ? 'text-yellow-600' :
              'text-red-600'
            }`}>
              {overallHealth.toUpperCase()}
            </div>
            <div className="text-sm text-gray-600">System Health</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">{optimalMetrics}</div>
            <div className="text-sm text-gray-600">Optimal</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">{goodMetrics}</div>
            <div className="text-sm text-gray-600">Good</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-yellow-600">{warningMetrics}</div>
            <div className="text-sm text-gray-600">Warning</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-red-600">{criticalMetrics}</div>
            <div className="text-sm text-gray-600">Critical</div>
          </div>
        </Card>
      </div>

      {/* System Metrics Grid */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">System Metrics</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {systemMetrics.map((metric) => (
            <div 
              key={metric.name} 
              className="p-4 border rounded-lg cursor-pointer hover:shadow-md transition-shadow"
              onClick={() => openMetricDetails(metric)}
            >
              <div className="flex justify-between items-start mb-2">
                <h3 className="font-medium text-gray-900">{metric.name}</h3>
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(metric.status)}`}>
                  {metric.status.toUpperCase()}
                </span>
              </div>
              <div className="text-2xl font-bold text-gray-900 mb-1">
                {metric.value}{metric.unit}
              </div>
              <div className="text-sm text-gray-600 mb-2">
                Threshold: {metric.threshold.warning}{metric.unit} / {metric.threshold.critical}{metric.unit}
              </div>
              <div className="flex items-center justify-between">
                <div className="text-xs text-gray-500">{metric.description}</div>
                <div className={`flex items-center text-xs ${
                  metric.trend === 'up' ? 'text-red-500' :
                  metric.trend === 'down' ? 'text-green-500' :
                  'text-gray-500'
                }`}>
                  {metric.trend === 'up' ? <TrendingUp className="w-3 h-3 mr-1" /> :
                   metric.trend === 'down' ? <TrendingDown className="w-3 h-3 mr-1" /> :
                   <span className="w-3 h-3 mr-1">—</span>}
                  {metric.trend === 'up' ? 'Increasing' :
                   metric.trend === 'down' ? 'Decreasing' : 'Stable'}
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Performance Alerts */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Performance Alerts</h2>
        <div className="space-y-4">
          {performanceAlerts.map((alert) => (
            <div key={alert.id} className="border rounded-lg p-4">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    {getAlertTypeIcon(alert.type)}
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getSeverityColor(alert.severity)}`}>
                      {alert.severity.toUpperCase()}
                    </span>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      alert.status === 'resolved' ? 'bg-green-100 text-green-800' :
                      alert.status === 'acknowledged' ? 'bg-blue-100 text-blue-800' :
                      'bg-yellow-100 text-yellow-800'
                    }`}>
                      {alert.status.toUpperCase()}
                    </span>
                  </div>
                  <h3 className="font-medium text-gray-900 mb-1">{alert.title}</h3>
                  <p className="text-sm text-gray-600 mb-2">{alert.message}</p>
                  <div className="text-xs text-gray-500">
                    <span className="font-medium">Component:</span> {alert.affectedComponent} | 
                    <span className="font-medium ml-2">Time:</span> {alert.timestamp}
                  </div>
                </div>
                <div className="ml-4">
                  <Button variant="outline" size="sm">
                    {alert.status === 'active' ? 'Acknowledge' : 
                     alert.status === 'acknowledged' ? 'Resolve' : 'View'}
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Optimization Suggestions */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Optimization Suggestions</h2>
        <div className="space-y-4">
          {optimizationSuggestions.map((suggestion) => (
            <div key={suggestion.id} className="border rounded-lg p-4">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    {getCategoryIcon(suggestion.category)}
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getPriorityColor(suggestion.priority)}`}>
                      {suggestion.priority.toUpperCase()}
                    </span>
                    <span className="text-sm text-gray-500">{suggestion.category}</span>
                  </div>
                  <h3 className="font-medium text-gray-900 mb-1">{suggestion.title}</h3>
                  <p className="text-sm text-gray-600 mb-2">{suggestion.description}</p>
                  <div className="grid grid-cols-3 gap-4 text-xs">
                    <div>
                      <span className="font-medium">Impact:</span>
                      <span className={`ml-1 ${getImpactColor(suggestion.impact)}`}>
                        {suggestion.impact.toUpperCase()}
                      </span>
                    </div>
                    <div>
                      <span className="font-medium">Effort:</span>
                      <span className={`ml-1 ${getEffortColor(suggestion.effort)}`}>
                        {suggestion.effort.toUpperCase()}
                      </span>
                    </div>
                    <div>
                      <span className="font-medium">Improvement:</span>
                      <span className="ml-1 text-gray-600">{suggestion.estimatedImprovement}</span>
                    </div>
                  </div>
                </div>
                <div className="ml-4">
                  <Button variant="outline" size="sm">
                    Implement
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Metric Details Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={`Metric: ${selectedMetric?.name}`}
        size="lg"
      >
        {selectedMetric && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Current Status</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Value:</span>
                  <span className="ml-2 font-medium">{selectedMetric.value}{selectedMetric.unit}</span>
                </div>
                <div>
                  <span className="text-gray-500">Status:</span>
                  <span className={`ml-2 inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(selectedMetric.status)}`}>
                    {selectedMetric.status.toUpperCase()}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Trend:</span>
                  <span className="ml-2 font-medium">{selectedMetric.trend}</span>
                </div>
                <div>
                  <span className="text-gray-500">Unit:</span>
                  <span className="ml-2 font-medium">{selectedMetric.unit}</span>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Thresholds</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Warning:</span>
                  <span className="ml-2 font-medium text-yellow-600">{selectedMetric.threshold.warning}{selectedMetric.unit}</span>
                </div>
                <div>
                  <span className="text-gray-500">Critical:</span>
                  <span className="ml-2 font-medium text-red-600">{selectedMetric.threshold.critical}{selectedMetric.unit}</span>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Description</h3>
              <p className="text-sm text-gray-600 mt-2">{selectedMetric.description}</p>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Actions</h3>
              <div className="flex space-x-2 mt-2">
                <Button size="sm">View History</Button>
                <Button variant="outline" size="sm">Configure Alerts</Button>
                <Button variant="outline" size="sm">Export Data</Button>
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default PerformanceMonitor; 