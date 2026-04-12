<?php

declare(strict_types=1);

namespace Libra\Shell\Bootstrap;

use Slim\App;

class RouteLoader
{
    /**
     * @var string[]
     */
    private $routes;

    /**
     * @param string[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function loadInto(App $app): void
    {
        foreach ($this->routes as $route) {
            if ($this->isValid($route)) {
                (require $route)($app);
            }
        }
    }

    private function isValid(string $path): bool
    {
        return trim($path) !== '' && file_exists($path);
    }
}
