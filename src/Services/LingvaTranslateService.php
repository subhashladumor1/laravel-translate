<?php

namespace Subhashladumor1\Translate\Services;

use Illuminate\Support\Facades\Http;
use Subhashladumor1\Translate\Services\Contracts\TranslationServiceInterface;

class LingvaTranslateService implements TranslationServiceInterface
{
    protected $config;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->endpoint = rtrim($config['endpoint'], '/');
    }

    /**
     * Translate text using Lingva API.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        $source = $sourceLang === 'auto' ? 'auto' : $sourceLang;
        $encodedText = urlencode($text);

        $response = Http::timeout($this->config['timeout'])
            ->get("{$this->endpoint}/api/v1/{$source}/{$targetLang}/{$encodedText}");

        if ($response->successful()) {
            $data = $response->json();
            return $data['translation'] ?? $text;
        }

        throw new \Exception("Lingva API error: " . $response->body());
    }

    /**
     * Detect language using Lingva API.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string
    {
        // Lingva uses auto detection in translation
        // We'll make a translation call to auto and parse the detected language
        try {
            $encodedText = urlencode($text);
            $response = Http::timeout($this->config['timeout'])
                ->get("{$this->endpoint}/api/v1/auto/en/{$encodedText}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['info']['detectedSource'] ?? null;
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
        try {
            $response = Http::timeout($this->config['timeout'])
                ->get("{$this->endpoint}/api/v1/languages");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Return empty if fails
        }

        return [];
    }
}
