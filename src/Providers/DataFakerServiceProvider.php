<?php

namespace Webkul\DataFaker\Providers;

use Illuminate\Support\ServiceProvider;
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
        $this->registerEloquentFactoriesFrom(__DIR__ . '/../Database/Factories');

        $this->commands([
            \Webkul\DataFaker\Commands\Console\SeedData::class
        ]);
    }

     /**
     * Register factories.
     *
     * @param  string  $path
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function registerEloquentFactoriesFrom($path): void
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }
}
