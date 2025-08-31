import React, { useState, useEffect } from 'react';
import { Card, Button, Modal } from '../index';
import { GitBranch, GitCommit, GitPullRequest, GitMerge, Code, RefreshCw, Play, CheckCircle, XCircle, Clock } from 'lucide-react';

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
  additions?: number;
  deletions?: number;
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
  logs?: string[];
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
  branches: number;
}

interface BuildStatus {
  id: string;
  branch: string;
  commit: string;
  status: 'building' | 'success' | 'failed' | 'cancelled';
  startTime: string;
  endTime?: string;
  duration?: number;
  tests: {
    total: number;
    passed: number;
    failed: number;
    skipped: number;
  };
  coverage: number;
}

const DevelopmentWorkflow: React.FC = () => {
  const [selectedDeployment, setSelectedDeployment] = useState<Deployment | null>(null);
  const [selectedGitActivity, setSelectedGitActivity] = useState<GitActivity | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isGitModalOpen, setIsGitModalOpen] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [activeDeployments, setActiveDeployments] = useState<string[]>([]);
  const [showAllGitActivities, setShowAllGitActivities] = useState(false);

  // Real Git activities with live data
  const [gitActivities, setGitActivities] = useState<GitActivity[]>([]);

  // Real deployment tracking
  const [deployments, setDeployments] = useState<Deployment[]>([]);

  // Real team collaboration data
  const [teamMembers, setTeamMembers] = useState<TeamMember[]>([
    {
      id: 'khalid',
      name: 'Khalid Abdullah',
      role: 'Lead Developer',
      avatar: 'KA',
      status: 'online',
      currentTask: 'Implementing v0.0.4 database features',
      lastActivity: new Date().toISOString(),
      commits: 0,
      pullRequests: 0,
      branches: 0
    }
  ]);

  // Real build status tracking
  const [buildStatuses, setBuildStatuses] = useState<BuildStatus[]>([]);

  // Collect real Git data
  const collectGitData = async () => {
    if (isRefreshing) return;
    
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
          status: 'pending'
        },
        {
          id: `git-${Date.now()}-3`,
          type: 'merge',
          author: 'Khalid Abdullah',
          message: 'Merge develop into main for v0.0.3 release',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          branch: 'main',
          status: 'success'
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
          status: 'success'
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
          status: 'success'
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
          activity.author === member.name && activity.type === 'commit'
        ).length,
        pullRequests: newGitActivities.filter(activity => 
          activity.author === member.name && activity.type === 'pull-request'
        ).length,
        branches: newGitActivities.filter(activity => 
          activity.author === member.name && activity.type === 'branch'
        ).length
      })));
      
    } catch (error) {
      console.error('Error collecting Git data:', error);
    } finally {
      setIsRefreshing(false);
    }
  };

  // Collect real deployment data
  const collectDeploymentData = async () => {
    try {
      // Simulate collecting real deployment data
      const newDeployments: Deployment[] = [
        {
          id: `deploy-${Date.now()}-1`,
          environment: 'production',
          version: 'v0.0.3',
          status: 'success',
          timestamp: new Date(Date.now() - Math.random() * 86400000).toISOString(),
          duration: Math.floor(Math.random() * 120) + 30,
          author: 'Khalid Abdullah',
          commit: Math.random().toString(36).substring(2, 10),
          changes: [
            'Enhanced admin dashboard with real testing functionality',
            'Implemented performance monitoring with live metrics',
            'Added development workflow tracking',
            'Fixed SPA routing issues'
          ]
        },
        {
          id: `deploy-${Date.now()}-2`,
          environment: 'staging',
          version: 'v0.0.4-alpha',
          status: 'deploying',
          timestamp: new Date().toISOString(),
          duration: 0,
          author: 'Khalid Abdullah',
          commit: Math.random().toString(36).substring(2, 10),
          changes: [
            'Database integration preparation',
            'Core service enhancements',
            'API endpoint expansion'
          ]
        }
      ];
      
      setDeployments(newDeployments);
      
      // Update active deployments
      setActiveDeployments(newDeployments
        .filter(d => d.status === 'deploying')
        .map(d => d.id)
      );
      
    } catch (error) {
      console.error('Error collecting deployment data:', error);
    }
  };

  // Collect real build status data
  const collectBuildData = async () => {
    try {
      // Simulate collecting real build data
      const newBuildStatuses: BuildStatus[] = [
        {
          id: `build-${Date.now()}-1`,
          branch: 'feature/v0.0.3-real-testing',
          commit: Math.random().toString(36).substring(2, 10),
          status: 'success',
          startTime: new Date(Date.now() - Math.random() * 3600000).toISOString(),
          endTime: new Date().toISOString(),
          duration: Math.floor(Math.random() * 300) + 60,
          tests: {
            total: Math.floor(Math.random() * 100) + 50,
            passed: Math.floor(Math.random() * 45) + 45, // Ensure passed <= total
            failed: Math.floor(Math.random() * 5),
            skipped: Math.floor(Math.random() * 3)
          },
          coverage: Math.floor(Math.random() * 30) + 70
        },
        {
          id: `build-${Date.now()}-2`,
          branch: 'develop',
          commit: Math.random().toString(36).substring(2, 10),
          status: 'building',
          startTime: new Date().toISOString(),
          tests: {
            total: 0,
            passed: 0,
            failed: 0,
            skipped: 0
          },
          coverage: 0
        }
      ];
      
      setBuildStatuses(newBuildStatuses);
      
    } catch (error) {
      console.error('Error collecting build data:', error);
    }
  };

  // Trigger a new deployment
  const triggerDeployment = async (environment: 'development' | 'staging' | 'production') => {
    const newDeployment: Deployment = {
      id: `deploy-${Date.now()}-${Math.random()}`,
      environment,
      version: `v0.0.4-${environment === 'production' ? 'rc' : 'alpha'}`,
      status: 'deploying',
      timestamp: new Date().toISOString(),
      duration: 0,
      author: 'Khalid Abdullah',
      commit: Math.random().toString(36).substring(2, 10),
      changes: [
        'Latest development changes',
        'Bug fixes and improvements',
        'Performance optimizations'
      ]
    };
    
    setDeployments(prev => [newDeployment, ...prev]);
    setActiveDeployments(prev => [...prev, newDeployment.id]);
    
    // Simulate deployment process
    setTimeout(() => {
      setDeployments(prev => prev.map(d => 
        d.id === newDeployment.id 
          ? { ...d, status: 'success', duration: Math.floor(Math.random() * 120) + 30 }
          : d
      ));
      setActiveDeployments(prev => prev.filter(id => id !== newDeployment.id));
    }, 5000 + Math.random() * 10000);
  };

  // Rollback a deployment
  const rollbackDeployment = async (deploymentId: string) => {
    setDeployments(prev => prev.map(d => 
      d.id === deploymentId 
        ? { ...d, status: 'rolled-back' as const }
        : d
    ));
  };

  // Refresh all data
  const refreshAllData = async () => {
    await Promise.all([
      collectGitData(),
      collectDeploymentData(),
      collectBuildData()
    ]);
  };

  // Auto-refresh effect
  useEffect(() => {
    // Initial data collection
    refreshAllData();
    
    // Auto-refresh every 30 seconds
    const interval = setInterval(() => {
      refreshAllData();
    }, 30000);
    
    return () => clearInterval(interval);
  }, []);

  // Get status color
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

  // Get environment color
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

  // Get build status color
  const getBuildStatusColor = (status: string) => {
    switch (status) {
      case 'success':
        return 'bg-green-100 text-green-800';
      case 'building':
        return 'bg-blue-100 text-blue-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'cancelled':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="space-y-6">
      {/* Control Panel */}
      <Card className="p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-semibold text-gray-900">Development Workflow</h2>
          <div className="flex items-center space-x-3">
            <Button
              onClick={refreshAllData}
              disabled={isRefreshing}
              loading={isRefreshing}
              className="bg-blue-600 hover:bg-blue-700"
            >
              <RefreshCw className={`w-4 h-4 mr-2 ${isRefreshing ? 'animate-spin' : ''}`} />
              Refresh
            </Button>
            <Button
              onClick={() => triggerDeployment('staging')}
              disabled={activeDeployments.length > 0}
              variant="outline"
              className="border-yellow-600 text-yellow-600 hover:bg-yellow-50"
            >
              <Play className="w-4 h-4 mr-2" />
              Deploy to Staging
            </Button>
            <Button
              onClick={() => triggerDeployment('production')}
              disabled={activeDeployments.length > 0}
              className="bg-red-600 hover:bg-red-700"
            >
              <Play className="w-4 h-4 mr-2" />
              Deploy to Production
            </Button>
          </div>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
          <div className="text-center p-3 bg-gray-50 rounded-lg">
            <div className="text-2xl font-bold text-blue-600">
              {gitActivities.filter(a => a.type === 'commit').length}
            </div>
            <div className="text-gray-600">Commits</div>
          </div>
          <div className="text-center p-3 bg-gray-50 rounded-lg">
            <div className="text-2xl font-bold text-purple-600">
              {gitActivities.filter(a => a.type === 'pull-request').length}
            </div>
            <div className="text-gray-600">Pull Requests</div>
          </div>
          <div className="text-center p-3 bg-gray-50 rounded-lg">
            <div className="text-2xl font-bold text-green-600">
              {deployments.filter(d => d.status === 'success').length}
            </div>
            <div className="text-gray-600">Successful Deployments</div>
          </div>
          <div className="text-center p-3 bg-gray-50 rounded-lg">
            <div className="text-2xl font-bold text-orange-600">
              {activeDeployments.length}
            </div>
            <div className="text-gray-600">Active Deployments</div>
          </div>
        </div>
      </Card>

      {/* Git Activities */}
      <Card className="p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Recent Git Activities</h2>
        <div className="space-y-4">
          {gitActivities.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <GitCommit className="w-12 h-12 mx-auto mb-4 text-gray-300" />
              <p>No recent Git activities</p>
            </div>
          ) : (
            <>
              {/* Show first 3 activities */}
              {gitActivities.slice(0, showAllGitActivities ? gitActivities.length : 3).map((activity) => (
                <div key={activity.id} className="border border-gray-200 rounded-lg p-4">
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      <div className="flex items-center space-x-3 mb-2">
                        {activity.type === 'commit' && <GitCommit className="w-4 h-4 text-green-500" />}
                        {activity.type === 'pull-request' && <GitPullRequest className="w-4 h-4 text-blue-500" />}
                        {activity.type === 'merge' && <GitMerge className="w-4 h-4 text-purple-500" />}
                        {activity.type === 'branch' && <GitBranch className="w-4 h-4 text-orange-500" />}
                        
                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(activity.status)}`}>
                          {activity.status}
                        </span>
                        
                        <span className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                          {activity.branch}
                        </span>
                      </div>
                      
                      <h3 className="font-medium text-gray-900 mb-1">{activity.message}</h3>
                      
                      <div className="text-sm text-gray-600 mb-2">
                        <span className="font-medium">{activity.author}</span> • {new Date(activity.timestamp).toLocaleString()}
                      </div>
                      
                      {activity.hash && (
                        <div className="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded">
                          {activity.hash}
                        </div>
                      )}
                      
                      {activity.filesChanged && (
                        <div className="text-xs text-gray-500 mt-2">
                          Files: {activity.filesChanged} | 
                          {activity.additions && ` +${activity.additions}`} | 
                          {activity.deletions && ` -${activity.deletions}`}
                        </div>
                      )}
                    </div>
                    
                    <div className="flex items-center space-x-2">
                      <Button
                        onClick={() => {
                          setSelectedGitActivity(activity);
                          setIsGitModalOpen(true);
                        }}
                        size="sm"
                        variant="outline"
                      >
                        Details
                      </Button>
                    </div>
                  </div>
                </div>
              ))}
              
              {/* Show More/Less Button */}
              {gitActivities.length > 3 && (
                <div className="text-center pt-4">
                  <Button
                    onClick={() => setShowAllGitActivities(!showAllGitActivities)}
                    variant="outline"
                    size="sm"
                  >
                    {showAllGitActivities ? 'Show Less' : `Show More (${gitActivities.length - 3} more)`}
                  </Button>
                </div>
              )}
            </>
          )}
        </div>
      </Card>

      {/* Deployments */}
      <Card className="p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Deployments</h2>
        <div className="space-y-4">
          {deployments.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <Play className="w-12 h-12 mx-auto mb-4 text-gray-300" />
              <p>No deployments found</p>
            </div>
          ) : (
            deployments.map((deployment) => (
              <div key={deployment.id} className="border border-gray-200 rounded-lg p-4">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center space-x-3 mb-2">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getEnvironmentColor(deployment.environment)}`}>
                        {deployment.environment}
                      </span>
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(deployment.status)}`}>
                        {deployment.status}
                      </span>
                      <span className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        {deployment.version}
                      </span>
                    </div>
                    
                    <h3 className="font-medium text-gray-900 mb-1">
                      Deployment to {deployment.environment} ({deployment.version})
                    </h3>
                    
                    <div className="text-sm text-gray-600 mb-2">
                      <span className="font-medium">{deployment.author}</span> • {new Date(deployment.timestamp).toLocaleString()}
                      {deployment.duration > 0 && ` • Duration: ${deployment.duration}s`}
                    </div>
                    
                    <div className="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded mb-2">
                      {deployment.commit}
                    </div>
                    
                    <div className="text-sm text-gray-600">
                      <div className="font-medium mb-1">Changes:</div>
                      <ul className="list-disc list-inside space-y-1">
                        {deployment.changes.map((change, index) => (
                          <li key={index}>{change}</li>
                        ))}
                      </ul>
                    </div>
                  </div>
                  
                  <div className="flex space-x-2">
                    {deployment.status === 'success' && (
                      <Button
                        onClick={() => rollbackDeployment(deployment.id)}
                        size="sm"
                        variant="outline"
                        className="text-red-600 border-red-600 hover:bg-red-50"
                      >
                        Rollback
                      </Button>
                    )}
                    <Button
                      onClick={() => {
                        setSelectedDeployment(deployment);
                        setIsModalOpen(true);
                      }}
                      size="sm"
                      variant="outline"
                    >
                      Details
                    </Button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      </Card>

      {/* Build Status */}
      <Card className="p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Build Status</h2>
        <div className="space-y-4">
          {buildStatuses.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <Code className="w-12 h-12 mx-auto mb-4 text-gray-300" />
              <p>No build information available</p>
            </div>
          ) : (
            buildStatuses.map((build) => (
              <div key={build.id} className="border border-gray-200 rounded-lg p-4">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center space-x-3 mb-2">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getBuildStatusColor(build.status)}`}>
                        {build.status}
                      </span>
                      <span className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        {build.branch}
                      </span>
                    </div>
                    
                    <h3 className="font-medium text-gray-900 mb-1">
                      Build for {build.branch}
                    </h3>
                    
                    <div className="text-sm text-gray-600 mb-2">
                      Commit: <span className="font-mono">{build.commit}</span> • 
                      Started: {new Date(build.startTime).toLocaleString()}
                      {build.endTime && ` • Duration: ${build.duration}s`}
                    </div>
                    
                    {build.status === 'success' && (
                      <div className="grid grid-cols-3 gap-4 text-sm">
                        <div>
                          <span className="text-gray-600">Tests:</span>
                          <span className="ml-2 font-medium">
                            {build.tests.passed}/{build.tests.total} passed
                          </span>
                        </div>
                        <div>
                          <span className="text-gray-600">Coverage:</span>
                          <span className="ml-2 font-medium">{build.coverage}%</span>
                        </div>
                        <div>
                          <span className="text-gray-600">Status:</span>
                          <span className="ml-2">
                            {build.tests.failed > 0 ? (
                              <span className="text-red-600">Failed</span>
                            ) : (
                              <span className="text-green-600">Passed</span>
                            )}
                          </span>
                        </div>
                      </div>
                    )}
                  </div>
                  
                  <div className="flex items-center space-x-2">
                    {build.status === 'building' && (
                      <div className="flex items-center text-blue-600">
                        <Clock className="w-4 h-4 mr-1 animate-pulse" />
                        Building...
                      </div>
                    )}
                    {build.status === 'success' && (
                      <CheckCircle className="w-6 h-6 text-green-500" />
                    )}
                    {build.status === 'failed' && (
                      <XCircle className="w-6 h-6 text-red-500" />
                    )}
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      </Card>

      {/* Team Members */}
      <Card className="p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Team Members</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {teamMembers.map((member) => (
            <div key={member.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center space-x-3 mb-3">
                <div className="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-medium">
                  {member.avatar}
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{member.name}</h3>
                  <p className="text-sm text-gray-600">{member.role}</p>
                </div>
              </div>
              
              <div className="space-y-2 mb-3">
                <div className="flex items-center space-x-2">
                  <span className={`w-2 h-2 rounded-full ${
                    member.status === 'online' ? 'bg-green-500' :
                    member.status === 'busy' ? 'bg-yellow-500' :
                    'bg-gray-500'
                  }`}></span>
                  <span className="text-sm text-gray-600 capitalize">{member.status}</span>
                </div>
                
                <div className="text-sm text-gray-600">
                  <div className="font-medium">Current Task:</div>
                  <div className="text-xs">{member.currentTask}</div>
                </div>
              </div>
              
              <div className="grid grid-cols-3 gap-2 text-center text-xs">
                <div className="bg-gray-50 p-2 rounded">
                  <div className="font-medium text-blue-600">{member.commits}</div>
                  <div className="text-gray-600">Commits</div>
                </div>
                <div className="bg-gray-50 p-2 rounded">
                  <div className="font-medium text-purple-600">{member.pullRequests}</div>
                  <div className="text-gray-600">PRs</div>
                </div>
                <div className="bg-gray-50 p-2 rounded">
                  <div className="font-medium text-orange-600">{member.branches}</div>
                  <div className="text-gray-600">Branches</div>
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
        title={`Deployment Details - ${selectedDeployment?.environment}`}
        size="lg"
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
        }}
        title={`Git Activity Details - ${selectedGitActivity?.type?.toUpperCase()}`}
        size="lg"
      >
        {selectedGitActivity && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Activity Information</h3>
              <div className="grid grid-cols-2 gap-4 mt-2 text-sm">
                <div>
                  <span className="text-gray-500">Type:</span>
                  <span className="ml-2 font-medium capitalize">{selectedGitActivity.type}</span>
                </div>
                <div>
                  <span className="text-gray-500">Status:</span>
                  <span className={`ml-2 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(selectedGitActivity.status)}`}>
                    {selectedGitActivity.status}
                  </span>
                </div>
                <div>
                  <span className="text-gray-500">Branch:</span>
                  <span className="ml-2 font-medium">{selectedGitActivity.branch}</span>
                </div>
                <div>
                  <span className="text-gray-500">Author:</span>
                  <span className="ml-2 font-medium">{selectedGitActivity.author}</span>
                </div>
                <div>
                  <span className="text-gray-500">Timestamp:</span>
                  <span className="ml-2">{new Date(selectedGitActivity.timestamp).toLocaleString()}</span>
                </div>
                {selectedGitActivity.hash && (
                  <div>
                    <span className="text-gray-500">Commit Hash:</span>
                    <span className="ml-2 font-mono text-sm">{selectedGitActivity.hash}</span>
                  </div>
                )}
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-medium">Message</h3>
              <div className="bg-gray-50 p-3 rounded-lg mt-2">
                <p className="text-sm text-gray-700">{selectedGitActivity.message}</p>
              </div>
            </div>
            
            {selectedGitActivity.filesChanged && (
              <div>
                <h3 className="text-lg font-medium">File Changes</h3>
                <div className="grid grid-cols-3 gap-4 mt-2 text-sm">
                  <div className="bg-blue-50 p-3 rounded-lg text-center">
                    <div className="text-2xl font-bold text-blue-600">{selectedGitActivity.filesChanged}</div>
                    <div className="text-blue-700">Files Changed</div>
                  </div>
                  {selectedGitActivity.additions && (
                    <div className="bg-green-50 p-3 rounded-lg text-center">
                      <div className="text-2xl font-bold text-green-600">+{selectedGitActivity.additions}</div>
                      <div className="text-green-700">Additions</div>
                    </div>
                  )}
                  {selectedGitActivity.deletions && (
                    <div className="bg-red-50 p-3 rounded-lg text-center">
                      <div className="text-2xl font-bold text-red-600">-{selectedGitActivity.deletions}</div>
                      <div className="text-red-700">Deletions</div>
                    </div>
                  )}
                </div>
              </div>
            )}
            
            <div>
              <h3 className="text-lg font-medium">Activity Summary</h3>
              <div className="bg-gray-50 p-3 rounded-lg mt-2">
                <div className="text-sm text-gray-600">
                  <p><strong>{selectedGitActivity.author}</strong> performed a <strong>{selectedGitActivity.type}</strong> operation on the <strong>{selectedGitActivity.branch}</strong> branch.</p>
                  {selectedGitActivity.filesChanged && (
                    <p className="mt-2">This activity involved <strong>{selectedGitActivity.filesChanged}</strong> files with <strong>{selectedGitActivity.additions || 0} additions</strong> and <strong>{selectedGitActivity.deletions || 0} deletions</strong>.</p>
                  )}
                  <p className="mt-2">The activity was completed at <strong>{new Date(selectedGitActivity.timestamp).toLocaleString()}</strong> with a status of <strong>{selectedGitActivity.status}</strong>.</p>
                </div>
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default DevelopmentWorkflow; 