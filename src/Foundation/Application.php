<?php

namespace AurexEngine\Foundation;

use AurexEngine\Container\Container;
use AurexEngine\Contracts\EventDispatcher;
use AurexEngine\Events\Dispatcher;

class Application extends Container
{
    protected string $basePath;

    /** @var array<string, mixed> */
    protected array $config = [];

    /** @var array<int, class-string<ServiceProvider>> */
    protected array $configuredProviders = [];

    /** @var list<ServiceProvider> */
    protected array $loadedProviders = [];

    protected bool $booted = false;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

        // container self-bindings
        $this->instance(self::class, $this);
        $this->alias(self::class, 'app');

        $this->instance(\AurexEngine\Contracts\Container::class, $this);

        // core services
        $this->singleton(EventDispatcher::class, fn () => new Dispatcher());
        $this->alias(EventDispatcher::class, 'events');
    }

    public function basePath(string $path = ''): string
    {
        return $path === ''
            ? $this->basePath
            : $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    public function envPath(string $file = '.env'): string
    {
        return $this->basePath($file);
    }

    public function configPath(string $path = ''): string
    {
        $base = $this->basePath('config');
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }

    public function bootstrapWith(array $bootstrappers): void
    {
        foreach ($bootstrappers as $bootstrapper) {
            (new $bootstrapper())->bootstrap($this);
        }
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $seg) {
            if (!is_array($value) || !array_key_exists($seg, $value)) {
                return $default;
            }
            $value = $value[$seg];
        }

        return $value;
    }

    public function addConfiguredProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            if (is_string($provider) && $provider !== '') {
                $this->configuredProviders[] = $provider;
            }
        }
    }

    public function registerConfiguredProviders(): void
    {
        foreach ($this->configuredProviders as $providerClass) {
            $this->register($providerClass);
        }
    }

    public function register(string $providerClass): ServiceProvider
    {
        /** @var ServiceProvider $provider */
        $provider = new $providerClass($this);

        $provider->register();

        $this->loadedProviders[] = $provider;

        if ($this->booted) {
            $provider->boot();
        }

        return $provider;
    }

    public function boot(): void
    {
        if ($this->booted) return;

        foreach ($this->loadedProviders as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}