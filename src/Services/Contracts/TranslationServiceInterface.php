<?php

namespace Subhashladumor1\Translate\Services\Contracts;

interface TranslationServiceInterface
{
    /**
     * Translate text from source to target language.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'auto'): string;

    /**
     * Detect the language of given text.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string;

    /**
     * Check if the service is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get supported languages.
     *
     * @return array
     */
    public function getSupportedLanguages(): array;
}
