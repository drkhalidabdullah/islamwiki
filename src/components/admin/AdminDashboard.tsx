import React, { useState, useEffect } from 'react';
import { Card, Button } from '../index';
import { adminService, AdminData } from '../../services/adminService';

const AdminDashboard: React.FC = () => {
  const [adminData, setAdminData] = useState<AdminData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Fetch real admin data
  useEffect(() => {
    const fetchAdminData = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const data = await adminService.getAdminData();
        setAdminData(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to fetch admin data');
      } finally {
        setIsLoading(false);
      }
    };

    fetchAdminData();
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'operational':
        return 'text-green-600 bg-green-100';
      case 'degraded':
        return 'text-yellow-600 bg-yellow-100';
      case 'down':
        return 'text-red-600 bg-red-100';
      default:
        return 'text-gray-600 bg-gray-100';
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading admin data...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="text-red-600 text-xl mb-2">⚠️</div>
          <p className="text-red-600">Error loading admin data</p>
          <p className="text-gray-600 text-sm mt-2">{error}</p>
          <Button 
            onClick={() => window.location.reload()} 
            className="mt-4"
          >
            Retry
          </Button>
        </div>
      </div>
    );
  }

  if (!adminData) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <p className="text-gray-600">No admin data available</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      {/* Overview Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">
              {adminData.user_statistics.total_users}
            </div>
            <div className="text-sm text-gray-600">Total Users</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">
              {adminData.user_statistics.active_users}
            </div>
            <div className="text-sm text-gray-600">Active Users</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-yellow-600">
              {adminData.user_statistics.new_users_today}
            </div>
            <div className="text-sm text-gray-600">New Today</div>
          </div>
        </Card>
        
        <Card>
          <div className="text-center">
            <div className="text-2xl font-bold text-purple-600">
              {adminData.role_statistics.length}
            </div>
            <div className="text-sm text-gray-600">User Roles</div>
          </div>
        </Card>
      </div>

      {/* System Status */}
      <Card>
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-semibold">System Status</h2>
          <span className={`inline-flex px-3 py-1 text-sm font-medium rounded-full ${getStatusColor(adminData.status)}`}>
            {adminData.status.toUpperCase()}
          </span>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 className="text-lg font-medium mb-3">System Information</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-600">Version:</span>
                <span className="font-medium">{adminData.version}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">PHP Version:</span>
                <span className="font-medium">{adminData.system_info.php_version}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">MySQL Version:</span>
                <span className="font-medium">{adminData.system_info.mysql_version}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Server Time:</span>
                <span className="font-medium">{new Date(adminData.system_info.server_time).toLocaleString()}</span>
              </div>
            </div>
          </div>
          
          <div>
            <h3 className="text-lg font-medium mb-3">Memory Usage</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-600">Current:</span>
                <span className="font-medium">{(adminData.system_info.memory_usage / 1024 / 1024).toFixed(2)} MB</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Peak:</span>
                <span className="font-medium">{(adminData.system_info.peak_memory / 1024 / 1024).toFixed(2)} MB</span>
              </div>
            </div>
          </div>
        </div>
      </Card>

      {/* User Role Statistics */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">User Role Distribution</h2>
        <div className="space-y-3">
          {adminData.role_statistics.map((role) => (
            <div key={role.role_name} className="flex items-center justify-between">
              <div className="flex items-center space-x-3">
                <div className="w-3 h-3 bg-blue-500 rounded-full"></div>
                <span className="font-medium capitalize">{role.role_name.replace('_', ' ')}</span>
              </div>
              <span className="text-lg font-bold text-blue-600">{role.user_count}</span>
            </div>
          ))}
        </div>
      </Card>

      {/* Recent User Activity */}
      <Card>
        <h2 className="text-xl font-semibold mb-4">Recent User Activity</h2>
        <div className="space-y-3">
          {adminData.recent_activity.map((user, index) => (
            <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div className="flex items-center space-x-3">
                <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                  {user.display_name.charAt(0).toUpperCase()}
                </div>
                <div>
                  <div className="font-medium">{user.display_name}</div>
                  <div className="text-sm text-gray-600">@{user.username}</div>
                </div>
              </div>
              <div className="text-right text-sm text-gray-600">
                <div>Last seen: {new Date(user.last_seen_at).toLocaleString()}</div>
                <div className="text-blue-600 capitalize">{user.roles}</div>
              </div>
            </div>
          ))}
        </div>
      </Card>
    </div>
  );
};

export default AdminDashboard; 