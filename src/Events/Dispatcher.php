<?php

namespace AurexEngine\Events;

use AurexEngine\Contracts\EventDispatcher;

class Dispatcher implements EventDispatcher
{
    /** @var array<string, list<callable>> */
    protected array $listeners = [];

    public function listen(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(object|string $event, mixed $payload = null): array
    {
        $name = is_object($event) ? $event::class : $event;
        $results = [];

        foreach ($this->listeners[$name] ?? [] as $listener) {
            $results[] = $listener($event, $payload);
        }

        return $results;
    }
}