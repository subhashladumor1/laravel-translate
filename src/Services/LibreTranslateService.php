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
        try {
            $payload = [
                'q' => $text,
                'source' => $sourceLang === 'auto' ? 'auto' : $sourceLang,
                'target' => $targetLang,
                'format' => 'text',
            ];

            // API key is now required for public instance
            if (!empty($this->config['api_key'])) {
                $payload['api_key'] = $this->config['api_key'];
            } else {
                // Throw descriptive error if no API key is configured
                throw new \Exception(
                    "LibreTranslate requires an API key. " .
                    "Get one at https://portal.libretranslate.com or use alternative endpoint. " .
                    "Set TRANSLATE_LIBRE_API_KEY in .env or disable this service."
                );
            }

            $response = Http::timeout($this->config['timeout'] ?? 15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->endpoint}/translate", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Handle different response formats
                if (isset($data['translatedText'])) {
                    $translation = $data['translatedText'];
                } elseif (isset($data['translation'])) {
                    $translation = $data['translation'];
                } else {
                    throw new \Exception("Unexpected response format: " . json_encode($data));
                }
                
                // Decode any URL encoding in the response
                return urldecode(trim($translation));
            }

            $errorMessage = $response->body();
            $statusCode = $response->status();
            
            // Check for API key error
            if ($statusCode === 400 || $statusCode === 403) {
                $errorData = $response->json();
                if (isset($errorData['error']) && stripos($errorData['error'], 'API key') !== false) {
                    throw new \Exception(
                        "LibreTranslate API key required. " .
                        "Get one at https://portal.libretranslate.com (free tier available) or " .
                        "use a self-hosted instance. Set TRANSLATE_LIBRE_API_KEY in .env"
                    );
                }
            }
            
            throw new \Exception("LibreTranslate API error (HTTP {$statusCode}): {$errorMessage}");
            
        } catch (\Exception $e) {
            \Log::warning("LibreTranslate translation failed", [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100),
                'target' => $targetLang,
                'has_api_key' => !empty($this->config['api_key']),
            ]);
            throw $e;
        }
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
