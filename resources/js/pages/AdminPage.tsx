import React from 'react';
import AdminDashboard from '../components/admin/AdminDashboard';

const AdminPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>
        <AdminDashboard />
      </div>
    </div>
  );
};

export default AdminPage; 