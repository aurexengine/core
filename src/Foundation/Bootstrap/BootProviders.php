<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;

class BootProviders implements Bootstrapper
{
    public function bootstrap(Application $app): void
    {
        $app->boot();
    }
}