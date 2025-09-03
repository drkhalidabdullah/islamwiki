import React, { useEffect } from 'react';
import { useAuthStore } from '../store/authStore';
import { settingsService } from '../services/settingsService';

/**
 * Global Accessibility Manager
 * 
 * This component automatically loads and applies accessibility settings
 * for any authenticated user, regardless of which page they're on.
 * 
 * It runs immediately when a user logs in and applies their saved
 * accessibility preferences automatically.
 */
const AccessibilityManager: React.FC = () => {
  const { user, isAuthenticated } = useAuthStore();

  useEffect(() => {
    if (isAuthenticated && user) {
      console.log('🎨 AccessibilityManager: User authenticated, loading accessibility settings for:', user.username);
      
      // Load user's accessibility settings immediately
      loadUserAccessibilitySettings();
    } else if (!isAuthenticated) {
      console.log('🎨 AccessibilityManager: User not authenticated, clearing all accessibility containers');
      clearAllAccessibilityContainers();
    }
  }, [isAuthenticated, user]);

  // Listen for accessibility settings changes from SettingsPage
  useEffect(() => {
    const handleAccessibilityChange = (event: CustomEvent) => {
      const { username, accessibility } = event.detail;
      console.log('🎨 AccessibilityManager: Received accessibility change event for user:', username, accessibility);
      
      if (username === user?.username) {
        console.log('🎨 AccessibilityManager: Applying updated accessibility settings for current user');
        applyAccessibilitySettings(username, accessibility);
      }
    };

    // Add event listener
    window.addEventListener('accessibilitySettingsChanged', handleAccessibilityChange as any);
    
    // Cleanup
    return () => {
      window.removeEventListener('accessibilitySettingsChanged', handleAccessibilityChange as any);
    };
  }, [user]);

  const loadUserAccessibilitySettings = async () => {
    if (!user) return;

    try {
      console.log('🎨 AccessibilityManager: Fetching accessibility settings for user:', user.username);
      const response = await settingsService.getUserSettings();
      
      if (response.success && response.data?.accessibility) {
        console.log('🎨 AccessibilityManager: Settings loaded, applying accessibility:', response.data.accessibility);
        applyAccessibilitySettings(user.username, response.data.accessibility);
      } else {
        console.log('🎨 AccessibilityManager: No accessibility settings found or failed to load');
      }
    } catch (error) {
      console.error('🎨 AccessibilityManager: Error loading accessibility settings:', error);
    }
  };

  const applyAccessibilitySettings = (username: string, accessibility: any) => {
    console.log('🎨 AccessibilityManager: Applying accessibility settings for user:', username, accessibility);
    
    // Check if there's a different user's accessibility currently applied
    const currentAccessibilityUser = document.documentElement.getAttribute('data-accessibility-user');
    if (currentAccessibilityUser && currentAccessibilityUser !== username) {
      console.log('🎨 AccessibilityManager: Different user accessibility detected, clearing first:', currentAccessibilityUser);
      // Clear all accessibility classes before applying new user's settings
      document.documentElement.classList.remove('high-contrast');
      document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
    }
    
    // Set the current accessibility user
    document.documentElement.setAttribute('data-accessibility-user', username);
    console.log('🎨 AccessibilityManager: Set accessibility user on document:', username);

    // Apply high contrast to main content
    if (accessibility.high_contrast) {
      document.documentElement.classList.add('high-contrast');
      console.log('🔴 AccessibilityManager: Applied high-contrast to document for user:', username);
    } else {
      document.documentElement.classList.remove('high-contrast');
      console.log('⚪ AccessibilityManager: Removed high-contrast from document for user:', username);
    }



    // Apply font size to main content
    document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
    document.documentElement.classList.add(`font-size-${accessibility.font_size}`);
    console.log('🔤 AccessibilityManager: Applied font-size-${accessibility.font_size} to document for user:', username);

    // Apply display mode to main content
    document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
    document.documentElement.classList.add(`display-mode-${accessibility.display_mode}`);
    console.log('🖥️ AccessibilityManager: Applied display-mode-${accessibility.display_mode} to document for user:', username);

    console.log('🎨 AccessibilityManager: Final document classes for user:', username, document.documentElement.className);
  };

  const clearAllAccessibilityContainers = () => {
    console.log('🎨 AccessibilityManager: Clearing all accessibility classes');
    
    // Clear accessibility classes from document
    document.documentElement.classList.remove('high-contrast');
    document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
    
    // Remove data attribute from document
    document.documentElement.removeAttribute('data-accessibility-user');
    console.log('🎨 AccessibilityManager: Removed accessibility user attribute from document');
    
    console.log('🎨 AccessibilityManager: All accessibility classes cleared from document');
  };

  // This component doesn't render anything visible
  return null;
};

export default AccessibilityManager; 