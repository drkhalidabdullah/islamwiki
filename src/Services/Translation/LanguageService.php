<?php

namespace IslamWiki\Services\Translation;

/**
 * Language Service
 * 
 * Manages language switching, detection, and preferences
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */
class LanguageService
{
    private $database;
    private $config;
    private $supportedLanguages;
    private $currentLanguage;
    private $defaultLanguage = 'en';

    public function __construct($database, $config)
    {
        $this->database = $database;
        $this->config = $config;
        $this->initializeSupportedLanguages();
        $this->detectCurrentLanguage();
    }

    /**
     * Initialize supported languages
     */
    private function initializeSupportedLanguages(): void
    {
        $this->supportedLanguages = [
            'en' => [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'flag' => '🇺🇸',
                'is_active' => true,
                'is_default' => true
            ],
            'ar' => [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🇸🇦',
                'is_active' => true,
                'is_default' => false
            ],
            'fr' => [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'direction' => 'ltr',
                'flag' => '🇫🇷',
                'is_active' => true,
                'is_default' => false
            ],
            'es' => [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'direction' => 'ltr',
                'flag' => '🇪🇸',
                'is_active' => true,
                'is_default' => false
            ],
            'de' => [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'direction' => 'ltr',
                'flag' => '🇩🇪',
                'is_active' => true,
                'is_default' => false
            ]
        ];
    }

    /**
     * Detect current language from various sources
     */
    private function detectCurrentLanguage(): void
    {
        // 1. Check URL parameter
        if (isset($_GET['lang']) && $this->isLanguageSupported($_GET['lang'])) {
            $this->currentLanguage = $_GET['lang'];
            $this->setLanguageCookie($_GET['lang']);
            return;
        }

        // 2. Check session
        if (isset($_SESSION['language']) && $this->isLanguageSupported($_SESSION['language'])) {
            $this->currentLanguage = $_SESSION['language'];
            return;
        }

        // 3. Check cookie
        if (isset($_COOKIE['language']) && $this->isLanguageSupported($_COOKIE['language'])) {
            $this->currentLanguage = $_COOKIE['language'];
            $_SESSION['language'] = $_COOKIE['language'];
            return;
        }

        // 4. Check browser language
        $browserLanguage = $this->detectBrowserLanguage();
        if ($browserLanguage && $this->isLanguageSupported($browserLanguage)) {
            $this->currentLanguage = $browserLanguage;
            return;
        }

        // 5. Use default language
        $this->currentLanguage = $this->defaultLanguage;
    }

    /**
     * Detect browser language from Accept-Language header
     */
    private function detectBrowserLanguage(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($languages as $language) {
            $lang = trim(explode(';', $language)[0]);
            $lang = explode('-', $lang)[0]; // Get primary language code
            
            if ($this->isLanguageSupported($lang)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Set language cookie
     */
    private function setLanguageCookie(string $language): void
    {
        setcookie('language', $language, time() + (365 * 24 * 60 * 60), '/'); // 1 year
    }

    /**
     * Switch to a different language
     */
    public function switchLanguage(string $languageCode): bool
    {
        if (!$this->isLanguageSupported($languageCode)) {
            return false;
        }

        $this->currentLanguage = $languageCode;
        $_SESSION['language'] = $languageCode;
        $this->setLanguageCookie($languageCode);

        return true;
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get current language info
     */
    public function getCurrentLanguageInfo(): array
    {
        return $this->supportedLanguages[$this->currentLanguage] ?? $this->supportedLanguages[$this->defaultLanguage];
    }

    /**
     * Get all supported languages
     */
    public function getSupportedLanguages(): array
    {
        return array_filter($this->supportedLanguages, function($lang) {
            return $lang['is_active'];
        });
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $languageCode): bool
    {
        return isset($this->supportedLanguages[$languageCode]) && 
               $this->supportedLanguages[$languageCode]['is_active'];
    }

    /**
     * Get language info by code
     */
    public function getLanguageInfo(string $languageCode): ?array
    {
        return $this->supportedLanguages[$languageCode] ?? null;
    }

    /**
     * Get text direction for current language
     */
    public function getCurrentDirection(): string
    {
        return $this->getCurrentLanguageInfo()['direction'];
    }

    /**
     * Check if current language is RTL
     */
    public function isCurrentLanguageRTL(): bool
    {
        return $this->getCurrentDirection() === 'rtl';
    }

    /**
     * Get language switcher data for frontend
     */
    public function getLanguageSwitcherData(): array
    {
        $languages = $this->getSupportedLanguages();
        $current = $this->getCurrentLanguage();

        return [
            'current_language' => $current,
            'current_info' => $this->getCurrentLanguageInfo(),
            'available_languages' => array_map(function($lang) use ($current) {
                return [
                    'code' => $lang['code'],
                    'name' => $lang['name'],
                    'native_name' => $lang['native_name'],
                    'flag' => $lang['flag'],
                    'direction' => $lang['direction'],
                    'is_current' => $lang['code'] === $current,
                    'url' => $this->getLanguageUrl($lang['code'])
                ];
            }, $languages)
        ];
    }

    /**
     * Generate URL for language switching
     */
    private function getLanguageUrl(string $languageCode): string
    {
        $currentUrl = $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrl);
        $query = [];
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }
        
        $query['lang'] = $languageCode;
        
        return $parsedUrl['path'] . '?' . http_build_query($query);
    }

    /**
     * Get translation key for current language
     */
    public function getTranslationKey(string $key): string
    {
        return $this->currentLanguage . '.' . $key;
    }

    /**
     * Apply language-specific CSS classes
     */
    public function getLanguageCSSClasses(): string
    {
        $classes = ['lang-' . $this->currentLanguage];
        
        if ($this->isCurrentLanguageRTL()) {
            $classes[] = 'rtl';
        } else {
            $classes[] = 'ltr';
        }
        
        return implode(' ', $classes);
    }
}
