<?php

namespace Subhashladumor1\Translate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string translate(string $text, string $targetLang = null, string $sourceLang = 'auto')
 * @method static array translateBatch(array $texts, string $targetLang = null, string $sourceLang = 'auto')
 * @method static string detectLanguage(string $text)
 * @method static array translateFile(string $filePath, string $targetLang)
 * @method static void clearCache()
 * @method static array getAnalytics()
 *
 * @see \Subhashladumor1\Translate\Services\TranslatorManager
 */
class Translate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translator';
    }
}
