<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;

interface Bootstrapper
{
    public function bootstrap(Application $app): void;
}