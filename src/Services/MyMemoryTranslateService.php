<?php

namespace Subhashladumor1\Translate\Services;

use Illuminate\Support\Facades\Http;
use Subhashladumor1\Translate\Services\Contracts\TranslationServiceInterface;

class MyMemoryTranslateService implements TranslationServiceInterface
{
    protected $config;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->endpoint = rtrim($config['endpoint'], '/');
    }

    /**
     * Translate text using MyMemory API.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        $langPair = $sourceLang === 'auto' ? "auto|{$targetLang}" : "{$sourceLang}|{$targetLang}";
        
        $params = [
            'q' => $text,
            'langpair' => $langPair,
        ];

        if (!empty($this->config['email'])) {
            $params['de'] = $this->config['email'];
        }

        $response = Http::timeout($this->config['timeout'])
            ->get("{$this->endpoint}/get", $params);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['responseData']['translatedText'])) {
                return $data['responseData']['translatedText'];
            }
        }

        throw new \Exception("MyMemory API error: " . $response->body());
    }

    /**
     * Detect language using MyMemory API.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string
    {
        // MyMemory doesn't have direct language detection
        // We use auto in langpair and extract detected language from response
        try {
            $response = Http::timeout($this->config['timeout'])
                ->get("{$this->endpoint}/get", [
                    'q' => $text,
                    'langpair' => 'auto|en',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                // MyMemory auto-detects, but doesn't explicitly return the source language
                // We'll return null and let the auto work in translation
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Check if service is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Get supported languages.
     *
     * @return array
     */
    public function getSupportedLanguages(): array
    {
        // MyMemory supports many language pairs
        // Return common ones
        return [
            'en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh',
            'ar', 'hi', 'nl', 'pl', 'tr', 'sv', 'da', 'fi', 'no', 'cs'
        ];
    }
}
