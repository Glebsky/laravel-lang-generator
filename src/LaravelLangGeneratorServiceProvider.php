<?php

namespace Glebsky\LaravelLangGenerator;

use Glebsky\LaravelLangGenerator\Console\Commands\LangGeneratorCommand;
use Illuminate\Support\ServiceProvider;

class LaravelLangGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lang-generator.php', 'lang-generator');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LangGeneratorCommand::class,
            ]);
        }
        $this->publishes([
            __DIR__.'/../config/lang-generator.php' => config_path('lang-generator.php'),
        ], 'config');
    }
}
