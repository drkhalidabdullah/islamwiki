import React, { useState } from 'react';
import { Card, Button, Modal } from '../index';
import { GitBranch, GitCommit, GitPullRequest, GitMerge, Code } from 'lucide-react';

interface GitActivity {
  id: string;
  type: 'commit' | 'branch' | 'merge' | 'pull-request';
  author: string;
  message: string;
  timestamp: string;
  branch: string;
  status: 'success' | 'pending' | 'failed';
  hash?: string;
  filesChanged?: number;
}

interface Deployment {
  id: string;
  environment: 'development' | 'staging' | 'production';
  version: string;
  status: 'deploying' | 'success' | 'failed' | 'rolled-back';
  timestamp: string;
  duration: number;
  author: string;
  commit: string;
  changes: string[];
}

interface TeamMember {
  id: string;
  name: string;
  role: string;
  avatar: string;
  status: 'online' | 'offline' | 'busy';
  currentTask: string;
  lastActivity: string;
  commits: number;
  pullRequests: number;
}

const DevelopmentWorkflow: React.FC = () => {
  const [selectedDeployment, setSelectedDeployment] = useState<Deployment | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  // Mock data for development workflow
  const gitActivities: GitActivity[] = [
    {
      id: 'git-001',
      type: 'commit',
      author: 'Khalid Abdullah',
      message: 'feat: Enhanced admin dashboard with testing tools',
      timestamp: '2025-08-30 15:30:00',
      branch: 'feature/v0.0.3-admin-enhancement',
      status: 'success',
      hash: 'a1b2c3d4',
      filesChanged: 12
    },
    {
      id: 'git-002',
      type: 'pull-request',
      author: 'Khalid Abdullah',
      message: 'Merge feature/v0.0.3-admin-enhancement into develop',
      timestamp: '2025-08-30 15:25:00',
      branch: 'develop',
      status: 'pending'
    },
    {
      id: 'git-003',
      type: 'merge',
      author: 'Khalid Abdullah',
      message: 'Merge develop into main for v0.0.2 release',
      timestamp: '2025-08-30 14:45:00',
      branch: 'main',
      status: 'success'
    },
    {
      id: 'git-004',
      type: 'commit',
      author: 'Khalid Abdullah',
      message: 'fix: Resolve SPA routing issues in Apache config',
      timestamp: '2025-08-30 14:30:00',
      branch: 'hotfix/routing-fix',
      status: 'success',
      hash: 'e5f6g7h8',
      filesChanged: 3
    }
  ];

  const deployments: Deployment[] = [
    {
      id: 'deploy-001',
      environment: 'production',
      version: 'v0.0.2',
      status: 'success',
      timestamp: '2025-08-30 14:00:00',
      duration: 45,
      author: 'Khalid Abdullah',
      commit: 'a1b2c3d4',
      changes: [
        'Enhanced admin dashboard',
        'React 18 SPA implementation',
        'Tailwind CSS integration',
        'Component library updates'
      ]
    },
    {
      id: 'deploy-002',
      environment: 'staging',
      version: 'v0.0.3-dev',
      status: 'deploying',
      timestamp: '2025-08-30 15:45:00',
      duration: 0,
      author: 'Khalid Abdullah',
      commit: 'i9j0k1l2',
      changes: [
        'Testing dashboard implementation',
        'Performance monitoring tools',
        'Development workflow integration'
      ]
    },
    {
      id: 'deploy-003',
      environment: 'development',
      version: 'v0.0.3-feature',
      status: 'success',
      timestamp: '2025-08-30 15:00:00',
      duration: 23,
      author: 'Khalid Abdullah',
      commit: 'm3n4o5p6',
      changes: [
        'Testing framework setup',
        'Performance metrics collection',
        'Admin panel enhancements'
      ]
    }
  ];

  const teamMembers: TeamMember[] = [
    {
      id: 'member-001',
      name: 'Khalid Abdullah',
      role: 'Lead Developer',
      avatar: 'KA',
      status: 'online',
      currentTask: 'Implementing v0.0.3 testing tools',
      lastActivity: '2025-08-30 15:45:00',
      commits: 156,
      pullRequests: 23
    },
    {
      id: 'member-002',
      name: 'Development Team',
      role: 'Contributors',
      avatar: 'DT',
      status: 'offline',
      currentTask: 'Code review and testing',
      lastActivity: '2025-08-30 14:30:00',
      commits: 89,
      pullRequests: 12
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'success':
        return 'bg-green-100 text-green-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'deploying':
        return 'bg-blue-100 text-blue-800';
      case 'rolled-back':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getGitTypeIcon = (type: string) => {
    switch (type) {
      case 'commit':
        return <GitCommit className="w-4 h-4" />;
      case 'branch':
        return <GitBranch className="w-4 h-4" />;
      case 'merge':
        return <GitMerge className="w-4 h-4" />;
      case 'pull-request':
        return <GitPullRequest className="w-4 h-4" />;
      default:
        return <Code className="w-4 h-4" />;
    }
  };

  const getEnvironmentColor = (environment: string) => {
    switch (environment) {
      case 'production':
        return 'bg-red-100 text-red-800';
      case 'staging':
        return 'bg-yellow-100 text-yellow-800';
      case 'development':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const openDeploymentDetails = (deployment: Deployment) => {
    setSelectedDeployment(deployment);
    setIsModalOpen(true);
  };

  // Calculate workflow metrics
  const totalCommits = gitActivities.filter(activity => activity.type === 'commit').length;
  const successfulDeployments = deployments.filter(deploy => deploy.status === 'success').length;
  const activeDeployments = deployments.filter(deploy => deploy.status === 'deploying').length;
  const teamOnline = teamMembers.filter(member => member.status === 'online').length;

  return (
    <div className="max-w-7xl mx-auto p-6 space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Development Workflow</h1>
          <p className="text-lg text-gray-600 mt-1">Git Integration, Deployment Tracking & Team Collaboration</p>
        </div>
        <div className="flex space-x-2">
          <Button variant="outline">
            <GitBranch className="w-4 h-4 mr-2" />
            Create Branch
          </Button>
          <Button variant="outline">
            <GitPullRequest className="w-4 h-4 mr-2" />
            New PR
          </Button>
          <Button>
            <Code className="w-4 h-4 mr-2" />
            Deploy
          </Button>
        </div>
      </div>

      {/* Workflow Overview */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">{totalCommits}</div>
            <div className="text-sm text-gray-600">Today's Commits</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">{successfulDeployments}</div>
            <div className="text-sm text-gray-600">Successful Deployments</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-yellow-600">{activeDeployments}</div>
            <div className="text-sm text-gray-600">Active Deployments</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-purple-600">{teamOnline}</div>
            <div className="text-sm text-gray-600">Team Online</div>
          </div>
        </Card>
      </div>

      {/* Git Activity */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Recent Git Activity</h2>
        <div className="space-y-4">
          {gitActivities.map((activity) => (
            <div key={activity.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    {getGitTypeIcon(activity.type)}
                    <h3 className="font-medium text-gray-900">{activity.message}</h3>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(activity.status)}`}>
                      {activity.status.toUpperCase()}
                    </span>
                  </div>
                  <div className="text-sm text-gray-600 mb-2">
                    <span className="font-medium">Author:</span> {activity.author} | 
                    <span className="font-medium ml-2">Branch:</span> {activity.branch} |
                    <span className="font-medium ml-2">Time:</span> {activity.timestamp}
                  </div>
                  {activity.hash && (
                    <div className="text-xs text-gray-500 font-mono">
                      Hash: {activity.hash}
                    </div>
                  )}
                  {activity.filesChanged && (
                    <div className="text-xs text-gray-500">
                      Files changed: {activity.filesChanged}
                    </div>
                  )}
                </div>
                <div className="ml-4">
                  <Button variant="outline" size="sm">
                    View Details
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Deployments */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Deployment Status</h2>
        <div className="space-y-4">
          {deployments.map((deployment) => (
            <div key={deployment.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getEnvironmentColor(deployment.environment)}`}>
                      {deployment.environment.toUpperCase()}
                    </span>
                    <h3 className="font-medium text-gray-900">v{deployment.version}</h3>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(deployment.status)}`}>
                      {deployment.status.toUpperCase()}
                    </span>
                  </div>
                  <div className="text-sm text-gray-600 mb-2">
                    <span className="font-medium">Author:</span> {deployment.author} | 
                    <span className="font-medium ml-2">Commit:</span> {deployment.commit} |
                    <span className="font-medium ml-2">Time:</span> {deployment.timestamp}
                  </div>
                  <div className="text-sm text-gray-600">
                    <span className="font-medium">Duration:</span> {deployment.duration}s
                  </div>
                  <div className="mt-2">
                    <h4 className="text-sm font-medium text-gray-700 mb-1">Changes:</h4>
                    <ul className="text-xs text-gray-600 space-y-1">
                      {deployment.changes.slice(0, 3).map((change, index) => (
                        <li key={index} className="flex items-center">
                          <span className="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                          {change}
                        </li>
                      ))}
                      {deployment.changes.length > 3 && (
                        <li className="text-xs text-gray-500 italic">
                          +{deployment.changes.length - 3} more changes...
                        </li>
                      )}
                    </ul>
                  </div>
                </div>
                <div className="ml-4">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => openDeploymentDetails(deployment)}
                  >
                    View Details
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Team Collaboration */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Team Collaboration</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {teamMembers.map((member) => (
            <div key={member.id} className="border rounded-lg p-4">
              <div className="flex items-center space-x-3 mb-3">
                <div className={`w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-medium ${
                  member.status === 'online' ? 'ring-2 ring-green-400' :
                  member.status === 'busy' ? 'ring-2 ring-yellow-400' :
                  'ring-2 ring-gray-400'
                }`}>
                  {member.avatar}
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{member.name}</h3>
                  <p className="text-sm text-gray-600">{member.role}</p>
                </div>
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                  member.status === 'online' ? 'bg-green-100 text-green-800' :
                  member.status === 'busy' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-gray-100 text-gray-800'
                }`}>
                  {member.status.toUpperCase()}
                </span>
              </div>
              <div className="space-y-2 text-sm">
                <div>
                  <span className="text-gray-500">Current Task:</span>
                  <span className="ml-2 text-gray-900">{member.currentTask}</span>
                </div>
                <div>
                  <span className="text-gray-500">Last Activity:</span>
                  <span className="ml-2 text-gray-900">{member.lastActivity}</span>
                </div>
                <div className="grid grid-cols-2 gap-4 mt-3">
                  <div className="text-center p-2 bg-gray-50 rounded">
                    <div className="text-lg font-bold text-blue-600">{member.commits}</div>
                    <div className="text-xs text-gray-600">Commits</div>
                  </div>
                  <div className="text-center p-2 bg-gray-50 rounded">
                    <div className="text-lg font-bold text-green-600">{member.pullRequests}</div>
                    <div className="text-xs text-gray-600">PRs</div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Deployment Details Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={`Deployment: ${selectedDeployment?.version}`}
        size="lg"
      >
        {selectedDeployment && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Deployment Information</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Environment:</span>
                  <span className={`ml-2 inline-flex px-2 py-1 text-xs font-medium rounded-full ${getEnvironmentColor(selectedDeployment.environment)}`}>
                    {selectedDeployment.environment.toUpperCase()}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Version:</span>
                  <span className="ml-2 font-medium">v{selectedDeployment.version}</span>
                </div>
                <div>
                  <span className="text-gray-500">Status:</span>
                  <span className={`ml-2 inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(selectedDeployment.status)}`}>
                    {selectedDeployment.status.toUpperCase()}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Duration:</span>
                  <span className="ml-2 font-medium">{selectedDeployment.duration}s</span>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Changes Deployed</h3>
              <ul className="mt-2 space-y-1">
                {selectedDeployment.changes.map((change, index) => (
                  <li key={index} className="flex items-center text-sm text-gray-600">
                    <span className="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                    {change}
                  </li>
                ))}
              </ul>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Actions</h3>
              <div className="flex space-x-2 mt-2">
                <Button size="sm">View Logs</Button>
                <Button variant="outline" size="sm">Rollback</Button>
                <Button variant="outline" size="sm">Redeploy</Button>
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default DevelopmentWorkflow; 