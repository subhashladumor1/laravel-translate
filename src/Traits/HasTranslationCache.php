<?php

namespace Subhashladumor1\Translate\Traits;

use Illuminate\Support\Facades\Cache;

trait HasTranslationCache
{
    /**
     * Get cached translation.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string|null
     */
    protected function getCachedTranslation(string $text, string $targetLang, string $sourceLang): ?string
    {
        $cacheKey = $this->getCacheKey($text, $targetLang, $sourceLang);
        return Cache::get($cacheKey);
    }

    /**
     * Cache translation result.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @param string $translation
     * @return void
     */
    protected function cacheTranslation(string $text, string $targetLang, string $sourceLang, string $translation): void
    {
        $cacheKey = $this->getCacheKey($text, $targetLang, $sourceLang);
        $ttl = $this->config['cache']['ttl'];
        
        Cache::put($cacheKey, $translation, $ttl);
    }

    /**
     * Generate cache key for translation.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    protected function getCacheKey(string $text, string $targetLang, string $sourceLang = ''): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'translate';
        $hash = md5($text);
        
        return "{$prefix}:{$sourceLang}:{$targetLang}:{$hash}";
    }

    /**
     * Invalidate cache for specific translation.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return void
     */
    protected function invalidateCache(string $text, string $targetLang, string $sourceLang): void
    {
        $cacheKey = $this->getCacheKey($text, $targetLang, $sourceLang);
        Cache::forget($cacheKey);
    }
}
