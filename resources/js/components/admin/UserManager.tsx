import React, { useState, useEffect } from 'react';
import { Card, Button, Input, Modal } from '../index';

interface User {
  id: number;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
  display_name: string;
  is_active: boolean;
  is_banned: boolean;
  email_verified_at: string | null;
  last_login_at: string | null;
  created_at: string;
  roles: string[];
}

interface Role {
  id: number;
  name: string;
  display_name: string;
  description: string;
}

interface UserStatistics {
  total_users: number;
  active_users: number;
  banned_users: number;
  verified_users: number;
  new_users_today: number;
  new_users_week: number;
  new_users_month: number;
}

const UserManager: React.FC = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [statistics, setStatistics] = useState<UserStatistics | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedRole, setSelectedRole] = useState('');

  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isRoleModalOpen, setIsRoleModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    first_name: '',
    last_name: '',
    password: '',
    password_confirmation: ''
  });

  // Mock data for development
  const mockUsers: User[] = [
    {
      id: 1,
      username: 'admin',
      email: 'admin@islamwiki.org',
      first_name: 'Admin',
      last_name: 'User',
      display_name: 'Administrator',
      is_active: true,
      is_banned: false,
      email_verified_at: '2025-01-27T10:00:00Z',
      last_login_at: '2025-01-27T15:30:00Z',
      created_at: '2025-01-01T00:00:00Z',
      roles: ['admin']
    },
    {
      id: 2,
      username: 'testuser',
      email: 'test@islamwiki.org',
      first_name: 'Test',
      last_name: 'User',
      display_name: 'Test User',
      is_active: true,
      is_banned: false,
      email_verified_at: '2025-01-27T11:00:00Z',
      last_login_at: '2025-01-27T14:20:00Z',
      created_at: '2025-01-15T00:00:00Z',
      roles: ['user']
    },
    {
      id: 3,
      username: 'moderator',
      email: 'mod@islamwiki.org',
      first_name: 'Moderator',
      last_name: 'User',
      display_name: 'Content Moderator',
      is_active: true,
      is_banned: false,
      email_verified_at: '2025-01-27T12:00:00Z',
      last_login_at: '2025-01-27T13:45:00Z',
      created_at: '2025-01-20T00:00:00Z',
      roles: ['moderator']
    }
  ];

  const mockRoles: Role[] = [
    {
      id: 1,
      name: 'admin',
      display_name: 'Administrator',
      description: 'Full system access and control'
    },
    {
      id: 2,
      name: 'moderator',
      display_name: 'Moderator',
      description: 'Content moderation and user management'
    },
    {
      id: 3,
      name: 'editor',
      display_name: 'Editor',
      description: 'Content creation and editing'
    },
    {
      id: 4,
      name: 'user',
      display_name: 'User',
      description: 'Standard user access'
    }
  ];

  const mockStatistics: UserStatistics = {
    total_users: 156,
    active_users: 142,
    banned_users: 3,
    verified_users: 134,
    new_users_today: 5,
    new_users_week: 23,
    new_users_month: 89
  };

  useEffect(() => {
    // Simulate API call
    setTimeout(() => {
      setUsers(mockUsers);
      setRoles(mockRoles);
      setStatistics(mockStatistics);
      setIsLoading(false);
    }, 1000);
  }, []);

  const filteredUsers = users.filter(user => {
    const matchesSearch = user.username.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         user.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         user.display_name.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesRole = !selectedRole || user.roles.includes(selectedRole);
    return matchesSearch && matchesRole;
  });

  const handleCreateUser = () => {
    // TODO: Implement API call
    alert('Create user functionality coming soon!');
    setIsCreateModalOpen(false);
  };

  const handleEditUser = (user: User) => {
    setSelectedUser(user);
    setIsEditModalOpen(true);
  };

  const handleUpdateUser = () => {
    // TODO: Implement API call
    alert('Update user functionality coming soon!');
    setIsEditModalOpen(false);
  };

  const handleDeleteUser = () => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
      // TODO: Implement API call
      alert('Delete user functionality coming soon!');
    }
  };

  const handleToggleUserStatus = (isActive: boolean) => {
    // TODO: Implement API call
    alert(`${isActive ? 'Deactivate' : 'Activate'} user functionality coming soon!`);
  };

  const handleAssignRole = (userId: number) => {
    setSelectedUser(users.find(u => u.id === userId) || null);
    setIsRoleModalOpen(true);
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
          <h1 className="text-2xl font-bold text-gray-900">User Management</h1>
          <p className="text-gray-600">Manage users, roles, and permissions</p>
        </div>
        <Button variant="primary" onClick={() => setIsCreateModalOpen(true)}>
          Create User
        </Button>
      </div>

      {/* Statistics Cards */}
      {statistics && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Total Users</p>
                  <p className="text-2xl font-semibold text-gray-900">{statistics.total_users}</p>
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
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Active Users</p>
                  <p className="text-2xl font-semibold text-gray-900">{statistics.active_users}</p>
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
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">New This Month</p>
                  <p className="text-2xl font-semibold text-gray-900">{statistics.new_users_month}</p>
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Banned Users</p>
                  <p className="text-2xl font-semibold text-gray-900">{statistics.banned_users}</p>
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
                placeholder="Search users by username, email, or name..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <div className="w-full sm:w-48">
              <select
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                value={selectedRole}
                onChange={(e) => setSelectedRole(e.target.value)}
              >
                <option value="">All Roles</option>
                {roles.map(role => (
                  <option key={role.id} value={role.name}>{role.display_name}</option>
                ))}
              </select>
            </div>
          </div>
        </div>
      </Card>

      {/* Users Table */}
      <Card>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredUsers.map(user => (
                <tr key={user.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className="flex-shrink-0 h-10 w-10">
                        <div className="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                          <span className="text-sm font-medium text-gray-700">
                            {user.first_name.charAt(0)}{user.last_name.charAt(0)}
                          </span>
                        </div>
                      </div>
                      <div className="ml-4">
                        <div className="text-sm font-medium text-gray-900">{user.display_name}</div>
                        <div className="text-sm text-gray-500">{user.email}</div>
                        <div className="text-sm text-gray-500">@{user.username}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex flex-wrap gap-1">
                      {user.roles.map(role => (
                        <span
                          key={role}
                          className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                        >
                          {role}
                        </span>
                      ))}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      {user.is_active ? (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Active
                        </span>
                      ) : (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          Inactive
                        </span>
                      )}
                      {user.is_banned && (
                        <span className="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          Banned
                        </span>
                      )}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : 'Never'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(user.created_at).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex space-x-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleEditUser(user)}
                      >
                        Edit
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleAssignRole(user.id)}
                      >
                        Roles
                      </Button>
                      <Button
                        variant={user.is_active ? "outline" : "primary"}
                        size="sm"
                        onClick={() => handleToggleUserStatus(user.is_active)}
                      >
                        {user.is_active ? 'Deactivate' : 'Activate'}
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleDeleteUser()}
                        className="text-red-600 hover:text-red-800"
                      >
                        Delete
                      </Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>

      {/* Create User Modal */}
      <Modal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        title="Create New User"
      >
        <div className="space-y-4">
          <Input
            label="Username"
            value={formData.username}
            onChange={(e) => setFormData({...formData, username: e.target.value})}
            placeholder="Enter username"
          />
          <Input
            label="Email"
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
            placeholder="Enter email"
          />
          <Input
            label="First Name"
            value={formData.first_name}
            onChange={(e) => setFormData({...formData, first_name: e.target.value})}
            placeholder="Enter first name"
          />
          <Input
            label="Last Name"
            value={formData.last_name}
            onChange={(e) => setFormData({...formData, last_name: e.target.value})}
            placeholder="Enter last name"
          />
          <Input
            label="Password"
            type="password"
            value={formData.password}
            onChange={(e) => setFormData({...formData, password: e.target.value})}
            placeholder="Enter password"
          />
          <Input
            label="Confirm Password"
            type="password"
            value={formData.password_confirmation}
            onChange={(e) => setFormData({...formData, password_confirmation: e.target.value})}
            placeholder="Confirm password"
          />
          <div className="flex justify-end space-x-3 pt-4">
            <Button variant="outline" onClick={() => setIsCreateModalOpen(false)}>
              Cancel
            </Button>
            <Button variant="primary" onClick={handleCreateUser}>
              Create User
            </Button>
          </div>
        </div>
      </Modal>

      {/* Edit User Modal */}
      <Modal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        title="Edit User"
      >
        {selectedUser && (
          <div className="space-y-4">
            <Input
              label="Username"
              value={selectedUser.username}
              onChange={(e) => setSelectedUser({...selectedUser, username: e.target.value})}
            />
            <Input
              label="Email"
              type="email"
              value={selectedUser.email}
              onChange={(e) => setSelectedUser({...selectedUser, email: e.target.value})}
            />
            <Input
              label="First Name"
              value={selectedUser.first_name}
              onChange={(e) => setSelectedUser({...selectedUser, first_name: e.target.value})}
            />
            <Input
              label="Last Name"
              value={selectedUser.last_name}
              onChange={(e) => setSelectedUser({...selectedUser, last_name: e.target.value})}
            />
            <div className="flex justify-end space-x-3 pt-4">
              <Button variant="outline" onClick={() => setIsEditModalOpen(false)}>
                Cancel
              </Button>
              <Button variant="primary" onClick={handleUpdateUser}>
                Update User
              </Button>
            </div>
          </div>
        )}
      </Modal>

      {/* Role Management Modal */}
      <Modal
        isOpen={isRoleModalOpen}
        onClose={() => setIsRoleModalOpen(false)}
        title="Manage User Roles"
      >
        {selectedUser && (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">
                Current Roles for {selectedUser.display_name}
              </h3>
              <div className="flex flex-wrap gap-2 mb-4">
                {selectedUser.roles.map(role => (
                  <span
                    key={role}
                    className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                  >
                    {role}
                    <button
                      className="ml-2 text-blue-600 hover:text-blue-800"
                      onClick={() => {
                        // TODO: Implement role removal
                        alert('Role removal functionality coming soon!');
                      }}
                    >
                      ×
                    </button>
                  </span>
                ))}
              </div>
            </div>
            <div>
              <h4 className="text-md font-medium text-gray-900 mb-2">Assign New Role</h4>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">Select a role</option>
                {roles.filter(role => !selectedUser.roles.includes(role.name)).map(role => (
                  <option key={role.id} value={role.name}>{role.display_name}</option>
                ))}
              </select>
            </div>
            <div className="flex justify-end space-x-3 pt-4">
              <Button variant="outline" onClick={() => setIsRoleModalOpen(false)}>
                Close
              </Button>
              <Button variant="primary" onClick={() => {
                // TODO: Implement role assignment
                alert('Role assignment functionality coming soon!');
                setIsRoleModalOpen(false);
              }}>
                Assign Role
              </Button>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default UserManager; 