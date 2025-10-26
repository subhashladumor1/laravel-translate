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
        try {
            $source = $sourceLang === 'auto' ? 'auto' : $sourceLang;
            $encodedText = rawurlencode($text);

            $response = Http::timeout($this->config['timeout'] ?? 10)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->endpoint}/api/v1/{$source}/{$targetLang}/{$encodedText}");

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['translation'])) {
                    $translation = $data['translation'];
                    // Decode any URL encoding in the response
                    return urldecode(trim($translation));
                }
                
                throw new \Exception("Unexpected Lingva response format: " . json_encode($data));
            }

            $errorMessage = $response->body();
            $statusCode = $response->status();
            throw new \Exception("Lingva API error (HTTP {$statusCode}): {$errorMessage}");
            
        } catch (\Exception $e) {
            \Log::warning("Lingva translation failed", [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100),
                'target' => $targetLang,
            ]);
            throw $e;
        }
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
            $encodedText = rawurlencode($text);
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
