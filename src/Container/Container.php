<?php

namespace AurexEngine\Container;

use AurexEngine\Contracts\Container as ContainerContract;
use Closure;
use ReflectionClass;
use ReflectionNamedType;

class Container implements ContainerContract
{
    /** @var array<string, array{concrete:mixed, shared:bool}> */
    protected array $bindings = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    /** @var array<string, string> alias => abstract */
    protected array $aliases = [];

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $abstract = $this->normalize($abstract);

        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => $shared];

        // If rebinding, drop old instance
        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $abstract = $this->normalize($abstract);
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function has(string $id): bool
    {
        $id = $this->normalize($id);

        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->normalize($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $object = $this->build($abstract, $parameters);

        if (($this->bindings[$abstract]['shared'] ?? false) === true) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    protected function build(string $abstract, array $parameters = []): mixed
    {
        $concrete = $this->getConcrete($abstract);

        // closure factory
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        // direct object instance
        if (is_object($concrete)) {
            return $concrete;
        }

        // class-string
        $class = is_string($concrete) ? $concrete : $abstract;

        if (!class_exists($class)) {
            throw new NotFoundException("Target class [$class] does not exist.");
        }

        $ref = new ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new BindingResolutionException("Target class [$class] is not instantiable.");
        }

        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            return new $class();
        }

        $deps = [];

        foreach ($ctor->getParameters() as $param) {
            $name = $param->getName();

            // explicit override by name
            if (array_key_exists($name, $parameters)) {
                $deps[] = $parameters[$name];
                continue;
            }

            // class dependency
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $deps[] = $this->make($type->getName());
                continue;
            }

            // default value
            if ($param->isDefaultValueAvailable()) {
                $deps[] = $param->getDefaultValue();
                continue;
            }

            throw new BindingResolutionException(
                "Unresolvable dependency resolving [$class]: parameter \$$name"
            );
        }

        return $ref->newInstanceArgs($deps);
    }

    protected function getConcrete(string $abstract): mixed
    {
        return $this->bindings[$abstract]['concrete'] ?? $abstract;
    }

    protected function normalize(string $id): string
    {
        // resolve alias chains safely
        $seen = [];
        while (isset($this->aliases[$id])) {
            if (isset($seen[$id])) {
                throw new BindingResolutionException("Circular alias detected for [$id].");
            }
            $seen[$id] = true;
            $id = $this->aliases[$id];
        }

        return $id;
    }
}