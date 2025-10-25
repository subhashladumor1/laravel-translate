<?php

namespace Subhashladumor1\Translate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class DetectLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->detectLocale($request);

        if ($locale) {
            App::setLocale($locale);
            Session::put(config('translate.middleware.session_key', 'locale'), $locale);
        }

        return $next($request);
    }

    /**
     * Detect locale from various sources.
     *
     * @param Request $request
     * @return string|null
     */
    protected function detectLocale(Request $request): ?string
    {
        $config = config('translate.middleware');

        // 1. Check query parameter
        if ($config['detect_from_query'] ?? true) {
            $queryParam = $config['query_param'] ?? 'lang';
            if ($request->has($queryParam)) {
                return $request->query($queryParam);
            }
        }

        // 2. Check session
        $sessionKey = $config['session_key'] ?? 'locale';
        if (Session::has($sessionKey)) {
            return Session::get($sessionKey);
        }

        // 3. Check authenticated user preference
        if (($config['detect_from_user'] ?? true) && $request->user()) {
            if (method_exists($request->user(), 'getPreferredLocale')) {
                return $request->user()->getPreferredLocale();
            }
            
            // Check common locale attributes
            if (isset($request->user()->locale)) {
                return $request->user()->locale;
            }
            
            if (isset($request->user()->language)) {
                return $request->user()->language;
            }
        }

        // 4. Check browser Accept-Language header
        if ($config['detect_from_browser'] ?? true) {
            $browserLocale = $this->detectFromBrowser($request);
            if ($browserLocale) {
                return $browserLocale;
            }
        }

        // 5. GeoIP detection (optional)
        if ($config['detect_from_geoip'] ?? false) {
            $geoLocale = $this->detectFromGeoIP($request);
            if ($geoLocale) {
                return $geoLocale;
            }
        }

        // Return null to use app default
        return null;
    }

    /**
     * Detect locale from browser Accept-Language header.
     *
     * @param Request $request
     * @return string|null
     */
    protected function detectFromBrowser(Request $request): ?string
    {
        $languages = $request->getLanguages();
        $supportedLocales = config('app.supported_locales', ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh']);

        foreach ($languages as $language) {
            // Extract base language (e.g., 'en' from 'en-US')
            $baseLanguage = substr($language, 0, 2);
            
            if (in_array($baseLanguage, $supportedLocales)) {
                return $baseLanguage;
            }

            if (in_array($language, $supportedLocales)) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Detect locale from GeoIP.
     *
     * @param Request $request
     * @return string|null
     */
    protected function detectFromGeoIP(Request $request): ?string
    {
        // This is a placeholder for GeoIP detection
        // You can integrate with services like MaxMind GeoIP2 or ipapi.co
        
        try {
            $ip = $request->ip();
            
            // Example: Using ipapi.co free API
            // $response = Http::get("https://ipapi.co/{$ip}/json/");
            // if ($response->successful()) {
            //     $data = $response->json();
            //     return $this->mapCountryToLocale($data['country_code']);
            // }
            
        } catch (\Exception $e) {
            // Silently fail
        }

        return null;
    }

    /**
     * Map country code to locale.
     *
     * @param string $countryCode
     * @return string|null
     */
    protected function mapCountryToLocale(string $countryCode): ?string
    {
        $mapping = [
            'US' => 'en',
            'GB' => 'en',
            'CA' => 'en',
            'ES' => 'es',
            'MX' => 'es',
            'FR' => 'fr',
            'DE' => 'de',
            'IT' => 'it',
            'PT' => 'pt',
            'BR' => 'pt',
            'RU' => 'ru',
            'JP' => 'ja',
            'KR' => 'ko',
            'CN' => 'zh',
        ];

        return $mapping[strtoupper($countryCode)] ?? null;
    }
}
