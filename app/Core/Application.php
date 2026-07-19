<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    private Container $container;
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', $this->basePath);
        }

        Env::load($this->basePath);

        $this->container = new Container();
        $this->router = new Router();

        $this->registerServices();
        $this->registerRoutes();
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $this->sendCorsHeaders();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        /** @var ErrorHandler $errorHandler */
        $errorHandler = $this->container->get(ErrorHandler::class);
        $errorHandler->register();

        $request = Request::capture();
        $response = $this->router->dispatch($request, $this->container);
        $response->send();
    }

    private function registerServices(): void
    {
        $this->container->singleton(Config::class, fn (): Config => new Config($this->basePath . '/config'));
        $this->container->singleton(Logger::class, fn (Container $c): Logger => new Logger($c->get(Config::class)));
        $this->container->singleton(Database::class, fn (Container $c): Database => new Database($c->get(Config::class)));
        $this->container->singleton(ErrorHandler::class, fn (Container $c): ErrorHandler => new ErrorHandler(
            $c->get(Config::class),
            $c->get(Logger::class),
        ));
        $this->container->singleton(Router::class, fn (): Router => $this->router);

        $this->container->singleton(\App\Services\AuthService::class, fn (Container $c): \App\Services\AuthService => new \App\Services\AuthService(
            $c->get(Config::class),
            $c->get(Database::class),
            $c->get(Logger::class),
        ));

        $this->container->singleton(\App\Models\User::class, fn (Container $c): \App\Models\User => new \App\Models\User(
            $c->get(Database::class),
        ));
    }

    private function registerRoutes(): void
    {
        $router = $this->router;

        require $this->basePath . '/routes/web.php';
        require $this->basePath . '/routes/api.php';
    }

    private function sendCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
}
