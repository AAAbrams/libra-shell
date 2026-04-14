<?php

declare(strict_types=1);

namespace Libra\Shell\Bootstrap;

use Dotenv\Dotenv;
use Slim\Container;
use Pimple\ServiceProviderInterface;
use Slim\App;

class ShellBootstrap
{
    /**
     * @var RouteLoader
     */
    private $routeLoader;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var array<string>
     */
    private $middlewares = [];

    public function __construct(
        ?string $environmentPath = null
    )
    {
        if ($environmentPath) {
            Dotenv::createImmutable($environmentPath)->safeLoad();
        }

        if (isset($_SERVER['REAL_FILE_PATH'])) {
            $_SERVER['SCRIPT_NAME'] = $_SERVER['REAL_FILE_PATH'];
        }

        $this->container = new Container();
    }

    /**
     * @param array<string, mixed> $definitions
     */
    public function registerDefinitions(array $definitions): self
    {
        foreach ($definitions as $key => $value) {
            $this->container[$key] = $value;
        }

        return $this;
    }

    public function registerServiceProviders(array $serviceProviders): self
    {
        foreach ($serviceProviders as $serviceProvider) {
            if (!$serviceProvider instanceof ServiceProviderInterface) {
                continue;
            }
            $this->container->register($serviceProvider);
        }
        return $this;
    }

    /**
     * @param array<string> $middlewares
     * @return self
     */
    public function registerMiddlewares(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function registerRoutes(
        ?string $auth = null,
        ?string $api = null,
        ?string $web = null
    ): self
    {
        return $this->registerRouteFiles([
            $auth ?? '',
            $api ?? '',
            $web ?? '',
        ]);
    }

    /**
     * @param string[] $routes
     * @return self
     */
    public function registerRouteFiles(array $routes): self
    {
        $this->routeLoader = new RouteLoader($routes);
        return $this;
    }

    public function create(): App
    {
        $app = new App($this->container);
        foreach ($this->middlewares as $middlewareName) {
            if (is_string($middlewareName) && $this->container->has($middlewareName)) {
                $app->add($this->container->get($middlewareName));
            }
        }
        if ($this->routeLoader instanceof RouteLoader) {
            $this->routeLoader->loadInto($app);
        }
        return $app;
    }
}
