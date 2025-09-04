<?php

namespace IslamWiki\Services\Translation\Providers;

/**
 * Google Translate Provider
 * 
 * Free translation service using Google Translate API
 * Works on shared hosting, no API key required
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */
class GoogleTranslateProvider implements TranslationProviderInterface
{
    private string $apiUrl;
    private array $config;
    private array $supportedLanguages;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['url'] ?? 'https://translate.googleapis.com/translate_a/single';
        $this->config = $config;
        $this->initializeSupportedLanguages();
    }

    /**
     * Initialize supported languages
     */
    private function initializeSupportedLanguages(): void
    {
        $this->supportedLanguages = [
            'en' => 'English',
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'hi' => 'Hindi',
            'tr' => 'Turkish',
            'pl' => 'Polish',
            'nl' => 'Dutch',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'no' => 'Norwegian',
            'fi' => 'Finnish',
            'cs' => 'Czech',
            'hu' => 'Hungarian',
            'ro' => 'Romanian',
            'bg' => 'Bulgarian',
            'hr' => 'Croatian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'et' => 'Estonian',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'th' => 'Thai',
            'vi' => 'Vietnamese',
            'id' => 'Indonesian',
            'ms' => 'Malay',
            'tl' => 'Filipino',
            'sw' => 'Swahili',
            'af' => 'Afrikaans',
            'sq' => 'Albanian',
            'am' => 'Amharic',
            'az' => 'Azerbaijani',
            'eu' => 'Basque',
            'be' => 'Belarusian',
            'bn' => 'Bengali',
            'bs' => 'Bosnian',
            'ca' => 'Catalan',
            'ceb' => 'Cebuano',
            'co' => 'Corsican',
            'cy' => 'Welsh',
            'eo' => 'Esperanto',
            'fa' => 'Persian',
            'ga' => 'Irish',
            'gl' => 'Galician',
            'gu' => 'Gujarati',
            'ha' => 'Hausa',
            'haw' => 'Hawaiian',
            'is' => 'Icelandic',
            'ig' => 'Igbo',
            'jw' => 'Javanese',
            'ka' => 'Georgian',
            'kk' => 'Kazakh',
            'km' => 'Khmer',
            'ku' => 'Kurdish',
            'ky' => 'Kyrgyz',
            'lo' => 'Lao',
            'la' => 'Latin',
            'lb' => 'Luxembourgish',
            'mk' => 'Macedonian',
            'mg' => 'Malagasy',
            'ml' => 'Malayalam',
            'mt' => 'Maltese',
            'mi' => 'Maori',
            'mr' => 'Marathi',
            'mn' => 'Mongolian',
            'my' => 'Myanmar',
            'ne' => 'Nepali',
            'ny' => 'Chichewa',
            'ps' => 'Pashto',
            'pa' => 'Punjabi',
            'sm' => 'Samoan',
            'gd' => 'Scots Gaelic',
            'sr' => 'Serbian',
            'st' => 'Sesotho',
            'sn' => 'Shona',
            'sd' => 'Sindhi',
            'si' => 'Sinhala',
            'so' => 'Somali',
            'su' => 'Sundanese',
            'tg' => 'Tajik',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'yo' => 'Yoruba',
            'zu' => 'Zulu'
        ];
    }

    /**
     * Translate text using Google Translate API
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): array
    {
        try {
            $params = [
                'client' => 'gtx',
                'sl' => $sourceLanguage,
                'tl' => $targetLanguage,
                'dt' => 't',
                'q' => $text
            ];

            $url = $this->apiUrl . '?' . http_build_query($params);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception("Google Translate API request failed with HTTP $httpCode");
            }

            $result = json_decode($response, true);
            
            if (!isset($result[0][0][0])) {
                throw new \Exception("Invalid response format from Google Translate API");
            }

            $translatedText = '';
            foreach ($result[0] as $translation) {
                if (isset($translation[0])) {
                    $translatedText .= $translation[0];
                }
            }

            return [
                'translated_text' => $translatedText,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'provider' => 'Google Translate',
                'confidence' => 0.9,
                'raw_response' => $result
            ];

        } catch (\Exception $e) {
            throw new \Exception("Google Translate failed: " . $e->getMessage());
        }
    }

    /**
     * Check if language pair is supported
     */
    public function isLanguagePairSupported(string $sourceLanguage, string $targetLanguage): bool
    {
        return isset($this->supportedLanguages[$sourceLanguage]) && 
               isset($this->supportedLanguages[$targetLanguage]);
    }

    /**
     * Get supported language pairs
     */
    public function getSupportedLanguagePairs(): array
    {
        $pairs = [];
        $languages = array_keys($this->supportedLanguages);
        
        foreach ($languages as $source) {
            foreach ($languages as $target) {
                if ($source !== $target) {
                    $pairs["$source-$target"] = [
                        $source => $this->supportedLanguages[$source],
                        $target => $this->supportedLanguages[$target]
                    ];
                }
            }
        }
        
        return $pairs;
    }

    /**
     * Check if provider is healthy
     */
    public function isHealthy(): bool
    {
        try {
            $testResult = $this->translate('Hello', 'en', 'ar');
            return !empty($testResult['translated_text']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider information
     */
    public function getProviderInfo(): array
    {
        return [
            'name' => 'Google Translate',
            'description' => 'Free translation service using Google Translate API',
            'website' => 'https://translate.google.com/',
            'pricing' => 'Free (unofficial API)',
            'languages' => count($this->supportedLanguages),
            'api_key_required' => false,
            'shared_hosting_compatible' => true
        ];
    }
}
