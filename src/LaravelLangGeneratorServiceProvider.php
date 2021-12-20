<?php

namespace Glebsky\LaravelLangGenerator;

use Glebsky\LaravelLangGenerator\Commands\LangGeneratorCommand;
use Illuminate\Support\ServiceProvider;

class LaravelLangGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lang-generator.php', 'lang-generator');

        if ($this->app->runningInConsole()) {
            $this->commands([
                LangGeneratorCommand::class,
            ]);
        }
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/lang-generator.php' => config_path('lang-generator.php'),
        ], 'config');
    }
}
