import React, { useState } from 'react';
import { Card, Button, Modal } from '../index';

interface Release {
  version: string;
  phase: string;
  date: string;
  status: 'completed' | 'in-progress' | 'planned';
  features: string[];
  tests: {
    total: number;
    passed: number;
    failed: number;
    coverage: number;
  };
  progress: number;
}

interface TestResult {
  component: string;
  tests: number;
  passed: number;
  failed: number;
  lastRun: string;
  status: 'passing' | 'failing' | 'not-run';
}



const AdminDashboard: React.FC = () => {
  const [selectedRelease, setSelectedRelease] = useState<Release | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  // Updated mock data with v0.0.3 completion
  const releases: Release[] = [
    {
      version: '0.0.1',
      phase: 'Alpha Foundation',
      date: '2025-08-30',
      status: 'completed',
      features: [
        'Core PHP Framework Architecture',
        'Dependency Injection Container',
        'Custom Router with Middleware',
        'Testing Infrastructure (PHPUnit)',
        'Database Schema Design',
        'API Layer Foundation',
        'Security & Authentication Services',
        'Comprehensive Documentation'
      ],
      tests: {
        total: 51,
        passed: 51,
        failed: 0,
        coverage: 90
      },
      progress: 100
    },
    {
      version: '0.0.2',
      phase: 'Alpha Enhancement',
      date: '2025-08-30',
      status: 'completed',
      features: [
        'React 18 SPA with TypeScript',
        'Tailwind CSS Integration',
        'Component Library (Button, Input, Card, Modal)',
        'Admin Dashboard Interface',
        'SPA Routing with React Router',
        'Vite Build System',
        'Asset Management & Bundling',
        'Apache SPA Configuration'
      ],
      tests: {
        total: 51,
        passed: 51,
        failed: 0,
        coverage: 90
      },
      progress: 100
    },
    {
      version: '0.0.3',
      phase: 'Alpha Enhancement',
      date: 'Q4 2025',
      status: 'completed',
      features: [
        'Enhanced Admin Dashboard',
        'Advanced Development Metrics',
        'Automated Testing Dashboard',
        'Performance Monitoring Tools',
        'Code Quality Metrics',
        'Development Workflow Tools',
        'Git Integration & Tracking',
        'System Health Diagnostics'
      ],
      tests: {
        total: 75,
        passed: 75,
        failed: 0,
        coverage: 92
      },
      progress: 100
    },
    {
      version: '0.0.4',
      phase: 'Alpha Enhancement',
      date: 'Q4 2025',
      status: 'planned',
      features: [
        'Real Database Implementation',
        'Database Migrations',
        'Core Service Functionality',
        'Data Persistence & CRUD',
        'API Endpoints with Real Data',
        'Database Testing & Optimization',
        'Backup & Recovery Systems'
      ],
      tests: {
        total: 0,
        passed: 0,
        failed: 0,
        coverage: 0
      },
      progress: 0
    },
    {
      version: '0.0.5',
      phase: 'Alpha Enhancement',
      date: 'Q1 2026',
      status: 'planned',
      features: [
        'User Registration & Login',
        'Email Verification System',
        'Password Reset Functionality',
        'User Profiles & Settings',
        'Role-based Access Control',
        'JWT Authentication',
        'Security Features (CSRF, Rate Limiting)'
      ],
      tests: {
        total: 0,
        passed: 0,
        failed: 0,
        coverage: 0
      },
      progress: 0
    },
    {
      version: '0.0.6',
      phase: 'Alpha Enhancement',
      date: 'Q1 2026',
      status: 'planned',
      features: [
        'Article Creation & Editing',
        'Markdown Support with Preview',
        'Category & Tag Management',
        'Content Versioning',
        'Basic Search Functionality',
        'Content Moderation Tools',
        'Admin Panel Interface'
      ],
      tests: {
        total: 0,
        passed: 0,
        failed: 0,
        coverage: 0
      },
      progress: 0
    },
    {
      version: '0.0.7',
      phase: 'Alpha Enhancement',
      date: 'Q2 2026',
      status: 'planned',
      features: [
        'Rich Text Editor',
        'Content Templates & Layouts',
        'Advanced Search with Filters',
        'Content Recommendations',
        'Media Upload & Management',
        'Performance Optimization',
        'Mobile Responsiveness'
      ],
      tests: {
        total: 0,
        passed: 0,
        failed: 0,
        coverage: 0
      },
      progress: 0
    },
    {
      version: '0.0.8',
      phase: 'Alpha Enhancement',
      date: 'Q2 2026',
      status: 'planned',
      features: [
        'Complete Feature Testing',
        'Performance Optimization',
        'Security Audit',
        'Documentation Completion',
        'User Acceptance Testing',
        'Beta Release Preparation',
        'Production Readiness Assessment'
      ],
      tests: {
        total: 0,
        passed: 0,
        failed: 0,
        coverage: 0
      },
      progress: 0
    },
    {
      version: '0.1.0',
      phase: 'Beta Release',
      date: 'Q2 2026',
      status: 'planned',
      features: [
        'Complete Working System',
        'User Management & Authentication',
        'Content Management System',
        'Admin Panel & Moderation',
        'API Documentation',
        'Performance Monitoring',
        'Security Hardening'
      ],
      tests: {
        total: 0,
        passed: 0,
        failed: 0,
        coverage: 0
      },
      progress: 0
    }
  ];

  const testResults: TestResult[] = [
    {
      component: 'Container (DI)',
      tests: 12,
      passed: 12,
      failed: 0,
      lastRun: '2025-08-30',
      status: 'passing'
    },
    {
      component: 'Router',
      tests: 15,
      passed: 15,
      failed: 0,
      lastRun: '2025-08-30',
      status: 'passing'
    },
    {
      component: 'FileCache',
      tests: 15,
      passed: 15,
      failed: 0,
      lastRun: '2025-08-30',
      status: 'passing'
    },
    {
      component: 'Database',
      tests: 9,
      passed: 9,
      failed: 0,
      lastRun: '2025-08-30',
      status: 'passing'
    },
    {
      component: 'Admin Dashboard',
      tests: 24,
      passed: 24,
      failed: 0,
      lastRun: '2025-08-30',
      status: 'passing'
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in-progress':
        return 'bg-yellow-100 text-yellow-800';
      case 'planned':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getTestStatusColor = (status: string) => {
    switch (status) {
      case 'passing':
        return 'bg-green-100 text-green-800';
      case 'failing':
        return 'bg-red-100 text-red-800';
      case 'not-run':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getPhaseColor = (phase: string) => {
    switch (phase) {
      case 'Alpha Foundation':
        return 'bg-purple-100 text-purple-800';
      case 'Alpha Enhancement':
        return 'bg-blue-100 text-blue-800';
      case 'Beta Release':
        return 'bg-orange-100 text-orange-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const openReleaseDetails = (release: Release) => {
    setSelectedRelease(release);
    setIsModalOpen(true);
  };

  // Calculate overall progress
  const overallProgress = releases.reduce((sum, r) => sum + r.progress, 0) / releases.length;
  const completedReleases = releases.filter(r => r.status === 'completed').length;
  const inProgressReleases = releases.filter(r => r.status === 'in-progress').length;
  const plannedReleases = releases.filter(r => r.status === 'planned').length;



  const renderOverview = () => (
    <div className="space-y-8">
      {/* Overview Stats */}
      <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">
              {completedReleases}
            </div>
            <div className="text-sm text-gray-600">Completed</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-yellow-600">
              {inProgressReleases}
            </div>
            <div className="text-sm text-gray-600">In Progress</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">
              {plannedReleases}
            </div>
            <div className="text-sm text-gray-600">Planned</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-purple-600">
              {testResults.reduce((sum, t) => sum + t.tests, 0)}
            </div>
            <div className="text-sm text-gray-600">Total Tests</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">
              {Math.round(overallProgress)}%
            </div>
            <div className="text-sm text-gray-600">Overall Progress</div>
          </div>
        </Card>
      </div>

      {/* Overall Progress Bar */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Alpha Development Progress</h2>
        <div className="space-y-4">
          <div>
            <div className="flex justify-between text-sm text-gray-600 mb-2">
              <span>Alpha Phase Completion (v0.0.1 → v0.0.8)</span>
              <span>{Math.round(overallProgress)}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-3">
              <div 
                className="bg-gradient-to-r from-purple-600 via-blue-600 to-green-600 h-3 rounded-full transition-all duration-500" 
                style={{ width: `${overallProgress}%` }}
              ></div>
            </div>
            <div className="flex justify-between text-xs text-gray-500 mt-1">
              <span>Foundation</span>
              <span>Enhancement</span>
              <span>Beta Prep</span>
            </div>
          </div>
        </div>
      </Card>

      {/* Releases Section */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Alpha Release Management</h2>
        <div className="space-y-4">
          {releases.map((release) => (
            <div key={release.version} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    <h3 className="text-lg font-medium">v{release.version}</h3>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getPhaseColor(release.phase)}`}>
                      {release.phase}
                    </span>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(release.status)}`}>
                      {release.status.replace('-', ' ').toUpperCase()}
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">Target: {release.date}</p>
                  
                  {/* Progress bar for each release */}
                  <div className="mb-3">
                    <div className="flex justify-between text-xs text-gray-600 mb-1">
                      <span>Progress</span>
                      <span>{release.progress}%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full transition-all duration-500 ${
                          release.progress === 100 ? 'bg-green-600' : 
                          release.progress > 0 ? 'bg-yellow-600' : 'bg-gray-300'
                        }`}
                        style={{ width: `${release.progress}%` }}
                      ></div>
                    </div>
                  </div>
                </div>
                
                <div className="text-right ml-4">
                  <div className="text-sm text-gray-600 mb-2">
                    Tests: {release.tests.passed}/{release.tests.total}
                  </div>
                  <div className="text-sm text-gray-600 mb-2">
                    Coverage: {release.tests.coverage}%
                  </div>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => openReleaseDetails(release)}
                  >
                    View Details
                  </Button>
                </div>
              </div>
              
              <div className="mt-3">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Key Features:</h4>
                <ul className="text-sm text-gray-600 space-y-1">
                  {release.features.slice(0, 4).map((feature, index) => (
                    <li key={index} className="flex items-center">
                      <span className="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                      {feature}
                    </li>
                  ))}
                  {release.features.length > 4 && (
                    <li className="text-xs text-gray-500 italic">
                      +{release.features.length - 4} more features...
                    </li>
                  )}
                </ul>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Test Results Section */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Test Results</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Component
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tests
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Passed
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Failed
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Last Run
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {testResults.map((result) => (
                <tr key={result.component}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {result.component}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {result.tests}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span className="text-green-600 font-medium">{result.passed}</span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span className="text-red-600 font-medium">{result.failed}</span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {result.lastRun}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getTestStatusColor(result.status)}`}>
                      {result.status.toUpperCase()}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>

      {/* Alpha Phase Summary */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Alpha Phase Summary</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="text-center p-4 bg-purple-50 rounded-lg">
            <div className="text-2xl font-bold text-purple-600">3/8</div>
            <div className="text-sm text-gray-600">Alpha Releases Complete</div>
            <div className="text-xs text-gray-500 mt-1">Foundation, Frontend & Admin</div>
          </div>
          
          <div className="text-center p-4 bg-blue-50 rounded-lg">
            <div className="text-2xl font-bold text-blue-600">0/8</div>
            <div className="text-sm text-gray-600">Currently In Progress</div>
            <div className="text-xs text-gray-500 mt-1">All v0.0.3 features complete</div>
          </div>
          
          <div className="text-center p-4 bg-green-50 rounded-lg">
            <div className="text-2xl font-bold text-green-600">Q2 2026</div>
            <div className="text-sm text-gray-600">Beta Target</div>
            <div className="text-xs text-gray-500 mt-1">v0.1.0 Release</div>
          </div>
        </div>
      </Card>
    </div>
  );

  return (
    <div className="space-y-8">
      {renderOverview()}

      {/* Release Details Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={`Release v${selectedRelease?.version} Details`}
        size="lg"
      >
        {selectedRelease && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Release Information</h3>
              <p className="text-sm text-gray-600">
                <strong>Version:</strong> v{selectedRelease.version}<br />
                <strong>Phase:</strong> {selectedRelease.phase}<br />
                <strong>Target Date:</strong> {selectedRelease.date}<br />
                <strong>Status:</strong> {selectedRelease.status.replace('-', ' ').toUpperCase()}<br />
                <strong>Progress:</strong> {selectedRelease.progress}%
              </p>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Test Results</h3>
              <div className="grid grid-cols-3 gap-4 mt-2">
                <div className="text-center p-3 bg-gray-50 rounded-lg">
                  <div className="text-2xl font-bold text-green-600">{selectedRelease.tests.passed}</div>
                  <div className="text-sm text-gray-600">Passed</div>
                </div>
                <div className="text-center p-3 bg-gray-50 rounded-lg">
                  <div className="text-2xl font-bold text-red-600">{selectedRelease.tests.failed}</div>
                  <div className="text-sm text-gray-600">Failed</div>
                </div>
                <div className="text-center p-3 bg-gray-50 rounded-lg">
                  <div className="text-2xl font-bold text-blue-600">{selectedRelease.tests.coverage}%</div>
                  <div className="text-sm text-gray-600">Coverage</div>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Features</h3>
              <ul className="mt-2 space-y-1">
                {selectedRelease.features.map((feature, index) => (
                  <li key={index} className="flex items-center text-sm text-gray-600">
                    <span className="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                    {feature}
                  </li>
                ))}
              </ul>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default AdminDashboard; 