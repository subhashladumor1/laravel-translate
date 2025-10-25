<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Translation Service
    |--------------------------------------------------------------------------
    |
    | This option defines the default translation service that will be used
    | by the package. Supported: "libre", "lingva", "mymemory", "argos", "google"
    |
    */
    'default_service' => env('TRANSLATE_DEFAULT_SERVICE', 'libre'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Chain
    |--------------------------------------------------------------------------
    |
    | Define the order of services to try if the default fails.
    | The package will automatically fallback through this chain.
    |
    */
    'fallback_chain' => ['libre', 'lingva', 'mymemory', 'google'],

    /*
    |--------------------------------------------------------------------------
    | Default Source Language
    |--------------------------------------------------------------------------
    |
    | The default source language for translations. Set to 'auto' for
    | automatic language detection.
    |
    */
    'source_lang' => env('TRANSLATE_SOURCE_LANG', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Default Target Language
    |--------------------------------------------------------------------------
    |
    | The default target language for translations.
    |
    */
    'target_lang' => env('TRANSLATE_TARGET_LANG', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Language
    |--------------------------------------------------------------------------
    |
    | Language to use when translation fails.
    |
    */
    'fallback_lang' => env('TRANSLATE_FALLBACK_LANG', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for translations.
    | Supported drivers: "redis", "file", "database", "array"
    |
    */
    'cache' => [
        'enabled' => env('TRANSLATE_CACHE_ENABLED', true),
        'driver' => env('TRANSLATE_CACHE_DRIVER', 'file'),
        'ttl' => env('TRANSLATE_CACHE_TTL', 86400), // 24 hours in seconds
        'prefix' => env('TRANSLATE_CACHE_PREFIX', 'translate'),
        'auto_invalidate' => true, // Auto invalidate when locale files change
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configure each translation service endpoint and options.
    |
    */
    'services' => [
        'libre' => [
            'enabled' => env('TRANSLATE_LIBRE_ENABLED', true),
            'endpoint' => env('TRANSLATE_LIBRE_ENDPOINT', 'https://libretranslate.com'),
            'api_key' => env('TRANSLATE_LIBRE_API_KEY', null), // Optional
            'timeout' => 10,
        ],

        'lingva' => [
            'enabled' => env('TRANSLATE_LINGVA_ENABLED', true),
            'endpoint' => env('TRANSLATE_LINGVA_ENDPOINT', 'https://lingva.ml'),
            'timeout' => 10,
        ],

        'mymemory' => [
            'enabled' => env('TRANSLATE_MYMEMORY_ENABLED', true),
            'endpoint' => env('TRANSLATE_MYMEMORY_ENDPOINT', 'https://api.mymemory.translated.net'),
            'email' => env('TRANSLATE_MYMEMORY_EMAIL', null), // Optional for higher limits
            'timeout' => 10,
        ],

        'google' => [
            'enabled' => env('TRANSLATE_GOOGLE_ENABLED', false),
            'endpoint' => env('TRANSLATE_GOOGLE_ENDPOINT', 'https://translate.googleapis.com'),
            'timeout' => 10,
        ],

        'argos' => [
            'enabled' => env('TRANSLATE_ARGOS_ENABLED', false),
            'endpoint' => env('TRANSLATE_ARGOS_ENDPOINT', 'http://localhost:5000'),
            'timeout' => 30,
            'offline_mode' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for batch translations.
    |
    */
    'queue' => [
        'enabled' => env('TRANSLATE_QUEUE_ENABLED', false),
        'connection' => env('TRANSLATE_QUEUE_CONNECTION', 'default'),
        'queue' => env('TRANSLATE_QUEUE_NAME', 'translations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Enhancement (Optional)
    |--------------------------------------------------------------------------
    |
    | Enable AI-powered translation improvements using free models.
    | Supports: "ollama", "huggingface", "local"
    |
    */
    'ai' => [
        'enabled' => env('TRANSLATE_AI_ENABLED', false),
        'provider' => env('TRANSLATE_AI_PROVIDER', 'ollama'),
        'model' => env('TRANSLATE_AI_MODEL', 'llama2'),
        'endpoint' => env('TRANSLATE_AI_ENDPOINT', 'http://localhost:11434'),
        'enhance_context' => true,
        'fix_grammar' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Enable analytics tracking for translation operations.
    |
    */
    'analytics' => [
        'enabled' => env('TRANSLATE_ANALYTICS_ENABLED', true),
        'track_cache_hits' => true,
        'track_api_latency' => true,
        'log_translations' => env('TRANSLATE_LOG_TRANSLATIONS', false),
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure auto-locale detection middleware.
    |
    */
    'middleware' => [
        'detect_from_browser' => true,
        'detect_from_user' => true,
        'detect_from_query' => true,
        'detect_from_geoip' => false,
        'query_param' => 'lang',
        'session_key' => 'locale',
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Translation
    |--------------------------------------------------------------------------
    |
    | Configure batch translation settings.
    |
    */
    'batch' => [
        'chunk_size' => 50,
        'delay_between_requests' => 100, // milliseconds
        'max_concurrent' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Detection
    |--------------------------------------------------------------------------
    |
    | Configure automatic language detection.
    |
    */
    'detection' => [
        'enabled' => true,
        'cache_detections' => true,
        'fallback_to_source' => true,
    ],
];
