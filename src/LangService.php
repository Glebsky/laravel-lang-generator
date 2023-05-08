<?php

namespace Glebsky\LaravelLangGenerator;

use Illuminate\Console\Command;

class LangService extends Command
{
    public $isSync = false;
    public $isNew = false;

    public $viewsFilesCount = 0;
    public $viewsKeysCount = 0;
    public $appFilesCount = 0;
    public $appKeysCount = 0;
    public $customFilesCount = 0;
    public $customKeysCount = 0;

    public $path;
    public $files = [];
    public $translationsKeys = [];

    public $fileType = 'array';
    public $fileName = 'lang';
    public $languages = ['en'];

    public $output;

    /**
     * Parse main directories for the availability of translations.
     *
     * @return void
     */
    public function parseProject()
    {
        $this->line('Start searching for language files...');

        //Parse custom path
        if ($this->path !== null) {
            $this->info('Parsing custom path...');
            $this->line('Path: '.base_path($this->path));

            if (!is_dir(base_path($this->path))) {
                $this->error('Can\'t find the specified directory. Please check --path parameter');
                exit();
            }

            $this->parseDirectory(base_path($this->path));

            $bar = $this->output->createProgressBar(count($this->files));
            $bar->start();
            foreach ($this->files as $file) {
                $this->parseFile($file);
                $bar->advance();
            }
            $bar->finish();

            $this->newLine(1);
            $this->line('Custom path parse finished. Found '.$this->customKeysCount.' keys in '.$this->customFilesCount.' files');
            unset($this->files);

            $this->newLine(1);
            $this->line('Total keys found: '.count($this->translationsKeys));

            if (empty($this->translationsKeys)) {
                $this->error('Nothing to generate.');
                exit();
            }

            $this->newLine(1);
            $this->info('Generating translations...');

            $this->generateLangsFiles($this->translationsKeys);

            $this->newLine(1);
            $this->info('Translation files generated.');
            exit();
        }

        //VIEWS FOLDER
        $this->info('Parsing views folder...');
        $this->parseDirectory(resource_path('views'));

        $bar = $this->output->createProgressBar(count($this->files));
        $bar->start();
        foreach ($this->files as $file) {
            $this->parseFile($file);
            $bar->advance();
        }
        $bar->finish();

        $this->newLine(1);
        $this->line('Views parse finished. Found '.$this->viewsKeysCount.' keys in '.$this->viewsFilesCount.' files');
        unset($this->files);

        //APP FOLDER
        $this->newLine(1);
        $this->info('Parsing app folder...');
        $this->parseDirectory(app_path());

        $bar = $this->output->createProgressBar(count($this->files));
        $bar->start();
        foreach ($this->files as $file) {
            $this->parseFile($file);
            $bar->advance();
        }
        $bar->finish();

        $this->newLine(1);
        $this->line('App parse finished. Found '.$this->appKeysCount.' keys in '.$this->appFilesCount.' files');

        $this->newLine(1);
        $this->line('Total keys found: '.count($this->translationsKeys));

        $this->newLine(1);
        $this->info('Generating translations...');

        $this->generateLangsFiles($this->translationsKeys);

        $this->newLine(1);
        $this->info('Translation files generated.');
    }

    /**
     * Parse single folder for the availability of translations files.
     *
     * @param string $directory
     *
     * @return void
     */
    public function parseDirectory(string $directory)
    {
        $handle = opendir($directory);
        while (false !== ($entry = readdir($handle))) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory.'/'.$entry;
            if (is_dir($path)) {
                $this->parseDirectory($path);
                continue;
            }

            if (is_file($path)) {
                $this->files[] = $path;
            }
        }
        closedir($handle);
    }

    /**
     * Parse translation file for translation keys.
     *
     * @param string $path
     *
     * @return void
     */
    public function parseFile(string $path)
    {
        $fileData = file_get_contents($path);

        $re = '/@lang\(\'(.+?)\'\)|trans\(\'(.+?)\'\)|__\(\'(.+?)\'\)/m';
        preg_match_all($re, $fileData, $matches, PREG_SET_ORDER, 0);

        $data = [];
        foreach ($matches as $match) {
            if (isset($match[3]) && !is_null($match[3])) {
                $key = str_replace("'",'',$match[3]);
                $data[$key] = '';
            } elseif (isset($match[2]) && !is_null($match[2])) {
                $key = str_replace("'",'',$match[2]);
                $data[$key] = '';
            } elseif (isset($match[1]) && !is_null($match[1])) {
                $key = str_replace("'",'',$match[1]);
                $data[$key] = '';
            }
        }

        if (str_contains($path, resource_path('views'))) {
            $this->viewsFilesCount++;
            $this->viewsKeysCount += count($data);
        } elseif (str_contains($path, app_path())) {
            $this->appFilesCount++;
            $this->appKeysCount += count($data);
        } elseif (str_contains($path, $this->path)) {
            $this->customFilesCount++;
            $this->customKeysCount += count($data);
        }

        $this->translationsKeys = array_merge($data, $this->translationsKeys);
    }

    /**
     * Generate new language files in resource/lang folder.
     *
     * @param array $dataArr All founded language keys in single file
     *
     * @return void
     */
    public function generateLangsFiles(array $dataArr)
    {
        if ($this->fileType === 'json') {
            foreach ($this->languages as $language) {
                if ($this->isNew === false) {
                    $dataArr = $this->updateValues(base_path('lang/'.$language.'.json'), $dataArr);
                }

                if ($this->isSync === true) {
                    $dataArr = $this->syncValues($this->translationsKeys, $dataArr);
                }

                file_put_contents(base_path('lang/'.$language.'.json'), json_encode($dataArr, JSON_PRETTY_PRINT));
            }
        } elseif ($this->fileType === 'array') {
            $res = [];
            $bar = $this->output->createProgressBar(count($dataArr));
            $bar->start();
            foreach ($dataArr as $key => $value) {
                if (str_contains($key, '.') && !str_contains($key, ' ')) {
                    data_fill($res, $key, $value);
                } else {
                    $res[$key] = '';
                }
                $bar->advance();
            }
            $bar->finish();

            $this->fillKeys($this->fileName, $res);
        }
    }

    /**
     * Assign existing translation keys values to new.
     *
     * @param string $path
     * @param array  $dataArr
     *
     * @return array|void
     */
    private function updateValues(string $path, array $dataArr)
    {
        if ($this->fileType === 'json') {
            if (file_exists($path)) {
                $existingArr = json_decode(file_get_contents($path), true);

                foreach ($existingArr as $key => $value) {
                    $dataArr[$key] = $value;
                }

                return $dataArr;
            }

            foreach ($dataArr as $key => $value) {
                $dataArr[$key] = $key;
            }

            return $dataArr;
        } elseif ($this->fileType === 'array') {
            if (file_exists($path)) {
                $existingArr = include $path;

                if (is_array($existingArr)) {
                    foreach ($existingArr as $key => $value) {
                        if (is_array($value) && isset($dataArr[$key]) && is_array($dataArr[$key])) {
                            $dataArr[$key] = $this->arrayUpdater($dataArr[$key], $value);
                        } else {
                            $dataArr[$key] = $value;
                        }
                    }
                }
            }

            return $dataArr;
        }
    }

    /**
     * Progressive merge two arrays into one.
     *
     * @param array $dataArr
     * @param array $existingArr
     *
     * @return array
     */
    private function arrayUpdater(array $dataArr, array $existingArr)
    {
        foreach ($existingArr as $key => $value) {
            if (is_array($value)) {
                if (isset($dataArr[$key])) {
                    $dataArr[$key] = $this->arrayUpdater($dataArr[$key], $value);
                } else {
                    $dataArr[$key] = $value;
                }
                continue;
            }
            $dataArr[$key] = $value;
        }

        return $dataArr;
    }

    /**
     * Delete unused translation keys.
     *
     * @param array $parsedArr
     * @param array $dataArr
     *
     * @return array
     */
    private function syncValues(array $parsedArr, array $dataArr)
    {
        foreach ($parsedArr as $key => $value) {
            if (str_contains($key, '.') && !str_contains($key, ' ')) {
                data_fill($parsedArr, $key, $value);
            } else {
                $parsedArr[$key] = $value;
            }
        }

        foreach ($dataArr as $key => $value) {
            if (!isset($parsedArr[$key])) {
                unset($dataArr[$key]);
                continue;
            }

            if (is_array($value)) {
                if (is_array($parsedArr[$key])) {
                    $dataArr[$key] = $this->syncValues($parsedArr[$key], $value);
                } else {
                    $dataArr[$key] = $key;
                }
            }
        }

        return $dataArr;
    }

    /**
     * Fill Array language file.
     *
     * @param $fileName
     * @param array $keys
     *
     * @return void
     */
    private function fillKeys($fileName, array $keys)
    {
        foreach ($this->languages as $language) {
            if (!file_exists(base_path('lang'."/{$language}"))) {
                if (!mkdir(base_path('lang'."/{$language}"), 0777, true) && !is_dir(base_path('lang'."/{$language}"))) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', 'path/to/directory'));
                }
            }
            $filePath = base_path('lang'."/{$language}/{$fileName}.php");

            if ($this->isNew === false) {
                $keys = $this->updateValues($filePath, $keys);
            }

            if ($this->isSync === true) {
                $keys = $this->syncValues($this->translationsKeys, $keys);
            }

            file_put_contents($filePath, "<?php\nreturn [];",LOCK_EX);
            $fileContent = $keys;

            $this->writeFile($filePath, $fileContent);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Array Translation methods
    |--------------------------------------------------------------------------
    */
    /**
     * Write translation keys to a .php arrays.
     *
     * @param $filePath
     * @param array $translations
     *
     * @return void
     */
    private function writeFile($filePath, array $translations)
    {
        $content = "<?php \n\nreturn [";

        $content .= $this->stringLineMaker($translations);

        $content .= "\n];";

        file_put_contents($filePath, $content,LOCK_EX);
    }

    /**
     * Generate a string line for a array translation file.
     *
     * @param $array
     * @param $prepend
     *
     * @return string
     */
    private function stringLineMaker($array, $prepend = '')
    {
        $output = '';

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->stringLineMaker($value, $prepend.'    ');

                $output .= "\n{$prepend}    '{$key}' => [{$value}\n{$prepend}    ],";
            } else {
                $value = str_replace('\"', '"', addslashes($value));

                $output .= "\n{$prepend}    '{$key}' => '{$value}',";
            }
        }

        return $output;
    }
}
