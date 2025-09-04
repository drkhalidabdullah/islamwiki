<?php

namespace IslamWiki\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Services\Translation\LanguageService;

/**
 * Language Controller
 * 
 * Handles language switching and management
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */
class LanguageController
{
    private $languageService;
    private $database;

    public function __construct($database, $config)
    {
        $this->database = $database;
        $this->languageService = new LanguageService($database, $config);
    }

    /**
     * Get current language information
     */
    public function getCurrentLanguage(Request $request): Response
    {
        try {
            $currentLanguage = $this->languageService->getCurrentLanguage();
            $languageInfo = $this->languageService->getCurrentLanguageInfo();
            $switcherData = $this->languageService->getLanguageSwitcherData();

            return new Response([
                'success' => true,
                'data' => [
                    'current_language' => $currentLanguage,
                    'language_info' => $languageInfo,
                    'switcher_data' => $switcherData,
                    'css_classes' => $this->languageService->getLanguageCSSClasses(),
                    'is_rtl' => $this->languageService->isCurrentLanguageRTL()
                ]
            ], 200);

        } catch (\Exception $e) {
            return new Response([
                'success' => false,
                'error' => 'Failed to get language information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switch to a different language
     */
    public function switchLanguage(Request $request): Response
    {
        try {
            $data = $request->getJsonData();
            $languageCode = $data['language'] ?? null;

            if (!$languageCode) {
                return new Response([
                    'success' => false,
                    'error' => 'Language code is required'
                ], 400);
            }

            if (!$this->languageService->isLanguageSupported($languageCode)) {
                return new Response([
                    'success' => false,
                    'error' => 'Language not supported: ' . $languageCode
                ], 400);
            }

            $success = $this->languageService->switchLanguage($languageCode);

            if ($success) {
                $newLanguageInfo = $this->languageService->getCurrentLanguageInfo();
                $switcherData = $this->languageService->getLanguageSwitcherData();

                return new Response([
                    'success' => true,
                    'message' => 'Language switched successfully',
                    'data' => [
                        'current_language' => $languageCode,
                        'language_info' => $newLanguageInfo,
                        'switcher_data' => $switcherData,
                        'css_classes' => $this->languageService->getLanguageCSSClasses(),
                        'is_rtl' => $this->languageService->isCurrentLanguageRTL(),
                        'redirect_url' => $this->generateRedirectUrl($languageCode)
                    ]
                ], 200);
            } else {
                return new Response([
                    'success' => false,
                    'error' => 'Failed to switch language'
                ], 500);
            }

        } catch (\Exception $e) {
            return new Response([
                'success' => false,
                'error' => 'Language switch failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all supported languages
     */
    public function getSupportedLanguages(Request $request): Response
    {
        try {
            $languages = $this->languageService->getSupportedLanguages();
            $switcherData = $this->languageService->getLanguageSwitcherData();

            return new Response([
                'success' => true,
                'data' => [
                    'languages' => $languages,
                    'switcher_data' => $switcherData,
                    'total_count' => count($languages)
                ]
            ], 200);

        } catch (\Exception $e) {
            return new Response([
                'success' => false,
                'error' => 'Failed to get supported languages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get language switcher data for frontend
     */
    public function getLanguageSwitcher(Request $request): Response
    {
        try {
            $switcherData = $this->languageService->getLanguageSwitcherData();

            return new Response([
                'success' => true,
                'data' => $switcherData
            ], 200);

        } catch (\Exception $e) {
            return new Response([
                'success' => false,
                'error' => 'Failed to get language switcher data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect browser language
     */
    public function detectBrowserLanguage(Request $request): Response
    {
        try {
            $browserLanguage = $this->detectBrowserLanguageFromRequest($request);
            $isSupported = $browserLanguage ? $this->languageService->isLanguageSupported($browserLanguage) : false;

            return new Response([
                'success' => true,
                'data' => [
                    'detected_language' => $browserLanguage,
                    'is_supported' => $isSupported,
                    'current_language' => $this->languageService->getCurrentLanguage(),
                    'should_switch' => $isSupported && $browserLanguage !== $this->languageService->getCurrentLanguage()
                ]
            ], 200);

        } catch (\Exception $e) {
            return new Response([
                'success' => false,
                'error' => 'Failed to detect browser language: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate redirect URL for language switching
     */
    private function generateRedirectUrl(string $languageCode): string
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
     * Detect browser language from request headers
     */
    private function detectBrowserLanguageFromRequest(Request $request): ?string
    {
        $acceptLanguage = $request->getHeader('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        $languages = explode(',', $acceptLanguage);
        foreach ($languages as $language) {
            $lang = trim(explode(';', $language)[0]);
            $lang = explode('-', $lang)[0]; // Get primary language code
            
            if ($this->languageService->isLanguageSupported($lang)) {
                return $lang;
            }
        }

        return null;
    }
}
