<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        $normalizedPath = $this->normalizePath($path);
        [$pattern, $parameterNames] = $this->compilePattern($normalizedPath);

        $this->routes[$method][] = [
            'path' => $normalizedPath,
            'pattern' => $pattern,
            'parameter_names' => $parameterNames,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $basePath = app_base_path();

        // Remove o caminho base quando a aplicação está dentro de uma subpasta.
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = $this->normalizePath($path);
        $route = $this->matchRoute($method, $path);

        if ($route === null) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');
            return;
        }

        foreach ($route['middleware'] as $middlewareClass) {
            $middleware = new $middlewareClass();

            if ($middleware->handle() === false) {
                return;
            }
        }

        [$controllerClass, $action] = $route['handler'];
        $controller = new $controllerClass();
        $controller->{$action}(...$route['parameters']);
    }

    private function matchRoute(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $parameters = [];

            foreach ($route['parameter_names'] as $name) {
                $parameters[] = rawurldecode((string) ($matches[$name] ?? ''));
            }

            $route['parameters'] = $parameters;
            return $route;
        }

        return null;
    }

    private function compilePattern(string $path): array
    {
        if ($path === '/') {
            return ['#^/$#', []];
        }

        $parameterNames = [];
        $segments = explode('/', trim($path, '/'));
        $compiledSegments = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $matches)) {
                $parameterNames[] = $matches[1];
                $compiledSegments[] = '(?P<' . $matches[1] . '>[^/]+)';
                continue;
            }

            $compiledSegments[] = preg_quote($segment, '#');
        }

        return ['#^/' . implode('/', $compiledSegments) . '$#', $parameterNames];
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        return $path !== '/' ? rtrim($path, '/') : '/';
    }
}
