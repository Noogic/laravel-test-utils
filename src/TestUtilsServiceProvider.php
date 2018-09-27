<?php

namespace Noogic\TestUtils;

use Illuminate\Support\ServiceProvider;

class TestUtilsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/test-utils.php' => config_path('test-utils.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/test-utils.php', 'test-utils'
        );
    }
}
