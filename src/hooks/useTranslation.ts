import { useState, useEffect } from 'react';
import { translationService } from '../services/translation/TranslationService';

/**
 * Hook for using translations
 */
export function useTranslation() {
  const [currentLanguage, setCurrentLanguage] = useState(translationService.getCurrentLanguage());

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
