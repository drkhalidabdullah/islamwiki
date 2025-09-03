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
      display_mode: 'standard',
      
      // Enhanced Visual Accessibility
      line_spacing: 'normal',
      word_spacing: 'normal',
      cursor_size: 'normal',
      focus_indicator: 'default',
      focus_color: '#0066cc',
      
      // Advanced Screen Reader Support
      aria_labels: true,
      live_regions: true,
      landmark_roles: true,
      skip_links: true,
      tab_order: 'logical',
      
      // Alt Text & Content Accessibility
      alt_text_required: true,
      form_labels_required: true,
      button_descriptions: true,
      link_descriptions: true,
      
      // Audio Accessibility
      audio_descriptions: false,
      volume_control: true,
      audio_cues: false,
      notification_sounds: true,
      
      // Cognitive & Learning Support
      reading_guides: false,
      text_highlighting: false,
      simplified_layout: false,
      distraction_free: false,
      
      // Motor & Physical Accessibility
      click_assist: false,
      hover_delay: 500,
      sticky_keys: false,
      bounce_keys: false,
      
      // Language & Communication
      language_support: ['en'],
      translation_tools: false,
      pronunciation_guides: false,
      glossary_terms: false
    }
  });

  const [activeTab, setActiveTab] = useState<'account' | 'preferences' | 'security' | 'privacy' | 'notifications' | 'accessibility'>('account');
  const [isLoading, setIsLoading] = useState(false);
  const [showToast, setShowToast] = useState(false);
  const [toastMessage, setToastMessage] = useState('');
  const [toastType, setToastType] = useState<'success' | 'error'>('success');
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteConfirmation, setDeleteConfirmation] = useState('');

  // Helper function to apply accessibility changes immediately and dispatch events
  const applyAccessibilityChange = (setting: keyof typeof settings.accessibility, value: any, settingName: string) => {
    console.log(`🎨 ${settingName} change:`, value);
    
    // Check if this is for the current user
    const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
    if (currentAccessibilityUser && currentAccessibilityUser !== user?.username) {
      console.log(`🎨 Different user accessibility detected, clearing first:`, currentAccessibilityUser);
      // Clear all accessibility classes
      document.documentElement.classList.remove('high-contrast');
      document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/line-spacing-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/word-spacing-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/cursor-size-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/focus-indicator-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/hover-delay-\d+/, '');
    }
    
    // Set current user
    document.documentElement.setAttribute('data-accessibility-user', user?.username || '');
    
    // Apply the specific accessibility setting
    if (setting === 'high_contrast') {
      if (value) {
        document.documentElement.classList.add('high-contrast');
        showToastNotification('High contrast mode enabled');
      } else {
        document.documentElement.classList.remove('high-contrast');
        showToastNotification('High contrast mode disabled');
      }
    } else if (setting === 'font_size') {
      document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      document.documentElement.classList.add(`font-size-${value}`);
      showToastNotification(`Text size changed to ${value}`);
    } else if (setting === 'display_mode') {
      document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
      document.documentElement.classList.add(`display-mode-${value}`);
      showToastNotification(`Display mode changed to ${value}`);
    } else if (setting === 'line_spacing') {
      document.documentElement.className = document.documentElement.className.replace(/line-spacing-\w+/, '');
      document.documentElement.classList.add(`line-spacing-${value}`);
      showToastNotification(`Line spacing changed to ${value}`);
    } else if (setting === 'word_spacing') {
      document.documentElement.className = document.documentElement.className.replace(/word-spacing-\w+/, '');
      document.documentElement.classList.add(`word-spacing-${value}`);
      showToastNotification(`Word spacing changed to ${value}`);
    } else if (setting === 'cursor_size') {
      document.documentElement.className = document.documentElement.className.replace(/cursor-size-\w+/, '');
      document.documentElement.classList.add(`cursor-size-${value}`);
      showToastNotification(`Cursor size changed to ${value}`);
    } else if (setting === 'focus_indicator') {
      document.documentElement.className = document.documentElement.className.replace(/focus-indicator-\w+/, '');
      document.documentElement.classList.add(`focus-indicator-${value}`);
      showToastNotification(`Focus indicator changed to ${value}`);
    } else if (setting === 'focus_color') {
      document.documentElement.style.setProperty('--custom-focus-color', value);
      showToastNotification(`Custom focus color changed to ${value}`);
    } else if (setting === 'hover_delay') {
      document.documentElement.className = document.documentElement.className.replace(/hover-delay-\d+/, '');
      document.documentElement.classList.add(`hover-delay-${value}`);
      showToastNotification(`Hover delay changed to ${value}ms`);
    } else if (setting === 'reduced_motion') {
      if (value) {
        document.documentElement.classList.add('reduced-motion');
        showToastNotification('Reduced motion enabled');
      } else {
        document.documentElement.classList.remove('reduced-motion');
        showToastNotification('Reduced motion disabled');
      }
    } else if (setting === 'color_blind_support') {
      if (value) {
        document.documentElement.classList.add('color-blind-support');
        showToastNotification('Color blind support enabled');
      } else {
        document.documentElement.classList.remove('color-blind-support');
        showToastNotification('Color blind support disabled');
      }
    } else if (setting === 'click_assist') {
      if (value) {
        document.documentElement.classList.add('click-assist');
        showToastNotification('Click assist enabled');
      } else {
        document.documentElement.classList.remove('click-assist');
        showToastNotification('Click assist disabled');
      }
    } else if (setting === 'alt_text_required') {
      if (value) {
        document.documentElement.classList.add('alt-text-required');
        showToastNotification('Alt text required enabled');
      } else {
        document.documentElement.classList.remove('alt-text-required');
        showToastNotification('Alt text required disabled');
      }
    } else if (setting === 'form_labels_required') {
      if (value) {
        document.documentElement.classList.add('form-labels-required');
        showToastNotification('Form labels required enabled');
      } else {
        document.documentElement.classList.remove('form-labels-required');
        showToastNotification('Form labels required disabled');
      }
    } else if (setting === 'button_descriptions') {
      if (value) {
        document.documentElement.classList.add('button-descriptions');
        showToastNotification('Button descriptions enabled');
      } else {
        document.documentElement.classList.remove('button-descriptions');
        showToastNotification('Button descriptions disabled');
      }
    } else if (setting === 'link_descriptions') {
      if (value) {
        document.documentElement.classList.add('link-descriptions');
        showToastNotification('Link descriptions enabled');
      } else {
        document.documentElement.classList.remove('link-descriptions');
        showToastNotification('Link descriptions disabled');
      }
    } else if (setting === 'audio_descriptions') {
      if (value) {
        document.documentElement.classList.add('audio-descriptions-active');
        showToastNotification('Audio descriptions enabled');
      } else {
        document.documentElement.classList.remove('audio-descriptions-active');
        showToastNotification('Audio descriptions disabled');
      }
    } else if (setting === 'volume_control') {
      if (value) {
        document.documentElement.classList.add('volume-control-active');
        showToastNotification('Volume control enabled');
      } else {
        document.documentElement.classList.remove('volume-control-active');
        showToastNotification('Volume control disabled');
      }
    } else if (setting === 'audio_cues') {
      if (value) {
        document.documentElement.classList.add('audio-cues-active');
        showToastNotification('Audio cues enabled');
      } else {
        document.documentElement.classList.remove('audio-cues-active');
        showToastNotification('Audio cues disabled');
      }
    } else if (setting === 'notification_sounds') {
      if (value) {
        document.documentElement.classList.add('notification-sounds-active');
        showToastNotification('Notification sounds enabled');
      } else {
        document.documentElement.classList.remove('notification-sounds-active');
        showToastNotification('Notification sounds disabled');
      }
    } else if (setting === 'reading_guides') {
      if (value) {
        document.documentElement.classList.add('reading-guides-active');
        showToastNotification('Reading guides enabled');
      } else {
        document.documentElement.classList.remove('reading-guides-active');
        showToastNotification('Reading guides disabled');
      }
    } else if (setting === 'text_highlighting') {
      if (value) {
        document.documentElement.classList.add('text-highlighting-active');
        showToastNotification('Text highlighting enabled');
      } else {
        document.documentElement.classList.remove('text-highlighting-active');
        showToastNotification('Text highlighting disabled');
      }
    } else if (setting === 'simplified_layout') {
      if (value) {
        document.documentElement.classList.add('simplified-layout-active');
        showToastNotification('Simplified layout enabled');
      } else {
        document.documentElement.classList.remove('simplified-layout-active');
        showToastNotification('Simplified layout disabled');
      }
    } else if (setting === 'distraction_free') {
      if (value) {
        document.documentElement.classList.add('distraction-free-active');
        showToastNotification('Distraction free mode enabled');
      } else {
        document.documentElement.classList.remove('distraction-free-active');
        showToastNotification('Distraction free mode disabled');
      }
    } else if (setting === 'sticky_keys') {
      if (value) {
        document.documentElement.classList.add('sticky-keys-active');
        showToastNotification('Sticky keys enabled');
      } else {
        document.documentElement.classList.remove('sticky-keys-active');
        showToastNotification('Sticky keys disabled');
      }
    } else if (setting === 'bounce_keys') {
      if (value) {
        document.documentElement.classList.add('bounce-keys-active');
        showToastNotification('Bounce keys enabled');
      } else {
        document.documentElement.classList.remove('bounce-keys-active');
        showToastNotification('Bounce keys disabled');
      }
    } else if (setting === 'translation_tools') {
      if (value) {
        document.documentElement.classList.add('translation-tools-active');
        showToastNotification('Translation tools enabled');
      } else {
        document.documentElement.classList.remove('translation-tools-active');
        showToastNotification('Translation tools disabled');
      }
    } else if (setting === 'pronunciation_guides') {
      if (value) {
        document.documentElement.classList.add('pronunciation-guides-active');
        showToastNotification('Pronunciation guides enabled');
      } else {
        document.documentElement.classList.remove('pronunciation-guides-active');
        showToastNotification('Pronunciation guides disabled');
      }
    } else if (setting === 'screen_reader_support') {
      if (value) {
        document.documentElement.classList.add('screen-reader-support-active');
        showToastNotification('Screen reader support enabled');
      } else {
        document.documentElement.classList.remove('screen-reader-support-active');
        showToastNotification('Screen reader support disabled');
      }
    } else if (setting === 'glossary_terms') {
      if (value) {
        document.documentElement.classList.add('glossary-terms-active');
        showToastNotification('Glossary terms enabled');
      } else {
        document.documentElement.classList.remove('glossary-terms-active');
        showToastNotification('Glossary terms disabled');
      }
    }
    
    // Trigger accessibility update event for immediate application
    if (user) {
      const updatedAccessibility = { ...settings.accessibility, [setting]: value };
      const accessibilityUpdateEvent = new CustomEvent('accessibilitySettingsChanged', {
        detail: {
          username: user.username,
          accessibility: updatedAccessibility
        }
      });
      window.dispatchEvent(accessibilityUpdateEvent);
    }
  };

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
                              applyAccessibilityChange('high_contrast', newValue, 'High contrast');
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
                            onChange={(e) => {
                              const newValue = e.target.checked;
                              setSettings(prev => ({
                                ...prev,
                                accessibility: { ...prev.accessibility, screen_reader_support: newValue }
                              }));
                              applyAccessibilityChange('screen_reader_support', newValue, 'Screen reader support');
                            }}
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
                          applyAccessibilityChange('font_size', newValue, 'Font size');
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
                          applyAccessibilityChange('display_mode', newValue, 'Display mode');
                        }}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      >
                        <option value="standard">Standard (1200px max)</option>
                        <option value="wide">Wide (1600px max)</option>
                        <option value="full">Full (100% width)</option>
                      </select>
                    </div>

                    {/* Enhanced Visual Accessibility */}
                    <div className="space-y-4">
                      <h4 className="text-md font-medium text-gray-800 border-b pb-2">Enhanced Visual Accessibility</h4>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Line Spacing
                          </label>
                          <select
                            value={settings.accessibility.line_spacing}
                            onChange={(e) => {
                              const newValue = e.target.value as any;
                              setSettings(prev => ({
                                ...prev,
                                accessibility: { ...prev.accessibility, line_spacing: newValue }
                              }));
                              applyAccessibilityChange('line_spacing', newValue, 'Line spacing');
                            }}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            <option value="normal">Normal (1.5)</option>
                            <option value="relaxed">Relaxed (1.8)</option>
                            <option value="very-relaxed">Very Relaxed (2.2)</option>
                          </select>
                        </div>

                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Word Spacing
                          </label>
                          <select
                            value={settings.accessibility.word_spacing}
                            onChange={(e) => {
                              const newValue = e.target.value as any;
                              setSettings(prev => ({
                                ...prev,
                                accessibility: { ...prev.accessibility, word_spacing: newValue }
                              }));
                              applyAccessibilityChange('word_spacing', newValue, 'Word spacing');
                            }}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            <option value="normal">Normal (0.25em)</option>
                            <option value="wide">Wide (0.5em)</option>
                            <option value="very-wide">Very Wide (1em)</option>
                          </select>
                        </div>

                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Cursor Size
                          </label>
                          <select
                            value={settings.accessibility.cursor_size}
                            onChange={(e) => {
                              const newValue = e.target.value as any;
                              setSettings(prev => ({
                                ...prev,
                                accessibility: { ...prev.accessibility, cursor_size: newValue }
                              }));
                              applyAccessibilityChange('cursor_size', newValue, 'Cursor size');
                            }}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            <option value="normal">Normal</option>
                            <option value="large">Large (2x)</option>
                            <option value="extra-large">Extra Large (3x)</option>
                          </select>
                        </div>

                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Focus Indicator
                          </label>
                          <select
                            value={settings.accessibility.focus_indicator}
                            onChange={(e) => {
                              const newValue = e.target.value as any;
                              setSettings(prev => ({
                                ...prev,
                                accessibility: { ...prev.accessibility, focus_indicator: newValue }
                              }));
                              applyAccessibilityChange('focus_indicator', newValue, 'Focus indicator');
                            }}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          >
                            <option value="default">Default</option>
                            <option value="high-visibility">High Visibility</option>
                            <option value="custom">Custom Color</option>
                          </select>
                        </div>
                      </div>

                      {settings.accessibility.focus_indicator === 'custom' && (
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-2">
                            Custom Focus Color
                          </label>
                          <div className="flex items-center space-x-3">
                            <input
                              type="color"
                              value={settings.accessibility.focus_color}
                              onChange={(e) => {
                                const newValue = e.target.value;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, focus_color: newValue }
                                }));
                                applyAccessibilityChange('focus_color', newValue, 'Custom focus color');
                              }}
                              className="w-16 h-10 border border-gray-300 rounded-md cursor-pointer"
                            />
                            <span className="text-sm text-gray-500">{settings.accessibility.focus_color}</span>
                          </div>
                        </div>
                      )}
                    </div>

                    {/* Advanced Screen Reader Support */}
                    <div className="space-y-4">
                      <h4 className="text-md font-medium text-gray-800 border-b pb-2">Advanced Screen Reader Support</h4>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">ARIA Labels</p>
                            <p className="text-sm text-gray-500">Enhanced screen reader support</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.aria_labels}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, aria_labels: newValue }
                                }));
                                applyAccessibilityChange('aria_labels', newValue, 'ARIA labels');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Live Regions</p>
                            <p className="text-sm text-gray-500">Dynamic content announcements</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.live_regions}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, live_regions: newValue }
                                }));
                                applyAccessibilityChange('live_regions', newValue, 'Live regions');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Landmark Roles</p>
                            <p className="text-sm text-gray-500">Page structure identification</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.landmark_roles}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, landmark_roles: newValue }
                                }));
                                applyAccessibilityChange('landmark_roles', newValue, 'Landmark roles');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Skip Links</p>
                            <p className="text-sm text-gray-500">Jump to main content</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.skip_links}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, skip_links: newValue }
                                }));
                                applyAccessibilityChange('skip_links', newValue, 'Skip links');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>
                      </div>
                    </div>

                    {/* Alt Text & Content Accessibility */}
                    <div className="space-y-4">
                      <h4 className="text-md font-medium text-gray-800 border-b pb-2">Content Accessibility</h4>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Alt Text Required</p>
                            <p className="text-sm text-gray-500">Ensure images have descriptions</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.alt_text_required}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, alt_text_required: newValue }
                                }));
                                applyAccessibilityChange('alt_text_required', newValue, 'Alt text required');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Form Labels Required</p>
                            <p className="text-sm text-gray-500">Ensure form inputs are labeled</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.form_labels_required}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, form_labels_required: newValue }
                                }));
                                applyAccessibilityChange('form_labels_required', newValue, 'Form labels required');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Button Descriptions</p>
                            <p className="text-sm text-gray-500">Enhanced button context</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.button_descriptions}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, button_descriptions: newValue }
                                }));
                                applyAccessibilityChange('button_descriptions', newValue, 'Button descriptions');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Link Descriptions</p>
                            <p className="text-sm text-gray-500">Enhanced link context</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.link_descriptions}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, link_descriptions: newValue }
                                }));
                                applyAccessibilityChange('link_descriptions', newValue, 'Link descriptions');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>
                      </div>
                    </div>

                    {/* Additional Accessibility Options */}
                    <div className="space-y-4">
                      <h4 className="text-md font-medium text-gray-800 border-b pb-2">Additional Options</h4>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Reduced Motion</p>
                            <p className="text-sm text-gray-500">Minimize animations</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.reduced_motion}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, reduced_motion: newValue }
                                }));
                                applyAccessibilityChange('reduced_motion', newValue, 'Reduced motion');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Color Blind Support</p>
                            <p className="text-sm text-gray-500">Enhanced color differentiation</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.color_blind_support}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, color_blind_support: newValue }
                                }));
                                applyAccessibilityChange('color_blind_support', newValue, 'Color blind support');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Keyboard Navigation</p>
                            <p className="text-sm text-gray-500">Full keyboard support</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.keyboard_navigation}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, keyboard_navigation: newValue }
                                }));
                                applyAccessibilityChange('keyboard_navigation', newValue, 'Keyboard navigation');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>

                        <div className="flex items-center justify-between">
                          <div>
                            <p className="text-sm font-medium text-gray-700">Click Assist</p>
                            <p className="text-sm text-gray-500">Enhanced click targeting</p>
                          </div>
                          <label className="relative inline-flex items-center cursor-pointer">
                            <input
                              type="checkbox"
                              checked={settings.accessibility.click_assist}
                              onChange={(e) => {
                                const newValue = e.target.checked;
                                setSettings(prev => ({
                                  ...prev,
                                  accessibility: { ...prev.accessibility, click_assist: newValue }
                                }));
                                applyAccessibilityChange('click_assist', newValue, 'Click assist');
                              }}
                              className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                          </label>
                        </div>
                      </div>

                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Hover Delay (milliseconds)
                        </label>
                        <input
                          type="range"
                          min="100"
                          max="2000"
                          step="100"
                          value={settings.accessibility.hover_delay}
                          onChange={(e) => {
                            const newValue = parseInt(e.target.value);
                            setSettings(prev => ({
                              ...prev,
                              accessibility: { ...prev.accessibility, hover_delay: newValue }
                            }));
                            applyAccessibilityChange('hover_delay', newValue, 'Hover delay');
                          }}
                          className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                        />
                        <div className="flex justify-between text-xs text-gray-500 mt-1">
                          <span>100ms</span>
                          <span>{settings.accessibility.hover_delay}ms</span>
                          <span>2000ms</span>
                        </div>
                      </div>
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