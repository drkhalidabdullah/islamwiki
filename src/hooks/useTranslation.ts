import { useState, useEffect } from 'react';
import { translationService } from '../services/translation/TranslationService';
import { useAuthStore } from '../store/authStore';

/**
 * Hook for using translations with user-specific language preferences
 */
export function useTranslation() {
  const { isAuthenticated } = useAuthStore();
  const [currentLanguage, setCurrentLanguage] = useState(translationService.getCurrentLanguage());

  // Handle authentication state changes
  useEffect(() => {
    // Reset to English when user logs out
    if (!isAuthenticated) {
      translationService.setLanguage('en');
    } else {
      // Load user's language preference when they log in
      loadUserLanguagePreference();
    }
  }, [isAuthenticated]);

  const loadUserLanguagePreference = async () => {
    try {
      const response = await fetch('/api/language/current');
      if (response.ok) {
        const langData = await response.json();
        translationService.setLanguage(langData.code);
      }
    } catch (error) {
      console.error('Error loading user language preference:', error);
    }
  };

  useEffect(() => {
    // Subscribe to language changes
    const unsubscribe = translationService.onLanguageChange((newLanguage) => {
      setCurrentLanguage(newLanguage);
    });

    return unsubscribe;
  }, []);

  const t = (key: string, fallback?: string) => {
    return translationService.t(key, fallback);
  };

  const setLanguage = (language: string) => {
    translationService.setLanguage(language);
  };

  return {
    t,
    currentLanguage,
    setLanguage,
    availableLanguages: translationService.getAvailableLanguages()
  };
}
