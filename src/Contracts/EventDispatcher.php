<?php

namespace AurexEngine\Contracts;

interface EventDispatcher
{
    public function listen(string $event, callable $listener): void;

    /** @return array<int, mixed> listener results */
    public function dispatch(object|string $event, mixed $payload = null): array;
}