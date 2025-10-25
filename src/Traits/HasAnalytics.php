<?php

namespace Subhashladumor1\Translate\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HasAnalytics
{
    protected $analyticsKey = 'translate:analytics';

    /**
     * Track cache hit.
     *
     * @return void
     */
    protected function trackCacheHit(): void
    {
        if (!$this->config['analytics']['enabled'] || !$this->config['analytics']['track_cache_hits']) {
            return;
        }

        $this->incrementAnalytics('cache_hits');
    }

    /**
     * Track cache miss.
     *
     * @return void
     */
    protected function trackCacheMiss(): void
    {
        if (!$this->config['analytics']['enabled'] || !$this->config['analytics']['track_cache_hits']) {
            return;
        }

        $this->incrementAnalytics('cache_misses');
    }

    /**
     * Track API latency.
     *
     * @param string $service
     * @param float $latency
     * @return void
     */
    protected function trackApiLatency(string $service, float $latency): void
    {
        if (!$this->config['analytics']['enabled'] || !$this->config['analytics']['track_api_latency']) {
            return;
        }

        $analytics = $this->getAnalyticsData();
        
        if (!isset($analytics['latency'][$service])) {
            $analytics['latency'][$service] = [
                'total' => 0,
                'count' => 0,
                'min' => $latency,
                'max' => $latency,
            ];
        }

        $analytics['latency'][$service]['total'] += $latency;
        $analytics['latency'][$service]['count']++;
        $analytics['latency'][$service]['min'] = min($analytics['latency'][$service]['min'], $latency);
        $analytics['latency'][$service]['max'] = max($analytics['latency'][$service]['max'], $latency);
        $analytics['latency'][$service]['avg'] = $analytics['latency'][$service]['total'] / $analytics['latency'][$service]['count'];

        $this->saveAnalyticsData($analytics);
    }

    /**
     * Log translation.
     *
     * @param string $source
     * @param string $translation
     * @param string $targetLang
     * @param string $service
     * @return void
     */
    protected function logTranslation(string $source, string $translation, string $targetLang, string $service): void
    {
        if (!$this->config['analytics']['log_translations']) {
            return;
        }

        $analytics = $this->getAnalyticsData();
        
        if (!isset($analytics['translations'])) {
            $analytics['translations'] = [];
        }

        $analytics['translations'][] = [
            'source' => substr($source, 0, 100),
            'translation' => substr($translation, 0, 100),
            'target_lang' => $targetLang,
            'service' => $service,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Keep only last 100 translations to avoid memory issues
        if (count($analytics['translations']) > 100) {
            $analytics['translations'] = array_slice($analytics['translations'], -100);
        }

        $this->saveAnalyticsData($analytics);
    }

    /**
     * Increment analytics counter.
     *
     * @param string $key
     * @param int $amount
     * @return void
     */
    protected function incrementAnalytics(string $key, int $amount = 1): void
    {
        $analytics = $this->getAnalyticsData();
        
        if (!isset($analytics[$key])) {
            $analytics[$key] = 0;
        }

        $analytics[$key] += $amount;
        
        $this->saveAnalyticsData($analytics);
    }

    /**
     * Get analytics data.
     *
     * @return array
     */
    protected function getAnalyticsData(): array
    {
        return Cache::get($this->analyticsKey, [
            'cache_hits' => 0,
            'cache_misses' => 0,
            'latency' => [],
            'translations' => [],
        ]);
    }

    /**
     * Save analytics data.
     *
     * @param array $data
     * @return void
     */
    protected function saveAnalyticsData(array $data): void
    {
        $retentionDays = $this->config['analytics']['retention_days'] ?? 30;
        $ttl = $retentionDays * 86400;
        
        Cache::put($this->analyticsKey, $data, $ttl);
    }

    /**
     * Clear analytics data.
     *
     * @return void
     */
    public function clearAnalytics(): void
    {
        Cache::forget($this->analyticsKey);
    }
}
