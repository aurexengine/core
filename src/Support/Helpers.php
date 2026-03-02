<?php

use AurexEngine\Foundation\Application;

if (!function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $app = $GLOBALS['aurex_app'] ?? null;

        if (!$app instanceof Application) {
            throw new RuntimeException('Aurex application is not set. Set $GLOBALS["aurex_app"] = $app;');
        }

        return $abstract ? $app->make($abstract, $parameters) : $app;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        /** @var Application $app */
        $app = app();
        return $app->config($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($val === false || $val === null) {
            return $default;
        }

        if (is_string($val)) {
            $lower = strtolower($val);
            if ($lower === 'true') return true;
            if ($lower === 'false') return false;
            if ($lower === 'null') return null;
        }

        return $val;
    }
}