<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;

class RegisterProviders implements Bootstrapper
{
    public function bootstrap(Application $app): void
    {
        $providers = $app->config('app.providers', []);
        if (is_array($providers)) {
            $app->addConfiguredProviders($providers);
        }

        $app->registerConfiguredProviders();
    }
}