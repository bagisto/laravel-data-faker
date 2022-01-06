<?php

namespace Webkul\DataFaker\Providers;

use Illuminate\Support\ServiceProvider;

class DataFakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../Database/Factories' => database_path('factories'),
        ]);

        $this->commands([
            \Webkul\DataFaker\Commands\Console\SeedData::class
        ]);
    }
}
