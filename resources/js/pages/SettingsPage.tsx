import React, { useState, useEffect } from 'react';
import { useAuthStore } from '../store/authStore';
import { useNavigate } from 'react-router-dom';
import Card from '../components/ui/Card';
import Button from '../components/ui/Button';
import Input from '../components/ui/Input';
import Textarea from '../components/ui/Textarea';
import Modal from '../components/ui/Modal';
import { settingsService, UserSettings } from '../services/settingsService';

const SettingsPage: React.FC = () => {
  const { user, isAuthenticated, setUser } = useAuthStore();
  const navigate = useNavigate();
  const [settings, setSettings] = useState<UserSettings>({
    account: {
      username: '',
      email: '',
      first_name: '',
      last_name: '',
      phone: '',
      date_of_birth: '',
      gender: '',
      location: '',
      website: '',
      bio: '',
      display_name: '',
      avatar_url: '',
      social_links: {}
    },
    preferences: {
      email_notifications: true,
      push_notifications: true,
      profile_public: true,
      show_email: false,
      show_last_seen: true,
      language: 'en',
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      theme: 'auto',
      content_language: 'en',
      notification_sound: true,
      email_digest: 'weekly',
      content_preferences: {
        show_nsfw_content: false,
        content_rating: 'G',
        auto_translate: false,
        translation_language: 'en'
      }
    },
    security: {
      two_factor_enabled: false,
      two_factor_method: 'totp',
      session_timeout: 30,
      login_notifications: true,
      password_change_required: false,
      security_alerts: true,
      max_concurrent_sessions: 5,
      trusted_devices: [],
      security_questions: []
    },
    privacy: {
      profile_visibility: 'public',
      activity_visibility: 'friends',
      search_visibility: true,
      analytics_consent: true,
      data_export: false,
      data_deletion: false,
      third_party_sharing: false,
      location_sharing: false,
      contact_info_visibility: 'friends'
    },
    notifications: {
      content_updates: true,
      comment_replies: true,
      mentions: true,
      new_followers: true,
      security_alerts: true,
      system_announcements: true,
      marketing_emails: false,
      digest_frequency: 'weekly'
    },
    accessibility: {
      high_contrast: false,
      screen_reader_support: true,
      keyboard_navigation: true,
      reduced_motion: false,
      color_blind_support: false,
      font_size: 'medium',
      display_mode: 'standard'
    }
  });

  const [activeTab, setActiveTab] = useState<'account' | 'preferences' | 'security' | 'privacy' | 'notifications' | 'accessibility'>('account');
  const [isLoading, setIsLoading] = useState(false);
  const [showToast, setShowToast] = useState(false);
  const [toastMessage, setToastMessage] = useState('');
  const [toastType, setToastType] = useState<'success' | 'error'>('success');
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteConfirmation, setDeleteConfirmation] = useState('');

  // Handle authentication state changes and load settings
  useEffect(() => {
    if (isAuthenticated && user) {
      // User is authenticated, load their settings
      console.log('🔐 User is authenticated, loading settings...');
      loadUserSettings();
    } else if (!isAuthenticated) {
      // User is not authenticated, clear accessibility classes and redirect
      console.log('🔐 User is not authenticated, clearing accessibility classes and redirecting');
      document.documentElement.classList.remove('high-contrast', 'large-text');
      document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      console.log('🎨 Cleared accessibility classes, final classes:', document.documentElement.className);
      navigate('/login');
    }
  }, [isAuthenticated, user]); // Removed navigate from dependencies to prevent infinite loop

  // Apply accessibility settings immediately when component mounts and user is authenticated
  useEffect(() => {
    if (isAuthenticated && user && settings.accessibility) {
      console.log('🎨 Component mounted with authenticated user, applying current accessibility settings');
      
      // Check if this is for the current user
      const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
      if (currentAccessibilityUser && currentAccessibilityUser !== user.username) {
        console.log('🎨 Different user accessibility detected, clearing first:', currentAccessibilityUser);
        document.documentElement.classList.remove('high-contrast', 'large-text');
        document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      }
      
      // Set current user
      document.documentElement.setAttribute('data-accessibility-user', user.username);
      
      // Apply current accessibility settings immediately to document
      if (settings.accessibility.high_contrast) {
        document.documentElement.classList.add('high-contrast');
        console.log('🔴 Applied high-contrast class on mount to document for user:', user.username);
      } else {
        document.documentElement.classList.remove('high-contrast');
      }
      

      
      // Apply font size
      document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      document.documentElement.classList.add(`font-size-${settings.accessibility.font_size}`);
      console.log('🔤 Applied font-size-${settings.accessibility.font_size} class on mount to document for user:', user.username);
      
      // Apply display mode
      document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
      document.documentElement.classList.add(`display-mode-${settings.accessibility.display_mode}`);
      console.log('🖥️ Applied display-mode-${settings.accessibility.display_mode} class on mount to document for user:', user.username);
    }
  }, [isAuthenticated, user, settings.accessibility]);

  // Apply accessibility settings when they change - SCOPE TO CURRENT USER
  useEffect(() => {
    console.log('🎨 Accessibility useEffect triggered for user:', user?.username, 'settings:', settings.accessibility);
    
    // Create user-specific accessibility container if it doesn't exist
    let userAccessibilityContainer = document.getElementById(`accessibility-${user?.username}`);
    if (!userAccessibilityContainer && user) {
      userAccessibilityContainer = document.createElement('div');
      userAccessibilityContainer.id = `accessibility-${user.username}`;
      userAccessibilityContainer.className = 'user-accessibility-container';
      document.body.appendChild(userAccessibilityContainer);
      console.log('🎨 Created accessibility container for user:', user.username);
    }
    
    if (userAccessibilityContainer) {
      // Apply high contrast
      if (settings.accessibility.high_contrast) {
        console.log('🔴 Adding high-contrast class to user container');
        userAccessibilityContainer.classList.add('high-contrast');
      } else {
        console.log('⚪ Removing high-contrast class from user container');
        userAccessibilityContainer.classList.remove('high-contrast');
      }



      // Apply font size
      const oldFontSize = userAccessibilityContainer.className.match(/font-size-\w+/);
      console.log('🔤 Old font size class:', oldFontSize);
      userAccessibilityContainer.className = userAccessibilityContainer.className.replace(/font-size-\w+/, '');
      userAccessibilityContainer.classList.add(`font-size-${settings.accessibility.font_size}`);
      console.log('🔤 New font size class: font-size-${settings.accessibility.font_size}');
      
      console.log('🎨 Final user container classes:', userAccessibilityContainer.className);
    }
  }, [settings.accessibility, user]);

  // Apply accessibility settings when user authentication state changes
  useEffect(() => {
    console.log('🔐 Auth state changed - isAuthenticated:', isAuthenticated, 'user:', user);
    
    if (isAuthenticated && user) {
      console.log('🔐 User logged in, applying saved accessibility settings');
      // Settings will be loaded and accessibility useEffect will handle applying them
    } else {
      console.log('🔐 User logged out, removing all accessibility classes');
      // Remove all accessibility classes when user logs out
      // Remove from document element (global fallback)
      document.documentElement.classList.remove('high-contrast');
      document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
      
      console.log('🎨 Removed accessibility classes, final document classes:', document.documentElement.className);
    }
  }, [isAuthenticated, user]);

  // Show toast notification
  const showToastNotification = (message: string, type: 'success' | 'error' = 'success') => {
    console.log('🍞 Toast notification:', { message, type });
    setToastMessage(message);
    setToastType(type);
    setShowToast(true);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
      setShowToast(false);
    }, 3000);
  };

  // Debug: Monitor settings state changes
  useEffect(() => {
    console.log('🔄 Settings state changed:', settings);
    console.log('🔍 Current gender in state:', settings.account.gender);
    console.log('🎨 Accessibility state changed:', settings.accessibility);
  }, [settings]);

  const loadUserSettings = async () => {
    // Double-check authentication before making API call
    if (!isAuthenticated || !user) {
      console.log('Authentication check failed, redirecting to login');
      navigate('/login');
      return;
    }

    // Prevent multiple simultaneous calls
    if (isLoading) {
      console.log('🔄 Already loading settings, skipping duplicate call');
      return;
    }

    try {
      console.log('🔄 Loading user settings from server...');
      const response = await settingsService.getUserSettings();
      console.log('📡 Server response:', response);
      if (response.success && response.data) {
        console.log('✅ Settings loaded successfully:', response.data);
        console.log('🔍 Current gender value:', response.data.account.gender);
        console.log('🎨 Accessibility settings from server:', response.data.accessibility);
        
        // Update settings from server
        console.log('🔍 Setting settings to:', response.data);
        setSettings(response.data);
        
        // Apply accessibility settings immediately after loading
        console.log('🎨 Applying accessibility settings from loaded data:', response.data.accessibility);
        
        // Check if this is for the current user
        const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
        if (currentAccessibilityUser && currentAccessibilityUser !== user.username) {
          console.log('🎨 Different user accessibility detected, clearing first:', currentAccessibilityUser);
          document.documentElement.classList.remove('high-contrast', 'large-text');
          document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
        }
        
        // Set current user
        document.documentElement.setAttribute('data-accessibility-user', user.username);
        
        // Apply accessibility directly to document
        if (response.data.accessibility.high_contrast) {
          document.documentElement.classList.add('high-contrast');
          console.log('🔴 Applied high-contrast class from loaded settings to document for user:', user.username);
        } else {
          document.documentElement.classList.remove('high-contrast');
        }
        

        
        // Apply font size
        document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
        document.documentElement.classList.add(`font-size-${response.data.accessibility.font_size}`);
        console.log('🔤 Applied font-size-${response.data.accessibility.font_size} class from loaded settings to document for user:', user.username);
        
        // Apply display mode
        document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
        document.documentElement.classList.add(`display-mode-${response.data.accessibility.display_mode}`);
        console.log('🖥️ Applied display-mode-${response.data.accessibility.display_mode} class from loaded settings to document for user:', user.username);
        
        console.log('🔄 State update triggered');
      } else {
        console.log('❌ Failed to load settings:', response.error);
      }
    } catch (error) {
      console.error('Failed to load settings:', error);
      // If we get a 401 error, redirect to login
      if (error instanceof Error && error.message.includes('401')) {
        navigate('/login');
      }
    }
  };

  const handleSaveSettings = async () => {
    console.log('💾 handleSaveSettings called with settings:', settings);
    console.log('🎨 Current accessibility settings:', settings.accessibility);
    setIsLoading(true);

    try {
      // Validate settings before saving
      const validation = settingsService.validateSettings(settings);
      if (!validation.isValid) {
        showToastNotification(`Validation errors: ${validation.errors.join(', ')}`, 'error');
        setIsLoading(false);
        return;
      }

      // Save all sections that have been modified
      const sectionsToSave: (keyof UserSettings)[] = ['account', 'preferences', 'security', 'privacy', 'notifications', 'accessibility'];
      console.log('💾 Saving sections:', sectionsToSave);
      let saveSuccess = true;
      let errorMessage = '';

      for (const section of sectionsToSave) {
        console.log(`💾 Saving ${section} section:`, settings[section]);
        try {
          const response = await settingsService.updateSettingsSection(section, settings[section]);
          console.log(`💾 ${section} save response:`, response);
          if (!response.success) {
            saveSuccess = false;
            errorMessage = `Failed to save ${section} settings: ${response.error}`;
            break;
          }
        } catch (error) {
          console.error(`💾 Error saving ${section}:`, error);
          saveSuccess = false;
          errorMessage = `Failed to save ${section} settings: ${error}`;
          break;
        }
      }

      if (!saveSuccess) {
        throw new Error(errorMessage);
      }

      // Update local user data
      if (setUser && user) {
        setUser({
          ...user,
          ...settings.account,
          preferences: settings.preferences,
        });
      }

      // Don't reload settings - keep the current state since we just saved it
      console.log('✅ All settings saved successfully - keeping current state');
      console.log('🎨 Final accessibility state:', settings.accessibility);

      // Trigger accessibility update after saving
      console.log('🎨 Triggering accessibility update after save');
      if (user) {
        const accessibilityUpdateEvent = new CustomEvent('accessibilitySettingsChanged', {
          detail: {
            username: user.username,
            accessibility: settings.accessibility
          }
        });
        window.dispatchEvent(accessibilityUpdateEvent);
      }

      showToastNotification('All settings saved successfully! 🎉');
    } catch (error) {
      showToastNotification('Failed to save settings. Please try again.', 'error');
    } finally {
      setIsLoading(false);
    }
  };

  const handleResetSettings = async () => {
    if (window.confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
      try {
        const response = await settingsService.resetSettingsToDefaults();
              if (response.success) {
        await loadUserSettings();
        showToastNotification('Settings reset to defaults successfully! 🔄');
      } else {
        showToastNotification('Failed to reset settings.', 'error');
      }
    } catch (error) {
      showToastNotification('Failed to reset settings.', 'error');
    }
    }
  };

  const handleExportData = async () => {
    try {
      const blob = await settingsService.exportUserData();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `islamwiki-user-data-${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error) {
      showToastNotification('Failed to export user data.', 'error');
    }
  };

  const handleDeleteAccount = async () => {
    if (deleteConfirmation === 'DELETE') {
      try {
        const response = await settingsService.deleteUserAccount(deleteConfirmation);
        if (response.success) {
          // Redirect to logout or home page
          window.location.href = '/';
        } else {
          showToastNotification('Failed to delete account.', 'error');
        }
      } catch (error) {
        showToastNotification('Failed to delete account.', 'error');
      }
    } else {
      showToastNotification('Please type DELETE to confirm account deletion.', 'error');
    }
  };

  const tabs = [
    { id: 'account', label: 'Account', icon: '👤' },
    { id: 'preferences', label: 'Preferences', icon: '⚙️' },
    { id: 'security', label: 'Security', icon: '🔒' },
    { id: 'privacy', label: 'Privacy', icon: '🛡️' },
    { id: 'notifications', label: 'Notifications', icon: '🔔' },
    { id: 'accessibility', label: 'Accessibility', icon: '♿' },
  ];

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      {/* Toast Notification */}
      {showToast && (
        <div className={`toast-notification fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 ${
          toastType === 'success' ? 'border-green-500' : 'border-red-500'
        } transform transition-all duration-300 ease-in-out ${
          showToast ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
        }`}>
          <div className="p-4">
            <div className="flex items-start">
              <div className="flex-shrink-0">
                {toastType === 'success' ? (
                  <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                    <svg className="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  </div>
                ) : (
                  <div className="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                    <svg className="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM15 12a3 3 0 11-6 0 3 3 0 016 0z" clipRule="evenodd" />
                    </svg>
                  </div>
                )}
              </div>
              <div className="ml-3 flex-1">
                <p className={`text-sm font-medium ${
                  toastType === 'success' ? 'text-green-800' : 'text-red-800'
                }`}>
                  {toastMessage}
                </p>
              </div>
              <div className="ml-4 flex-shrink-0">
                <button
                  onClick={() => setShowToast(false)}
                  className="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors"
                >
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Settings</h1>
          <p className="text-gray-600 mt-2">Manage your account settings and preferences</p>
        </div>



        {/* Settings Container */}
        <Card>
          <div className="p-6">
            {/* Tab Navigation */}
            <div className="border-b border-gray-200 mb-6">
              <nav className="-mb-px flex space-x-8 overflow-x-auto">
                {tabs.map((tab) => (
                  <button
                    key={tab.id}
                    onClick={() => setActiveTab(tab.id as any)}
                    className={`py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap ${
                      activeTab === tab.id
                        ? 'border-green-500 text-green-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    }`}
                  >
                    <span className="mr-2">{tab.icon}</span>
                    {tab.label}
                  </button>
                ))}
              </nav>
            </div>

            {/* Tab Content */}
            <div className="space-y-6">
              {/* Account Tab */}
              {activeTab === 'account' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Account Settings v10</h3>
                  
                  {/* TEST BUTTON - REMOVE AFTER TESTING */}

                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Username
                      </label>
                      <Input
                        type="text"
                        value={settings.account.username}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, username: e.target.value }
                        }))}
                        placeholder="Enter username"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Email
                      </label>
                      <Input
                        type="email"
                        value={settings.account.email}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, email: e.target.value }
                        }))}
                        placeholder="Enter email"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        First Name
                      </label>
                      <Input
                        type="text"
                        value={settings.account.first_name}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, first_name: e.target.value }
                        }))}
                        placeholder="Enter first name"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Last Name
                      </label>
                      <Input
                        type="text"
                        value={settings.account.last_name}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, last_name: e.target.value }
                        }))}
                        placeholder="Enter last name"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Display Name
                      </label>
                      <Input
                        type="text"
                        value={settings.account.display_name}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, display_name: e.target.value }
                        }))}
                        placeholder="Enter display name"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Phone
                      </label>
                      <Input
                        type="tel"
                        value={settings.account.phone}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, phone: e.target.value }
                        }))}
                        placeholder="Enter phone number"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Date of Birth
                      </label>
                      <Input
                        type="date"
                        value={settings.account.date_of_birth}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, date_of_birth: e.target.value }
                        }))}
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Gender
                      </label>
                      <select
                        value={settings.account.gender}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, gender: e.target.value }
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer not to say">Prefer not to say</option>
                      </select>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Location
                      </label>
                      <Input
                        type="text"
                        value={settings.account.location}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, location: e.target.value }
                        }))}
                        placeholder="Enter location"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Website
                      </label>
                      <Input
                        type="url"
                        value={settings.account.website}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...prev.account, website: e.target.value }
                        }))}
                        placeholder="https://example.com"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Bio
                    </label>
                    <Textarea
                      value={settings.account.bio}
                      onChange={(e) => setSettings(prev => ({
                        ...prev,
                        account: { ...prev.account, bio: e.target.value }
                      }))}
                      placeholder="Tell us about yourself..."
                      rows={4}
                    />
                  </div>
                </div>
              )}

              {/* Preferences Tab */}
              {activeTab === 'preferences' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Preferences</h3>
                  
                  <div className="space-y-6">
                    {/* Notifications */}
                    <div>
                      <h4 className="text-md font-medium text-gray-900 mb-4">Notifications</h4>
                      <div className="space-y-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Email Notifications</p>
                            <p className="text-sm text-gray-500">Receive notifications via email</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.preferences.email_notifications}
                              onChange={(e) => setSettings(prev => ({
                                ...prev,
                                preferences: { ...prev.preferences, email_notifications: e.target.checked }
                              }))}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Push Notifications</p>
                            <p className="text-sm text-gray-500">Receive push notifications</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.preferences.push_notifications}
                              onChange={(e) => setSettings(prev => ({
                                ...prev,
                                preferences: { ...prev.preferences, push_notifications: e.target.checked }
                              }))}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Notification Sound</p>
                            <p className="text-sm text-gray-500">Play sound for notifications</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.preferences.notification_sound}
                              onChange={(e) => setSettings(prev => ({
                                ...prev,
                                preferences: { ...prev.preferences, notification_sound: e.target.checked }
                              }))}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>
                      </div>
                    </div>

                    {/* Display Settings */}
                    <div>
                      <h4 className="text-md font-medium text-gray-900 mb-4">Display Settings</h4>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Language
                          </label>
                          <select
                            value={settings.preferences.language}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, language: e.target.value }
                            }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            {settingsService.getAvailableLanguages().map(lang => (
                              <option key={lang.code} value={lang.code}>
                                {lang.name} ({lang.native})
                              </option>
                            ))}
                          </select>
                        </div>

                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Theme
                          </label>
                          <select
                            value={settings.preferences.theme}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, theme: e.target.value as any }
                            }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            {settingsService.getAvailableThemes().map(theme => (
                              <option key={theme.value} value={theme.value}>
                                {theme.icon} {theme.label}
                              </option>
                            ))}
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* Security Tab */}
              {activeTab === 'security' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Security Settings</h3>
                  
                  <div className="space-y-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-700">Two-Factor Authentication</p>
                        <p className="text-sm text-gray-500">Add an extra layer of security to your account</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input
                          type="checkbox"
                          checked={settings.security.two_factor_enabled}
                          onChange={(e) => setSettings(prev => ({
                            ...prev,
                            security: { ...prev.security, two_factor_enabled: e.target.checked }
                          }))}
                          className="sr-only peer"
                        />
                        <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                      </label>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Session Timeout (minutes)
                      </label>
                      <select
                        value={settings.security.session_timeout}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          security: { ...prev.security, session_timeout: parseInt(e.target.value) }
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value={15}>15 minutes</option>
                        <option value={30}>30 minutes</option>
                        <option value={60}>1 hour</option>
                        <option value={120}>2 hours</option>
                        <option value={0}>Never (until logout)</option>
                      </select>
                    </div>

                    <div className="pt-4 border-t border-gray-200">
                      <Button variant="outline" className="text-red-600 hover:text-red-800">
                        Change Password
                      </Button>
                    </div>
                  </div>
                </div>
              )}

              {/* Privacy Tab */}
              {activeTab === 'privacy' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Privacy Settings</h3>
                  
                  <div className="space-y-6">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Profile Visibility
                      </label>
                      <select
                        value={settings.privacy.profile_visibility}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          privacy: { ...prev.privacy, profile_visibility: e.target.value as any }
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value="public">Public - Anyone can view</option>
                        <option value="friends">Friends - Only friends can view</option>
                        <option value="private">Private - Only you can view</option>
                      </select>
                    </div>

                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-700">Search Visibility</p>
                        <p className="text-sm text-gray-500">Allow others to find you in search results</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input
                          type="checkbox"
                          checked={settings.privacy.search_visibility}
                          onChange={(e) => setSettings(prev => ({
                            ...prev,
                            privacy: { ...prev.privacy, search_visibility: e.target.checked }
                          }))}
                          className="sr-only peer"
                        />
                        <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                      </label>
                    </div>
                  </div>
                </div>
              )}

              {/* Notifications Tab */}
              {activeTab === 'notifications' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Notification Preferences</h3>
                  
                  <div className="space-y-6">
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="text-sm font-medium text-gray-700">Content Updates</p>
                          <p className="text-sm text-gray-500">Notify when content you follow is updated</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={settings.notifications.content_updates}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              notifications: { ...prev.notifications, content_updates: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>

                      <div className="flex items-center justify-between">
                        <div>
                          <p className="text-sm font-medium text-gray-700">Comment Replies</p>
                          <p className="text-sm text-gray-500">Notify when someone replies to your comments</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={settings.notifications.comment_replies}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              notifications: { ...prev.notifications, comment_replies: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>

                      <div className="flex items-center justify-between">
                        <div>
                          <p className="text-sm font-medium text-gray-700">Mentions</p>
                          <p className="text-sm text-gray-500">Notify when someone mentions you</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={settings.notifications.mentions}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              notifications: { ...prev.notifications, mentions: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Digest Frequency
                      </label>
                      <select
                        value={settings.notifications.digest_frequency}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          notifications: { ...prev.notifications, digest_frequency: e.target.value as any }
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        {settingsService.getNotificationFrequencyOptions().map(option => (
                          <option key={option.value} value={option.value}>
                            {option.label} - {option.description}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>
                </div>
              )}

              {/* Accessibility Tab */}
              {activeTab === 'accessibility' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Accessibility Settings</h3>
                  
                  <div className="space-y-6">
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="text-sm font-medium text-gray-700">High Contrast</p>
                          <p className="text-sm text-gray-500">Increase contrast for better visibility</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={settings.accessibility.high_contrast}
                            onChange={(e) => {
                              const newValue = e.target.checked;
                              setSettings(prev => ({
                                ...prev,
                                accessibility: { ...prev.accessibility, high_contrast: newValue }
                              }));
                              // Apply high contrast immediately
                              console.log('🔴 High contrast toggle:', newValue);
                              
                              // Check if this is for the current user
                              const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
                              if (currentAccessibilityUser && currentAccessibilityUser !== user?.username) {
                                console.log('🔴 Different user accessibility detected, clearing first:', currentAccessibilityUser);
                                document.documentElement.classList.remove('high-contrast', 'large-text');
                                document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
                              }
                              
                              // Set current user
                              document.documentElement.setAttribute('data-accessibility-user', user?.username || '');
                              
                              if (newValue) {
                                console.log('🔴 Adding high-contrast class immediately to document for user:', user?.username);
                                document.documentElement.classList.add('high-contrast');
                                showToastNotification('High contrast mode enabled');
                              } else {
                                console.log('⚪ Removing high-contrast class immediately from document for user:', user?.username);
                                document.documentElement.classList.remove('high-contrast');
                                showToastNotification('High contrast mode disabled');
                              }
                              
                              // Trigger accessibility update event for immediate application
                              if (user) {
                                const updatedAccessibility = { ...settings.accessibility, high_contrast: newValue };
                                const accessibilityUpdateEvent = new CustomEvent('accessibilitySettingsChanged', {
                                  detail: {
                                    username: user.username,
                                    accessibility: updatedAccessibility
                                  }
                                });
                                window.dispatchEvent(accessibilityUpdateEvent);
                              }
                            }}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>



                      <div className="flex items-center justify-between">
                        <div>
                          <p className="text-sm font-medium text-gray-700">Screen Reader Support</p>
                          <p className="text-sm text-gray-500">Optimize for screen readers</p>
                        </div>
                        <label className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={settings.accessibility.screen_reader_support}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              accessibility: { ...prev.accessibility, screen_reader_support: e.target.checked }
                            }))}
                            className="sr-only peer"
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                      </div>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Text Size
                      </label>
                      <select
                        value={settings.accessibility.font_size}
                        onChange={(e) => {
                          const newValue = e.target.value as any;
                          setSettings(prev => ({
                            ...prev,
                            accessibility: { ...prev.accessibility, font_size: newValue }
                          }));
                          // Apply font size immediately
                          console.log('🔤 Font size change:', newValue);
                          
                          // Check if this is for the current user
                          const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
                          if (currentAccessibilityUser && currentAccessibilityUser !== user?.username) {
                            console.log('🔤 Different user accessibility detected, clearing first:', currentAccessibilityUser);
                            document.documentElement.classList.remove('high-contrast');
                            document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
                          }
                          
                          // Set current user
                          document.documentElement.setAttribute('data-accessibility-user', user?.username || '');
                          
                          const oldClasses = document.documentElement.className;
                          console.log('🔤 Old classes:', oldClasses);
                          document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
                          document.documentElement.classList.add(`font-size-${newValue}`);
                          console.log('🔤 New classes:', document.documentElement.className);
                          showToastNotification(`Text size changed to ${newValue}`);
                          
                          // Trigger accessibility update event for immediate application
                          if (user) {
                            const updatedAccessibility = { ...settings.accessibility, font_size: newValue };
                            const accessibilityUpdateEvent = new CustomEvent('accessibilitySettingsChanged', {
                              detail: {
                                username: user.username,
                                accessibility: updatedAccessibility
                              }
                            });
                            window.dispatchEvent(accessibilityUpdateEvent);
                          }
                        }}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value="small">Small (0.875rem)</option>
                        <option value="medium">Medium (1rem)</option>
                        <option value="large">Large (1.125rem)</option>
                        <option value="x-large">Extra Large (1.25rem)</option>
                      </select>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Display Mode
                      </label>
                      <select
                        value={settings.accessibility.display_mode}
                        onChange={(e) => {
                          const newValue = e.target.value as 'standard' | 'wide' | 'full';
                          setSettings(prev => ({
                            ...prev,
                            accessibility: { ...prev.accessibility, display_mode: newValue }
                          }));
                          // Apply display mode immediately
                          console.log('🖥️ Display mode change:', newValue);
                          
                          // Check if this is for the current user
                          const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
                          if (currentAccessibilityUser && currentAccessibilityUser !== user?.username) {
                            console.log('🖥️ Different user accessibility detected, clearing first:', currentAccessibilityUser);
                            document.documentElement.classList.remove('high-contrast');
                            document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
                            document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
                          }
                          
                          // Set current user
                          document.documentElement.setAttribute('data-accessibility-user', user?.username || '');
                          
                          // Remove old display mode classes
                          document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
                          document.documentElement.classList.add(`display-mode-${newValue}`);
                          console.log('🖥️ Applied display-mode-${newValue} class');
                          showToastNotification(`Display mode changed to ${newValue}`);
                          
                          // Trigger accessibility update event for immediate application
                          if (user) {
                            const updatedAccessibility = { ...settings.accessibility, display_mode: newValue };
                            const accessibilityUpdateEvent = new CustomEvent('accessibilitySettingsChanged', {
                              detail: {
                                username: user.username,
                                accessibility: updatedAccessibility
                              }
                            });
                            window.dispatchEvent(accessibilityUpdateEvent);
                          }
                        }}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value="standard">Standard (1200px max)</option>
                        <option value="wide">Wide (1600px max)</option>
                        <option value="full">Full (100% width)</option>
                      </select>
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* Action Buttons */}
            <div className="pt-6 border-t border-gray-200">
              <div className="flex flex-col sm:flex-row justify-between space-y-3 sm:space-y-0 sm:space-x-3">
                <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                  <Button variant="outline" onClick={handleResetSettings}>
                    Reset to Defaults
                  </Button>
                  <Button variant="outline" onClick={handleExportData}>
                    Export Data
                  </Button>
                  <Button 
                    variant="outline" 
                    className="text-red-600 hover:text-red-800"
                    onClick={() => setShowDeleteModal(true)}
                  >
                    Delete Account
                  </Button>
                </div>
                <Button 
                  variant="primary" 
                  onClick={handleSaveSettings}
                  loading={isLoading}
                >
                  {isLoading ? 'Saving...' : 'Save Changes'}
                </Button>
              </div>
            </div>
          </div>
        </Card>
      </div>

      {/* Delete Account Modal */}
      <Modal
        isOpen={showDeleteModal}
        onClose={() => setShowDeleteModal(false)}
        title="Delete Account"
        size="md"
      >
        <div className="space-y-4">
          <p className="text-gray-700">
            This action cannot be undone. This will permanently delete your account and remove all your data from our servers.
          </p>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Type &quot;DELETE&quot; to confirm
            </label>
            <Input
              type="text"
              value={deleteConfirmation}
              onChange={(e) => setDeleteConfirmation(e.target.value)}
              placeholder="DELETE"
              className="border-red-300 focus:border-red-500 focus:ring-red-500"
            />
          </div>
          <div className="flex justify-end space-x-3">
            <Button variant="outline" onClick={() => setShowDeleteModal(false)}>
              Cancel
            </Button>
            <Button 
              variant="primary" 
              className="bg-red-600 hover:bg-red-700"
              onClick={handleDeleteAccount}
              disabled={deleteConfirmation !== 'DELETE'}
            >
              Delete Account
            </Button>
          </div>
        </div>
      </Modal>
    </div>
  );
};

export default SettingsPage; 