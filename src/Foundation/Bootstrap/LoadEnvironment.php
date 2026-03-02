<?php

namespace AurexEngine\Foundation\Bootstrap;

use AurexEngine\Foundation\Application;

class LoadEnvironment implements Bootstrapper
{
    public function bootstrap(Application $app): void
    {
        $path = $app->envPath('.env');
        if (!is_file($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) return;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) continue;

            $pos = strpos($line, '=');
            if ($pos === false) continue;

            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));

            // strip surrounding quotes
            if ($val !== '' && (
                ($val[0] === '"' && str_ends_with($val, '"')) ||
                ($val[0] === "'" && str_ends_with($val, "'"))
            )) {
                $val = substr($val, 1, -1);
            }

            // do not override already existing env
            if (getenv($key) === false) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}