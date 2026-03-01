<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;

class LoadEnvironment
{
    public function bootstrap(Application $app): void
    {
        $path = $app->basePath('.env');

        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) return;

        foreach ($lines as $line) {
            $line = trim($line);

            // skip comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // KEY=VALUE
            $pos = strpos($line, '=');
            if ($pos === false) continue;

            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));

            // strip quotes
            if ($val !== '' && (
                ($val[0] === '"' && str_ends_with($val, '"')) ||
                ($val[0] === "'" && str_ends_with($val, "'"))
            )) {
                $val = substr($val, 1, -1);
            }

            // do not override existing env
            if (getenv($key) !== false || array_key_exists($key, $_ENV)) {
                continue;
            }

            $_ENV[$key] = $val;
            putenv($key . '=' . $val);
        }
    }
}