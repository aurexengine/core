<?php

namespace AurexEngine\Support;

class ConfigRepository
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $seg) {
            if (!is_array($value) || !array_key_exists($seg, $value)) {
                return $default;
            }
            $value = $value[$seg];
        }

        return $value;
    }

    public function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $ref =& $this->items;

        foreach ($segments as $seg) {
            if (!isset($ref[$seg]) || !is_array($ref[$seg])) {
                $ref[$seg] = [];
            }
            $ref =& $ref[$seg];
        }

        $ref = $value;
    }
}