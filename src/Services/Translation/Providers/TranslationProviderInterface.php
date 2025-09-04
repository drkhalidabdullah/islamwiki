<?php

namespace IslamWiki\Services\Translation\Providers;

/**
 * Translation Provider Interface
 * 
 * Defines the contract for all translation providers
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */
interface TranslationProviderInterface
{
    /**
     * Translate text from source language to target language
     * 
     * @param string $text Text to translate
     * @param string $sourceLanguage Source language code
     * @param string $targetLanguage Target language code
     * @return array Translation result with metadata
     * @throws \Exception If translation fails
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): array;

    /**
     * Check if a language pair is supported
     * 
     * @param string $sourceLanguage Source language code
     * @param string $targetLanguage Target language code
     * @return bool True if supported, false otherwise
     */
    public function isLanguagePairSupported(string $sourceLanguage, string $targetLanguage): bool;

    /**
     * Get all supported language pairs
     * 
     * @return array Array of supported language pairs
     */
    public function getSupportedLanguagePairs(): array;

    /**
     * Check if the provider is healthy and available
     * 
     * @return bool True if healthy, false otherwise
     */
    public function isHealthy(): bool;

    /**
     * Get provider information
     * 
     * @return array Provider information
     */
    public function getProviderInfo(): array;
}
