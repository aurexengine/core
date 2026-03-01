<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;
use AurexEngine\Support\ConfigRepository;

class LoadConfiguration
{
    public function bootstrap(Application $app): void
    {
        $configPath = $app->basePath('config');

        $items = [];

        if (is_dir($configPath)) {
            $files = glob($configPath . DIRECTORY_SEPARATOR . '*.php') ?: [];

            foreach ($files as $file) {
                $key = basename($file, '.php');
                $value = require $file;

                $items[$key] = is_array($value) ? $value : [];
            }
        }

        $repo = new ConfigRepository($items);

        $app->instance('config', $repo);
        $app->instance(ConfigRepository::class, $repo);
    }
}