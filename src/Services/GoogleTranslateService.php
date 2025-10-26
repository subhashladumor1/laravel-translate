<?php

namespace Subhashladumor1\Translate\Services;

use Illuminate\Support\Facades\Http;
use Subhashladumor1\Translate\Services\Contracts\TranslationServiceInterface;

class GoogleTranslateService implements TranslationServiceInterface
{
    protected $config;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->endpoint = 'https://translate.googleapis.com';
    }

    /**
     * Translate text using Google Translate free endpoint.
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        try {
            // Use Google Translate free endpoint (unofficial)
            $params = [
                'client' => 'gtx',
                'sl' => $sourceLang === 'auto' ? 'auto' : $sourceLang,
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text,
            ];

            $response = Http::timeout($this->config['timeout'] ?? 10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => '*/*',
                ])
                ->get("{$this->endpoint}/translate_a/single", $params);

            if ($response->successful()) {
                $data = $response->json();
                
                // Parse Google's response format
                // Response is array: [[[ "translation", "original", null, null, 3]]] 
                if (isset($data[0][0][0])) {
                    $translation = $data[0][0][0];
                    // Decode any URL encoding in the response
                    return urldecode(trim($translation));
                }
                
                // Alternative format check
                if (isset($data[0]) && is_array($data[0])) {
                    $translations = [];
                    foreach ($data[0] as $item) {
                        if (isset($item[0])) {
                            $translations[] = $item[0];
                        }
                    }
                    if (!empty($translations)) {
                        return urldecode(trim(implode(' ', $translations)));
                    }
                }
                
                throw new \Exception("Unexpected Google response format: " . json_encode($data));
            }

            $errorMessage = $response->body();
            $statusCode = $response->status();
            throw new \Exception("Google Translate API error (HTTP {$statusCode}): {$errorMessage}");
            
        } catch (\Exception $e) {
            \Log::warning("Google Translate translation failed", [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100),
                'target' => $targetLang,
            ]);
            throw $e;
        }
    }

    /**
     * Detect language using Google Translate.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string
    {
        try {
            $params = [
                'client' => 'gtx',
                'sl' => 'auto',
                'tl' => 'en',
                'dt' => 't',
                'q' => $text,
            ];

            $response = Http::timeout($this->config['timeout'])
                ->get("{$this->endpoint}/translate_a/single", $params);

            if ($response->successful()) {
                $data = $response->json();
                // Google returns detected language in index 2
                return $data[2] ?? null;
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
        return [
            'af', 'sq', 'am', 'ar', 'hy', 'az', 'eu', 'be', 'bn', 'bs',
            'bg', 'ca', 'ceb', 'ny', 'zh', 'co', 'hr', 'cs', 'da', 'nl',
            'en', 'eo', 'et', 'tl', 'fi', 'fr', 'fy', 'gl', 'ka', 'de',
            'el', 'gu', 'ht', 'ha', 'haw', 'iw', 'hi', 'hmn', 'hu', 'is',
            'ig', 'id', 'ga', 'it', 'ja', 'jw', 'kn', 'kk', 'km', 'ko',
            'ku', 'ky', 'lo', 'la', 'lv', 'lt', 'lb', 'mk', 'mg', 'ms',
            'ml', 'mt', 'mi', 'mr', 'mn', 'my', 'ne', 'no', 'ps', 'fa',
            'pl', 'pt', 'pa', 'ro', 'ru', 'sm', 'gd', 'sr', 'st', 'sn',
            'sd', 'si', 'sk', 'sl', 'so', 'es', 'su', 'sw', 'sv', 'tg',
            'ta', 'te', 'th', 'tr', 'uk', 'ur', 'uz', 'vi', 'cy', 'xh',
            'yi', 'yo', 'zu'
        ];
    }
}
