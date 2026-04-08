<?php

namespace App\Core;

class Hooks
{
    private static array $actions = [];

    private static array $filters = [];

    public static function addAction(string $event, callable $callback, int $priority = 10): void
    {
        self::$actions[$event][$priority][] = $callback;
    }

    public static function doAction(string $event, mixed $data = null): void
    {
        foreach (self::callbacksFor(self::$actions, $event) as $callback) {
            $callback($data);
        }
    }

    public static function addFilter(string $event, callable $callback, int $priority = 10): void
    {
        self::$filters[$event][$priority][] = $callback;
    }

    public static function applyFilters(string $event, mixed $data = null): mixed
    {
        foreach (self::callbacksFor(self::$filters, $event) as $callback) {
            $data = $callback($data);
        }

        return $data;
    }

    private static function callbacksFor(array $registry, string $event): array
    {
        if (empty($registry[$event])) {
            return [];
        }

        ksort($registry[$event]);

        $callbacks = [];
        foreach ($registry[$event] as $group) {
            foreach ($group as $callback) {
                $callbacks[] = $callback;
            }
        }

        return $callbacks;
    }
}
