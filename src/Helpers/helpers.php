<?php

use Subhashladumor1\Translate\Facades\Translate;

if (!function_exists('translateText')) {
    /**
     * Translate text to target language.
     *
     * @param string $text
     * @param string|null $targetLang
     * @param string $sourceLang
     * @return string
     */
    function translateText(string $text, ?string $targetLang = null, string $sourceLang = 'auto'): string
    {
        return Translate::translate($text, $targetLang, $sourceLang);
    }
}

if (!function_exists('translate')) {
    /**
     * Translate text to target language.
     *
     * @param string $text
     * @param string|null $targetLang
     * @param string $sourceLang
     * @return string
     */
    function translate(string $text, ?string $targetLang = null, string $sourceLang = 'auto'): string
    {
        return Translate::translate($text, $targetLang, $sourceLang);
    }
}

if (!function_exists('translate_batch')) {
    /**
     * Translate batch of texts.
     *
     * @param array $texts
     * @param string|null $targetLang
     * @param string $sourceLang
     * @return array
     */
    function translate_batch(array $texts, ?string $targetLang = null, string $sourceLang = 'auto'): array
    {
        return Translate::translateBatch($texts, $targetLang, $sourceLang);
    }
}

if (!function_exists('detect_language')) {
    /**
     * Detect language of text.
     *
     * @param string $text
     * @return string
     */
    function detect_language(string $text): string
    {
        return Translate::detectLanguage($text);
    }
}

if (!function_exists('translate_array')) {
    /**
     * Recursively translate array values.
     *
     * @param array $data
     * @param string|null $targetLang
     * @param string $sourceLang
     * @return array
     */
    function translate_array(array $data, ?string $targetLang = null, string $sourceLang = 'auto'): array
    {
        return app('translator')->translateArray($data, $targetLang ?? config('translate.target_lang'), $sourceLang);
    }
}
