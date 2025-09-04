import apiClient from './apiClient';

export interface UserSettings {
  account: {
    username: string;
    email: string;
    first_name: string;
    last_name: string;
    phone: string;
    date_of_birth: string;
    gender: string;
    location: string;
    website: string;
    bio: string;
    display_name: string;
    avatar_url?: string;
    social_links?: {
      twitter?: string;
      facebook?: string;
      instagram?: string;
      linkedin?: string;
      youtube?: string;
    };
  };
  preferences: {
    email_notifications: boolean;
    push_notifications: boolean;
    profile_public: boolean;
    show_email: boolean;
    show_last_seen: boolean;
    language: string;
    timezone: string;
    theme: 'light' | 'dark' | 'auto';
    content_language: string;
    notification_sound: boolean;
    email_digest: 'daily' | 'weekly' | 'monthly' | 'never';
    content_preferences: {
      show_nsfw_content: boolean;
      content_rating: 'G' | 'PG' | 'PG-13' | 'R';
      auto_translate: boolean;
      translation_language: string;
    };
  };
  security: {
    two_factor_enabled: boolean;
    two_factor_method: 'totp' | 'sms' | 'email';
    session_timeout: number;
    login_notifications: boolean;
    password_change_required: boolean;
    security_alerts: boolean;
    max_concurrent_sessions: number;
    trusted_devices: Array<{
      id: string;
      name: string;
      device_type: string;
      last_used: string;
      ip_address: string;
    }>;
    security_questions: Array<{
      question: string;
      answer_hash: string;
    }>;
  };
  privacy: {
    profile_visibility: 'public' | 'friends' | 'private';
    activity_visibility: 'public' | 'friends' | 'private';
    search_visibility: boolean;
    analytics_consent: boolean;
    data_export: boolean;
    data_deletion: boolean;
    third_party_sharing: boolean;
    location_sharing: boolean;
    contact_info_visibility: 'public' | 'friends' | 'private';
  };
  notifications: {
    content_updates: boolean;
    comment_replies: boolean;
    mentions: boolean;
    new_followers: boolean;
    security_alerts: boolean;
    system_announcements: boolean;
    marketing_emails: boolean;
    digest_frequency: 'daily' | 'weekly' | 'monthly' | 'never';
  };
  accessibility: {
    high_contrast: boolean;
    screen_reader_support: boolean;
    keyboard_navigation: boolean;
    reduced_motion: boolean;
    color_blind_support: boolean;
    font_size: 'small' | 'medium' | 'large' | 'x-large';
    display_mode: 'standard' | 'wide' | 'full';
    
    // Enhanced Visual Accessibility
    line_spacing: 'normal' | 'relaxed' | 'very-relaxed';
    word_spacing: 'normal' | 'wide' | 'very-wide';
    cursor_size: 'normal' | 'large' | 'extra-large';
    focus_indicator: 'default' | 'high-visibility' | 'custom';
    focus_color: string; // Custom focus color hex code
    
    // Advanced Screen Reader Support
    aria_labels: boolean;
    live_regions: boolean;
    landmark_roles: boolean;
    skip_links: boolean;
    tab_order: 'logical' | 'custom';
    
    // Alt Text & Content Accessibility
    alt_text_required: boolean;
    form_labels_required: boolean;
    button_descriptions: boolean;
    link_descriptions: boolean;
    
    // Audio Accessibility
    audio_descriptions: boolean;
    volume_control: boolean;
    audio_cues: boolean;
    notification_sounds: boolean;
    
    // Cognitive & Learning Support
    reading_guides: boolean;
    text_highlighting: boolean;
    simplified_layout: boolean;
    distraction_free: boolean;
    
    // Motor & Physical Accessibility
    click_assist: boolean;
    hover_delay: number; // milliseconds
    sticky_keys: boolean;
    bounce_keys: boolean;
    
    // Language & Communication
    language_support: string[];
    translation_tools: boolean;
    pronunciation_guides: boolean;
    glossary_terms: boolean;
  };
}

export interface SettingsResponse {
  success: boolean;
  data?: UserSettings;
  error?: string;
  message?: string;
}

export interface SettingsUpdateRequest {
  section: keyof UserSettings;
  data: Partial<UserSettings[keyof UserSettings]>;
}

class SettingsService {
  /**
   * Get user settings
   */
  async getUserSettings(): Promise<SettingsResponse> {
    try {
      const response = await apiClient.get('/api/user/settings') as SettingsResponse;
      return response;
    } catch (error) {
      console.error('Failed to fetch user settings:', error);
      return {
        success: false,
        error: 'Failed to fetch user settings'
      };
    }
  }

  /**
   * Update user settings
   */
  async updateUserSettings(updateRequest: SettingsUpdateRequest): Promise<SettingsResponse> {
    try {
      const response = await apiClient.put('/api/user/settings', updateRequest) as SettingsResponse;
      return response;
    } catch (error) {
      console.error('Failed to update user settings:', error);
      return {
        success: false,
        error: 'Failed to update user settings'
      };
    }
  }

  /**
   * Update specific setting section
   */
  async updateSettingsSection<T extends keyof UserSettings>(
    section: T,
    data: Partial<UserSettings[T]>
  ): Promise<SettingsResponse> {
    return this.updateUserSettings({ section, data });
  }

  /**
   * Reset settings to defaults
   */
  async resetSettingsToDefaults(): Promise<SettingsResponse> {
    try {
      const response = await apiClient.post('/api/user/settings/reset', {}) as SettingsResponse;
      return response;
    } catch (error) {
      console.error('Failed to reset settings:', error);
      return {
        success: false,
        error: 'Failed to reset settings'
      };
    }
  }

  /**
   * Export user data
   */
  async exportUserData(): Promise<Blob> {
    try {
      const response = await apiClient.get('/api/user/data/export') as Blob;
      return response;
    } catch (error) {
      console.error('Failed to export user data:', error);
      throw new Error('Failed to export user data');
    }
  }

  /**
   * Delete user account
   */
  async deleteUserAccount(confirmation: string): Promise<SettingsResponse> {
    try {
      // For now, we'll just validate the confirmation on the frontend
      // In a real implementation, this would be sent to the backend
      if (confirmation !== 'DELETE') {
        return {
          success: false,
          error: 'Invalid confirmation'
        };
      }
      
      const response = await apiClient.delete('/api/user/account') as SettingsResponse;
      return response;
    } catch (error) {
      console.error('Failed to delete user account:', error);
      return {
        success: false,
        error: 'Failed to delete user account'
      };
    }
  }

  /**
   * Get available languages
   */
  getAvailableLanguages() {
    return [
      { code: 'en', name: 'English', native: 'English' },
      { code: 'ar', name: 'Arabic', native: 'العربية' },
      { code: 'ur', name: 'Urdu', native: 'اردو' },
      { code: 'tr', name: 'Turkish', native: 'Türkçe' },
      { code: 'ms', name: 'Malay', native: 'Bahasa Melayu' },
      { code: 'id', name: 'Indonesian', native: 'Bahasa Indonesia' },
      { code: 'bn', name: 'Bengali', native: 'বাংলা' },
      { code: 'fa', name: 'Persian', native: 'فارسی' },
      { code: 'hi', name: 'Hindi', native: 'हिन्दी' },
      { code: 'sw', name: 'Swahili', native: 'Kiswahili' }
    ];
  }

  /**
   * Get available timezones
   */
  getAvailableTimezones() {
    // Common timezones for Islamic regions
    const commonTimezones = [
      'UTC', 'Asia/Riyadh', 'Asia/Dubai', 'Asia/Karachi', 'Asia/Istanbul',
      'Asia/Jakarta', 'Asia/Kuala_Lumpur', 'Europe/London', 'America/New_York'
    ];
    
    return commonTimezones.map((timezone: string) => ({
      value: timezone,
      label: timezone.replace(/_/g, ' '),
      offset: new Date().toLocaleString('en', { timeZone: timezone, timeZoneName: 'short' })
    }));
  }

  /**
   * Get available themes
   */
  getAvailableThemes() {
    return [
      { value: 'light', label: 'Light', icon: '☀️' },
      { value: 'dark', label: 'Dark', icon: '🌙' },
      { value: 'auto', label: 'Auto (System)', icon: '🔄' },
      { value: 'sepia', label: 'Sepia', icon: '📜' },
      { value: 'high-contrast', label: 'High Contrast', icon: '🎨' }
    ];
  }

  /**
   * Get content rating options
   */
  getContentRatingOptions() {
    return [
      { value: 'G', label: 'General Audience', description: 'Suitable for all ages' },
      { value: 'PG', label: 'Parental Guidance', description: 'Some material may not be suitable for children' },
      { value: 'PG-13', label: 'Parental Guidance 13+', description: 'Some material may be inappropriate for children under 13' },
      { value: 'R', label: 'Restricted', description: 'Under 17 requires accompanying parent or adult guardian' }
    ];
  }

  /**
   * Get notification frequency options
   */
  getNotificationFrequencyOptions() {
    return [
      { value: 'immediate', label: 'Immediate', description: 'Receive notifications as they happen' },
      { value: 'hourly', label: 'Hourly', description: 'Receive notifications once per hour' },
      { value: 'daily', label: 'Daily', description: 'Receive notifications once per day' },
      { value: 'weekly', label: 'Weekly', description: 'Receive notifications once per week' },
      { value: 'never', label: 'Never', description: 'Do not receive notifications' }
    ];
  }

  /**
   * Validate settings data
   */
  validateSettings(settings: Partial<UserSettings>): { isValid: boolean; errors: string[] } {
    const errors: string[] = [];

    // Validate email format
    if (settings.account?.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(settings.account.email)) {
      errors.push('Invalid email format');
    }

    // Validate phone format (basic validation)
    if (settings.account?.phone && !/^[+]?[1-9][\d]{0,15}$/.test(settings.account.phone.replace(/\s/g, ''))) {
      errors.push('Invalid phone number format');
    }

    // Validate website URL
    if (settings.account?.website && !/^https?:\/\/.+/.test(settings.account.website)) {
      errors.push('Website must start with http:// or https://');
    }

    // Validate session timeout
    if (settings.security?.session_timeout && settings.security.session_timeout < 0) {
      errors.push('Session timeout cannot be negative');
    }

    // Validate max concurrent sessions
    if (settings.security?.max_concurrent_sessions && settings.security.max_concurrent_sessions < 1) {
      errors.push('Maximum concurrent sessions must be at least 1');
    }

    return {
      isValid: errors.length === 0,
      errors
    };
  }
}

export const settingsService = new SettingsService();
export default settingsService; 