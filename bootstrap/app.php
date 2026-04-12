<?php

declare(strict_types=1);

use Bitrix\Main\Loader;
use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Libra\Shell\Bootstrap\ShellBootstrap;
use Libra\Shell\ServiceProvider\InertiaServiceProvider;

const STOP_STATISTICS = true;
const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const DisableEventsCheck = true;
const BX_SECURITY_SHOW_MESSAGE = true;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

return (new ShellBootstrap(__DIR__ . '/libra.shell'))
    ->registerServiceProviders([
        new InertiaServiceProvider(),
    ])
    ->registerMiddlewares([
        InertiaMiddleware::class,
    ])
    ->registerRoutes(
        __DIR__ . '/../routes/auth.php',
        null,
        __DIR__ . '/../routes/web.php'
    )->create();