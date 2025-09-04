import React, { useState, useEffect } from 'react';
import { useAuthStore } from '../store/authStore';
import { useNavigate } from 'react-router-dom';
import Card from '../components/ui/Card';
import Button from '../components/ui/Button';
import Input from '../components/ui/Input';
import Textarea from '../components/ui/Textarea';
import Modal from '../components/ui/Modal';
import { settingsService, UserSettings } from '../services/settingsService';
import { LanguagePreference } from '../components/settings/LanguagePreference';

const SettingsPage: React.FC = () => {
  const { isAuthenticated } = useAuthStore();
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
      activity_visibility: 'public',
      search_visibility: true,
      analytics_consent: true,
      data_export: true,
      data_deletion: true,
      third_party_sharing: false,
      location_sharing: false,
      contact_info_visibility: 'public'
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
      screen_reader_support: false,
      keyboard_navigation: true,
      reduced_motion: false,
      color_blind_support: false,
      font_size: 'medium',
      display_mode: 'standard',
      line_spacing: 'normal',
      word_spacing: 'normal',
      cursor_size: 'normal',
      focus_indicator: 'default',
      focus_color: '#3b82f6',
      aria_labels: true,
      live_regions: true,
      landmark_roles: true,
      skip_links: true,
      tab_order: 'logical',
      alt_text_required: true,
      form_labels_required: true,
      button_descriptions: false,
      link_descriptions: false,
      audio_descriptions: false,
      volume_control: true,
      audio_cues: true,
      notification_sounds: true,
      reading_guides: false,
      text_highlighting: false,
      simplified_layout: false,
      distraction_free: false,
      click_assist: false,
      hover_delay: 0,
      sticky_keys: false,
      bounce_keys: false,
      language_support: ['en'],
      translation_tools: false,
      pronunciation_guides: false,
      glossary_terms: true
    }
  });

  const [activeTab, setActiveTab] = useState<'account' | 'preferences' | 'security' | 'privacy' | 'notifications' | 'accessibility'>('account');
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [showToast, setShowToast] = useState(false);
  const [toastMessage, setToastMessage] = useState('');
  const [toastType, setToastType] = useState<'success' | 'error'>('success');
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteConfirmation, setDeleteConfirmation] = useState('');

  // Language switching state
  const [availableLanguages, setAvailableLanguages] = useState([
    { code: 'en', name: 'English', native_name: 'English', flag: '🇺🇸', direction: 'ltr' as const, is_active: true, is_default: true },
    { code: 'ar', name: 'Arabic', native_name: 'العربية', flag: '🇸🇦', direction: 'rtl' as const, is_active: true, is_default: false },
    { code: 'fr', name: 'French', native_name: 'Français', flag: '🇫🇷', direction: 'ltr' as const, is_active: true, is_default: false },
    { code: 'es', name: 'Spanish', native_name: 'Español', flag: '🇪🇸', direction: 'ltr' as const, is_active: true, is_default: false },
    { code: 'de', name: 'German', native_name: 'Deutsch', flag: '🇩🇪', direction: 'ltr' as const, is_active: true, is_default: false }
  ]);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }

    loadSettings();
  }, [isAuthenticated, navigate]);

  const loadSettings = async () => {
    // Also load available languages with correct current state
    const langResponse = await fetch('/api/language/supported');
    if (langResponse.ok) {
      const languages = await langResponse.json();
      const currentLang = await fetch('/api/language/current');
      if (currentLang.ok) {
        const currentLangData = await currentLang.json();
        const updatedLanguages = languages.map((lang: any) => ({
          ...lang,
          is_current: lang.code === currentLangData.code
        }));
        setAvailableLanguages(updatedLanguages);
      }
    }
    try {
      setIsLoading(true);
      const userSettingsResponse = await settingsService.getUserSettings();
      if (userSettingsResponse && userSettingsResponse.success && userSettingsResponse.data) {
        setSettings(userSettingsResponse.data as UserSettings);
      }
    } catch (error) {
      console.error('Error loading settings:', error);
      showToastNotification('Failed to load settings.', 'error');
    } finally {
      setIsLoading(false);
    }
  };

  const showToastNotification = (message: string, type: 'success' | 'error') => {
    setToastMessage(message);
    setToastType(type);
    setShowToast(true);
    setTimeout(() => setShowToast(false), 5000);
  };

  const handleSaveSettings = async () => {
    try {
      setIsSaving(true);
      // Save each section separately to match the API
      const response = await settingsService.updateUserSettings({
        section: 'preferences',
        data: settings.preferences
      });
      if (response.success) {
        showToastNotification('Settings saved successfully!', 'success');
      } else {
        showToastNotification('Failed to save settings.', 'error');
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      showToastNotification('Failed to save settings.', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const handleLanguageChange = async (languageCode: string) => {
    try {
      // Update local state immediately for UI responsiveness
      setSettings(prev => ({
        ...prev,
        preferences: { ...prev.preferences, language: languageCode }
      }));

      // Call API to switch language
      const response = await fetch('/api/language/switch', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ lang: languageCode }),
      });

      if (response.ok) {
        const newLangData = await response.json();
        showToastNotification(`Language switched to ${newLangData.native_name}`, 'success');
        
        // Update HTML direction for RTL languages
        const selectedLang = availableLanguages.find(lang => lang.code === languageCode);
        if (selectedLang) {
          document.documentElement.dir = selectedLang.direction;
        }
      } else {
        throw new Error('Failed to switch language');
      }
    } catch (error) {
      console.error('Error switching language:', error);
      showToastNotification('Failed to switch language.', 'error');
      // Revert the change on error
      setSettings(prev => ({
        ...prev,
        preferences: { ...prev.preferences, language: settings.preferences.language }
      }));
    }
  };

  const handleSaveLanguagePreferences = async (preferences: any) => {
    try {
      // Update settings with language preferences
      setSettings(prev => ({
        ...prev,
        preferences: { 
          ...prev.preferences, 
          language: preferences.language,
          content_language: preferences.language
        }
      }));

      // Save to backend
      await handleSaveSettings();
      showToastNotification('Language preferences saved!', 'success');
    } catch (error) {
      console.error('Error saving language preferences:', error);
      showToastNotification('Failed to save language preferences.', 'error');
    }
  };

  const handleExportData = async () => {
    try {
      const response = await settingsService.exportUserData();
      if (response instanceof Blob) {
        // Create download link
        const url = window.URL.createObjectURL(response);
        const a = document.createElement('a');
        a.href = url;
        a.download = `islamwiki-data-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        showToastNotification('Data exported successfully!', 'success');
      } else {
        showToastNotification('Failed to export data.', 'error');
      }
    } catch (error) {
      showToastNotification('Failed to export data.', 'error');
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

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading settings...</p>
        </div>
      </div>
    );
  }

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
                  <h3 className="text-lg font-medium text-gray-900">Account Settings</h3>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Username
                      </label>
                      <Input
                        type="text"
                        value={settings.account?.username || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), username: e.target.value }
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
                        value={settings.account?.email || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), email: e.target.value }
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
                        value={settings.account?.first_name || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), first_name: e.target.value }
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
                        value={settings.account?.last_name || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), last_name: e.target.value }
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
                        value={settings.account?.display_name || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), display_name: e.target.value }
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
                        value={settings.account?.phone || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), phone: e.target.value }
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
                        value={settings.account?.date_of_birth || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), date_of_birth: e.target.value }
                        }))}
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Gender
                      </label>
                      <select
                        value={settings.account?.gender || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), gender: e.target.value }
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
                        value={settings.account?.location || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), location: e.target.value }
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
                        value={settings.account?.website || ''}
                        onChange={(e) => setSettings(prev => ({
                          ...prev,
                          account: { ...(prev.account || {}), website: e.target.value }
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
                      value={settings.account?.bio || ''}
                      onChange={(e) => setSettings(prev => ({
                        ...prev,
                        account: { ...(prev.account || {}), bio: e.target.value }
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
                  
                  {/* Language Preference Component */}
                  <LanguagePreference
                    currentLanguage={settings.preferences.language}
                    availableLanguages={availableLanguages}
                    onLanguageChange={handleLanguageChange}
                    onSavePreferences={handleSaveLanguagePreferences}
                  />

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
                            <option value="light">☀️ Light</option>
                            <option value="dark">🌙 Dark</option>
                            <option value="auto">🔄 Auto</option>
                          </select>
                        </div>

                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Timezone
                          </label>
                          <select
                            value={settings.preferences.timezone}
                            onChange={(e) => setSettings(prev => ({
                              ...prev,
                              preferences: { ...prev.preferences, timezone: e.target.value }
                            }))}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">Eastern Time</option>
                            <option value="America/Chicago">Central Time</option>
                            <option value="America/Denver">Mountain Time</option>
                            <option value="America/Los_Angeles">Pacific Time</option>
                            <option value="Europe/London">London</option>
                            <option value="Europe/Paris">Paris</option>
                            <option value="Asia/Dubai">Dubai</option>
                            <option value="Asia/Riyadh">Riyadh</option>
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
                  <h3 className="text-lg font-medium text-gray-900">Notification Settings</h3>
                  
                  <div className="space-y-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-700">Content Updates</p>
                        <p className="text-sm text-gray-500">Get notified when content you follow is updated</p>
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
                        <p className="text-sm text-gray-500">Get notified when someone replies to your comments</p>
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
                  </div>
                </div>
              )}

              {/* Accessibility Tab */}
              {activeTab === 'accessibility' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900">Accessibility Settings</h3>
                  
                  <div className="space-y-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-700">High Contrast</p>
                        <p className="text-sm text-gray-500">Increase contrast for better visibility</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input
                          type="checkbox"
                          checked={settings.accessibility.high_contrast}
                          onChange={(e) => setSettings(prev => ({
                            ...prev,
                            accessibility: { ...prev.accessibility, high_contrast: e.target.checked }
                          }))}
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
                </div>
              )}
            </div>

            {/* Save Button */}
            <div className="mt-8 pt-6 border-t border-gray-200 flex justify-end">
              <Button
                onClick={handleSaveSettings}
                disabled={isSaving}
                className="bg-green-600 hover:bg-green-700 text-white px-6 py-2"
              >
                {isSaving ? 'Saving...' : 'Save Settings'}
              </Button>
            </div>
          </div>
        </Card>

        {/* Data Management */}
        <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Export Data</h3>
              <p className="text-sm text-gray-600 mb-4">
                Download a copy of your account data including settings, preferences, and activity.
              </p>
              <Button
                onClick={handleExportData}
                variant="outline"
                className="w-full"
              >
                Export My Data
              </Button>
            </div>
          </Card>

          <Card>
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Delete Account</h3>
              <p className="text-sm text-gray-600 mb-4">
                Permanently delete your account and all associated data. This action cannot be undone.
              </p>
              <Button
                onClick={() => setShowDeleteModal(true)}
                variant="outline"
                className="w-full text-red-600 hover:text-red-800 border-red-300 hover:border-red-400"
              >
                Delete Account
              </Button>
            </div>
          </Card>
        </div>
      </div>

      {/* Delete Account Modal */}
      <Modal
        isOpen={showDeleteModal}
        onClose={() => setShowDeleteModal(false)}
        title="Delete Account"
      >
        <div className="space-y-4">
          <p className="text-sm text-gray-600">
            Are you sure you want to delete your account? This action cannot be undone and will permanently remove all your data.
          </p>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Type "DELETE" to confirm
            </label>
            <Input
              type="text"
              value={deleteConfirmation}
              onChange={(e) => setDeleteConfirmation(e.target.value)}
              placeholder="DELETE"
              className="w-full"
            />
          </div>
          <div className="flex justify-end space-x-3">
            <Button
              variant="outline"
              onClick={() => setShowDeleteModal(false)}
            >
              Cancel
            </Button>
            <Button
              onClick={handleDeleteAccount}
              className="bg-red-600 hover:bg-red-700 text-white"
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
