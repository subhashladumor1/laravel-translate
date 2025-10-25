<?php

namespace Subhashladumor1\Translate;

use Illuminate\Support\ServiceProvider;
use Subhashladumor1\Translate\Commands\TranslateStringCommand;
use Subhashladumor1\Translate\Commands\TranslateFileCommand;
use Subhashladumor1\Translate\Commands\TranslateSyncCommand;
use Subhashladumor1\Translate\Commands\TranslateClearCacheCommand;
use Subhashladumor1\Translate\Http\Middleware\DetectLocale;
use Subhashladumor1\Translate\Services\TranslatorManager;
use Illuminate\Support\Facades\Blade;

class TranslateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/config/translate.php',
            'translate'
        );

        // Register the main service
        $this->app->singleton('translator', function ($app) {
            return new TranslatorManager($app);
        });

        // Register facade alias
        $this->app->alias('translator', TranslatorManager::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/translate.php' => config_path('translate.php'),
        ], 'translate-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/translate'),
        ], 'translate-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'translate');

        // Load routes for dashboard
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslateStringCommand::class,
                TranslateFileCommand::class,
                TranslateSyncCommand::class,
                TranslateClearCacheCommand::class,
            ]);
        }

        // Register middleware
        $this->app['router']->aliasMiddleware('detect.locale', DetectLocale::class);

        // Register Blade directives
        $this->registerBladeDirectives();

        // Load helpers
        require_once __DIR__ . '/Helpers/helpers.php';
    }

    /**
     * Register Blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        // @translate directive
        Blade::directive('translate', function ($expression) {
            return "<?php echo app('translator')->translate({$expression}); ?>";
        });

        // @translateStart and @translateEnd for block translation
        Blade::directive('translateStart', function ($expression) {
            return "<?php ob_start(); ?>";
        });

        Blade::directive('translateEnd', function ($expression) {
            return "<?php echo app('translator')->translate(ob_get_clean(), {$expression}); ?>";
        });

        // @detectLang directive
        Blade::directive('detectLang', function ($expression) {
            return "<?php echo app('translator')->detectLanguage({$expression}); ?>";
        });
    }
}
