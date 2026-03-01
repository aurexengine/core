<?php

namespace AurexEngine\Foundation;

use AurexEngine\Container\Container;

class Application extends Container
{
    protected string $basePath;
    protected array $serviceProviders = [];
    protected array $bootedProviders = [];
    protected bool $booted = false;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

        $this->instance(static::class, $this);
        $this->instance(Container::class, $this);
        $this->instance('app', $this);

        $this->instance('path.base', $this->basePath);
    }

    public function basePath(string $path = ''): string
    {
        return $path ? $this->basePath . DIRECTORY_SEPARATOR . $path : $this->basePath;
    }

    public function register(ServiceProvider $provider): void
    {
        $provider->register();
        $this->serviceProviders[] = $provider;

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    public function boot(): void
    {
        if ($this->booted) return;

        foreach ($this->serviceProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    protected function bootProvider(ServiceProvider $provider): void
    {
        $class = $provider::class;
        if (isset($this->bootedProviders[$class])) return;

        $provider->boot();
        $this->bootedProviders[$class] = true;
    }

    public function bootstrapWith(array $bootstrappers): void
    {
        foreach ($bootstrappers as $bootstrapper) {
            (new $bootstrapper)->bootstrap($this);
        }
    }
}
