/**
 * Translation Service
 * 
 * Handles text translation and language switching
 */

// Basic translations for common UI elements
const translations = {
  en: {
    // Navigation
    'nav.home': 'Home',
    'nav.dashboard': 'Dashboard',
    'nav.settings': 'Settings',
    'nav.login': 'Login',
    'nav.register': 'Register',
    'nav.logout': 'Logout',
    'nav.admin': 'Admin',
    'nav.profile': 'Profile',
    
    // Common actions
    'action.save': 'Save',
    'action.cancel': 'Cancel',
    'action.delete': 'Delete',
    'action.edit': 'Edit',
    'action.create': 'Create',
    'action.search': 'Search',
    'action.submit': 'Submit',
    'action.reset': 'Reset',
    
    // Forms
    'form.username': 'Username',
    'form.email': 'Email',
    'form.password': 'Password',
    'form.firstName': 'First Name',
    'form.lastName': 'Last Name',
    'form.confirmPassword': 'Confirm Password',
    
    // Messages
    'message.loading': 'Loading...',
    'message.success': 'Success!',
    'message.error': 'Error occurred',
    'message.saved': 'Settings saved successfully!',
    'message.languageChanged': 'Language changed successfully!',
    
    // Language names
    'language.english': 'English',
    'language.arabic': 'Arabic',
    'language.french': 'French',
    'language.spanish': 'Spanish',
    'language.german': 'German',
    
    // Placeholders
    'placeholder.search': 'Search articles...',
    'placeholder.username': 'Enter username',
    'placeholder.email': 'Enter email',
    'placeholder.password': 'Enter password',
    
    // Settings Page
    'settings.title': 'Settings',
    'settings.account': 'Account',
    'settings.preferences': 'Preferences',
    'settings.security': 'Security',
    'settings.privacy': 'Privacy',
    'settings.notifications': 'Notifications',
    'settings.accessibility': 'Accessibility',
  },
  
  ar: {
    // Navigation
    'nav.home': 'الرئيسية',
    'nav.dashboard': 'لوحة التحكم',
    'nav.settings': 'الإعدادات',
    'nav.login': 'تسجيل الدخول',
    'nav.register': 'إنشاء حساب',
    'nav.logout': 'تسجيل الخروج',
    'nav.admin': 'الإدارة',
    'nav.profile': 'الملف الشخصي',
    
    // Common actions
    'action.save': 'حفظ',
    'action.cancel': 'إلغاء',
    'action.delete': 'حذف',
    'action.edit': 'تعديل',
    'action.create': 'إنشاء',
    'action.search': 'بحث',
    'action.submit': 'إرسال',
    'action.reset': 'إعادة تعيين',
    
    // Forms
    'form.username': 'اسم المستخدم',
    'form.email': 'البريد الإلكتروني',
    'form.password': 'كلمة المرور',
    'form.firstName': 'الاسم الأول',
    'form.lastName': 'اسم العائلة',
    'form.confirmPassword': 'تأكيد كلمة المرور',
    
    // Messages
    'message.loading': 'جاري التحميل...',
    'message.success': 'نجح!',
    'message.error': 'حدث خطأ',
    'message.saved': 'تم حفظ الإعدادات بنجاح!',
    'message.languageChanged': 'تم تغيير اللغة بنجاح!',
    
    // Language names
    'language.english': 'الإنجليزية',
    'language.arabic': 'العربية',
    'language.french': 'الفرنسية',
    'language.spanish': 'الإسبانية',
    'language.german': 'الألمانية',
    
    // Placeholders
    'placeholder.search': 'البحث في المقالات...',
    'placeholder.username': 'أدخل اسم المستخدم',
    'placeholder.email': 'أدخل البريد الإلكتروني',
    'placeholder.password': 'أدخل كلمة المرور',
    
    // Settings Page
    'settings.title': 'الإعدادات',
    'settings.account': 'الحساب',
    'settings.preferences': 'التفضيلات',
    'settings.security': 'الأمان',
    'settings.privacy': 'الخصوصية',
    'settings.notifications': 'الإشعارات',
    'settings.accessibility': 'إمكانية الوصول',
  }
};

class TranslationService {
  private currentLanguage: string = 'en';
  private listeners: Array<(lang: string) => void> = [];

  constructor() {
    // Load language from localStorage or default to 'en'
    this.currentLanguage = localStorage.getItem('language') || 'en';
    
    // Initialize from backend API
    this.initializeFromBackend();
  }

  /**
   * Initialize language from backend API
   */
  private async initializeFromBackend() {
    try {
      const response = await fetch('/api/language/current', {
        credentials: 'include'
      });
      if (response.ok) {
        const data = await response.json();
        if (data.code && data.code !== this.currentLanguage) {
          this.setLanguage(data.code);
        }
      }
    } catch (error) {
      console.warn('Failed to initialize language from backend:', error);
    }
  }

  /**
   * Get translation for a key
   */
  t(key: string, fallback?: string): string {
    const translation = translations[this.currentLanguage as keyof typeof translations];
    if (translation && translation[key as keyof typeof translation]) {
      return translation[key as keyof typeof translation];
    }
    
    // Fallback to English if not found
    const englishTranslation = translations.en[key as keyof typeof translations.en];
    if (englishTranslation) {
      return englishTranslation;
    }
    
    // Return fallback or key if nothing found
    return fallback || key;
  }

  /**
   * Get current language
   */
  getCurrentLanguage(): string {
    return this.currentLanguage;
  }

  /**
   * Set current language
   */
  setLanguage(language: string): void {
    if (translations[language as keyof typeof translations]) {
      this.currentLanguage = language;
      localStorage.setItem('language', language);
      
      // Notify listeners
      this.listeners.forEach(listener => listener(language));
    }
  }

  /**
   * Subscribe to language changes
   */
  onLanguageChange(callback: (lang: string) => void): () => void {
    this.listeners.push(callback);
    
    // Return unsubscribe function
    return () => {
      const index = this.listeners.indexOf(callback);
      if (index > -1) {
        this.listeners.splice(index, 1);
      }
    };
  }

  /**
   * Get all available languages
   */
  getAvailableLanguages(): Array<{code: string, name: string, native_name: string, flag: string, direction: 'ltr' | 'rtl', is_active: boolean, is_default: boolean}> {
    return [
      { code: 'en', name: 'English', native_name: this.t('language.english'), flag: '🇺🇸', direction: 'ltr' as const, is_active: true, is_default: true },
      { code: 'ar', name: 'Arabic', native_name: this.t('language.arabic'), flag: '🇸🇦', direction: 'rtl' as const, is_active: true, is_default: false },
      { code: 'fr', name: 'French', native_name: this.t('language.french'), flag: '🇫🇷', direction: 'ltr' as const, is_active: true, is_default: false },
      { code: 'es', name: 'Spanish', native_name: this.t('language.spanish'), flag: '🇪🇸', direction: 'ltr' as const, is_active: true, is_default: false },
      { code: 'de', name: 'German', native_name: this.t('language.german'), flag: '🇩🇪', direction: 'ltr' as const, is_active: true, is_default: false }
    ];
  }
}

// Create singleton instance
export const translationService = new TranslationService();

// Export the class for testing
export { TranslationService };
