import React, { useState } from 'react';
import Header from '../components/layout/Header';
import Footer from '../components/layout/Footer';
import AdminDashboard from '../components/admin/AdminDashboard';
import TestingDashboard from '../components/admin/TestingDashboard';
import PerformanceMonitor from '../components/admin/PerformanceMonitor';
import DevelopmentWorkflow from '../components/admin/DevelopmentWorkflow';
import SystemHealth from '../components/admin/SystemHealth';

type AdminView = 'overview' | 'testing' | 'performance' | 'workflow' | 'health';

const AdminPage: React.FC = () => {
  const [currentView, setCurrentView] = useState<AdminView>('overview');

  const renderCurrentView = () => {
    switch (currentView) {
      case 'testing':
        return <TestingDashboard />;
      case 'performance':
        return <PerformanceMonitor />;
      case 'workflow':
        return <DevelopmentWorkflow />;
      case 'health':
        return <SystemHealth />;
      default:
        return <AdminDashboard />;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      <main className="flex-grow">
        <div className="flex">
          {/* Sidebar Navigation */}
          <div className="w-64 bg-white border-r border-gray-200 min-h-screen p-4">
            <div className="mb-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-2">Admin Tools</h3>
              <p className="text-sm text-gray-600">v0.0.3 Complete</p>
            </div>
            
            <nav className="space-y-2">
              <button
                onClick={() => setCurrentView('overview')}
                className={`w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                  currentView === 'overview'
                    ? 'bg-green-100 text-green-700 border-r-2 border-green-500'
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                <div className="flex items-center">
                  <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  Overview
                </div>
              </button>
              
              <button
                onClick={() => setCurrentView('testing')}
                className={`w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                  currentView === 'testing'
                    ? 'bg-green-100 text-green-700 border-r-2 border-green-500'
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                <div className="flex items-center">
                  <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Testing Dashboard
                </div>
              </button>
              
              <button
                onClick={() => setCurrentView('performance')}
                className={`w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                  currentView === 'performance'
                    ? 'bg-green-100 text-green-700 border-r-2 border-green-500'
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                <div className="flex items-center">
                  <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  Performance Monitor
                </div>
              </button>
              
              <button
                onClick={() => setCurrentView('workflow')}
                className={`w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                  currentView === 'workflow'
                    ? 'bg-green-100 text-green-700 border-r-2 border-green-500'
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                <div className="flex items-center">
                  <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2m14 0V5a2 2 0 00-2-2H5a2 2 0 00-2 2v2" />
                  </svg>
                  Development Workflow
                </div>
              </button>
              
              <button
                onClick={() => setCurrentView('health')}
                className={`w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
                  currentView === 'health'
                    ? 'bg-green-100 text-green-700 border-r-2 border-green-500'
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                <div className="flex items-center">
                  <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                  System Health
                </div>
              </button>
            </nav>
            
            <div className="mt-8 p-4 bg-gray-50 rounded-lg">
              <h4 className="text-sm font-medium text-gray-900 mb-2">Quick Stats</h4>
              <div className="space-y-2 text-xs text-gray-600">
                <div className="flex justify-between">
                  <span>Tests Passing:</span>
                  <span className="font-medium text-green-600">75/75</span>
                </div>
                <div className="flex justify-between">
                  <span>System Status:</span>
                  <span className="font-medium text-green-600">Healthy</span>
                </div>
                <div className="flex justify-between">
                  <span>Last Updated:</span>
                  <span className="font-medium">{new Date().toLocaleDateString()}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Main Content Area */}
          <div className="flex-1">
            <div className="max-w-7xl mx-auto p-6">
              <div className="mb-6">
                <h1 className="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p className="text-lg text-gray-600 mt-1">v0.0.3 - Complete Development & Monitoring Tools</p>
              </div>
              
              {renderCurrentView()}
            </div>
          </div>
        </div>
      </main>
      
      <Footer />
    </div>
  );
};

export default AdminPage; 