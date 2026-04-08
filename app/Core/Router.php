<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): bool
    {
        return $this->register('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): bool
    {
        return $this->register('POST', $path, $handler);
    }

    private function normalize(string $path): string
    {
        $clean = '/' . trim($path, '/');
        return $clean === '/' ? $clean : rtrim($clean, '/');
    }

    public function has(string $method, string $path): bool
    {
        return isset($this->routes[strtoupper($method)][$this->normalize($path)]);
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = $this->normalize($uri);
        $route = $this->routes[$method][$uri] ?? $this->matchDynamicRoute($method, $uri);
        $handler = $route['handler'] ?? null;

        if (! $handler) {
            http_response_code(404);
            View::render('public/404', [
                'pageTitle' => 'Page Not Found',
                'metaDescription' => 'The requested page could not be found.',
            ], 'layouts/main');
            return;
        }

        if (is_array($handler)) {
            [$class, $action] = $handler;
            (new $class())->{$action}();
            return;
        }

        $handler();
    }

    private function register(string $method, string $path, callable|array $handler): bool
    {
        $method = strtoupper($method);
        $normalized = $this->normalize($path);

        if (isset($this->routes[$method][$normalized])) {
            return false;
        }

        $this->routes[$method][$normalized] = [
            'handler' => $handler,
            'regex' => $this->compilePattern($normalized),
        ];

        return true;
    }

    private function compilePattern(string $path): ?string
    {
        if (! preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $path, $matches)) {
            return null;
        }

        $pattern = preg_quote($path, '#');
        foreach ($matches[1] as $name) {
            $pattern = str_replace('\{' . $name . '\}', '(?P<' . $name . '>[^/]+)', $pattern);
        }

        return '#^' . $pattern . '$#';
    }

    private function matchDynamicRoute(string $method, string $uri): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (empty($route['regex']) || ! preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            foreach ($matches as $key => $value) {
                if (! is_string($key)) {
                    continue;
                }

                $_GET[$key] = $_GET[$key] ?? $value;
            }

            return $route;
        }

        return null;
    }
}
