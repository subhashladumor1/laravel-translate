<?php

namespace Subhashladumor1\Translate\Services;

use Illuminate\Support\Facades\Http;
use Subhashladumor1\Translate\Services\Contracts\TranslationServiceInterface;

class LibreTranslateService implements TranslationServiceInterface
{
    protected $config;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->endpoint = rtrim($config['endpoint'], '/');
    }

    /**
     * Translate text using LibreTranslate API.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        $payload = [
            'q' => $text,
            'source' => $sourceLang === 'auto' ? 'auto' : $sourceLang,
            'target' => $targetLang,
            'format' => 'text',
        ];

        if (!empty($this->config['api_key'])) {
            $payload['api_key'] = $this->config['api_key'];
        }

        $response = Http::timeout($this->config['timeout'])
            ->post("{$this->endpoint}/translate", $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data['translatedText'] ?? $text;
        }

        throw new \Exception("LibreTranslate API error: " . $response->body());
    }

    /**
     * Detect language using LibreTranslate API.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string
    {
        $payload = ['q' => $text];

        if (!empty($this->config['api_key'])) {
            $payload['api_key'] = $this->config['api_key'];
        }

        $response = Http::timeout($this->config['timeout'])
            ->post("{$this->endpoint}/detect", $payload);

        if ($response->successful()) {
            $data = $response->json();
            return $data[0]['language'] ?? null;
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
                ->get("{$this->endpoint}/languages");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Return empty if fails
        }

        return [];
    }
}
