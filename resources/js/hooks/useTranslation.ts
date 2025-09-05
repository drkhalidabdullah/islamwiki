import { useState, useEffect } from 'react';
import { translationService } from '../services/translation/TranslationService';

/**
 * Simplified hook for using translations
 */
export function useTranslation() {
  const [currentLanguage, setCurrentLanguage] = useState(translationService.getCurrentLanguage());

  // Sync with backend on mount and listen for changes
  useEffect(() => {
    // Initialize from backend
    const initializeLanguage = async () => {
      try {
        const response = await fetch('/api/language/current', {
          credentials: 'include'
        });
        if (response.ok) {
          const data = await response.json();
          console.log('useTranslation: Initializing with language:', data.code);
          translationService.setLanguage(data.code);
          setCurrentLanguage(data.code);
        }
      } catch (error) {
        console.warn('Failed to sync language with backend:', error);
      }
    };

    initializeLanguage();

    // Listen for language changes
    const unsubscribe = translationService.onLanguageChange((lang) => {
      console.log('useTranslation: Language change received:', lang);
      setCurrentLanguage(lang);
    });

    return unsubscribe;
  }, []); // Remove currentLanguage from dependency array to prevent infinite loop

  const t = (key: string, fallback?: string) => {
    return translationService.t(key, fallback);
  };

  const switchLanguage = async (langCode: string) => {
    try {
      const response = await fetch('/api/language/switch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ lang: langCode })
      });
      
      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          translationService.setLanguage(langCode);
          setCurrentLanguage(langCode);
          
          // Update HTML direction for RTL languages
          const direction = data.direction || 'ltr';
          document.documentElement.dir = direction;
          document.documentElement.lang = langCode;
        }
      }
    } catch (error) {
      console.error('Error switching language:', error);
    }
  };

  return {
    t,
    currentLanguage,
    switchLanguage
  };
}