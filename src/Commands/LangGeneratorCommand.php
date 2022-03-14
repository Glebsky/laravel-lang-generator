<?php

namespace Glebsky\LaravelLangGenerator\Commands;

use Glebsky\LaravelLangGenerator\LangService;
use Illuminate\Console\Command;

class LangGeneratorCommand extends Command
{
    protected $signature = 'lang:generate {--T|type=} {--N|name=} {--L|langs=*} {--S|sync} {--C|clear} {--P|path=}';

    protected $description = 'Searches for multilingual phrases in Laravel project and automatically generates language files for you.';

    protected $manager;

    public function __construct(LangService $manager)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->manager->fileType = config('lang-generator.file_type');
        $this->manager->fileName = config('lang-generator.file_name');
        $this->manager->languages = config('lang-generator.languages');
    }

    public function handle()
    {
        $this->manager->output = $this->output;

        //Get user input configs
        $this->manager->isSync = $this->option('sync');
        $this->manager->isNew = $this->option('clear');

        $this->manager->fileType = $this->option('type') ?: $this->manager->fileType;
        $this->manager->fileName = $this->option('name') ?: $this->manager->fileName;
        $this->manager->languages = $this->option('langs') ?: $this->manager->languages;
        $this->manager->path = $this->option('path');

        if ($this->manager->isNew) {
            if ($this->confirm('You really want to generate new language files? This will clear all existing files!')) {
                $this->manager->parseProject();
            } else {
                $this->error('Generating translations files aborted.');
            }

            return;
        }

        $this->manager->parseProject();
    }
}
