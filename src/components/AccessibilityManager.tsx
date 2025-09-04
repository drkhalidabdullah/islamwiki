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
      document.documentElement.className = document.documentElement.className.replace(/line-spacing-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/word-spacing-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/cursor-size-\w+/, '');
      document.documentElement.className = document.documentElement.className.replace(/focus-indicator-\w+/, '');
      document.documentElement.classList.remove('reduced-motion', 'color-blind-support', 'click-assist');
      document.documentElement.classList.remove('alt-text-required', 'form-labels-required', 'button-descriptions', 'link-descriptions');
      document.documentElement.classList.remove('audio-descriptions-active', 'volume-control-active', 'audio-cues-active', 'notification-sounds-active');
      document.documentElement.classList.remove('reading-guides-active', 'text-highlighting-active', 'simplified-layout-active', 'distraction-free-active');
      document.documentElement.classList.remove('sticky-keys-active', 'bounce-keys-active');
      document.documentElement.classList.remove('language-support-active', 'translation-tools-active', 'pronunciation-guides-active', 'glossary-terms-active');
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

    // Apply line spacing
    document.documentElement.className = document.documentElement.className.replace(/line-spacing-\w+/, '');
    document.documentElement.classList.add(`line-spacing-${accessibility.line_spacing}`);
    console.log('📏 AccessibilityManager: Applied line-spacing-${accessibility.line_spacing} to document for user:', username);

    // Apply word spacing
    document.documentElement.className = document.documentElement.className.replace(/word-spacing-\w+/, '');
    document.documentElement.classList.add(`word-spacing-${accessibility.word_spacing}`);
    console.log('🔤 AccessibilityManager: Applied word-spacing-${accessibility.word_spacing} to document for user:', username);

    // Apply cursor size
    document.documentElement.className = document.documentElement.className.replace(/cursor-size-\w+/, '');
    document.documentElement.classList.add(`cursor-size-${accessibility.cursor_size}`);
    console.log('🖱️ AccessibilityManager: Applied cursor-size-${accessibility.cursor_size} to document for user:', username);

    // Apply focus indicator
    document.documentElement.className = document.documentElement.className.replace(/focus-indicator-\w+/, '');
    document.documentElement.classList.add(`focus-indicator-${accessibility.focus_indicator}`);
    if (accessibility.focus_indicator === 'custom') {
      document.documentElement.style.setProperty('--custom-focus-color', accessibility.focus_color);
      console.log('🎨 AccessibilityManager: Applied custom focus color:', accessibility.focus_color);
    }
    console.log('🎯 AccessibilityManager: Applied focus-indicator-${accessibility.focus_indicator} to document for user:', username);

    // Apply reduced motion
    if (accessibility.reduced_motion) {
      document.documentElement.classList.add('reduced-motion');
      console.log('🔄 AccessibilityManager: Applied reduced-motion to document for user:', username);
    } else {
      document.documentElement.classList.remove('reduced-motion');
    }

    // Apply color blind support
    if (accessibility.color_blind_support) {
      document.documentElement.classList.add('color-blind-support');
      console.log('🎨 AccessibilityManager: Applied color-blind-support to document for user:', username);
    } else {
      document.documentElement.classList.remove('color-blind-support');
    }

    // Apply click assist
    if (accessibility.click_assist) {
      document.documentElement.classList.add('click-assist');
      console.log('🖱️ AccessibilityManager: Applied click-assist to document for user:', username);
    } else {
      document.documentElement.classList.remove('click-assist');
    }

    // Apply content accessibility features
    if (accessibility.alt_text_required) {
      document.documentElement.classList.add('alt-text-required');
      console.log('🖼️ AccessibilityManager: Applied alt-text-required to document for user:', username);
    } else {
      document.documentElement.classList.remove('alt-text-required');
    }

    if (accessibility.form_labels_required) {
      document.documentElement.classList.add('form-labels-required');
      console.log('📝 AccessibilityManager: Applied form-labels-required to document for user:', username);
    } else {
      document.documentElement.classList.remove('form-labels-required');
    }

    if (accessibility.button_descriptions) {
      document.documentElement.classList.add('button-descriptions');
      console.log('🔘 AccessibilityManager: Applied button-descriptions to document for user:', username);
    } else {
      document.documentElement.classList.remove('button-descriptions');
    }

    if (accessibility.link_descriptions) {
      document.documentElement.classList.add('link-descriptions');
      console.log('🔗 AccessibilityManager: Applied link-descriptions to document for user:', username);
    } else {
      document.documentElement.classList.remove('link-descriptions');
    }

    // Apply audio accessibility features
    if (accessibility.audio_descriptions) {
      document.documentElement.classList.add('audio-descriptions-active');
      console.log('🔊 AccessibilityManager: Applied audio-descriptions-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('audio-descriptions-active');
    }

    if (accessibility.volume_control) {
      document.documentElement.classList.add('volume-control-active');
      console.log('🔊 AccessibilityManager: Applied volume-control-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('volume-control-active');
    }

    if (accessibility.audio_cues) {
      document.documentElement.classList.add('audio-cues-active');
      console.log('🔊 AccessibilityManager: Applied audio-cues-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('audio-cues-active');
    }

    if (accessibility.notification_sounds) {
      document.documentElement.classList.add('notification-sounds-active');
      console.log('🔊 AccessibilityManager: Applied notification-sounds-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('notification-sounds-active');
    }

    // Apply cognitive support features
    if (accessibility.reading_guides) {
      document.documentElement.classList.add('reading-guides-active');
      console.log('📖 AccessibilityManager: Applied reading-guides-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('reading-guides-active');
    }

    if (accessibility.text_highlighting) {
      document.documentElement.classList.add('text-highlighting-active');
      console.log('🖍️ AccessibilityManager: Applied text-highlighting-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('text-highlighting-active');
    }

    if (accessibility.simplified_layout) {
      document.documentElement.classList.add('simplified-layout-active');
      console.log('📱 AccessibilityManager: Applied simplified-layout-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('simplified-layout-active');
    }

    if (accessibility.distraction_free) {
      document.documentElement.classList.add('distraction-free-active');
      console.log('🧘 AccessibilityManager: Applied distraction-free-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('distraction-free-active');
    }

    // Apply motor accessibility features
    if (accessibility.sticky_keys) {
      document.documentElement.classList.add('sticky-keys-active');
      console.log('⌨️ AccessibilityManager: Applied sticky-keys-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('sticky-keys-active');
    }

    if (accessibility.bounce_keys) {
      document.documentElement.classList.add('bounce-keys-active');
      console.log('⌨️ AccessibilityManager: Applied bounce-keys-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('bounce-keys-active');
    }

    // Apply language support features
    if (accessibility.language_support && accessibility.language_support.length > 0) {
      document.documentElement.classList.add('language-support-active');
      console.log('🌍 AccessibilityManager: Applied language-support-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('language-support-active');
    }

    if (accessibility.translation_tools) {
      document.documentElement.classList.add('translation-tools-active');
      console.log('🌍 AccessibilityManager: Applied translation-tools-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('translation-tools-active');
    }

    if (accessibility.pronunciation_guides) {
      document.documentElement.classList.add('pronunciation-guides-active');
      console.log('🗣️ AccessibilityManager: Applied pronunciation-guides-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('pronunciation-guides-active');
    }

    if (accessibility.glossary_terms) {
      document.documentElement.classList.add('glossary-terms-active');
      console.log('📚 AccessibilityManager: Applied glossary-terms-active to document for user:', username);
    } else {
      document.documentElement.classList.remove('glossary-terms-active');
    }

    // Apply hover delay
    document.documentElement.className = document.documentElement.className.replace(/hover-delay-\d+/, '');
    document.documentElement.classList.add(`hover-delay-${accessibility.hover_delay}`);
    console.log('⏱️ AccessibilityManager: Applied hover-delay-${accessibility.hover_delay} to document for user:', username);

    console.log('🎨 AccessibilityManager: Final document classes for user:', username, document.documentElement.className);
  };

  const clearAllAccessibilityContainers = () => {
    console.log('🎨 AccessibilityManager: Clearing all accessibility classes');
    
    // Clear all accessibility classes from document
    document.documentElement.classList.remove('high-contrast');
    document.documentElement.className = document.documentElement.className.replace(/font-size-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/display-mode-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/line-spacing-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/word-spacing-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/cursor-size-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/focus-indicator-\w+/, '');
    document.documentElement.className = document.documentElement.className.replace(/hover-delay-\d+/, '');
    
    // Remove boolean-based accessibility classes
    document.documentElement.classList.remove(
      'reduced-motion', 'color-blind-support', 'click-assist',
      'alt-text-required', 'form-labels-required', 'button-descriptions', 'link-descriptions',
      'audio-descriptions-active', 'volume-control-active', 'audio-cues-active', 'notification-sounds-active',
      'reading-guides-active', 'text-highlighting-active', 'simplified-layout-active', 'distraction-free-active',
      'sticky-keys-active', 'bounce-keys-active',
      'language-support-active', 'translation-tools-active', 'pronunciation-guides-active', 'glossary-terms-active'
    );
    
    // Remove custom focus color
    document.documentElement.style.removeProperty('--custom-focus-color');
    
    // Remove data attribute from document
    document.documentElement.removeAttribute('data-accessibility-user');
    console.log('🎨 AccessibilityManager: Removed accessibility user attribute from document');
    
    console.log('🎨 AccessibilityManager: All accessibility classes cleared from document');
  };

  // This component doesn't render anything visible
  return null;
};

export default AccessibilityManager; 