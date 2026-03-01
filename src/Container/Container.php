<?php

namespace AurexEngine\Container;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use AurexEngine\Container\Exceptions\BindingResolutionException;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $concrete ??= $abstract;

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => $shared,
        ];
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        [$concrete, $shared] = $this->getConcreteAndShared($abstract);

        $object = $this->build($concrete, $parameters);

        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    protected function getConcreteAndShared(string $abstract): array
    {
        if (!isset($this->bindings[$abstract])) {
            return [$abstract, false];
        }

        $binding = $this->bindings[$abstract];
        return [$binding['concrete'], $binding['shared']];
    }

    protected function build($concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (is_object($concrete)) {
            return $concrete;
        }

        if (!is_string($concrete)) {
            throw new BindingResolutionException("Invalid binding concrete type.");
        }

        if (!class_exists($concrete)) {
            throw new BindingResolutionException("Class [$concrete] does not exist.");
        }

        $ref = new ReflectionClass($concrete);

        if (!$ref->isInstantiable()) {
            throw new BindingResolutionException("Class [$concrete] is not instantiable.");
        }

        $ctor = $ref->getConstructor();
        if (!$ctor) {
            return new $concrete();
        }

        $deps = [];
        foreach ($ctor->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $parameters)) {
                $deps[] = $parameters[$name];
                continue;
            }

            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $deps[] = $this->make($type->getName());
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $deps[] = $param->getDefaultValue();
                continue;
            }

            throw new BindingResolutionException("Unresolvable dependency [$name] in [$concrete].");
        }

        return $ref->newInstanceArgs($deps);
    }
}