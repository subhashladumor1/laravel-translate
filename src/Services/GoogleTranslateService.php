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
        // Use Google Translate free endpoint (unofficial)
        $params = [
            'client' => 'gtx',
            'sl' => $sourceLang === 'auto' ? 'auto' : $sourceLang,
            'tl' => $targetLang,
            'dt' => 't',
            'q' => $text,
        ];

        $response = Http::timeout($this->config['timeout'])
            ->get("{$this->endpoint}/translate_a/single", $params);

        if ($response->successful()) {
            $data = $response->json();
            
            // Parse Google's response format
            if (isset($data[0][0][0])) {
                return $data[0][0][0];
            }
        }

        throw new \Exception("Google Translate API error: " . $response->body());
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
