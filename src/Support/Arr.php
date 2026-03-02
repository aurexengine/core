<?php

namespace AurexEngine\Support;

class Arr
{
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if ($key === '') return $array;

        $segments = explode('.', $key);
        $value = $array;

        foreach ($segments as $seg) {
            if (!is_array($value) || !array_key_exists($seg, $value)) {
                return $default;
            }
            $value = $value[$seg];
        }

        return $value;
    }
}