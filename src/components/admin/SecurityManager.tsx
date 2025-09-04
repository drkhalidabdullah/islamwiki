import React, { useState, useEffect } from 'react';
import { Card, Button } from '../index';

interface SecurityEvent {
  id: number;
  type: 'login_attempt' | 'suspicious_activity' | 'failed_login' | 'admin_action' | 'file_upload';
  severity: 'low' | 'medium' | 'high' | 'critical';
  description: string;
  ip_address: string;
  user_agent: string;
  timestamp: string;
  resolved: boolean;
}

interface SecurityStats {
  total_events: number;
  critical_events: number;
  high_events: number;
  medium_events: number;
  low_events: number;
  resolved_events: number;
  active_threats: number;
  blocked_ips: number;
}

const SecurityManager: React.FC = () => {
  const [securityEvents, setSecurityEvents] = useState<SecurityEvent[]>([]);
  const [securityStats, setSecurityStats] = useState<SecurityStats | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedEvent, setSelectedEvent] = useState<SecurityEvent | null>(null);
  const [isEventModalOpen, setIsEventModalOpen] = useState(false);

  // Mock data for development
  const mockSecurityEvents: SecurityEvent[] = [
    {
      id: 1,
      type: 'failed_login',
      severity: 'medium',
      description: 'Multiple failed login attempts from IP 192.168.1.100',
      ip_address: '192.168.1.100',
      user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      timestamp: '2025-01-27T15:30:00Z',
      resolved: false
    },
    {
      id: 2,
      type: 'suspicious_activity',
      severity: 'high',
      description: 'Unusual file upload pattern detected',
      ip_address: '203.0.113.45',
      user_agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
      timestamp: '2025-01-27T14:45:00Z',
      resolved: true
    },
    {
      id: 3,
      type: 'admin_action',
      severity: 'low',
      description: 'Admin user changed system settings',
      ip_address: '10.0.0.50',
      user_agent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
      timestamp: '2025-01-27T13:20:00Z',
      resolved: true
    },
    {
      id: 4,
      type: 'login_attempt',
      severity: 'critical',
      description: 'Brute force attack detected from multiple IPs',
      ip_address: '198.51.100.25',
      user_agent: 'Unknown',
      timestamp: '2025-01-27T12:15:00Z',
      resolved: false
    }
  ];

  const mockSecurityStats: SecurityStats = {
    total_events: 156,
    critical_events: 3,
    high_events: 12,
    medium_events: 45,
    low_events: 96,
    resolved_events: 142,
    active_threats: 14,
    blocked_ips: 8
  };

  useEffect(() => {
    // Simulate API call
    setTimeout(() => {
      setSecurityEvents(mockSecurityEvents);
      setSecurityStats(mockSecurityStats);
      setIsLoading(false);
    }, 1000);
  }, []);

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
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getEventTypeIcon = (type: string) => {
    switch (type) {
      case 'login_attempt':
        return (
          <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
        );
      case 'suspicious_activity':
        return (
          <svg className="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        );
      case 'failed_login':
        return (
          <svg className="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
          </svg>
        );
      case 'admin_action':
        return (
          <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        );
      case 'file_upload':
        return (
          <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
        );
      default:
        return (
          <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
    }
  };

  const handleResolveEvent = (eventId: number) => {
    setSecurityEvents(prev => 
      prev.map(event => 
        event.id === eventId ? { ...event, resolved: true } : event
      )
    );
  };

  const handleBlockIP = (ipAddress: string) => {
    // TODO: Implement IP blocking
    alert(`IP ${ipAddress} blocked successfully`);
  };

  const handleViewEvent = (event: SecurityEvent) => {
    setSelectedEvent(event);
    setIsEventModalOpen(true);
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
          <h1 className="text-2xl font-bold text-gray-900">Security Management</h1>
          <p className="text-gray-600">Monitor and manage security threats and events</p>
        </div>
        <div className="flex space-x-3">
          <Button variant="outline">
            Export Security Report
          </Button>
          <Button variant="primary">
            Run Security Scan
          </Button>
        </div>
      </div>

      {/* Security Statistics */}
      {securityStats && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                  <p className="text-sm font-medium text-gray-500">Active Threats</p>
                  <p className="text-2xl font-semibold text-gray-900">{securityStats.active_threats}</p>
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Blocked IPs</p>
                  <p className="text-2xl font-semibold text-gray-900">{securityStats.blocked_ips}</p>
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
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-500">Critical Events</p>
                  <p className="text-2xl font-semibold text-gray-900">{securityStats.critical_events}</p>
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
                  <p className="text-sm font-medium text-gray-500">Resolved Events</p>
                  <p className="text-2xl font-semibold text-gray-900">{securityStats.resolved_events}</p>
                </div>
              </div>
            </div>
          </Card>
        </div>
      )}

      {/* Security Events Table */}
      <Card>
        <div className="p-6">
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-lg font-semibold text-gray-900">Security Events</h3>
            <div className="flex space-x-2">
              <select className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="all">All Severities</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
              <select className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="all">All Types</option>
                <option value="login_attempt">Login Attempts</option>
                <option value="suspicious_activity">Suspicious Activity</option>
                <option value="failed_login">Failed Logins</option>
                <option value="admin_action">Admin Actions</option>
                <option value="file_upload">File Uploads</option>
              </select>
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {securityEvents.map((event) => (
                  <tr key={event.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="flex-shrink-0">
                          {getEventTypeIcon(event.type)}
                        </div>
                        <div className="ml-3">
                          <div className="text-sm font-medium text-gray-900">
                            {event.description}
                          </div>
                          <div className="text-sm text-gray-500">
                            {event.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getSeverityColor(event.severity)}`}>
                        {event.severity.charAt(0).toUpperCase() + event.severity.slice(1)}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {event.ip_address}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {new Date(event.timestamp).toLocaleString()}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {event.resolved ? (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Resolved
                        </span>
                      ) : (
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          Active
                        </span>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex space-x-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleViewEvent(event)}
                        >
                          View
                        </Button>
                        {!event.resolved && (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleResolveEvent(event.id)}
                          >
                            Resolve
                          </Button>
                        )}
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleBlockIP(event.ip_address)}
                          className="text-red-600 hover:text-red-800"
                        >
                          Block IP
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </Card>

      {/* Security Settings */}
      <Card>
        <div className="p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Security Settings</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 className="font-medium text-gray-900 mb-4">Authentication Security</h4>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div>
                    <h5 className="text-sm font-medium text-gray-900">Two-Factor Authentication</h5>
                    <p className="text-sm text-gray-500">Require 2FA for all admin accounts</p>
                  </div>
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" defaultChecked className="sr-only peer" />
                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                  </label>
                </div>
                
                <div className="flex items-center justify-between">
                  <div>
                    <h5 className="text-sm font-medium text-gray-900">Session Timeout</h5>
                    <p className="text-sm text-gray-500">Auto-logout after inactivity</p>
                  </div>
                  <select className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="15">15 minutes</option>
                    <option value="30" selected>30 minutes</option>
                    <option value="60">1 hour</option>
                    <option value="120">2 hours</option>
                  </select>
                </div>
                
                <div className="flex items-center justify-between">
                  <div>
                    <h5 className="text-sm font-medium text-gray-900">Failed Login Lockout</h5>
                    <p className="text-sm text-gray-500">Lock account after failed attempts</p>
                  </div>
                  <select className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="3">3 attempts</option>
                    <option value="5" selected>5 attempts</option>
                    <option value="10">10 attempts</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div>
              <h4 className="font-medium text-gray-900 mb-4">System Security</h4>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div>
                    <h5 className="text-sm font-medium text-gray-900">File Upload Scanning</h5>
                    <p className="text-sm text-gray-500">Scan uploaded files for malware</p>
                  </div>
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" defaultChecked className="sr-only peer" />
                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                  </label>
                </div>
                
                <div className="flex items-center justify-between">
                  <div>
                    <h5 className="text-sm font-medium text-gray-900">Rate Limiting</h5>
                    <p className="text-sm text-gray-500">Limit API requests per IP</p>
                  </div>
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" defaultChecked className="sr-only peer" />
                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                  </label>
                </div>
                
                <div className="flex items-center justify-between">
                  <div>
                    <h5 className="text-sm font-medium text-gray-900">Security Headers</h5>
                    <p className="text-sm text-gray-500">Enable security HTTP headers</p>
                  </div>
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" defaultChecked className="sr-only peer" />
                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                  </label>
                </div>
              </div>
            </div>
          </div>
          
          <div className="mt-6 pt-6 border-t border-gray-200">
            <Button variant="primary">
              Save Security Settings
            </Button>
          </div>
        </div>
      </Card>

      {/* Event Detail Modal */}
      {isEventModalOpen && selectedEvent && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900">Security Event Details</h3>
                <button
                  onClick={() => setIsEventModalOpen(false)}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Event Type</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {selectedEvent.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                  </p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Severity</label>
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getSeverityColor(selectedEvent.severity)}`}>
                    {selectedEvent.severity.charAt(0).toUpperCase() + selectedEvent.severity.slice(1)}
                  </span>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Description</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedEvent.description}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">IP Address</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedEvent.ip_address}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">User Agent</label>
                  <p className="mt-1 text-sm text-gray-900">{selectedEvent.user_agent}</p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Timestamp</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {new Date(selectedEvent.timestamp).toLocaleString()}
                  </p>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Status</label>
                  <p className="mt-1 text-sm text-gray-900">
                    {selectedEvent.resolved ? 'Resolved' : 'Active'}
                  </p>
                </div>
              </div>
              
              <div className="mt-6 flex justify-end space-x-3">
                <Button variant="outline" onClick={() => setIsEventModalOpen(false)}>
                  Close
                </Button>
                {!selectedEvent.resolved && (
                  <Button variant="primary" onClick={() => {
                    handleResolveEvent(selectedEvent.id);
                    setIsEventModalOpen(false);
                  }}>
                    Mark as Resolved
                  </Button>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default SecurityManager; 