<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array<string, mixed>> */
    private array $routes = [];

    /** @var array<int, string> */
    private array $groupMiddleware = [];

    /** @var array<int, string> */
    private array $groupPrefix = [];

    public function get(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $handler, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousMiddleware = $this->groupMiddleware;
        $previousPrefix = $this->groupPrefix;

        $this->groupMiddleware = array_merge(
            $this->groupMiddleware,
            $attributes['middleware'] ?? []
        );

        if (isset($attributes['prefix'])) {
            $this->groupPrefix[] = rtrim((string) $attributes['prefix'], '/');
        }

        $callback($this);

        $this->groupMiddleware = $previousMiddleware;
        $this->groupPrefix = array_slice($this->groupPrefix, 0, -1);
    }

    public function dispatch(Request $request, Container $container): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $params = $this->match($route['pattern'], $request->path());

            if ($params === null) {
                continue;
            }

            $handler = $route['handler'];
            $middleware = $route['middleware'];

            $next = function (Request $currentRequest) use ($handler, $params, $container): Response {
                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = $container->get($class);

                    return $container->call([$controller, $method], array_merge(['request' => $currentRequest], $params));
                }

                if (is_string($handler) && str_contains($handler, '@')) {
                    return $container->call($handler, array_merge(['request' => $currentRequest], $params));
                }

                return $container->call($handler, array_merge(['request' => $currentRequest], $params));
            };

            foreach (array_reverse($middleware) as $middlewareClass) {
                $previous = $next;
                $next = function (Request $currentRequest) use ($middlewareClass, $container, $previous): Response {
                    $instance = $container->get($middlewareClass);

                    return $instance->handle($currentRequest, $previous);
                };
            }

            return $next($request);
        }

        return Response::json([
            'success' => false,
            'message' => 'Route not found.',
        ], 404);
    }

    private function add(string $method, string $path, callable|array|string $handler, array $middleware = []): self
    {
        $prefix = implode('', $this->groupPrefix);
        $fullPath = rtrim($prefix . '/' . ltrim($path, '/'), '/') ?: '/';

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $this->compilePattern($fullPath),
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];

        return $this;
    }

    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);

        return '#^' . $pattern . '$#';
    }

    /** @return array<string, string>|null */
    private function match(string $pattern, string $path): ?array
    {
        if (preg_match($pattern, $path, $matches) !== 1) {
            return null;
        }

        $params = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
