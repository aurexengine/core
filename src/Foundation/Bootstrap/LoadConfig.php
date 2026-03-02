<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;

class LoadConfig implements Bootstrapper
{
    public function bootstrap(Application $app): void
    {
        $dir = $app->configPath();
        $config = [];

        if (is_dir($dir)) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
                $name = basename($file, '.php');
                $data = require $file;
                $config[$name] = is_array($data) ? $data : [];
            }
        }

        $app->setConfig($config);
    }
}