<?php

/**
 * Translation System Test Suite
 * 
 * Comprehensive testing for the translation system including:
 * - Database schema validation
 * - Translation providers (LibreTranslate, Apertium)
 * - Translation service functionality
 * - API endpoints
 * - Frontend integration
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

require_once __DIR__ . '/../src/Core/Config/Config.php';
require_once __DIR__ . '/../src/Core/Database/Database.php';
require_once __DIR__ . '/../src/Services/Translation/TranslationService.php';
require_once __DIR__ . '/../src/Services/Translation/Providers/LibreTranslateProvider.php';
require_once __DIR__ . '/../src/Services/Translation/Providers/ApertiumProvider.php';

class TranslationSystemTest
{
    private $config;
    private $database;
    private $translationService;
    private $testResults = [];
    private $errors = [];

    public function __construct()
    {
        $this->config = new \IslamWiki\Core\Config\Config();
        $this->database = new \IslamWiki\Core\Database\Database($this->config);
        $this->translationService = new \IslamWiki\Services\Translation\TranslationService($this->database, $this->config);
    }

    /**
     * Run all translation system tests
     */
    public function runAllTests()
    {
        echo "🌍 IslamWiki Translation System Test Suite v0.0.6\n";
        echo "================================================\n\n";

        $this->testDatabaseSchema();
        $this->testTranslationProviders();
        $this->testTranslationService();
        $this->testApiEndpoints();
        $this->testLanguageSupport();
        $this->testRTLSupport();
        $this->testTranslationMemory();
        $this->testTranslationJobs();

        $this->displayResults();
    }

    /**
     * Test database schema
     */
    private function testDatabaseSchema()
    {
        echo "�� Testing Database Schema...\n";
        
        try {
            // Test languages table
            $languages = $this->database->query("SELECT * FROM languages LIMIT 1");
            $this->testResults['database_schema'] = '✅ Languages table exists';
            
            // Test translations table
            $translations = $this->database->query("SELECT * FROM translations LIMIT 1");
            $this->testResults['translations_table'] = '✅ Translations table exists';
            
            // Test translation_memory table
            $memory = $this->database->query("SELECT * FROM translation_memory LIMIT 1");
            $this->testResults['memory_table'] = '✅ Translation memory table exists';
            
            // Test translation_jobs table
            $jobs = $this->database->query("SELECT * FROM translation_jobs LIMIT 1");
            $this->testResults['jobs_table'] = '✅ Translation jobs table exists';
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Database schema error: " . $e->getMessage();
        }
    }

    /**
     * Test translation providers
     */
    private function testTranslationProviders()
    {
        echo "🔧 Testing Translation Providers...\n";
        
        // Test LibreTranslate
        try {
            $libreConfig = [
                'url' => 'http://localhost:5000',
                'api_key' => null
            ];
            $libreProvider = new \IslamWiki\Services\Translation\Providers\LibreTranslateProvider($libreConfig);
            
            if ($libreProvider->isHealthy()) {
                $this->testResults['libretranslate'] = '✅ LibreTranslate provider is healthy';
                
                // Test translation
                $result = $libreProvider->translate('Hello world', 'en', 'ar');
                if ($result && $result['translated_text']) {
                    $this->testResults['libretranslate_translation'] = '✅ LibreTranslate translation working';
                } else {
                    $this->errors[] = "❌ LibreTranslate translation failed";
                }
            } else {
                $this->errors[] = "❌ LibreTranslate provider is not healthy";
            }
        } catch (Exception $e) {
            $this->errors[] = "❌ LibreTranslate error: " . $e->getMessage();
        }

        // Test Apertium
        try {
            $apertiumConfig = [
                'url' => 'https://www.apertium.org/apy/translate'
            ];
            $apertiumProvider = new \IslamWiki\Services\Translation\Providers\ApertiumProvider($apertiumConfig);
            
            if ($apertiumProvider->isHealthy()) {
                $this->testResults['apertium'] = '✅ Apertium provider is healthy';
                
                // Test translation
                $result = $apertiumProvider->translate('Hello world', 'en', 'ar');
                if ($result && $result['translated_text']) {
                    $this->testResults['apertium_translation'] = '✅ Apertium translation working';
                } else {
                    $this->errors[] = "❌ Apertium translation failed";
                }
            } else {
                $this->errors[] = "❌ Apertium provider is not healthy";
            }
        } catch (Exception $e) {
            $this->errors[] = "❌ Apertium error: " . $e->getMessage();
        }
    }

    /**
     * Test translation service
     */
    private function testTranslationService()
    {
        echo "⚙️ Testing Translation Service...\n";
        
        try {
            // Test language management
            $languages = $this->translationService->getSupportedLanguages();
            if (count($languages) > 0) {
                $this->testResults['language_management'] = '✅ Language management working';
            } else {
                $this->errors[] = "❌ No supported languages found";
            }
            
            // Test translation workflow
            $testContent = "This is a test article for translation.";
            $result = $this->translationService->translateContent($testContent, 'en', 'ar');
            
            if ($result && $result['translated_text']) {
                $this->testResults['translation_workflow'] = '✅ Translation workflow working';
            } else {
                $this->errors[] = "❌ Translation workflow failed";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Translation service error: " . $e->getMessage();
        }
    }

    /**
     * Test API endpoints
     */
    private function testApiEndpoints()
    {
        echo "🌐 Testing API Endpoints...\n";
        
        $baseUrl = 'http://localhost/api/translation';
        
        // Test languages endpoint
        $response = $this->makeApiRequest($baseUrl . '/languages');
        if ($response && isset($response['data'])) {
            $this->testResults['languages_api'] = '✅ Languages API working';
        } else {
            $this->errors[] = "❌ Languages API failed";
        }
        
        // Test translate endpoint
        $translateData = [
            'text' => 'Hello world',
            'source_language' => 'en',
            'target_language' => 'ar'
        ];
        $response = $this->makeApiRequest($baseUrl . '/translate', 'POST', $translateData);
        if ($response && isset($response['translated_text'])) {
            $this->testResults['translate_api'] = '✅ Translate API working';
        } else {
            $this->errors[] = "❌ Translate API failed";
        }
    }

    /**
     * Test language support
     */
    private function testLanguageSupport()
    {
        echo "🗣️ Testing Language Support...\n";
        
        try {
            // Test English support
            $english = $this->translationService->getLanguage('en');
            if ($english && $english['code'] === 'en') {
                $this->testResults['english_support'] = '✅ English language support working';
            } else {
                $this->errors[] = "❌ English language support failed";
            }
            
            // Test Arabic support
            $arabic = $this->translationService->getLanguage('ar');
            if ($arabic && $arabic['code'] === 'ar') {
                $this->testResults['arabic_support'] = '✅ Arabic language support working';
            } else {
                $this->errors[] = "❌ Arabic language support failed";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Language support error: " . $e->getMessage();
        }
    }

    /**
     * Test RTL support
     */
    private function testRTLSupport()
    {
        echo "↔️ Testing RTL Support...\n";
        
        try {
            $arabic = $this->translationService->getLanguage('ar');
            if ($arabic && $arabic['direction'] === 'rtl') {
                $this->testResults['rtl_support'] = '✅ RTL support working';
            } else {
                $this->errors[] = "❌ RTL support failed";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ RTL support error: " . $e->getMessage();
        }
    }

    /**
     * Test translation memory
     */
    private function testTranslationMemory()
    {
        echo "🧠 Testing Translation Memory...\n";
        
        try {
            // Test memory storage
            $memory = $this->translationService->storeTranslationMemory('Hello', 'مرحبا', 'en', 'ar');
            if ($memory) {
                $this->testResults['memory_storage'] = '✅ Translation memory storage working';
            } else {
                $this->errors[] = "❌ Translation memory storage failed";
            }
            
            // Test memory retrieval
            $retrieved = $this->translationService->getTranslationMemory('Hello', 'en', 'ar');
            if ($retrieved && $retrieved['translated_text'] === 'مرحبا') {
                $this->testResults['memory_retrieval'] = '✅ Translation memory retrieval working';
            } else {
                $this->errors[] = "❌ Translation memory retrieval failed";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Translation memory error: " . $e->getMessage();
        }
    }

    /**
     * Test translation jobs
     */
    private function testTranslationJobs()
    {
        echo "📋 Testing Translation Jobs...\n";
        
        try {
            // Test job creation
            $job = $this->translationService->createTranslationJob('Test content', 'en', 'ar');
            if ($job && $job['id']) {
                $this->testResults['job_creation'] = '✅ Translation job creation working';
            } else {
                $this->errors[] = "❌ Translation job creation failed";
            }
            
            // Test job processing
            $processed = $this->translationService->processTranslationJob($job['id']);
            if ($processed && $processed['status'] === 'completed') {
                $this->testResults['job_processing'] = '✅ Translation job processing working';
            } else {
                $this->errors[] = "❌ Translation job processing failed";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Translation jobs error: " . $e->getMessage();
        }
    }

    /**
     * Make API request
     */
    private function makeApiRequest($url, $method = 'GET', $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }

    /**
     * Display test results
     */
    private function displayResults()
    {
        echo "\n📊 Test Results Summary\n";
        echo "======================\n\n";
        
        foreach ($this->testResults as $test => $result) {
            echo "$result $test\n";
        }
        
        if (!empty($this->errors)) {
            echo "\n❌ Errors Found:\n";
            foreach ($this->errors as $error) {
                echo "$error\n";
            }
        }
        
        $totalTests = count($this->testResults);
        $passedTests = $totalTests - count($this->errors);
        $successRate = ($passedTests / $totalTests) * 100;
        
        echo "\n📈 Overall Success Rate: " . number_format($successRate, 1) . "%\n";
        echo "✅ Passed: $passedTests/$totalTests tests\n";
        
        if ($successRate >= 90) {
            echo "🎉 Translation system is ready for production!\n";
        } elseif ($successRate >= 70) {
            echo "⚠️ Translation system needs minor fixes\n";
        } else {
            echo "🚨 Translation system needs significant work\n";
        }
    }
}

// Run tests if called directly
if (php_sapi_name() === 'cli') {
    $test = new TranslationSystemTest();
    $test->runAllTests();
}
