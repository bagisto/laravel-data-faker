<?php

namespace Webkul\DataFaker\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class DataFakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEloquentFactoriesFrom(__DIR__.'/../Database/factories');
    }

    /**
     * Register factories.
     *
     * @param  string  $path
     * @return void
     */
    protected function registerEloquentFactoriesFrom($path = __DIR__ . 'Webkul\DataFaker')
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }
}