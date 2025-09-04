import React, { useState, useEffect, useRef } from 'react';
import Button from '../ui/Button';

interface Language {
  code: string;
  name: string;
  native_name: string;
  direction: 'ltr' | 'rtl';
  flag: string;
  url: string;
  is_current: boolean;
}

interface LanguageSwitcherProps {
  currentLanguage: string;
  availableLanguages: Language[];
  onLanguageChange: (languageCode: string) => void;
}

const LanguageSwitcher: React.FC<LanguageSwitcherProps> = ({
  currentLanguage,
  availableLanguages,
  onLanguageChange
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleLanguageChange = async (langCode: string) => {
    if (currentLanguage === langCode) {
      setIsOpen(false);
      return;
    }

    try {
      await onLanguageChange(langCode);
      setIsOpen(false);
    } catch (error) {
      console.error('Error switching language:', error);
    }
  };

  const currentLang = availableLanguages.find(lang => lang.code === currentLanguage);

  if (!currentLang) {
    return null;
  }

  return (
    <div className={`relative ${currentLang.direction === 'rtl' ? 'rtl' : 'ltr'}`} ref={dropdownRef}>
      <Button 
        onClick={() => setIsOpen(!isOpen)} 
        className="flex items-center space-x-2 p-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm"
      >
        <span className="text-xl">{currentLang.flag}</span>
        <span className="hidden md:inline">{currentLang.name}</span>
        <svg className={`w-4 h-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </Button>

      {isOpen && (
        <div className={`absolute ${currentLang.direction === 'rtl' ? 'left-0' : 'right-0'} mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200`}>
          {availableLanguages.map((lang) => (
            <button
              key={lang.code}
              onClick={() => handleLanguageChange(lang.code)}
              className={`flex items-center space-x-2 w-full text-left px-4 py-2 text-sm ${
                lang.is_current ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100'
              }`}
              disabled={lang.is_current}
            >
              <span className="text-xl">{lang.flag}</span>
              <span>{lang.native_name}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  );
};

export default LanguageSwitcher;
