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
        try {
            // MyMemory requires source language, default to 'en' if auto
            $source = $sourceLang === 'auto' ? 'en' : $sourceLang;
            $langPair = "{$source}|{$targetLang}";
            
            $params = [
                'q' => $text,
                'langpair' => $langPair,
            ];

            if (!empty($this->config['email'])) {
                $params['de'] = $this->config['email'];
            }

            $response = Http::timeout($this->config['timeout'] ?? 10)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->endpoint}/get", $params);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check for valid response
                if (isset($data['responseData']['translatedText'])) {
                    $translation = $data['responseData']['translatedText'];
                    
                    // Check response status/matches
                    $matches = $data['responseData']['match'] ?? 1;
                    
                    // If match is too low and same as input, it might be untranslated
                    if ($matches < 0.3 && $translation === $text) {
                        throw new \Exception("Translation quality too low (match: {$matches})");
                    }
                    
                    // Decode any URL encoding in the response
                    return urldecode(trim($translation));
                }
                
                throw new \Exception("Invalid MyMemory response format: " . json_encode($data));
            }

            $errorMessage = $response->body();
            $statusCode = $response->status();
            throw new \Exception("MyMemory API error (HTTP {$statusCode}): {$errorMessage}");
            
        } catch (\Exception $e) {
            \Log::warning("MyMemory translation failed", [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100),
                'target' => $targetLang,
            ]);
            throw $e;
        }
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
