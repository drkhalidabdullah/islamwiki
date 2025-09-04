<?php

namespace IslamWiki\Services\Translation\Providers;

/**
 * MyMemory Translation Provider
 * 
 * Free translation service that works on shared hosting
 * 1000 requests per day, no API key required
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */
class MyMemoryProvider implements TranslationProviderInterface
{
    private string $apiUrl;
    private array $config;
    private array $supportedPairs;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['url'] ?? 'https://api.mymemory.translated.net/get';
        $this->config = $config;
        $this->initializeSupportedPairs();
    }

    /**
     * Initialize supported language pairs
     */
    private function initializeSupportedPairs(): void
    {
        $this->supportedPairs = [
            'en-ar' => ['en' => 'English', 'ar' => 'Arabic'],
            'ar-en' => ['ar' => 'Arabic', 'en' => 'English'],
            'en-fr' => ['en' => 'English', 'fr' => 'French'],
            'fr-en' => ['fr' => 'French', 'en' => 'English'],
            'en-es' => ['en' => 'English', 'es' => 'Spanish'],
            'es-en' => ['es' => 'Spanish', 'en' => 'English'],
            'en-de' => ['en' => 'English', 'de' => 'German'],
            'de-en' => ['de' => 'German', 'en' => 'English'],
            'en-it' => ['en' => 'English', 'it' => 'Italian'],
            'it-en' => ['it' => 'Italian', 'en' => 'English'],
            'en-pt' => ['en' => 'English', 'pt' => 'Portuguese'],
            'pt-en' => ['pt' => 'Portuguese', 'en' => 'English'],
            'en-ru' => ['en' => 'English', 'ru' => 'Russian'],
            'ru-en' => ['ru' => 'Russian', 'en' => 'English'],
            'en-zh' => ['en' => 'English', 'zh' => 'Chinese'],
            'zh-en' => ['zh' => 'Chinese', 'en' => 'English'],
            'en-ja' => ['en' => 'English', 'ja' => 'Japanese'],
            'ja-en' => ['ja' => 'Japanese', 'en' => 'English'],
            'en-ko' => ['en' => 'English', 'ko' => 'Korean'],
            'ko-en' => ['ko' => 'Korean', 'en' => 'English']
        ];
    }

    /**
     * Translate text using MyMemory API
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): array
    {
        try {
            $langPair = $sourceLanguage . '|' . $targetLanguage;
            
            $params = [
                'q' => $text,
                'langpair' => $langPair
            ];

            $url = $this->apiUrl . '?' . http_build_query($params);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'IslamWiki/1.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception("MyMemory API request failed with HTTP $httpCode");
            }

            $result = json_decode($response, true);
            
            if (!isset($result['responseData']['translatedText'])) {
                throw new \Exception("Invalid response format from MyMemory API");
            }

            return [
                'translated_text' => $result['responseData']['translatedText'],
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'provider' => 'MyMemory',
                'confidence' => $result['responseData']['match'] ?? 0.8,
                'raw_response' => $result
            ];

        } catch (\Exception $e) {
            throw new \Exception("MyMemory translation failed: " . $e->getMessage());
        }
    }

    /**
     * Check if language pair is supported
     */
    public function isLanguagePairSupported(string $sourceLanguage, string $targetLanguage): bool
    {
        $pair = $sourceLanguage . '-' . $targetLanguage;
        return isset($this->supportedPairs[$pair]);
    }

    /**
     * Get supported language pairs
     */
    public function getSupportedLanguagePairs(): array
    {
        return $this->supportedPairs;
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
            'name' => 'MyMemory',
            'description' => 'Free translation service with 1000 requests per day',
            'website' => 'https://mymemory.translated.net/',
            'pricing' => 'Free (1000 requests/day)',
            'languages' => count($this->supportedPairs),
            'api_key_required' => false,
            'shared_hosting_compatible' => true
        ];
    }
}
