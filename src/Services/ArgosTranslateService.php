<?php

namespace Subhashladumor1\Translate\Services;

use Illuminate\Support\Facades\Http;
use Subhashladumor1\Translate\Services\Contracts\TranslationServiceInterface;

class ArgosTranslateService implements TranslationServiceInterface
{
    protected $config;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->endpoint = rtrim($config['endpoint'], '/');
    }

    /**
     * Translate text using Argos Translate (offline/local).
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        // Argos Translate requires explicit source language
        if ($sourceLang === 'auto') {
            $sourceLang = 'en'; // Default fallback
        }

        $payload = [
            'q' => $text,
            'source' => $sourceLang,
            'target' => $targetLang,
        ];

        $response = Http::timeout($this->config['timeout'])
            ->post("{$this->endpoint}/translate", $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data['translatedText'] ?? $text;
        }

        throw new \Exception("Argos Translate error: " . $response->body());
    }

    /**
     * Detect language using Argos Translate.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string
    {
        // Argos Translate may not have built-in detection
        // Return null to use other services
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
                ->get("{$this->endpoint}/languages");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Return empty if fails
        }

        return ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh'];
    }
}
