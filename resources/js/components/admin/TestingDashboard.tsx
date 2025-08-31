import React, { useState, useEffect } from 'react';
import { Card, Button, Modal } from '../index';
import { Play, StopCircle, RefreshCw, BarChart3, Zap, TrendingUp, Activity, Shield, Code, Database, Globe } from 'lucide-react';

interface TestSuite {
  id: string;
  name: string;
  description: string;
  category: 'unit' | 'integration' | 'feature' | 'performance' | 'security';
  totalTests: number;
  passedTests: number;
  failedTests: number;
  skippedTests: number;
  duration: number;
  lastRun: string;
  status: 'passing' | 'failing' | 'not-run' | 'running';
  coverage: number;
  priority: 'high' | 'medium' | 'low';
}

interface CodeQualityMetric {
  metric: string;
  value: number;
  target: number;
  status: 'excellent' | 'good' | 'warning' | 'critical';
  trend: 'improving' | 'stable' | 'declining';
  description: string;
}

interface PerformanceMetric {
  endpoint: string;
  method: string;
  avgResponseTime: number;
  p95ResponseTime: number;
  requestsPerSecond: number;
  errorRate: number;
  lastTested: string;
  status: 'optimal' | 'good' | 'warning' | 'critical';
}

interface SecurityScan {
  id: string;
  type: 'vulnerability' | 'dependency' | 'code' | 'infrastructure';
  severity: 'critical' | 'high' | 'medium' | 'low' | 'info';
  title: string;
  description: string;
  affectedComponent: string;
  discovered: string;
  status: 'open' | 'investigating' | 'fixing' | 'resolved';
  cve?: string;
  cvss?: number;
}

const TestingDashboard: React.FC = () => {
  const [activeTests, setActiveTests] = useState<string[]>([]);
  const [selectedTest, setSelectedTest] = useState<TestSuite | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [autoRefresh, setAutoRefresh] = useState(false);

  // Mock data for comprehensive testing dashboard
  const testSuites: TestSuite[] = [
    {
      id: 'core-framework',
      name: 'Core Framework Tests',
      description: 'PHP framework core functionality tests',
      category: 'unit',
      totalTests: 51,
      passedTests: 51,
      failedTests: 0,
      skippedTests: 0,
      duration: 2.3,
      lastRun: '2025-08-30 14:30:00',
      status: 'passing',
      coverage: 92,
      priority: 'high'
    },
    {
      id: 'api-endpoints',
      name: 'API Endpoint Tests',
      description: 'RESTful API functionality and response tests',
      category: 'integration',
      totalTests: 24,
      passedTests: 24,
      failedTests: 0,
      skippedTests: 0,
      duration: 1.8,
      lastRun: '2025-08-30 14:32:00',
      status: 'passing',
      coverage: 88,
      priority: 'high'
    },
    {
      id: 'frontend-components',
      name: 'Frontend Component Tests',
      description: 'React component rendering and interaction tests',
      category: 'unit',
      totalTests: 18,
      passedTests: 18,
      failedTests: 0,
      skippedTests: 0,
      duration: 1.2,
      lastRun: '2025-08-30 14:35:00',
      status: 'passing',
      coverage: 85,
      priority: 'medium'
    },
    {
      id: 'database-operations',
      name: 'Database Operation Tests',
      description: 'Database CRUD operations and query tests',
      category: 'integration',
      totalTests: 12,
      passedTests: 12,
      failedTests: 0,
      skippedTests: 0,
      duration: 3.1,
      lastRun: '2025-08-30 14:38:00',
      status: 'passing',
      coverage: 90,
      priority: 'high'
    },
    {
      id: 'security-tests',
      name: 'Security & Authentication Tests',
      description: 'Security features and authentication flow tests',
      category: 'security',
      totalTests: 15,
      passedTests: 15,
      failedTests: 0,
      skippedTests: 0,
      duration: 2.7,
      lastRun: '2025-08-30 14:41:00',
      status: 'passing',
      coverage: 87,
      priority: 'high'
    },
    {
      id: 'performance-tests',
      name: 'Performance & Load Tests',
      description: 'Response time and load handling tests',
      category: 'performance',
      totalTests: 8,
      passedTests: 8,
      failedTests: 0,
      skippedTests: 0,
      duration: 45.2,
      lastRun: '2025-08-30 14:45:00',
      status: 'passing',
      coverage: 75,
      priority: 'medium'
    }
  ];

  const codeQualityMetrics: CodeQualityMetric[] = [
    {
      metric: 'Code Coverage',
      value: 89,
      target: 90,
      status: 'good',
      trend: 'improving',
      description: 'Percentage of code covered by tests'
    },
    {
      metric: 'Test Pass Rate',
      value: 100,
      target: 95,
      status: 'excellent',
      trend: 'stable',
      description: 'Percentage of tests passing successfully'
    },
    {
      metric: 'Code Duplication',
      value: 3.2,
      target: 5,
      status: 'excellent',
      trend: 'improving',
      description: 'Percentage of duplicated code'
    },
    {
      metric: 'Cyclomatic Complexity',
      value: 4.8,
      target: 6,
      status: 'excellent',
      trend: 'stable',
      description: 'Average complexity per function'
    },
    {
      metric: 'Technical Debt',
      value: 2.1,
      target: 5,
      status: 'excellent',
      trend: 'improving',
      description: 'Technical debt ratio'
    },
    {
      metric: 'Documentation Coverage',
      value: 85,
      target: 80,
      status: 'excellent',
      trend: 'stable',
      description: 'Percentage of documented functions'
    }
  ];

  const performanceMetrics: PerformanceMetric[] = [
    {
      endpoint: '/api/health',
      method: 'GET',
      avgResponseTime: 45,
      p95ResponseTime: 78,
      requestsPerSecond: 1250,
      errorRate: 0.01,
      lastTested: '2025-08-30 14:30:00',
      status: 'optimal'
    },
    {
      endpoint: '/api/wiki/articles',
      method: 'GET',
      avgResponseTime: 120,
      p95ResponseTime: 245,
      requestsPerSecond: 450,
      errorRate: 0.05,
      lastTested: '2025-08-30 14:30:00',
      status: 'good'
    },
    {
      endpoint: '/api/users/profile',
      method: 'GET',
      avgResponseTime: 89,
      p95ResponseTime: 156,
      requestsPerSecond: 320,
      errorRate: 0.02,
      lastTested: '2025-08-30 14:30:00',
      status: 'optimal'
    },
    {
      endpoint: '/api/content/search',
      method: 'POST',
      avgResponseTime: 234,
      p95ResponseTime: 456,
      requestsPerSecond: 180,
      errorRate: 0.08,
      lastTested: '2025-08-30 14:30:00',
      status: 'warning'
    }
  ];

  const securityScans: SecurityScan[] = [
    {
      id: 'sec-001',
      type: 'dependency',
      severity: 'low',
      title: 'Outdated jQuery Version',
      description: 'jQuery 3.6.0 has known vulnerabilities, recommend upgrade to 3.7.0+',
      affectedComponent: 'Frontend Dependencies',
      discovered: '2025-08-30 10:15:00',
      status: 'investigating',
      cve: 'CVE-2023-1234',
      cvss: 3.1
    },
    {
      id: 'sec-002',
      type: 'code',
      severity: 'medium',
      title: 'Potential SQL Injection in Search',
      description: 'User input in search functionality may be vulnerable to SQL injection',
      affectedComponent: 'ContentService::search',
      discovered: '2025-08-30 11:30:00',
      status: 'fixing',
      cvss: 5.4
    },
    {
      id: 'sec-003',
      type: 'infrastructure',
      severity: 'info',
      title: 'HTTPS Enforcement',
      description: 'Recommend enforcing HTTPS for all production traffic',
      affectedComponent: 'Web Server Configuration',
      discovered: '2025-08-30 12:00:00',
      status: 'open'
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'passing':
      case 'excellent':
      case 'optimal':
        return 'bg-green-100 text-green-800';
      case 'good':
        return 'bg-blue-100 text-blue-800';
      case 'warning':
        return 'bg-yellow-100 text-yellow-800';
      case 'critical':
      case 'failing':
        return 'bg-red-100 text-red-800';
      case 'running':
        return 'bg-purple-100 text-purple-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'unit':
        return <Code className="w-4 h-4" />;
      case 'integration':
        return <Database className="w-4 h-4" />;
      case 'feature':
        return <Globe className="w-4 h-4" />;
      case 'performance':
        return <Zap className="w-4 h-4" />;
      case 'security':
        return <Shield className="w-4 h-4" />;
      default:
        return <Code className="w-4 h-4" />;
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
      case 'info':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const runTestSuite = (testId: string) => {
    setActiveTests(prev => [...prev, testId]);
    // Simulate test running
    setTimeout(() => {
      setActiveTests(prev => prev.filter(id => id !== testId));
    }, 3000);
  };

  const runAllTests = () => {
    const allTestIds = testSuites.map(test => test.id);
    setActiveTests(allTestIds);
    // Simulate all tests running
    setTimeout(() => {
      setActiveTests([]);
    }, 5000);
  };

  const stopAllTests = () => {
    setActiveTests([]);
  };

  // Auto-refresh effect
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(() => {
        // Refresh test data
      }, 30000); // 30 seconds
      return () => clearInterval(interval);
    }
  }, [autoRefresh]);

  // Calculate overall metrics
  const totalTests = testSuites.reduce((sum, test) => sum + test.totalTests, 0);
  const totalPassed = testSuites.reduce((sum, test) => sum + test.passedTests, 0);
  const totalFailed = testSuites.reduce((sum, test) => sum + test.failedTests, 0);
  const overallCoverage = Math.round(testSuites.reduce((sum, test) => sum + test.coverage, 0) / testSuites.length);


  return (
    <div className="max-w-7xl mx-auto p-6 space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Testing Dashboard</h1>
          <p className="text-lg text-gray-600 mt-1">Comprehensive Testing & Quality Assurance Tools</p>
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
            variant="outline"
            size="sm"
            onClick={() => window.location.reload()}
          >
            <RefreshCw className="w-4 h-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="flex space-x-4">
        <Button
          onClick={runAllTests}
          disabled={activeTests.length > 0}
          className="bg-green-600 hover:bg-green-700"
        >
          <Play className="w-4 h-4 mr-2" />
          Run All Tests
        </Button>
        <Button
          onClick={stopAllTests}
          disabled={activeTests.length === 0}
          variant="outline"
          className="border-red-300 text-red-700 hover:bg-red-50"
        >
          <StopCircle className="w-4 h-4 mr-2" />
          Stop All Tests
        </Button>
        <Button variant="outline">
          <BarChart3 className="w-4 h-4 mr-2" />
          Generate Report
        </Button>
      </div>

      {/* Overview Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">{totalTests}</div>
            <div className="text-sm text-gray-600">Total Tests</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">{totalPassed}</div>
            <div className="text-sm text-gray-600">Passed</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-red-600">{totalFailed}</div>
            <div className="text-sm text-gray-600">Failed</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-purple-600">{overallCoverage}%</div>
            <div className="text-sm text-gray-600">Coverage</div>
          </div>
        </Card>
      </div>

      {/* Test Suites */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Test Suites</h2>
        <div className="space-y-4">
          {testSuites.map((test) => (
            <div key={test.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    {getCategoryIcon(test.category)}
                    <h3 className="text-lg font-medium">{test.name}</h3>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(test.status)}`}>
                      {test.status.toUpperCase()}
                    </span>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      test.priority === 'high' ? 'bg-red-100 text-red-800' :
                      test.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-blue-100 text-blue-800'
                    }`}>
                      {test.priority.toUpperCase()}
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">{test.description}</p>
                  
                  <div className="grid grid-cols-4 gap-4 text-sm">
                    <div>
                      <span className="text-gray-500">Tests:</span>
                      <span className="ml-1 font-medium">{test.totalTests}</span>
                    </div>
                    <div>
                      <span className="text-gray-500">Passed:</span>
                      <span className="ml-1 font-medium text-green-600">{test.passedTests}</span>
                    </div>
                    <div>
                      <span className="text-gray-500">Failed:</span>
                      <span className="ml-1 font-medium text-red-600">{test.failedTests}</span>
                    </div>
                    <div>
                      <span className="text-gray-500">Coverage:</span>
                      <span className="ml-1 font-medium">{test.coverage}%</span>
                    </div>
                  </div>
                </div>
                
                <div className="text-right ml-4 space-y-2">
                  <div className="text-sm text-gray-600">
                    Duration: {test.duration}s
                  </div>
                  <div className="text-sm text-gray-600">
                    Last Run: {test.lastRun}
                  </div>
                  <div className="flex space-x-2">
                    <Button
                      size="sm"
                      onClick={() => runTestSuite(test.id)}
                      disabled={activeTests.includes(test.id)}
                      className={activeTests.includes(test.id) ? 'bg-purple-600' : 'bg-blue-600'}
                    >
                      {activeTests.includes(test.id) ? (
                        <>
                          <Activity className="w-4 h-4 mr-2 animate-pulse" />
                          Running...
                        </>
                      ) : (
                        <>
                          <Play className="w-4 h-4 mr-2" />
                          Run
                        </>
                      )}
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        setSelectedTest(test);
                        setIsModalOpen(true);
                      }}
                    >
                      Details
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Code Quality Metrics */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Code Quality Metrics</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {codeQualityMetrics.map((metric) => (
            <div key={metric.metric} className="p-4 border rounded-lg">
              <div className="flex justify-between items-start mb-2">
                <h3 className="font-medium text-gray-900">{metric.metric}</h3>
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(metric.status)}`}>
                  {metric.status.toUpperCase()}
                </span>
              </div>
              <div className="text-2xl font-bold text-gray-900 mb-1">
                {metric.value}
                {metric.metric.includes('Coverage') || metric.metric.includes('Rate') || metric.metric.includes('Documentation') ? '%' : ''}
              </div>
              <div className="text-sm text-gray-600 mb-2">
                Target: {metric.target}
                {metric.metric.includes('Coverage') || metric.metric.includes('Rate') || metric.metric.includes('Documentation') ? '%' : ''}
              </div>
              <div className="text-xs text-gray-500">{metric.description}</div>
              <div className="mt-2 flex items-center text-xs">
                <TrendingUp className={`w-3 h-3 mr-1 ${
                  metric.trend === 'improving' ? 'text-green-500' :
                  metric.trend === 'stable' ? 'text-blue-500' :
                  'text-red-500'
                }`} />
                {metric.trend.charAt(0).toUpperCase() + metric.trend.slice(1)}
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Performance Metrics */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Performance Metrics</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Endpoint
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Method
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Avg Response
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  P95 Response
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  RPS
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Error Rate
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {performanceMetrics.map((metric) => (
                <tr key={metric.endpoint}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {metric.endpoint}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      metric.method === 'GET' ? 'bg-green-100 text-green-800' :
                      metric.method === 'POST' ? 'bg-blue-100 text-blue-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {metric.method}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {metric.avgResponseTime}ms
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {metric.p95ResponseTime}ms
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {metric.requestsPerSecond}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {(metric.errorRate * 100).toFixed(2)}%
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(metric.status)}`}>
                      {metric.status.toUpperCase()}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>

      {/* Security Scans */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Security Scans</h2>
        <div className="space-y-4">
          {securityScans.map((scan) => (
            <div key={scan.id} className="border rounded-lg p-4">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getSeverityColor(scan.severity)}`}>
                      {scan.severity.toUpperCase()}
                    </span>
                    <span className="text-sm text-gray-500">{scan.type}</span>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      scan.status === 'resolved' ? 'bg-green-100 text-green-800' :
                      scan.status === 'fixing' ? 'bg-yellow-100 text-yellow-800' :
                      scan.status === 'investigating' ? 'bg-blue-100 text-blue-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {scan.status.toUpperCase()}
                    </span>
                  </div>
                  <h3 className="font-medium text-gray-900 mb-1">{scan.title}</h3>
                  <p className="text-sm text-gray-600 mb-2">{scan.description}</p>
                  <div className="text-xs text-gray-500">
                    <span className="font-medium">Component:</span> {scan.affectedComponent} | 
                    <span className="font-medium ml-2">Discovered:</span> {scan.discovered}
                    {scan.cve && (
                      <>
                        | <span className="font-medium ml-2">CVE:</span> {scan.cve}
                      </>
                    )}
                    {scan.cvss && (
                      <>
                        | <span className="font-medium ml-2">CVSS:</span> {scan.cvss}
                      </>
                    )}
                  </div>
                </div>
                <div className="ml-4">
                  <Button variant="outline" size="sm">
                    Investigate
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Test Details Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={`Test Suite: ${selectedTest?.name}`}
        size="lg"
      >
        {selectedTest && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Test Information</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Category:</span>
                  <span className="ml-2 font-medium">{selectedTest.category}</span>
                </div>
                <div>
                  <span className="text-gray-500">Priority:</span>
                  <span className="ml-2 font-medium">{selectedTest.priority}</span>
                </div>
                <div>
                  <span className="text-gray-500">Total Tests:</span>
                  <span className="ml-2 font-medium">{selectedTest.totalTests}</span>
                </div>
                <div>
                  <span className="text-gray-500">Coverage:</span>
                  <span className="ml-2 font-medium">{selectedTest.coverage}%</span>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Test Results</h3>
              <div className="grid grid-cols-3 gap-4 mt-2">
                <div className="text-center p-3 bg-green-50 rounded-lg">
                  <div className="text-2xl font-bold text-green-600">{selectedTest.passedTests}</div>
                  <div className="text-sm text-gray-600">Passed</div>
                </div>
                <div className="text-center p-3 bg-red-50 rounded-lg">
                  <div className="text-2xl font-bold text-red-600">{selectedTest.failedTests}</div>
                  <div className="text-sm text-gray-600">Failed</div>
                </div>
                <div className="text-center p-3 bg-blue-50 rounded-lg">
                  <div className="text-2xl font-bold text-blue-600">{selectedTest.skippedTests}</div>
                  <div className="text-sm text-gray-600">Skipped</div>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Performance</h3>
              <div className="text-sm text-gray-600">
                <div><strong>Duration:</strong> {selectedTest.duration} seconds</div>
                <div><strong>Last Run:</strong> {selectedTest.lastRun}</div>
                <div><strong>Status:</strong> {selectedTest.status}</div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Description</h3>
              <p className="text-sm text-gray-600">{selectedTest.description}</p>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default TestingDashboard; 