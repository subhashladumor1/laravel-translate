<?php

namespace Subhashladumor1\Translate\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Subhashladumor1\Translate\TranslateServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Additional setup can go here
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TranslateServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Translate' => \Subhashladumor1\Translate\Facades\Translate::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default configuration
        $app['config']->set('translate.default_service', 'libre');
        $app['config']->set('translate.cache.enabled', true);
        $app['config']->set('translate.cache.driver', 'array');
    }
}
