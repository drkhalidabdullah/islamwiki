import React from 'react';
import { Link } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import Card from '../components/ui/Card';
import Button from '../components/ui/Button';

const DashboardPage: React.FC = () => {
  const { user, logout } = useAuthStore();

  const handleLogout = () => {
    logout();
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            <div>
              <h1 className="text-2xl font-bold text-gray-900">User Dashboard</h1>
              <p className="text-sm text-gray-600">Welcome back, {user?.first_name ? `${user.first_name} ${user.last_name}` : user?.username}</p>
            </div>
            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-600">
                Role: {user?.role_name || 'User'}
              </span>
              <Button onClick={handleLogout} variant="outline" size="sm">
                Logout
              </Button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {/* Welcome Card */}
          <Card className="md:col-span-2 lg:col-span-3">
            <div className="p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-2">
                Welcome to IslamWiki Framework
              </h2>
              <p className="text-gray-600 mb-4">
                This is your personal dashboard where you can manage your profile, view your content, 
                and access platform features based on your permissions.
              </p>
              <div className="flex space-x-3">
                <Link to={`/user/${user?.username}`}>
                  <Button variant="primary" size="sm">
                    View Profile
                  </Button>
                </Link>
                <Button variant="outline" size="sm">
                  Edit Settings
                </Button>
              </div>
            </div>
          </Card>

          {/* Quick Stats */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Quick Stats</h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">Profile Views</span>
                  <span className="font-medium">0</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Content Created</span>
                  <span className="font-medium">0</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Last Login</span>
                  <span className="font-medium">Today</span>
                </div>
              </div>
            </div>
          </Card>

          {/* Recent Activity */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
              <div className="space-y-3">
                <div className="text-sm text-gray-600">
                  <p>• Account created successfully</p>
                  <p>• Email verified</p>
                  <p>• Profile updated</p>
                </div>
              </div>
            </div>
          </Card>

          {/* Quick Actions */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
              <div className="space-y-3">
                <Button variant="outline" size="sm" className="w-full justify-start">
                  📝 Create New Content
                </Button>
                <Button variant="outline" size="sm" className="w-full justify-start">
                  🔍 Search Content
                </Button>
                <Button variant="outline" size="sm" className="w-full justify-start">
                  👥 View Community
                </Button>
                <Button variant="outline" size="sm" className="w-full justify-start">
                  ⚙️ Account Settings
                </Button>
              </div>
            </div>
          </Card>

          {/* Platform Features */}
          <Card className="md:col-span-2">
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Available Features</h3>
              <div className="grid grid-cols-2 gap-4">
                <div className="text-sm">
                  <h4 className="font-medium text-gray-900 mb-2">Content Management</h4>
                  <ul className="space-y-1 text-gray-600">
                    <li>• Create and edit articles</li>
                    <li>• Manage your content</li>
                    <li>• View content history</li>
                  </ul>
                </div>
                <div className="text-sm">
                  <h4 className="font-medium text-gray-900 mb-2">Community Features</h4>
                  <ul className="space-y-1 text-gray-600">
                    <li>• Connect with other users</li>
                    <li>• Join discussions</li>
                    <li>• Share content</li>
                  </ul>
                </div>
              </div>
            </div>
          </Card>

          {/* System Status */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">System Status</h3>
              <div className="space-y-2">
                <div className="flex items-center">
                  <div className="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                  <span className="text-sm text-gray-600">Platform Online</span>
                </div>
                <div className="flex items-center">
                  <div className="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                  <span className="text-sm text-gray-600">Database Connected</span>
                </div>
                <div className="flex items-center">
                  <div className="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                  <span className="text-sm text-gray-600">Authentication Active</span>
                </div>
              </div>
            </div>
          </Card>
        </div>

        {/* Footer Note */}
        <div className="mt-8 text-center">
          <p className="text-sm text-gray-500">
            This is your personal user dashboard. Create content, manage your profile, and explore the platform.
          </p>
        </div>
      </main>
    </div>
  );
};

export default DashboardPage; 