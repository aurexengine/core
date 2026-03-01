<?php

namespace Tests\Unit;

use Tests\TestCase;
use AurexEngine\Foundation\Application;
use AurexEngine\Foundation\Bootstrap\LoadEnvironment;
use AurexEngine\Foundation\Bootstrap\LoadConfiguration;

class BootstrapTest extends TestCase
{
    public function test_env_and_config_load(): void
    {
        $base = sys_get_temp_dir() . '/aurex_core_' . uniqid();
        mkdir($base . '/config', 0777, true);

        file_put_contents($base . '/.env', "APP_NAME=MyApp\n");
        file_put_contents($base . '/config/app.php', <<<PHP
        <?php
            return [
                'name' => env('APP_NAME', 'Default'),
            ];
        PHP);

        $app = new Application($base);
        $app->bootstrapWith([LoadEnvironment::class, LoadConfiguration::class]);

        $config = $app->make('config');
        $this->assertSame('MyApp', $config->get('app.name'));

        // cleanup (optional)
        @unlink($base . '/.env');
        @unlink($base . '/config/app.php');
        @rmdir($base . '/config');
        @rmdir($base);
    }
}