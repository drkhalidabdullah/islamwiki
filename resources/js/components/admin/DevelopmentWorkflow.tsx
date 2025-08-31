import React, { useState, useEffect } from 'react';
import Modal from '../ui/Modal';
import { 
  GitBranch, 
  GitCommit, 
  GitPullRequest, 
  GitMerge, 
  Users, 
  Calendar, 
  FileText,
  RefreshCw
} from 'lucide-react';

interface GitActivity {
  id: string;
  type: 'commit' | 'pull-request' | 'merge' | 'branch';
  author: string;
  message: string;
  timestamp: string;
  branch: string;
  status: 'success' | 'pending' | 'failed';
  hash?: string;
  filesChanged?: number;
  additions?: number;
  deletions?: number;
}

interface Deployment {
  id: string;
  environment: string;
  version: string;
  status: 'success' | 'pending' | 'failed' | 'deploying';
  author: string;
  timestamp: string;
  duration: number;
  commit: string;
  changes: string[];
  logs?: string[];
}

interface BuildStatus {
  id: string;
  branch: string;
  status: 'success' | 'pending' | 'failed' | 'building';
  timestamp: string;
  duration: number;
  passed: number;
  total: number;
  coverage: number;
}

interface TeamMember {
  id: string;
  name: string;
  role: string;
  status: 'online' | 'offline' | 'away';
  currentTask: string;
  commits: number;
  prs: number;
  branches: number;
}

const DevelopmentWorkflow: React.FC = () => {
  const [gitActivities, setGitActivities] = useState<GitActivity[]>([]);
  const [deployments, setDeployments] = useState<Deployment[]>([]);
  const [buildStatuses, setBuildStatuses] = useState<BuildStatus[]>([]);
  const [teamMembers, setTeamMembers] = useState<TeamMember[]>([
    {
      id: '1',
      name: 'Khalid Abdullah',
      role: 'Lead Developer',
      status: 'online',
      currentTask: 'Implementing v0.0.4 database features',
      commits: 0,
      prs: 0,
      branches: 0
    }
  ]);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [selectedDeployment, setSelectedDeployment] = useState<Deployment | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedGitActivity, setSelectedGitActivity] = useState<GitActivity | null>(null);
  const [isGitModalOpen, setIsGitModalOpen] = useState(false);
  const [showAllGitActivities, setShowAllGitActivities] = useState(false);
  const [showAllFiles, setShowAllFiles] = useState(false);
  const [showFullDiff, setShowFullDiff] = useState(false);

  useEffect(() => {
    refreshData();
  }, []);

  const refreshData = async () => {
    setIsRefreshing(true);
    
    try {
      // Simulate collecting real Git data
      // In a real implementation, this would call Git APIs or execute Git commands
      
      const newGitActivities: GitActivity[] = [
        {
          id: `git-${Date.now()}-1`,
          type: 'commit',
          author: 'Khalid Abdullah',
          message: 'feat: Implement real testing dashboard functionality',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'feature/v0.0.3-real-testing',
          status: 'success',
          hash: Math.random().toString(36).substring(2, 10),
          filesChanged: Math.floor(Math.random() * 20) + 1,
          additions: Math.floor(Math.random() * 500) + 50,
          deletions: Math.floor(Math.random() * 200) + 10
        },
        {
          id: `git-${Date.now()}-2`,
          type: 'pull-request',
          author: 'Khalid Abdullah',
          message: 'Merge feature/v0.0.3-real-testing into develop',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'develop',
          status: 'pending',
          filesChanged: Math.floor(Math.random() * 8) + 2,
          additions: Math.floor(Math.random() * 150) + 20,
          deletions: Math.floor(Math.random() * 50) + 5
        },
        {
          id: `git-${Date.now()}-3`,
          type: 'merge',
          author: 'Khalid Abdullah',
          message: 'Merge develop into main for v0.0.3 release',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'main',
          status: 'success',
          filesChanged: Math.floor(Math.random() * 12) + 3,
          additions: Math.floor(Math.random() * 200) + 30,
          deletions: Math.floor(Math.random() * 80) + 10
        },
        {
          id: `git-${Date.now()}-4`,
          type: 'commit',
          author: 'Khalid Abdullah',
          message: 'fix: Resolve System Health history collection issue',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'feature/v0.0.3-system-health',
          status: 'success',
          hash: Math.random().toString(36).substring(2, 10),
          filesChanged: Math.floor(Math.random() * 15) + 1,
          additions: Math.floor(Math.random() * 300) + 30,
          deletions: Math.floor(Math.random() * 100) + 5
        },
        {
          id: `git-${Date.now()}-5`,
          type: 'branch',
          author: 'Khalid Abdullah',
          message: 'Create feature branch for v0.0.4 development',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'feature/v0.0.4-database',
          status: 'success',
          filesChanged: 0, // New branch, no files changed yet
          additions: 0,
          deletions: 0
        },
        {
          id: `git-${Date.now()}-6`,
          type: 'commit',
          author: 'Khalid Abdullah',
          message: 'feat: Add comprehensive performance averages to System Health',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'feature/v0.0.3-system-health',
          status: 'success',
          hash: Math.random().toString(36).substring(2, 10),
          filesChanged: Math.floor(Math.random() * 25) + 1,
          additions: Math.floor(Math.random() * 600) + 80,
          deletions: Math.floor(Math.random() * 150) + 10
        },
        {
          id: `git-${Date.now()}-7`,
          type: 'pull-request',
          author: 'Khalid Abdullah',
          message: 'Enhance Performance Monitor with trend analysis',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'feature/v0.0.3-performance',
          status: 'success',
          filesChanged: Math.floor(Math.random() * 18) + 5,
          additions: Math.floor(Math.random() * 400) + 60,
          deletions: Math.floor(Math.random() * 120) + 15
        },
        {
          id: `git-${Date.now()}-8`,
          type: 'commit',
          author: 'Khalid Abdullah',
          message: 'docs: Update release notes for v0.0.3 completion',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'main',
          status: 'success',
          hash: Math.random().toString(36).substring(2, 10),
          filesChanged: Math.floor(Math.random() * 10) + 1,
          additions: Math.floor(Math.random() * 200) + 20,
          deletions: Math.floor(Math.random() * 50) + 5
        }
      ];
      
      setGitActivities(newGitActivities);
      
      // Update team member stats
      setTeamMembers(prev => prev.map(member => ({
        ...member,
        commits: newGitActivities.filter(activity => 
          activity.type === 'commit' && activity.status === 'success'
        ).length,
        prs: newGitActivities.filter(activity => 
          activity.type === 'pull-request'
        ).length,
        branches: newGitActivities.filter(activity => 
          activity.type === 'branch'
        ).length
      })));

      // Simulate deployment data
      const newDeployments: Deployment[] = [
        {
          id: `deploy-${Date.now()}-1`,
          environment: 'production',
          version: 'v0.0.3',
          status: 'success',
          author: 'Khalid Abdullah',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          duration: Math.floor(Math.random() * 300) + 60,
          commit: Math.random().toString(36).substring(2, 10),
          changes: [
            'Enhanced testing dashboard with real-time execution',
            'Added comprehensive performance monitoring',
            'Implemented system health diagnostics',
            'Improved development workflow tracking'
          ],
          logs: [
            'Starting deployment to production...',
            'Building application...',
            'Running tests...',
            'Deploying to production servers...',
            'Health checks passed',
            'Deployment completed successfully'
          ]
        },
        {
          id: `deploy-${Date.now()}-2`,
          environment: 'staging',
          version: 'v0.0.4-alpha',
          status: 'deploying',
          author: 'Khalid Abdullah',
          timestamp: new Date(Date.now() - Math.random() * 3600000).toISOString(),
          duration: 0,
          commit: Math.random().toString(36).substring(2, 10),
          changes: [
            'Database optimization features',
            'Enhanced security middleware',
            'Performance improvements'
          ]
        }
      ];
      
      setDeployments(newDeployments);

      // Simulate build statuses
      const newBuildStatuses: BuildStatus[] = [
        {
          id: `build-${Date.now()}-1`,
          branch: 'feature/v0.0.3-real-testing',
          status: 'success',
          timestamp: new Date(Date.now() - Math.random() * 3600000).toISOString(),
          duration: Math.floor(Math.random() * 300) + 120,
          passed: Math.floor(Math.random() * 45) + 45,
          total: 75,
          coverage: Math.floor(Math.random() * 20) + 75
        },
        {
          id: `build-${Date.now()}-2`,
          branch: 'develop',
          status: 'building',
          timestamp: new Date(Date.now() - Math.random() * 1800000).toISOString(),
          duration: 0,
          passed: 0,
          total: 0,
          coverage: 0
        }
      ];
      
      setBuildStatuses(newBuildStatuses);

    } catch (error) {
      console.error('Error refreshing data:', error);
    } finally {
      setIsRefreshing(false);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'success': return 'bg-green-100 text-green-800';
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'failed': return 'bg-red-100 text-red-800';
      case 'deploying':
      case 'building': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getEnvironmentColor = (environment: string) => {
    switch (environment) {
      case 'production': return 'bg-red-100 text-red-800';
      case 'staging': return 'bg-yellow-100 text-yellow-800';
      case 'development': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'commit': return <GitCommit className="w-4 h-4" />;
      case 'pull-request': return <GitPullRequest className="w-4 h-4" />;
      case 'merge': return <GitMerge className="w-4 h-4" />;
      case 'branch': return <GitBranch className="w-4 h-4" />;
      default: return <GitCommit className="w-4 h-4" />;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Development Workflow</h2>
              <p className="text-gray-600">Track Git activities, deployments, and team collaboration</p>
            </div>
            <button
              onClick={refreshData}
              disabled={isRefreshing}
              className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center space-x-2"
            >
              <RefreshCw className={`w-4 h-4 ${isRefreshing ? 'animate-spin' : ''}`} />
              <span>{isRefreshing ? 'Refreshing...' : 'Refresh'}</span>
            </button>
          </div>
        </div>
      </div>

      {/* Git Activities */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-medium text-gray-900">Recent Git Activities</h3>
            <button
              onClick={() => setShowAllGitActivities(!showAllGitActivities)}
              className="text-blue-600 hover:text-blue-700 text-sm font-medium"
            >
              {showAllGitActivities ? 'Show Less' : 'Show More'}
            </button>
          </div>
        </div>
        <div className="p-6">
          <div className="space-y-4">
            {gitActivities.slice(0, showAllGitActivities ? gitActivities.length : 3).map((activity) => (
              <div key={activity.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-4">
                  <div className="flex items-center space-x-2">
                    {getActivityIcon(activity.type)}
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(activity.status)}`}>
                      {activity.status}
                    </span>
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">{activity.message}</p>
                    <div className="flex items-center space-x-4 text-sm text-gray-500 mt-1">
                      <span className="flex items-center space-x-1">
                        <GitBranch className="w-3 h-3" />
                        <span>{activity.branch}</span>
                      </span>
                      <span className="flex items-center space-x-1">
                        <Users className="w-3 h-3" />
                        <span>{activity.author}</span>
                      </span>
                      <span className="flex items-center space-x-1">
                        <Calendar className="w-3 h-3" />
                        <span>{new Date(activity.timestamp).toLocaleDateString()}</span>
                      </span>
                      {activity.filesChanged && (
                        <span className="flex items-center space-x-1">
                          <FileText className="w-3 h-3" />
                          <span>Files: {activity.filesChanged} | +{activity.additions || 0} | -{activity.deletions || 0}</span>
                        </span>
                      )}
                    </div>
                  </div>
                </div>
                <div className="flex items-center space-x-2">
                  <button
                    onClick={() => {
                      setSelectedGitActivity(activity);
                      setIsGitModalOpen(true);
                    }}
                    className="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
                  >
                    Details
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Deployments and Builds */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Deployments */}
        <div className="bg-white rounded-lg shadow">
          <div className="p-6 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Recent Deployments</h3>
          </div>
          <div className="p-6">
            <div className="space-y-4">
              {deployments.map((deployment) => (
                <div key={deployment.id} className="p-4 bg-gray-50 rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <h4 className="font-medium text-gray-900">
                      Deployment to {deployment.environment} ({deployment.version})
                    </h4>
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(deployment.status)}`}>
                      {deployment.status}
                    </span>
                  </div>
                  <div className="text-sm text-gray-600 space-y-1">
                    <p>Author: {deployment.author}</p>
                    <p>Duration: {deployment.duration > 0 ? `${deployment.duration}s` : 'In progress...'}</p>
                    <p>Commit: {deployment.commit}</p>
                    <div className="mt-2">
                      <p className="font-medium mb-1">Changes:</p>
                      <ul className="list-disc list-inside space-y-1 text-xs">
                        {deployment.changes.map((change, index) => (
                          <li key={index}>{change}</li>
                        ))}
                      </ul>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2 mt-3">
                    <button
                      onClick={() => {
                        setSelectedDeployment(deployment);
                        setIsModalOpen(true);
                      }}
                      className="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
                    >
                      Details
                    </button>
                    {deployment.status === 'success' && (
                      <button className="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                        Rollback
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Build Statuses */}
        <div className="bg-white rounded-lg shadow">
          <div className="p-6 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Build Status</h3>
          </div>
          <div className="p-6">
            <div className="space-y-4">
              {buildStatuses.map((build) => (
                <div key={build.id} className="p-4 bg-gray-50 rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <h4 className="font-medium text-gray-900">Build for {build.branch}</h4>
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(build.status)}`}>
                      {build.status === 'building' ? 'Building...' : build.status}
                    </span>
                  </div>
                  <div className="text-sm text-gray-600 space-y-1">
                    <p>Tests: {build.passed}/{build.total} passed</p>
                    <p>Coverage: {build.coverage}%</p>
                    <p>Duration: {build.duration > 0 ? `${build.duration}s` : 'In progress...'}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Team Members */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">Team Members</h3>
        </div>
        <div className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {teamMembers.map((member) => (
              <div key={member.id} className="p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-3 mb-3">
                  <div className={`w-3 h-3 rounded-full ${
                    member.status === 'online' ? 'bg-green-500' : 
                    member.status === 'away' ? 'bg-yellow-500' : 'bg-gray-500'
                  }`} />
                  <div>
                    <h4 className="font-medium text-gray-900">{member.name}</h4>
                    <p className="text-sm text-gray-600">{member.role}</p>
                  </div>
                </div>
                <div className="text-sm text-gray-600 space-y-1">
                  <p>Current Task: {member.currentTask}</p>
                  <div className="flex items-center space-x-4 mt-2 text-xs">
                    <span className="flex items-center space-x-1">
                      <GitCommit className="w-3 h-3" />
                      <span>{member.commits}</span>
                    </span>
                    <span className="flex items-center space-x-1">
                      <GitPullRequest className="w-3 h-3" />
                      <span>{member.prs}</span>
                    </span>
                    <span className="flex items-center space-x-1">
                      <GitBranch className="w-3 h-3" />
                      <span>{member.branches}</span>
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Deployment Details Modal */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={`Deployment Details - ${selectedDeployment?.environment}`}
        size="full"
      >
        {selectedDeployment && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Deployment Information</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Environment:</span>
                  <span className={`ml-2 px-2 py-1 rounded-full text-xs font-medium ${getEnvironmentColor(selectedDeployment.environment)}`}>
                    {selectedDeployment.environment}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Version:</span>
                  <span className="ml-2 font-medium">{selectedDeployment.version}</span>
                </div>
                <div>
                  <span className="text-gray-500">Status:</span>
                  <span className={`ml-2 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(selectedDeployment.status)}`}>
                    {selectedDeployment.status}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Author:</span>
                  <span className="ml-2 font-medium">{selectedDeployment.author}</span>
                </div>
                <div>
                  <span className="text-gray-500">Start Time:</span>
                  <span className="ml-2">{new Date(selectedDeployment.timestamp).toLocaleString()}</span>
                </div>
                {selectedDeployment.duration > 0 && (
                  <div>
                    <span className="text-gray-500">Duration:</span>
                    <span className="ml-2 font-medium">{selectedDeployment.duration}s</span>
                  </div>
                )}
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Commit Information</h3>
              <div className="bg-gray-50 p-3 rounded-lg mt-2">
                <div className="text-sm font-mono">{selectedDeployment.commit}</div>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Changes</h3>
              <ul className="list-disc list-inside space-y-1 mt-2 text-sm text-gray-600">
                {selectedDeployment.changes.map((change, index) => (
                  <li key={index}>{change}</li>
                ))}
              </ul>
            </div>
            
            {selectedDeployment.logs && selectedDeployment.logs.length > 0 && (
              <div>
                <h3 className="text-lg font-medium">Deployment Logs</h3>
                <div className="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto mt-2">
                  <pre className="text-xs whitespace-pre-wrap">{selectedDeployment.logs.join('\n')}</pre>
                </div>
              </div>
            )}
          </div>
        )}
      </Modal>

      {/* Git Activity Details Modal */}
      <Modal
        isOpen={isGitModalOpen}
        onClose={() => {
          setIsGitModalOpen(false);
          setSelectedGitActivity(null);
          setShowAllFiles(false);
          setShowFullDiff(false);
        }}
        title={`Git Activity Details - ${selectedGitActivity?.type?.toUpperCase()}`}
        size="full"
      >
        {selectedGitActivity && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {/* Left Column - Activity Information */}
            <div className="space-y-6">
              <div>
                <h3 className="text-lg font-medium text-gray-900 mb-4">Activity Information</h3>
                <div className="grid grid-cols-1 gap-4 text-sm">
                  <div className="flex justify-between items-center py-2 border-b border-gray-100">
                    <span className="text-gray-500 font-medium">Type:</span>
                    <span className="font-medium capitalize text-gray-900">{selectedGitActivity.type}</span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-100">
                    <span className="text-gray-500 font-medium">Status:</span>
                    <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(selectedGitActivity.status)}`}>
                      {selectedGitActivity.status}
                    </span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-100">
                    <span className="text-gray-500 font-medium">Branch:</span>
                    <span className="font-medium text-gray-900">{selectedGitActivity.branch}</span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-100">
                    <span className="text-gray-500 font-medium">Author:</span>
                    <span className="font-medium text-gray-900">{selectedGitActivity.author}</span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-gray-100">
                    <span className="text-gray-500 font-medium">Timestamp:</span>
                    <span className="font-medium text-gray-900">{new Date(selectedGitActivity.timestamp).toLocaleString()}</span>
                  </div>
                  {selectedGitActivity.hash && (
                    <div className="flex justify-between items-center py-2 border-b border-gray-100">
                      <span className="text-gray-500 font-medium">Commit Hash:</span>
                      <span className="font-mono text-sm text-gray-900 bg-gray-100 px-2 py-1 rounded">{selectedGitActivity.hash}</span>
                    </div>
                  )}
                </div>
              </div>
              
              <div>
                <h3 className="text-lg font-medium text-gray-900 mb-4">Commit Message</h3>
                <div className="bg-gray-50 p-4 rounded-lg border">
                  <p className="text-sm text-gray-700 leading-relaxed">{selectedGitActivity.message}</p>
                </div>
              </div>
              
              <div>
                <h3 className="text-lg font-medium text-gray-900 mb-4">Activity Summary</h3>
                <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                  <div className="text-sm text-blue-800 leading-relaxed">
                    <p><strong>{selectedGitActivity.author}</strong> performed a <strong>{selectedGitActivity.type}</strong> operation on the <strong>{selectedGitActivity.branch}</strong> branch.</p>
                    {selectedGitActivity.filesChanged && (
                      <p className="mt-2">This activity involved <strong>{selectedGitActivity.filesChanged}</strong> files with <strong>{selectedGitActivity.additions || 0} additions</strong> and <strong>{selectedGitActivity.deletions || 0} deletions</strong>.</p>
                    )}
                    <p className="mt-2">The activity was completed at <strong>{new Date(selectedGitActivity.timestamp).toLocaleString()}</strong> with a status of <strong>{selectedGitActivity.status}</strong>.</p>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Right Column - File Changes and Statistics */}
            <div className="space-y-6">
              {/* File Changes Overview - Show for all activities */}
              <div>
                <h3 className="text-lg font-medium text-gray-900 mb-4">File Changes Overview</h3>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                  <div className="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center">
                    <div className="text-3xl font-bold text-blue-600 mb-1">{selectedGitActivity.filesChanged || 0}</div>
                    <div className="text-blue-700 font-medium">Files Changed</div>
                    <div className="text-xs text-blue-600 mt-1">Total modified files</div>
                  </div>
                  <div className="bg-green-50 p-4 rounded-lg border border-green-200 text-center">
                    <div className="text-3xl font-bold text-green-600 mb-1">+{(selectedGitActivity.additions || 0).toLocaleString()}</div>
                    <div className="text-green-700 font-medium">Additions</div>
                    <div className="text-xs text-green-600 mt-1">Lines of code added</div>
                  </div>
                  <div className="bg-red-50 p-4 rounded-lg border border-red-200 text-center">
                    <div className="text-3xl font-bold text-red-600 mb-1">-{(selectedGitActivity.deletions || 0).toLocaleString()}</div>
                    <div className="text-red-700 font-medium">Deletions</div>
                    <div className="text-xs text-red-600 mt-1">Lines of code removed</div>
                  </div>
                </div>
                
                {/* Detailed File Changes Analysis - Only show when there are actual changes */}
                {(selectedGitActivity.additions || selectedGitActivity.deletions) && (
                  <div className="bg-gray-50 p-4 rounded-lg border">
                    <h4 className="font-medium text-gray-900 mb-3">Change Analysis</h4>
                    <div className="space-y-3 text-sm">
                      <div className="flex justify-between items-center">
                        <span className="text-gray-600">Net Change:</span>
                        <span className={`font-medium ${
                          (selectedGitActivity.additions || 0) - (selectedGitActivity.deletions || 0) > 0 
                            ? 'text-green-600' 
                            : (selectedGitActivity.additions || 0) - (selectedGitActivity.deletions || 0) < 0 
                            ? 'text-red-600' 
                            : 'text-gray-600'
                        }`}>
                          {((selectedGitActivity.additions || 0) - (selectedGitActivity.deletions || 0)) > 0 ? '+' : ''}
                          {(selectedGitActivity.additions || 0) - (selectedGitActivity.deletions || 0)}
                        </span>
                      </div>
                      <div className="flex justify-between items-center">
                        <span className="text-gray-600">Change Ratio:</span>
                        <span className="font-medium text-gray-900">
                          {selectedGitActivity.additions && selectedGitActivity.deletions 
                            ? `${((selectedGitActivity.additions / (selectedGitActivity.additions + selectedGitActivity.deletions)) * 100).toFixed(1)}% additions`
                            : 'N/A'
                          }
                        </span>
                      </div>
                      <div className="flex justify-between items-center">
                        <span className="text-gray-600">Average per File:</span>
                        <span className="font-medium text-gray-900">
                          {selectedGitActivity.filesChanged 
                            ? `${Math.round(((selectedGitActivity.additions || 0) + (selectedGitActivity.deletions || 0)) / selectedGitActivity.filesChanged)} lines`
                            : 'N/A'
                          }
                        </span>
                      </div>
                    </div>
                  </div>
                )}

                {/* File Changes Details - Show actual file-by-file changes */}
                {(selectedGitActivity.additions || selectedGitActivity.deletions) && (
                  <div>
                    <div className="flex items-center justify-between mb-4">
                      <h3 className="text-lg font-medium text-gray-900">File Changes Details</h3>
                      <div className="flex items-center space-x-2">
                        <button 
                          onClick={() => setShowFullDiff(true)}
                          className="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors"
                        >
                          View Full Diff
                        </button>
                      </div>
                    </div>
                    <div className="space-y-3">
                      {/* Simulated file changes - in real implementation, this would come from Git diff */}
                      {(() => {
                        const files = [
                          'resources/js/components/admin/TestingDashboard.tsx',
                          'resources/js/components/admin/PerformanceMonitor.tsx',
                          'resources/js/components/admin/SystemHealth.tsx',
                          'resources/js/components/admin/DevelopmentWorkflow.tsx',
                          'resources/js/pages/AdminPage.tsx',
                          'resources/js/store/authStore.ts',
                          'resources/js/services/jwtService.ts',
                          'resources/js/services/sessionService.ts',
                          'resources/js/services/rateLimitService.ts',
                          'public/.htaccess',
                          'package.json',
                          'docs/releases/RELEASE_NOTES_0.0.3.md'
                        ].slice(0, selectedGitActivity.filesChanged || 0);

                        // Show files based on showAllFiles state
                        const displayFiles = showAllFiles ? files : files.slice(0, 5);
                        const hasMoreFiles = files.length > 5;
                        
                        return (
                          <>
                            {displayFiles.map((file, index) => {
                              const fileAdditions = Math.floor((selectedGitActivity.additions || 0) / files.length) + (index < (selectedGitActivity.additions || 0) % files.length ? 1 : 0);
                              const fileDeletions = Math.floor((selectedGitActivity.deletions || 0) / files.length) + (index < (selectedGitActivity.deletions || 0) % files.length ? 1 : 0);
                              
                              return (
                                <div key={index} className="bg-white p-3 rounded-lg border border-gray-200">
                                  <div className="flex items-center justify-between mb-2">
                                    <h4 className="font-medium text-gray-900 text-sm truncate max-w-xs">{file}</h4>
                                    <div className="flex items-center space-x-2 text-xs ml-2">
                                      <span className="text-green-600">+{fileAdditions}</span>
                                      <span className="text-red-600">-{fileDeletions}</span>
                                    </div>
                                  </div>
                                  
                                  {/* Compact file diff preview */}
                                  <div className="bg-gray-50 p-2 rounded border text-xs font-mono">
                                    <div className="space-y-1">
                                      {/* Simulated additions */}
                                      {fileAdditions > 0 && (
                                        <div className="text-green-700">
                                          {Array.from({ length: Math.min(fileAdditions, 2) }, (_, i) => (
                                            <div key={i} className="flex">
                                              <span className="text-green-600 mr-2">+</span>
                                              <span className="text-gray-600">// Added line {i + 1}</span>
                                            </div>
                                          ))}
                                          {fileAdditions > 2 && (
                                            <div className="text-green-600 text-xs mt-1">
                                              +{fileAdditions - 2} more
                                            </div>
                                          )}
                                        </div>
                                      )}
                                      
                                      {/* Simulated deletions */}
                                      {fileDeletions > 0 && (
                                        <div className="text-red-700">
                                          {Array.from({ length: Math.min(fileDeletions, 2) }, (_, i) => (
                                            <div key={i} className="flex">
                                              <span className="text-red-600 mr-2">-</span>
                                              <span className="text-gray-600">// Removed line {i + 1}</span>
                                            </div>
                                          ))}
                                          {fileDeletions > 2 && (
                                            <div className="text-red-600 text-xs mt-1">
                                              -{fileDeletions - 2} more
                                            </div>
                                          )}
                                        </div>
                                      )}
                                    </div>
                                  </div>
                                </div>
                              );
                            })}
                            
                            {/* Show remaining files count and expand/collapse option */}
                            {hasMoreFiles && (
                              <div className="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div className="text-center">
                                  <p className="text-sm text-gray-600 mb-2">
                                    {showAllFiles ? 'Showing all files' : `${files.length - 5} more file${files.length - 5 !== 1 ? 's' : ''} changed`}
                                  </p>
                                  <button 
                                    onClick={() => setShowAllFiles(!showAllFiles)}
                                    className="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors"
                                  >
                                    {showAllFiles ? 'Show Less' : 'View All Files'}
                                  </button>
                                </div>
                              </div>
                            )}
                            
                            {/* Show total summary */}
                            <div className="bg-blue-50 p-3 rounded-lg border border-blue-200">
                              <div className="text-center text-sm text-blue-800">
                                <p className="font-medium">Total Changes Summary</p>
                                <p className="text-xs mt-1">
                                  {files.length} files • +{(selectedGitActivity.additions || 0).toLocaleString()} additions • -{(selectedGitActivity.deletions || 0).toLocaleString()} deletions
                                </p>
                              </div>
                            </div>
                          </>
                        );
                      })()}
                    </div>
                  </div>
                )}
                
                {/* Activity Impact Assessment */}
                <div>
                  <h3 className="text-lg font-medium text-gray-900 mb-4">Impact Assessment</h3>
                  <div className="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div className="text-sm text-yellow-800">
                      <div className="flex items-center mb-2">
                        <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                          <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                        <span className="font-medium">Change Impact</span>
                      </div>
                      <p className="mb-2">
                        {(() => {
                          const totalChanges = (selectedGitActivity.additions || 0) + (selectedGitActivity.deletions || 0);
                          if (totalChanges > 1000) return "This is a significant change affecting many lines of code.";
                          if (totalChanges > 500) return "This is a substantial change with moderate impact.";
                          if (totalChanges > 100) return "This is a moderate change affecting several files.";
                          return "This is a small, focused change with minimal impact.";
                        })()}
                      </p>
                      <p className="text-xs opacity-75">
                        Impact assessment based on total lines changed and files affected.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </Modal>

      {/* Full Diff Modal */}
      <Modal
        isOpen={showFullDiff}
        onClose={() => setShowFullDiff(false)}
        title={`Complete Diff - ${selectedGitActivity?.type?.toUpperCase()} on ${selectedGitActivity?.branch}`}
        size="full"
      >
        {selectedGitActivity && (
          <div className="space-y-6">
            {/* Diff Header */}
            <div className="bg-gray-50 p-4 rounded-lg border">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                  <div className="text-2xl font-bold text-blue-600">{selectedGitActivity.filesChanged || 0}</div>
                  <div className="text-blue-700 font-medium">Files Changed</div>
                </div>
                <div>
                  <div className="text-2xl font-bold text-green-600">+{(selectedGitActivity.additions || 0).toLocaleString()}</div>
                  <div className="text-green-700 font-medium">Additions</div>
                </div>
                <div>
                  <div className="text-2xl font-bold text-red-600">-{(selectedGitActivity.deletions || 0).toLocaleString()}</div>
                  <div className="text-red-700 font-medium">Deletions</div>
                </div>
              </div>
            </div>

            {/* Complete File Diff */}
            <div className="space-y-4">
              <h3 className="text-lg font-medium text-gray-900">Complete File Changes</h3>
              {(() => {
                const files = [
                  'resources/js/components/admin/TestingDashboard.tsx',
                  'resources/js/components/admin/PerformanceMonitor.tsx',
                  'resources/js/components/admin/SystemHealth.tsx',
                  'resources/js/components/admin/DevelopmentWorkflow.tsx',
                  'resources/js/pages/AdminPage.tsx',
                  'resources/js/store/authStore.ts',
                  'resources/js/services/jwtService.ts',
                  'resources/js/services/sessionService.ts',
                  'resources/js/services/rateLimitService.ts',
                  'public/.htaccess',
                  'package.json',
                  'docs/releases/RELEASE_NOTES_0.0.3.md'
                ].slice(0, selectedGitActivity.filesChanged || 0);

                return files.map((file, index) => {
                  const fileAdditions = Math.floor((selectedGitActivity.additions || 0) / files.length) + (index < (selectedGitActivity.additions || 0) % files.length ? 1 : 0);
                  const fileDeletions = Math.floor((selectedGitActivity.deletions || 0) / files.length) + (index < (selectedGitActivity.deletions || 0) % files.length ? 1 : 0);
                  
                  return (
                    <div key={index} className="bg-white p-4 rounded-lg border border-gray-200">
                      <div className="flex items-center justify-between mb-3">
                        <h4 className="font-medium text-gray-900 text-sm">{file}</h4>
                        <div className="flex items-center space-x-2 text-xs">
                          <span className="text-green-600">+{fileAdditions}</span>
                          <span className="text-red-600">-{fileDeletions}</span>
                        </div>
                      </div>
                      
                      {/* Detailed file diff */}
                      <div className="bg-gray-50 p-3 rounded border text-xs font-mono">
                        <div className="space-y-1">
                          {/* Simulated additions */}
                          {fileAdditions > 0 && (
                            <div className="text-green-700">
                              {Array.from({ length: Math.min(fileAdditions, 5) }, (_, i) => (
                                <div key={i} className="flex">
                                  <span className="text-green-600 mr-2">+</span>
                                  <span className="text-gray-600">// Added line {i + 1} - {Math.random().toString(36).substring(2, 10)}</span>
                                </div>
                              ))}
                              {fileAdditions > 5 && (
                                <div className="text-green-600 text-xs mt-1">
                                  ... and {fileAdditions - 5} more additions
                                </div>
                              )}
                            </div>
                          )}
                          
                          {/* Simulated deletions */}
                          {fileDeletions > 0 && (
                            <div className="text-red-700">
                              {Array.from({ length: Math.min(fileDeletions, 5) }, (_, i) => (
                                <div key={i} className="flex">
                                  <span className="text-red-600 mr-2">-</span>
                                  <span className="text-gray-600">// Removed line {i + 1} - {Math.random().toString(36).substring(2, 10)}</span>
                                </div>
                              ))}
                              {fileDeletions > 5 && (
                                <div className="text-red-600 text-xs mt-1">
                                  ... and {fileDeletions - 5} more deletions
                                </div>
                              )}
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                });
              })()}
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default DevelopmentWorkflow; 
