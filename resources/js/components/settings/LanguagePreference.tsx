import React, { useState, useEffect } from 'react';
import Card from '../ui/Card';
import Button from '../ui/Button';

interface Language {
  code: string;
  name: string;
  native_name: string;
  flag: string;
  direction: 'ltr' | 'rtl';
  is_active: boolean;
  is_default: boolean;
}

interface LanguagePreferenceProps {
  currentLanguage: string;
  availableLanguages: Language[];
  onLanguageChange: (languageCode: string) => void;
  onSavePreferences: (preferences: any) => void;
}

export const LanguagePreference: React.FC<LanguagePreferenceProps> = ({
  currentLanguage,
  availableLanguages,
  onLanguageChange,
  onSavePreferences
}) => {
  const [selectedLanguage, setSelectedLanguage] = useState(currentLanguage);
  const [autoDetect, setAutoDetect] = useState(true);
  const [rememberPreference, setRememberPreference] = useState(true);
  const [showAdvanced, setShowAdvanced] = useState(false);

  useEffect(() => {
    setSelectedLanguage(currentLanguage);
  }, [currentLanguage]);

  const handleLanguageSelect = (languageCode: string) => {
    setSelectedLanguage(languageCode);
    onLanguageChange(languageCode);
  };

  const handleSavePreferences = () => {
    const preferences = {
      language: selectedLanguage,
      auto_detect: autoDetect,
      remember_preference: rememberPreference
    };
    onSavePreferences(preferences);
  };

  const currentLang = availableLanguages.find(lang => lang.code === selectedLanguage);

  return (
    <Card className="p-6">
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
            Language Preferences
          </h3>
          <p className="text-sm text-gray-600 dark:text-gray-400">
            Choose your preferred language for the interface and content
          </p>
        </div>

        {/* Current Language Display */}
        <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
          <div className="flex items-center space-x-3 rtl:space-x-reverse">
            <span className="text-2xl">{currentLang?.flag}</span>
            <div>
              <h4 className="font-medium text-gray-900 dark:text-white">
                {currentLang?.native_name}
              </h4>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                {currentLang?.name} • {currentLang?.direction.toUpperCase()}
              </p>
            </div>
          </div>
        </div>

        {/* Language Selection */}
        <div>
          <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Select Language
          </label>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {availableLanguages.map((language) => (
              <button
                key={language.code}
                onClick={() => handleLanguageSelect(language.code)}
                className={`p-4 rounded-lg border-2 transition-all ${
                  selectedLanguage === language.code
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                }`}
              >
                <div className="flex items-center space-x-3 rtl:space-x-reverse">
                  <span className="text-xl">{language.flag}</span>
                  <div className="text-left rtl:text-right">
                    <div className="font-medium text-gray-900 dark:text-white">
                      {language.native_name}
                    </div>
                    <div className="text-sm text-gray-600 dark:text-gray-400">
                      {language.name}
                    </div>
                  </div>
                  {selectedLanguage === language.code && (
                    <svg className="w-5 h-5 text-blue-500 ml-auto rtl:mr-auto rtl:ml-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  )}
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Advanced Options */}
        <div>
          <button
            onClick={() => setShowAdvanced(!showAdvanced)}
            className="flex items-center space-x-2 rtl:space-x-reverse text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
          >
            <span>Advanced Options</span>
            <svg
              className={`w-4 h-4 transition-transform ${showAdvanced ? 'rotate-180' : ''}`}
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          {showAdvanced && (
            <div className="mt-4 space-y-4 pl-6 border-l-2 border-gray-200 dark:border-gray-700">
              {/* Auto-detect Language */}
              <div className="flex items-center justify-between">
                <div>
                  <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Auto-detect Language
                  </label>
                  <p className="text-xs text-gray-600 dark:text-gray-400">
                    Automatically detect language from browser settings
                  </p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={autoDetect}
                    onChange={(e) => setAutoDetect(e.target.checked)}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              {/* Remember Preference */}
              <div className="flex items-center justify-between">
                <div>
                  <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Remember Preference
                  </label>
                  <p className="text-xs text-gray-600 dark:text-gray-400">
                    Save language preference for future visits
                  </p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={rememberPreference}
                    onChange={(e) => setRememberPreference(e.target.checked)}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
          )}
        </div>

        {/* Save Button */}
        <div className="flex justify-end space-x-3 rtl:space-x-reverse">
          <Button
            variant="outline"
            onClick={() => setSelectedLanguage(currentLanguage)}
          >
            Reset
          </Button>
          <Button
            onClick={handleSavePreferences}
            className="bg-blue-600 hover:bg-blue-700"
          >
            Save Preferences
          </Button>
        </div>

        {/* Language Info */}
        <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
          <div className="flex items-start space-x-3 rtl:space-x-reverse">
            <svg className="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
            </svg>
            <div>
              <h4 className="text-sm font-medium text-blue-900 dark:text-blue-100">
                Language Switching
              </h4>
              <p className="text-sm text-blue-700 dark:text-blue-300 mt-1">
                You can also change language using the language switcher in the header, 
                or by adding <code className="bg-blue-100 dark:bg-blue-800 px-1 rounded">?lang=ar</code> to any URL.
              </p>
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
};
