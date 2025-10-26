<?php

namespace Subhashladumor1\Translate\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Subhashladumor1\Translate\Services\Contracts\TranslationServiceInterface;
use Subhashladumor1\Translate\Services\LibreTranslateService;
use Subhashladumor1\Translate\Services\LingvaTranslateService;
use Subhashladumor1\Translate\Services\MyMemoryTranslateService;
use Subhashladumor1\Translate\Services\GoogleTranslateService;
use Subhashladumor1\Translate\Services\ArgosTranslateService;
use Subhashladumor1\Translate\Traits\HasTranslationCache;
use Subhashladumor1\Translate\Traits\HasAnalytics;

class TranslatorManager
{
    use HasTranslationCache, HasAnalytics;

    protected $app;
    protected $services = [];
    protected $config;

    /**
     * Create a new translator manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->config = config('translate');
        $this->initializeServices();
    }

    /**
     * Initialize translation services.
     *
     * @return void
     */
    protected function initializeServices()
    {
        $this->services = [
            'libre' => new LibreTranslateService($this->config['services']['libre']),
            'lingva' => new LingvaTranslateService($this->config['services']['lingva']),
            'mymemory' => new MyMemoryTranslateService($this->config['services']['mymemory']),
            'google' => new GoogleTranslateService($this->config['services']['google']),
            'argos' => new ArgosTranslateService($this->config['services']['argos']),
        ];
    }

    /**
     * Translate text with automatic fallback.
     *
     * @param string $text
     * @param string|null $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, ?string $targetLang = null, string $sourceLang = 'auto'): string
    {
        $targetLang = $targetLang ?? $this->config['target_lang'];
        $sourceLang = $sourceLang ?? $this->config['source_lang'];

        // Check cache first
        if ($this->config['cache']['enabled']) {
            $cached = $this->getCachedTranslation($text, $targetLang, $sourceLang);
            if ($cached !== null) {
                $this->trackCacheHit();
                return $cached;
            }
            $this->trackCacheMiss();
        }

        // Auto-detect source language if needed
        if ($sourceLang === 'auto') {
            $sourceLang = $this->detectLanguage($text);
        }

        // Try translation with fallback chain
        $fallbackChain = $this->config['fallback_chain'];
        $errors = [];

        foreach ($fallbackChain as $serviceName) {
            if (!isset($this->services[$serviceName])) {
                continue;
            }

            $service = $this->services[$serviceName];

            if (!$service->isEnabled()) {
                continue;
            }

            try {
                $startTime = microtime(true);
                $translation = $service->translate($text, $targetLang, $sourceLang);
                $latency = (microtime(true) - $startTime) * 1000;

                $this->trackApiLatency($serviceName, $latency);

                if (!empty($translation)) {
                    // Cache the result
                    if ($this->config['cache']['enabled']) {
                        $this->cacheTranslation($text, $targetLang, $sourceLang, $translation);
                    }

                    // Log if enabled
                    if ($this->config['analytics']['log_translations']) {
                        $this->logTranslation($text, $translation, $targetLang, $serviceName);
                    }

                    return $translation;
                }
            } catch (\Exception $e) {
                $errors[$serviceName] = $e->getMessage();
                Log::warning("Translation failed with {$serviceName}: " . $e->getMessage());
                continue;
            }
        }

        // All services failed, log and return original
        Log::error('All translation services failed', [
            'text' => $text,
            'errors' => $errors
        ]);

        return $text;
    }

    /**
     * Translate batch of texts.
     *
     * @param array $texts
     * @param string|null $targetLang
     * @param string $sourceLang
     * @return array
     */
    public function translateBatch(array $texts, ?string $targetLang = null, string $sourceLang = 'auto'): array
    {
        $results = [];
        $chunkSize = $this->config['batch']['chunk_size'] ?? 50;
        $delay = $this->config['batch']['delay_between_requests'] ?? 100;

        // Check cache for all texts first
        $uncachedTexts = [];
        $uncachedKeys = [];
        
        foreach ($texts as $key => $text) {
            if ($this->config['cache']['enabled']) {
                $cached = $this->getCachedTranslation($text, $targetLang ?? $this->config['target_lang'], $sourceLang);
                if ($cached !== null) {
                    $results[$key] = $cached;
                    $this->trackCacheHit();
                    continue;
                }
            }
            $uncachedTexts[] = $text;
            $uncachedKeys[] = $key;
        }

        // Process uncached texts in chunks
        if (!empty($uncachedTexts)) {
            foreach (array_chunk($uncachedTexts, $chunkSize, true) as $chunkKeys => $chunk) {
                $chunkResults = [];
                
                foreach ($chunk as $idx => $text) {
                    $translation = $this->translate($text, $targetLang, $sourceLang);
                    $chunkResults[$idx] = $translation;
                    
                    // Add delay between requests to avoid rate limiting
                    if ($delay > 0 && $idx < count($chunk) - 1) {
                        usleep($delay * 1000); // Convert to microseconds
                    }
                }
                
                // Merge chunk results
                foreach ($chunkResults as $idx => $translation) {
                    $originalKey = $uncachedKeys[array_search($idx, array_keys($chunkResults))];
                    $results[$originalKey] = $translation;
                }
            }
        }

        // Sort results by original keys
        ksort($results);
        return $results;
    }

    /**
     * Detect language of given text.
     *
     * @param string $text
     * @return string
     */
    public function detectLanguage(string $text): string
    {
        if (!$this->config['detection']['enabled']) {
            return $this->config['source_lang'];
        }

        // Check cache
        if ($this->config['detection']['cache_detections']) {
            $cacheKey = $this->getCacheKey('detect', $text);
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        // Try detection with primary service
        $defaultService = $this->config['default_service'];
        
        if (isset($this->services[$defaultService]) && $this->services[$defaultService]->isEnabled()) {
            try {
                $detected = $this->services[$defaultService]->detectLanguage($text);
                
                if ($detected && $this->config['detection']['cache_detections']) {
                    $cacheKey = $this->getCacheKey('detect', $text);
                    Cache::put($cacheKey, $detected, $this->config['cache']['ttl']);
                }
                
                return $detected ?: $this->config['source_lang'];
            } catch (\Exception $e) {
                Log::warning("Language detection failed: " . $e->getMessage());
            }
        }

        return $this->config['source_lang'];
    }

    /**
     * Translate an entire file.
     *
     * @param string $filePath
     * @param string $targetLang
     * @param callable|null $progressCallback
     * @return array
     */
    public function translateFile(string $filePath, string $targetLang, ?callable $progressCallback = null): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $content = include $filePath;

        if (!is_array($content)) {
            throw new \InvalidArgumentException("File must return an array");
        }

        return $this->translateArray($content, $targetLang, 'auto', $progressCallback);
    }

    /**
     * Recursively translate array values.
     *
     * @param array $data
     * @param string $targetLang
     * @param string $sourceLang
     * @param callable|null $progressCallback
     * @return array
     */
    public function translateArray(array $data, string $targetLang, string $sourceLang = 'auto', ?callable $progressCallback = null): array
    {
        $result = [];
        $flatTexts = [];
        $flatKeys = [];

        // Flatten array and collect all string values
        $this->flattenArray($data, $flatTexts, $flatKeys);

        if (empty($flatTexts)) {
            return $data;
        }

        // Translate all texts in batch
        $translations = [];
        $chunkSize = $this->config['batch']['chunk_size'] ?? 50;
        $delay = $this->config['batch']['delay_between_requests'] ?? 100;

        foreach (array_chunk($flatTexts, $chunkSize, true) as $chunk) {
            foreach ($chunk as $idx => $text) {
                $translation = $this->translate($text, $targetLang, $sourceLang);
                $translations[$idx] = $translation;

                // Call progress callback
                if ($progressCallback) {
                    $progressCallback();
                }

                // Add delay to avoid rate limiting
                if ($delay > 0) {
                    usleep($delay * 1000);
                }
            }
        }

        // Rebuild array structure with translations
        $result = $this->rebuildArray($data, $flatKeys, $translations);

        return $result;
    }

    /**
     * Flatten array and collect string values.
     *
     * @param array $data
     * @param array &$texts
     * @param array &$keys
     * @param string $prefix
     * @return void
     */
    protected function flattenArray(array $data, array &$texts, array &$keys, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $currentKey = $prefix === '' ? $key : $prefix . '.' . $key;
            
            if (is_array($value)) {
                $this->flattenArray($value, $texts, $keys, $currentKey);
            } elseif (is_string($value)) {
                $texts[] = $value;
                $keys[] = $currentKey;
            }
        }
    }

    /**
     * Rebuild array structure with translated values.
     *
     * @param array $original
     * @param array $flatKeys
     * @param array $translations
     * @return array
     */
    protected function rebuildArray(array $original, array $flatKeys, array $translations): array
    {
        $result = [];
        $translationIndex = 0;

        foreach ($original as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->rebuildArrayRecursive($value, $flatKeys, $translations, $translationIndex, $key);
            } elseif (is_string($value)) {
                $result[$key] = $translations[$translationIndex] ?? $value;
                $translationIndex++;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Recursively rebuild array structure.
     *
     * @param array $data
     * @param array $flatKeys
     * @param array $translations
     * @param int &$index
     * @param string $prefix
     * @return array
     */
    protected function rebuildArrayRecursive(array $data, array $flatKeys, array $translations, int &$index, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->rebuildArrayRecursive($value, $flatKeys, $translations, $index, $prefix . '.' . $key);
            } elseif (is_string($value)) {
                $result[$key] = $translations[$index] ?? $value;
                $index++;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Clear all translation cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $prefix = $this->config['cache']['prefix'];
        Cache::flush(); // In production, use Cache::tags() for more precision
        
        Log::info('Translation cache cleared');
    }

    /**
     * Get analytics data.
     *
     * @return array
     */
    public function getAnalytics(): array
    {
        return $this->getAnalyticsData();
    }

    /**
     * Get specific service instance.
     *
     * @param string $name
     * @return TranslationServiceInterface|null
     */
    public function service(string $name): ?TranslationServiceInterface
    {
        return $this->services[$name] ?? null;
    }
}
