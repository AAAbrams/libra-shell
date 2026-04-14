<?php

declare(strict_types=1);

namespace Libra\Shell\ServiceProvider;

use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Cherif\InertiaPsr15\Service\InertiaFactory;
use Cherif\InertiaPsr15\Service\InertiaFactoryInterface;
use Cherif\InertiaPsr15\Service\RootViewProviderInterface;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Libra\Shell\Service\SsrGateway;
use Libra\Shell\Service\SsrRootViewProvider;
use Libra\Shell\Service\ViteService;
use Libra\Shell\TwigExtension\InertiaTwigExtension;
use Libra\Shell\TwigExtension\ViteExtension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\Views\Twig;

class InertiaServiceProvider implements ServiceProviderInterface
{
    private const DEFAULT_BUILD_DIR = 'public/build';
    private const DEFAULT_DEV_SERVER = 'http://node:5173';
    public const ROOT_VIEW_KEY = 'libra.shell.inertia.root_view';
    public const VIEW_PATHS_KEY = 'libra.shell.inertia.view_paths';

    /**
     * @var array<string, mixed>
     */
    private $defaults;

    /**
     * @param array<string, mixed> $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Container $pimple)
    {
        $this->registerDefaults($pimple);

        $pimple[InertiaMiddleware::class] = function (Container $c) {
            return new InertiaMiddleware($c[InertiaFactoryInterface::class]);
        };
        $pimple[InertiaFactoryInterface::class] = function (Container $c) {
            return new InertiaFactory(
                new ResponseFactory(),
                new StreamFactory(),
                $c[RootViewProviderInterface::class]
            );
        };
        $pimple[RootViewProviderInterface::class] = function (Container $c) {
            $twig = $c[Twig::class];
            return new SsrRootViewProvider(
                [$twig->getEnvironment(), 'render'],
                $this->resolveRootView($c),
                $c[SsrGateway::class]
            );
        };
        $pimple[SsrGateway::class] = function (Container $c) {
            $endpoint = getenv('INERTIA_SSR_URL') ?: 'http://node:13714/render';
            $enabled = filter_var(
                getenv('INERTIA_SSR_ENABLED') ?: false,
                FILTER_VALIDATE_BOOLEAN
            );
            $timeout = (float) (getenv('INERTIA_SSR_TIMEOUT') ?: 2);

            return new SsrGateway($endpoint, $enabled, $timeout);
        };
        $pimple[ViteService::class] = function (Container $c) {
            $moduleRoot = dirname(__DIR__, 2);
            $projectRoot = $this->resolveProjectRoot($moduleRoot);
            $configuredBuildDir = getenv('VITE_OUT_DIR') ?: self::DEFAULT_BUILD_DIR;
            $buildDir = $this->resolveBuildDir(
                $moduleRoot,
                $projectRoot,
                $configuredBuildDir
            );
            $publicBuildPath = getenv('VITE_PUBLIC_BASE')
                ?: $this->resolvePublicBuildPath($configuredBuildDir);
            $devServer = getenv('VITE_DEV_SERVER') ?: self::DEFAULT_DEV_SERVER;
            $isDev = $this->resolveDevMode();

            return new ViteService($buildDir, $publicBuildPath, $devServer, $isDev);
        };
        $pimple[Twig::class] = function (Container $c) {
            $twig = new Twig(
                $this->resolveViewPaths($c),
                ['cache' => false]
            );
            $twig->addExtension(new InertiaTwigExtension());
            $twig->addExtension(new ViteExtension($c[ViteService::class]));
            return $twig;
        };
        $pimple['view'] = function (Container $c) {
            return $c[Twig::class];
        };
    }

    private function registerDefaults(Container $pimple): void
    {
        if (!$pimple->offsetExists(self::ROOT_VIEW_KEY)) {
            $pimple[self::ROOT_VIEW_KEY] = $this->defaults['rootView'] ?? 'app.twig';
        }

        if (!$pimple->offsetExists(self::VIEW_PATHS_KEY)) {
            $pimple[self::VIEW_PATHS_KEY] = $this->defaults['viewPaths']
                ?? [dirname(__DIR__, 2) . '/resources/views'];
        }
    }

    private function resolveRootView(Container $container): string
    {
        $rootView = trim((string) $container[self::ROOT_VIEW_KEY]);

        return $rootView !== '' ? $rootView : 'app.twig';
    }

    /**
     * @return string[]
     */
    private function resolveViewPaths(Container $container): array
    {
        $viewPaths = $container[self::VIEW_PATHS_KEY];

        if (!is_array($viewPaths)) {
            return [dirname(__DIR__, 2) . '/resources/views'];
        }

        $normalized = array_values(array_filter(array_map(
            function ($path): string {
                return is_string($path) ? trim($path) : '';
            },
            $viewPaths
        )));

        if ($normalized === []) {
            return [dirname(__DIR__, 2) . '/resources/views'];
        }

        return $normalized;
    }

    private function resolveBuildDir(
        string $moduleRoot,
        string $projectRoot,
        string $buildDir
    ): string
    {
        if ($buildDir === '') {
            $buildDir = self::DEFAULT_BUILD_DIR;
        }

        if ($buildDir[0] === '/') {
            return rtrim($buildDir, '/');
        }

        if (
            strpos($buildDir, './') === 0
            || strpos($buildDir, '../') === 0
            || strpos($buildDir, 'bootstrap/') === 0
        ) {
            return rtrim($moduleRoot . '/' . ltrim($buildDir, '/'), '/');
        }

        return rtrim($projectRoot . '/' . ltrim($buildDir, '/'), '/');
    }

    private function resolveProjectRoot(string $moduleRoot): string
    {
        if (strpos($moduleRoot, '/vendor/') !== false) {
            return dirname($moduleRoot, 3);
        }

        return $moduleRoot;
    }

    private function resolvePublicBuildPath(string $buildDir): string
    {
        $normalizedBuildDir = trim($buildDir, '/');

        if (strpos($normalizedBuildDir, 'public/') === 0) {
            return '/' . substr($normalizedBuildDir, strlen('public/'));
        }

        return '/' . $normalizedBuildDir;
    }

    private function resolveDevMode(): bool
    {
        $configuredMode = getenv('VITE_DEV_MODE');

        if ($configuredMode !== false && $configuredMode !== '') {
            return filter_var($configuredMode, FILTER_VALIDATE_BOOLEAN);
        }

        $appEnv = getenv('APP_ENV') ?: 'prod';

        return in_array(strtolower($appEnv), ['dev', 'development', 'local'], true);
    }
}
